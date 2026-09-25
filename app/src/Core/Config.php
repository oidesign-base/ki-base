<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Configuration: config.php (defaults, in git) merged with
 * config.local.php (environment + secrets, not in git).
 */
final class Config
{
    private static array $items = [];
    private static bool $hasLocal = false;

    public static function load(string $dir): void
    {
        $items = require $dir . '/config.php';

        $localFile = $dir . '/config.local.php';
        if (is_file($localFile)) {
            $local = require $localFile;
            if (is_array($local)) {
                $items = array_replace_recursive($items, $local);
                self::$hasLocal = true;
            }
        }

        self::$items = $items;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    /** True when config.local.php exists and the database is configured. */
    public static function isConfigured(): bool
    {
        return self::$hasLocal && self::get('db.name') !== '' && self::get('db.user') !== '';
    }
}
