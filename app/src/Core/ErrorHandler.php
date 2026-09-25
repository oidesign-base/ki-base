<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Turns PHP errors into exceptions, logs everything unexpected and shows
 * a clean error page. Details are shown in the browser only with app.debug.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            if (in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED], true)) {
                // Deprecations must not break the panel after a PHP upgrade: log only.
                Logger::info('Deprecated: ' . $message, ['file' => $file . ':' . $line]);
                return true;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler([self::class, 'handle']);

        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    public static function handle(\Throwable $e): void
    {
        $status = $e instanceof HttpException ? $e->status() : 500;

        if ($status >= 500) {
            Logger::error(get_class($e) . ': ' . $e->getMessage(), [
                'file' => $e->getFile() . ':' . $e->getLine(),
                'uri'  => $_SERVER['REQUEST_URI'] ?? 'cli',
            ]);
        }

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: text/html; charset=utf-8');
        }

        try {
            echo View::renderError($status, (bool) config('app.debug') ? $e : null);
        } catch (\Throwable) {
            // The error page itself failed (e.g. broken template): plain fallback.
            echo '<!doctype html><meta charset="utf-8"><title>' . $status . '</title><p>' . $status . '</p>';
        }
    }
}
