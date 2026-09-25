<?php
/**
 * KI-BASE front controller. Everything except static files goes through here.
 * The application code lives outside the web root, in ../app.
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

App\Core\App::run();
