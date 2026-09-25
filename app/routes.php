<?php
/**
 * Routes. $router is provided by App\Core\App::run().
 * All routes require login unless 'auth' => false.
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\DashboardController;
use App\Controllers\SectionController;
use App\Controllers\SetupController;
use App\Controllers\SupplierController;

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

// Settings: categories
$router->get('/settings/categories', [CategoryController::class, 'index']);
$router->get('/settings/categories/create', [CategoryController::class, 'create']);
$router->post('/settings/categories', [CategoryController::class, 'store']);
$router->get('/settings/categories/{id}/edit', [CategoryController::class, 'edit']);
$router->post('/settings/categories/{id}', [CategoryController::class, 'update']);
$router->post('/settings/categories/{id}/toggle', [CategoryController::class, 'toggle']);

// Settings: suppliers
$router->get('/settings/suppliers', [SupplierController::class, 'index']);
$router->get('/settings/suppliers/create', [SupplierController::class, 'create']);
$router->post('/settings/suppliers', [SupplierController::class, 'store']);
$router->get('/settings/suppliers/{id}/edit', [SupplierController::class, 'edit']);
$router->post('/settings/suppliers/{id}', [SupplierController::class, 'update']);
$router->post('/settings/suppliers/{id}/toggle', [SupplierController::class, 'toggle']);

// Sections from the sidebar menu that are not built yet: placeholder pages.
// Registered last, so a real route with the same path always wins.
foreach (require APP_ROOT . '/config/menu.php' as $group) {
    foreach ($group['items'] as $item) {
        if ($item['path'] === '/') {
            continue;
        }
        $label = $item['label'];
        $router->get($item['path'], static fn ($request) => (new SectionController())->show($request, $label));
    }
}
