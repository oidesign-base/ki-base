<?php

declare(strict_types=1);

namespace App\Core;

/** UI strings. The interface is Ukrainian: app/lang/uk.php. */
final class Lang
{
    private static ?array $strings = null;

    public static function get(string $key, array $params = []): string
    {
        if (self::$strings === null) {
            $file = APP_ROOT . '/lang/' . config('app.locale', 'uk') . '.php';
            self::$strings = is_file($file) ? require $file : [];
        }

        $text = self::$strings[$key] ?? $key;
        foreach ($params as $name => $value) {
            $text = str_replace('{' . $name . '}', (string) $value, $text);
        }
        return $text;
    }
}
