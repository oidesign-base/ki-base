<?php

declare(strict_types=1);

namespace App\Core;

/** One CSRF token per session; every POST must carry it. */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function verify(?string $token): void
    {
        $expected = Session::get('_csrf');
        if (!is_string($expected) || !is_string($token) || !hash_equals($expected, $token)) {
            throw new HttpException(419);
        }
    }
}
