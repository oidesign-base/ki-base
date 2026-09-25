<?php

declare(strict_types=1);

namespace App\Core;

/** Web entry point: HTTPS, session, security headers, routing. */
final class App
{
    public static function run(): void
    {
        $request = Request::capture();
        $secure  = $request->isSecure();

        if (config('security.force_https') && !$secure) {
            Response::redirect('https://' . $request->host() . $request->uri(), 301)->send();
            return;
        }

        self::sendSecurityHeaders($secure);

        if (!Config::isConfigured()) {
            throw new HttpException(503);
        }

        Session::start($secure);
        Session::ageFlashInput();

        $router = new Router();
        require APP_ROOT . '/routes.php';

        $router->dispatch($request)->send();
    }

    private static function sendSecurityHeaders(bool $secure): void
    {
        header_remove('X-Powered-By');
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: same-origin');
        header('X-Robots-Tag: noindex, nofollow');
        header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');
        header('Cache-Control: no-store, private');
        if ($secure) {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }
}
