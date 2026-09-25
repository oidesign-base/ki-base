<?php
/**
 * Routes. $router is provided by App\Core\App::run().
 * All routes require login unless 'auth' => false.
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SectionController;
use App\Controllers\SetupController;

/** @var App\Core\Router $router */

// Authentication
$router->get('/login', [AuthController::class, 'showLogin'], ['auth' => false, 'guest' => true]);
$router->post('/login', [AuthController::class, 'login'], ['auth' => false, 'guest' => true]);
$router->post('/logout', [AuthController::class, 'logout']);

// First run: create the panel users (works only while the users table is empty)
$router->get('/setup', [SetupController::class, 'show'], ['auth' => false]);
$router->post('/setup', [SetupController::class, 'store'], ['auth' => false]);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Sections from the sidebar menu that are not built yet: placeholder pages.
foreach (require APP_ROOT . '/config/menu.php' as $group) {
    foreach ($group['items'] as $item) {
        if ($item['path'] === '/') {
            continue;
        }
        $label = $item['label'];
        $router->get($item['path'], static fn ($request) => (new SectionController())->show($request, $label));
    }
}
