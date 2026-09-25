<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Plain-PHP templates in app/views. A page template is rendered first,
 * then wrapped in a layout (app/views/layouts/*.php) that receives $content.
 */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'main'): string
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture('layouts/' . $layout, $data + ['content' => $content]);
    }

    /** Render a partial from within a template. */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    private static function capture(string $__template, array $__data): string
    {
        $__file = APP_ROOT . '/views/' . $__template . '.php';
        if (!is_file($__file)) {
            throw new \RuntimeException('View not found: ' . $__template);
        }
        extract($__data, EXTR_SKIP);
        ob_start();
        try {
            include $__file;
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Error page. Uses the full panel layout for logged-in users and a bare
     * layout otherwise (also when the database is unavailable).
     */
    public static function renderError(int $status, ?\Throwable $debug = null): string
    {
        $known = [403, 404, 405, 419, 500, 503];
        $code  = in_array($status, $known, true) ? $status : 500;

        $layout = 'error';
        try {
            if (Config::isConfigured() && Auth::check()) {
                $layout = 'main';
            }
        } catch (\Throwable) {
            $layout = 'error';
        }

        return self::render('errors/error', [
            'status' => $code,
            'title'  => t('error.' . $code . '.title'),
            'debug'  => $debug,
        ], $layout);
    }
}
