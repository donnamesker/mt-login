<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Support\Router;

return static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
};