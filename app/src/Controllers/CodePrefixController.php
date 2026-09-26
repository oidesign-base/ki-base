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
 * Labels → Code prefixes. A label code is prefix + 6 digits + check digit;
 * numbering continues separately for each prefix. Prefixes are switched off
 * rather than deleted, and the letters are locked once codes exist.
 * Exactly one active prefix can be the default (preselected for new batches
 * and new label sheets).
 */
final class CodePrefixController extends Controller
{
    private const BASE = '/labels/prefixes';

    /** Reserved for test prints (made outside the panel); never issued. */
    public const RESERVED = ['ZZ'];

    public function index(Request $request): string
    {
        $prefixes = Database::fetchAll(
            'SELECT p.id, p.prefix, p.description, p.is_default, p.is_active,
                    (SELECT COUNT(*) FROM code_pool cp JOIN code_sheets cs ON cs.id = cp.sheet_id
                      WHERE cs.code_prefix_id = p.id) AS codes_count
               FROM code_prefixes p
              ORDER BY p.is_active DESC, p.prefix'
        );

        return View::render('labels/prefixes/index', [
            'title'      => t('menu.code_prefixes'),
            'activePath' => self::BASE,
            'prefixes'   => $prefixes,
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

        // The first prefix becomes the default automatically.
        $hasDefault = Database::value('SELECT id FROM code_prefixes WHERE is_default = 1') !== null;
        $data['is_default'] = ($data['is_default'] === 1 || !$hasDefault) ? 1 : 0;

        $id = Database::transaction(function () use ($data): int {
            if ($data['is_default'] === 1) {
                Database::query('UPDATE code_prefixes SET is_default = 0 WHERE is_default = 1');
            }
            Database::query(
                'INSERT INTO code_prefixes (prefix, description, is_default) VALUES (?, ?, ?)',
                [$data['prefix'], $data['description'], $data['is_default']]
            );
            return Database::lastInsertId();
        });
        ActivityLog::log('create', 'code_prefix', $id, $this->changes([], $data, ['prefix', 'description', 'is_default']));

        Session::flash('success', t('prefixes.created', ['prefix' => $data['prefix']]));
        return Response::redirect(url(self::BASE));
    }

    public function update(Request $request, int $id): Response
    {
        $prefix = $this->find($id);
        [$data, $errors] = $this->validated($request, $prefix);
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/' . $id . '/edit'), $errors, $request->all());
        }

        $changes = $this->changes($prefix, $data, ['prefix', 'description', 'is_default']);
        if ($changes !== []) {
            Database::transaction(function () use ($data, $id): void {
                if ($data['is_default'] === 1) {
                    Database::query('UPDATE code_prefixes SET is_default = 0 WHERE is_default = 1 AND id <> ?', [$id]);
                }
                Database::query(
                    'UPDATE code_prefixes SET prefix = ?, description = ?, is_default = ? WHERE id = ?',
                    [$data['prefix'], $data['description'], $data['is_default'], $id]
                );
            });
            ActivityLog::log('update', 'code_prefix', $id, $changes);
        }

        Session::flash('success', t('prefixes.updated', ['prefix' => $data['prefix']]));
        return Response::redirect(url(self::BASE));
    }

    public function toggle(Request $request, int $id): Response
    {
        $prefix = $this->find($id);
        if ((int) $prefix['is_default'] === 1) {
            Session::flash('danger', t('prefixes.cannot_disable_default'));
            return Response::redirect(url(self::BASE));
        }

        $active = (int) $prefix['is_active'] === 1 ? 0 : 1;
        Database::query('UPDATE code_prefixes SET is_active = ? WHERE id = ?', [$active, $id]);
        ActivityLog::log('update', 'code_prefix', $id, ['is_active' => [(int) $prefix['is_active'], $active]]);

        Session::flash('success', t($active === 1 ? 'prefixes.enabled' : 'prefixes.disabled', ['prefix' => $prefix['prefix']]));
        return Response::redirect(url(self::BASE));
    }

    private function form(?array $prefix): string
    {
        $isNew = $prefix === null;
        return View::render('labels/prefixes/form', [
            'title'       => $isNew ? t('prefixes.new') : t('prefixes.edit'),
            'activePath'  => self::BASE,
            'breadcrumbs' => [[t('menu.code_prefixes'), url(self::BASE)]],
            'prefix'      => $prefix,
            'locked'      => !$isNew && $this->hasCodes((int) $prefix['id']),
            'action'      => url($isNew ? self::BASE : self::BASE . '/' . $prefix['id']),
            'backUrl'     => url(self::BASE),
        ]);
    }

    private function find(int $id): array
    {
        $prefix = Database::fetch(
            'SELECT id, prefix, description, is_default, is_active FROM code_prefixes WHERE id = ?',
            [$id]
        );
        if ($prefix === null) {
            throw new HttpException(404);
        }
        return $prefix;
    }

    private function hasCodes(int $id): bool
    {
        return Database::value(
            'SELECT 1 FROM code_sheets WHERE code_prefix_id = ? LIMIT 1',
            [$id]
        ) !== null;
    }

    /** @return array{0: array, 1: array} [data, errors] */
    private function validated(Request $request, ?array $current): array
    {
        $letters     = strtoupper(trim($request->input('prefix')));
        $description = preg_replace('/\s+/u', ' ', trim($request->input('description'))) ?? '';
        $isDefault   = $request->input('is_default') === '1' ? 1 : 0;
        $errors      = [];

        // Letters are locked once codes with this prefix exist.
        if ($current !== null && $this->hasCodes((int) $current['id'])) {
            if ($letters !== '' && $letters !== $current['prefix']) {
                $errors['prefix'] = t('prefixes.locked');
            }
            $letters = $current['prefix'];
        }

        if ($letters === '') {
            $errors['prefix'] = t('validation.required');
        } elseif (!preg_match('/^[A-Z]{2}$/', $letters)) {
            $errors['prefix'] = t('prefixes.invalid');
        } elseif (in_array($letters, self::RESERVED, true)) {
            $errors['prefix'] = t('prefixes.reserved', ['prefix' => $letters]);
        } elseif (Database::value(
            'SELECT id FROM code_prefixes WHERE prefix = ? AND id <> ?',
            [$letters, $current['id'] ?? 0]
        ) !== null) {
            $errors['prefix'] = t('prefixes.duplicate');
        }

        if ($description === '') {
            $errors['description'] = t('validation.required');
        } elseif (!Validate::maxLength($description, 200)) {
            $errors['description'] = t('validation.max_length', ['max' => 200]);
        }

        if ($current !== null) {
            // The default can only be moved by marking another prefix as default.
            if ((int) $current['is_default'] === 1) {
                $isDefault = 1;
            } elseif ($isDefault === 1 && (int) $current['is_active'] !== 1) {
                $errors['is_default'] = t('prefixes.default_inactive');
            }
        }

        return [[
            'prefix'      => $letters,
            'description' => $description,
            'is_default'  => $isDefault,
        ], $errors];
    }
}
