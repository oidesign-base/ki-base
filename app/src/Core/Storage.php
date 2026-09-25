<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Writable storage outside the web root: logs, sessions, uploads, cache, backups.
 * Directories are created on first run, because deploy never uploads app/storage.
 */
final class Storage
{
    private const DIRECTORIES = ['logs', 'sessions', 'uploads', 'cache', 'backups'];

    public static function path(string $sub = ''): string
    {
        return APP_ROOT . '/storage' . ($sub !== '' ? '/' . ltrim($sub, '/') : '');
    }

    public static function ensureDirectories(): void
    {
        foreach (self::DIRECTORIES as $dir) {
            $path = self::path($dir);
            if (!is_dir($path)) {
                @mkdir($path, 0750, true);
            }
        }
    }
}
