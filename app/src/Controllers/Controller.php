<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;

/** Shared helpers for controllers. */
abstract class Controller
{
    /**
     * Redirect back to a form after failed validation: keeps the submitted
     * values and shows the error under each field.
     */
    protected function backWithErrors(string $url, array $errors, array $input): Response
    {
        Session::flashInput($input);
        Session::flashErrors($errors);
        Session::flash('danger', t('form.check_fields'));
        return Response::redirect($url);
    }

    /**
     * Fields that differ between two records, for the activity log:
     * ['name' => ['old', 'new'], ...]. Values are compared as strings.
     */
    protected function changes(array $old, array $new, array $fields): array
    {
        $diff = [];
        foreach ($fields as $field) {
            $before = $old[$field] ?? null;
            $after  = $new[$field] ?? null;
            if ((string) $before !== (string) $after) {
                $diff[$field] = [$before, $after];
            }
        }
        return $diff;
    }
}
