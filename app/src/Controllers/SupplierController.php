<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLog;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validate;
use App\Core\View;

/**
 * Settings → Suppliers of parcel batches. Like categories, suppliers are
 * switched off rather than deleted: batches keep referring to them.
 */
final class SupplierController extends Controller
{
    private const BASE = '/settings/suppliers';

    private const FIELDS = ['name', 'tax_id', 'phone', 'email', 'street', 'postal_code', 'city', 'country_code'];

    public function index(Request $request): string
    {
        $suppliers = Database::fetchAll(
            'SELECT s.id, s.name, s.tax_id, s.phone, s.email, s.is_active,
                    (SELECT COUNT(*) FROM batches b WHERE b.supplier_id = s.id AND b.deleted_at IS NULL) AS batches_count
               FROM suppliers s
              WHERE s.deleted_at IS NULL
              ORDER BY s.is_active DESC, s.name'
        );

        return View::render('settings/suppliers/index', [
            'title'      => t('menu.suppliers'),
            'activePath' => self::BASE,
            'suppliers'  => $suppliers,
            'scripts'    => ['datatables'],
        ]);
    }

    public function create(Request $request): string
    {
        return $this->form(null);
    }

    public function edit(Request $request, int $id): string
    {
        return $this->form($this->find($id));
    }

    public function store(Request $request): Response
    {
        [$data, $errors] = $this->validated($request, null);
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/create'), $errors, $request->all());
        }

        Database::query(
            'INSERT INTO suppliers (name, tax_id, phone, email, street, postal_code, city, country_code)
             VALUES (:name, :tax_id, :phone, :email, :street, :postal_code, :city, :country_code)',
            $data
        );
        $id = Database::lastInsertId();
        ActivityLog::log('create', 'supplier', $id, $this->changes([], $data, self::FIELDS));

        Session::flash('success', t('suppliers.created', ['name' => $data['name']]));
        return Response::redirect(url(self::BASE));
    }

    public function update(Request $request, int $id): Response
    {
        $supplier = $this->find($id);
        [$data, $errors] = $this->validated($request, $id);
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/' . $id . '/edit'), $errors, $request->all());
        }

        $changes = $this->changes($supplier, $data, self::FIELDS);
        if ($changes !== []) {
            Database::query(
                'UPDATE suppliers
                    SET name = :name, tax_id = :tax_id, phone = :phone, email = :email,
                        street = :street, postal_code = :postal_code, city = :city, country_code = :country_code
                  WHERE id = :id',
                $data + ['id' => $id]
            );
            ActivityLog::log('update', 'supplier', $id, $changes);
        }

        Session::flash('success', t('suppliers.updated', ['name' => $data['name']]));
        return Response::redirect(url(self::BASE));
    }

    public function toggle(Request $request, int $id): Response
    {
        $supplier = $this->find($id);
        $active   = (int) $supplier['is_active'] === 1 ? 0 : 1;

        Database::query('UPDATE suppliers SET is_active = ? WHERE id = ?', [$active, $id]);
        ActivityLog::log('update', 'supplier', $id, ['is_active' => [(int) $supplier['is_active'], $active]]);

        Session::flash('success', t($active === 1 ? 'suppliers.enabled' : 'suppliers.disabled', ['name' => $supplier['name']]));
        return Response::redirect(url(self::BASE));
    }

    private function form(?array $supplier): string
    {
        $isNew = $supplier === null;
        return View::render('settings/suppliers/form', [
            'title'       => $isNew ? t('suppliers.new') : t('suppliers.edit'),
            'activePath'  => self::BASE,
            'breadcrumbs' => [[t('menu.suppliers'), url(self::BASE)]],
            'supplier'    => $supplier,
            'countries'   => require APP_ROOT . '/config/countries.php',
            'action'      => url($isNew ? self::BASE : self::BASE . '/' . $supplier['id']),
            'backUrl'     => url(self::BASE),
        ]);
    }

    private function find(int $id): array
    {
        $supplier = Database::fetch(
            'SELECT id, name, tax_id, phone, email, street, postal_code, city, country_code, is_active
               FROM suppliers WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
        if ($supplier === null) {
            throw new HttpException(404);
        }
        return $supplier;
    }

    /** @return array{0: array, 1: array} [data, errors] */
    private function validated(Request $request, ?int $ignoreId): array
    {
        $clean = static fn (string $v): string => preg_replace('/\s+/u', ' ', $v) ?? '';

        $name       = $clean($request->input('name'));
        $taxId      = $request->input('tax_id');
        $phone      = $clean($request->input('phone'));
        $email      = $request->input('email');
        $street     = $clean($request->input('street'));
        $postalCode = strtoupper($clean($request->input('postal_code')));
        $city       = $clean($request->input('city'));
        $country    = strtoupper($request->input('country_code', 'PL'));
        $countries  = require APP_ROOT . '/config/countries.php';
        $errors     = [];

        if ($name === '') {
            $errors['name'] = t('validation.required');
        } elseif (!Validate::maxLength($name, 150)) {
            $errors['name'] = t('validation.max_length', ['max' => 150]);
        } elseif (Database::value(
            'SELECT id FROM suppliers WHERE name = ? AND deleted_at IS NULL AND id <> ?',
            [$name, $ignoreId ?? 0]
        ) !== null) {
            $errors['name'] = t('suppliers.duplicate');
        }

        $normalizedNip = null;
        if ($taxId !== '') {
            $normalizedNip = Validate::nip($taxId);
            if ($normalizedNip === null) {
                $errors['tax_id'] = t('suppliers.invalid_nip');
            }
        }

        if ($phone !== '' && !preg_match('/^\+?[0-9 ()\-]{5,30}$/', $phone)) {
            $errors['phone'] = t('suppliers.invalid_phone');
        }

        if ($email !== '' && (filter_var($email, FILTER_VALIDATE_EMAIL) === false || !Validate::maxLength($email, 150))) {
            $errors['email'] = t('suppliers.invalid_email');
        }

        if (!Validate::maxLength($street, 200)) {
            $errors['street'] = t('validation.max_length', ['max' => 200]);
        }

        if (!isset($countries[$country])) {
            $errors['country_code'] = t('suppliers.invalid_country');
        }

        if ($postalCode !== '') {
            if ($country === 'PL' && !preg_match('/^\d{2}-\d{3}$/', $postalCode)) {
                $errors['postal_code'] = t('suppliers.invalid_postal_code_pl');
            } elseif (!preg_match('/^[A-Z0-9 \-]{2,12}$/', $postalCode)) {
                $errors['postal_code'] = t('suppliers.invalid_postal_code');
            }
        }

        if (!Validate::maxLength($city, 100)) {
            $errors['city'] = t('validation.max_length', ['max' => 100]);
        }

        $orNull = static fn (string $v): ?string => $v !== '' ? $v : null;

        return [[
            'name'         => $name,
            'tax_id'       => $normalizedNip,
            'phone'        => $orNull($phone),
            'email'        => $orNull(mb_strtolower($email)),
            'street'       => $orNull($street),
            'postal_code'  => $orNull($postalCode),
            'city'         => $orNull($city),
            'country_code' => $country,
        ], $errors];
    }
}
