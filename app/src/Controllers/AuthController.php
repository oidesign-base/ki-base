<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

final class AuthController
{
    public function showLogin(Request $request): Response|string
    {
        // No users yet: the first visit goes to the setup page.
        if ((int) Database::value('SELECT COUNT(*) FROM users') === 0) {
            return Response::redirect(url('/setup'));
        }

        return View::render('auth/login', ['title' => t('auth.login.title')], 'auth');
    }

    public function login(Request $request): Response
    {
        $username = $request->input('username');
        $password = $request->raw('password');

        if ($username === '' || $password === '') {
            Session::flashInput(['username' => $username]);
            Session::flash('danger', t('auth.login.required'));
            return Response::redirect(url('/login'));
        }

        [$status, $minutes] = Auth::attempt($username, $password, $request->ip());

        if ($status === Auth::LOCKED) {
            Session::flashInput(['username' => $username]);
            Session::flash('danger', t('auth.login.locked', ['minutes' => $minutes]));
            return Response::redirect(url('/login'));
        }

        if ($status === Auth::INVALID) {
            Session::flashInput(['username' => $username]);
            Session::flash('danger', t('auth.login.invalid'));
            return Response::redirect(url('/login'));
        }

        $intended = Session::get('_intended');
        Session::forget('_intended');

        // Only local paths: never redirect to another site.
        $target = is_string($intended) && preg_match('#^/(?!/)#', $intended) ? $intended : url('/');

        return Response::redirect($target);
    }

    public function logout(Request $request): Response
    {
        Auth::logout();
        // New empty session for the flash message.
        Session::start($request->isSecure());
        Session::flash('success', t('auth.logout.done'));
        return Response::redirect(url('/login'));
    }
}
