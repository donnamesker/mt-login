<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Auth;
use App\Support\View;

class DashboardController
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function index(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $user = $this->auth->user();

        View::render('dashboard/index', [
            'user' => $user
        ]);
    }
}