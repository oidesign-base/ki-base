<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session with its own storage directory: shared hosting cleans the default
 * session directory after ~24 minutes, which would log users out.
 */
final class Session
{
    public static function start(bool $secure): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $lifetime = (int) config('security.session_lifetime', 604800);

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_divisor', '100');

        session_save_path(Storage::path('sessions'));
        session_name((string) config('security.session_name', 'kibase_sid'));
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** New session id, keeps data. Call on login to prevent session fixation. */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }

    /** One-time message shown on the next page (type: success|danger|warning|info). */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $messages;
    }

    /** Remember submitted form values for one request (to refill the form). */
    public static function flashInput(array $input): void
    {
        unset($input['_token'], $input['password'], $input['password_confirm']);
        foreach ($input as $key => $value) {
            if (str_starts_with((string) $key, 'password')) {
                unset($input[$key]);
            }
        }
        $_SESSION['_old_input_next'] = $input;
    }

    /** Called once per request: moves "next" old input into place. */
    public static function ageFlashInput(): void
    {
        $_SESSION['_old_input'] = $_SESSION['_old_input_next'] ?? [];
        unset($_SESSION['_old_input_next']);
    }
}
