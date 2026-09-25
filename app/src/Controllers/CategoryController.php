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
 * Settings → Categories. Categories are never deleted (products refer to
 * them); they are switched off instead and then hidden from selection lists.
 */
final class CategoryController extends Controller
{
    private const BASE = '/settings/categories';

    public function index(Request $request): string
    {
        $categories = Database::fetchAll(
            'SELECT c.id, c.name, c.is_active,
                    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.deleted_at IS NULL) AS products_count
               FROM categories c
              ORDER BY c.is_active DESC, c.name'
        );

        return View::render('settings/categories/index', [
            'title'      => t('menu.categories'),
            'activePath' => self::BASE,
            'categories' => $categories,
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
        $data   = $this->input($request);
        $errors = $this->validate($data, null);
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/create'), $errors, $request->all());
        }

        Database::query('INSERT INTO categories (name) VALUES (?)', [$data['name']]);
        $id = Database::lastInsertId();
        ActivityLog::log('create', 'category', $id, ['name' => [null, $data['name']]]);

        Session::flash('success', t('categories.created', ['name' => $data['name']]));
        return Response::redirect(url(self::BASE));
    }

    public function update(Request $request, int $id): Response
    {
        $category = $this->find($id);
        $data     = $this->input($request);
        $errors   = $this->validate($data, $id);
        if ($errors !== []) {
            return $this->backWithErrors(url(self::BASE . '/' . $id . '/edit'), $errors, $request->all());
        }

        $changes = $this->changes($category, $data, ['name']);
        if ($changes !== []) {
            Database::query('UPDATE categories SET name = ? WHERE id = ?', [$data['name'], $id]);
            ActivityLog::log('update', 'category', $id, $changes);
        }

        Session::flash('success', t('categories.updated', ['name' => $data['name']]));
        return Response::redirect(url(self::BASE));
    }

    /** Switch a category on/off. */
    public function toggle(Request $request, int $id): Response
    {
        $category = $this->find($id);
        $active   = (int) $category['is_active'] === 1 ? 0 : 1;

        Database::query('UPDATE categories SET is_active = ? WHERE id = ?', [$active, $id]);
        ActivityLog::log('update', 'category', $id, ['is_active' => [(int) $category['is_active'], $active]]);

        Session::flash('success', t($active === 1 ? 'categories.enabled' : 'categories.disabled', ['name' => $category['name']]));
        return Response::redirect(url(self::BASE));
    }

    private function form(?array $category): string
    {
        $isNew = $category === null;
        return View::render('settings/categories/form', [
            'title'       => $isNew ? t('categories.new') : t('categories.edit'),
            'activePath'  => self::BASE,
            'breadcrumbs' => [[t('menu.categories'), url(self::BASE)]],
            'category'    => $category,
            'action'      => url($isNew ? self::BASE : self::BASE . '/' . $category['id']),
            'backUrl'     => url(self::BASE),
        ]);
    }

    private function find(int $id): array
    {
        $category = Database::fetch('SELECT id, name, is_active FROM categories WHERE id = ?', [$id]);
        if ($category === null) {
            throw new HttpException(404);
        }
        return $category;
    }

    private function input(Request $request): array
    {
        return ['name' => preg_replace('/\s+/u', ' ', $request->input('name')) ?? ''];
    }

    private function validate(array $data, ?int $ignoreId): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = t('validation.required');
        } elseif (!Validate::maxLength($data['name'], 100)) {
            $errors['name'] = t('validation.max_length', ['max' => 100]);
        } else {
            // The collation ignores case and accents, so "Odzież" = "odzież".
            $duplicate = Database::value(
                'SELECT id FROM categories WHERE name = ? AND parent_id IS NULL AND id <> ?',
                [$data['name'], $ignoreId ?? 0]
            );
            if ($duplicate !== null) {
                $errors['name'] = t('categories.duplicate');
            }
        }

        return $errors;
    }
}
