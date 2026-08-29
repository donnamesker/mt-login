<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContextService;
use App\Support\Auth;
use App\Support\View;

class AccountController
{
    private Auth $auth;
    private ContextService $context;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->context = new ContextService();
    }

    /**
     * Display the current account.
     */
    public function index(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $context = $this->context->current();

        if ($context === null) {
            header('Location: /onboarding');
            exit;
        }

        View::render('account/index', [
            'context' => $context
        ]);
    }
}