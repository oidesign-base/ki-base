<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Login / logout for the two panel users, with brute-force protection
 * based on the login_attempts table.
 */
final class Auth
{
    public const OK      = 'ok';
    public const INVALID = 'invalid';
    public const LOCKED  = 'locked';

    /** Valid bcrypt hash (cost 10) of a random string; used only to equalise timing. */
    private const DUMMY_HASH = '$2y$10$G/b9mdiDYW4jWoRlJiA0muqBjqPxMxdBLEexoZuOQ9kOePcsYgkaW';

    private static ?array $user = null;
    private static bool $resolved = false;

    /** Current user row or null. The user is re-checked in the database on every request. */
    public static function user(): ?array
    {
        if (self::$resolved) {
            return self::$user;
        }
        self::$resolved = true;

        $id = Session::get('user_id');
        if (!is_int($id)) {
            return null;
        }

        $user = Database::fetch(
            'SELECT id, username, display_name, last_login_at
               FROM users
              WHERE id = ? AND is_active = 1 AND deleted_at IS NULL',
            [$id]
        );

        if ($user === null) {
            // Account disabled or removed while logged in.
            Session::forget('user_id');
            return null;
        }

        self::$user = $user;
        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user === null ? null : (int) $user['id'];
    }

    /**
     * Try to log in. Returns [status, minutesLeft]:
     *   [Auth::OK, 0] | [Auth::INVALID, 0] | [Auth::LOCKED, minutes]
     */
    public static function attempt(string $username, string $password, string $ip): array
    {
        $minutesLeft = self::lockoutMinutesLeft($username, $ip);
        if ($minutesLeft > 0) {
            return [self::LOCKED, $minutesLeft];
        }

        $user = Database::fetch(
            'SELECT id, password_hash FROM users
              WHERE username = ? AND is_active = 1 AND deleted_at IS NULL',
            [$username]
        );

        // Verify against a dummy hash when the user does not exist, so the
        // response time does not reveal which usernames are valid.
        $hash  = $user['password_hash'] ?? self::DUMMY_HASH;
        $valid = password_verify($password, $hash) && $user !== null;

        Database::query(
            'INSERT INTO login_attempts (username, ip, success) VALUES (?, ?, ?)',
            [mb_substr($username, 0, 50), $ip, $valid ? 1 : 0]
        );

        if (!$valid) {
            ActivityLog::log('login_failed', null, null, ['username' => mb_substr($username, 0, 50)], null);
            return [self::INVALID, 0];
        }

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            Database::query('UPDATE users SET password_hash = ? WHERE id = ?', [
                password_hash($password, PASSWORD_DEFAULT),
                $user['id'],
            ]);
        }

        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('login_at', time());
        Database::query('UPDATE users SET last_login_at = UTC_TIMESTAMP() WHERE id = ?', [$user['id']]);

        self::$resolved = false;
        ActivityLog::log('login', 'user', (int) $user['id']);

        return [self::OK, 0];
    }

    public static function logout(): void
    {
        $id = self::id();
        if ($id !== null) {
            ActivityLog::log('logout', 'user', $id);
        }
        Session::destroy();
        self::$user = null;
        self::$resolved = true;
    }

    /**
     * Minutes until login is allowed again (0 = not locked).
     * Counts failures for this username OR this IP within the lockout window,
     * ignoring failures that happened before the last successful login
     * (compared by id: timestamps have only one-second precision).
     */
    public static function lockoutMinutesLeft(string $username, string $ip): int
    {
        $max    = (int) config('security.login_max_attempts', 5);
        $window = (int) config('security.login_lockout_minutes', 15);

        $row = Database::fetch(
            'SELECT COUNT(*) AS failures, MAX(attempted_at) AS last_failure
               FROM login_attempts
              WHERE success = 0
                AND (username = :u OR ip = :ip)
                AND attempted_at > UTC_TIMESTAMP() - INTERVAL :w MINUTE
                AND id > COALESCE(
                      (SELECT MAX(id) FROM login_attempts
                        WHERE success = 1 AND (username = :u2 OR ip = :ip2)),
                      0)',
            ['u' => $username, 'ip' => $ip, 'w' => $window, 'u2' => $username, 'ip2' => $ip]
        );

        if ($row === null || (int) $row['failures'] < $max || $row['last_failure'] === null) {
            return 0;
        }

        $lastFailure = new \DateTimeImmutable((string) $row['last_failure'], new \DateTimeZone('UTC'));
        $unlockAt    = $lastFailure->getTimestamp() + $window * 60;
        $secondsLeft = $unlockAt - time();

        return $secondsLeft > 0 ? (int) ceil($secondsLeft / 60) : 0;
    }
}
