<?php
/**
 * Application bootstrap: autoloading, configuration, error handling.
 * Included by public/index.php (web) and by cron scripts (CLI).
 */

declare(strict_types=1);

define('APP_ROOT', __DIR__);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = APP_ROOT . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_ROOT . '/src/helpers.php';

// Error handling and storage first: a broken config file must still
// produce a logged error and a proper error page.
App\Core\ErrorHandler::register();
App\Core\Storage::ensureDirectories();

App\Core\Config::load(APP_ROOT . '/config');

date_default_timezone_set((string) config('app.timezone', 'Europe/Warsaw'));
mb_internal_encoding('UTF-8');
