<?php
/**
 * Front controller — every request enters here (see .htaccess rewrite).
 */
declare(strict_types=1);

require dirname(__DIR__) . '/app/core/bootstrap.php';

use App\Core\Request;

/** @var App\Core\Router $router */
$router = require APP_PATH . '/routes.php';

$router->dispatch(new Request());
