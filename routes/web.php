<?php

declare(strict_types=1);

use App\Controllers\Auth\LoginController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Support\Router;

return static function (Router $router): void {

    $router->get('/', [HomeController::class, 'index']);

    $router->get('/login', [LoginController::class, 'show']);
    $router->post('/login', [LoginController::class, 'login']);
    $router->post('/logout', [LoginController::class, 'logout']);

    $router->get('/dashboard', [DashboardController::class, 'index']);

};