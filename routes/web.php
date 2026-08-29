<?php

declare(strict_types=1);

use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Controllers\DashboardController;
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

    $router->get('/onboarding', [OnboardingController::class, 'show']);

    $router->post('/onboarding', [OnboardingController::class, 'create']);

    $router->post('/context/tenant', [ContextController::class, 'switchTenant']);
    $router->post('/context/business', [ContextController::class, 'switchBusiness']);
};