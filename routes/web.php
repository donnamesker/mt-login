<?php

declare(strict_types=1);

use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\DashboardController;
use App\Controllers\AccountController;
use App\Controllers\BusinessController;
use App\Controllers\UserController;
use App\Controllers\HomeController;
use App\Controllers\OnboardingController;
use App\Controllers\ContextController;
use App\Support\Router;

return static function (Router $router): void {

    $router->get('/', [HomeController::class, 'index']);

    $router->get('/login', [LoginController::class, 'show']);
    $router->post('/login', [LoginController::class, 'login']);
    $router->post('/logout', [LoginController::class, 'logout']);

    $router->get('/register', [RegisterController::class, 'show']);
    $router->post('/register', [RegisterController::class, 'register']);

    $router->get('/dashboard', [DashboardController::class, 'index']);

    $router->get('/account', [AccountController::class, 'index']);
    $router->post('/account', [AccountController::class, 'update']);

    $router->get('/businesses', [BusinessController::class, 'index']);
    $router->post('/businesses', [BusinessController::class, 'create']);
    $router->get('/businesses/edit', [BusinessController::class, 'edit']);
    $router->post('/businesses/update', [BusinessController::class, 'update']);

    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/new', [UserController::class, 'create']);
    $router->post('/users', [UserController::class, 'store']);

    $router->post('/onboarding', [OnboardingController::class, 'create']);

    $router->post('/context/tenant', [ContextController::class, 'switchTenant']);
    $router->post('/context/business', [ContextController::class, 'switchBusiness']);
};
