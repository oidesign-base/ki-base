<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ActivityLog;
use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

/**
 * First-run setup: creates the panel users. Available only while the
 * users table is empty; afterwards the page does not exist (404).
 */
final class SetupController
{
    private const MIN_PASSWORD_LENGTH = 10;

    public function show(Request $request): string
    {
        $this->guard();
        return View::render('setup/index', ['title' => t('setup.title')], 'auth');
    }

    public function store(Request $request): Response
    {
        $this->guard();

        $users  = [];
        $errors = [];

        foreach ([1, 2] as $n) {
            $username = $request->input("username_$n");
            $name     = $request->input("display_name_$n");
            $password = $request->raw("password_$n");
            $confirm  = $request->raw("password_confirm_$n");

            $filled = $username !== '' || $name !== '' || $password !== '';
            if ($n === 2 && !$filled) {
                continue; // the second user is optional
            }

            if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $username)) {
                $errors[] = t('setup.error.username', ['n' => $n]);
            }
            if ($name === '' || mb_strlen($name) > 100) {
                $errors[] = t('setup.error.display_name', ['n' => $n]);
            }
            if (mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = t('setup.error.password_short', ['n' => $n, 'min' => self::MIN_PASSWORD_LENGTH]);
            } elseif ($password !== $confirm) {
                $errors[] = t('setup.error.password_mismatch', ['n' => $n]);
            }

            $users[] = ['username' => $username, 'display_name' => $name, 'password' => $password];
        }

        if (count($users) === 2 && strcasecmp($users[0]['username'], $users[1]['username']) === 0) {
            $errors[] = t('setup.error.same_username');
        }

        if ($errors !== []) {
            Session::flashInput($request->all());
            foreach ($errors as $error) {
                Session::flash('danger', $error);
            }
            return Response::redirect(url('/setup'));
        }

        Database::transaction(static function () use ($users): void {
            // Re-check inside the transaction: two simultaneous submits must not both succeed.
            if ((int) Database::value('SELECT COUNT(*) FROM users FOR UPDATE') > 0) {
                throw new HttpException(404);
            }
            foreach ($users as $u) {
                Database::query(
                    'INSERT INTO users (username, display_name, password_hash) VALUES (?, ?, ?)',
                    [$u['username'], $u['display_name'], password_hash($u['password'], PASSWORD_DEFAULT)]
                );
                ActivityLog::log('create', 'user', Database::lastInsertId(), ['username' => $u['username']], null);
            }
        });

        Session::flash('success', t('setup.done'));
        return Response::redirect(url('/login'));
    }

    private function guard(): void
    {
        if ((int) Database::value('SELECT COUNT(*) FROM users') > 0) {
            throw new HttpException(404);
        }
    }
}
