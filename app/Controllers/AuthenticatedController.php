<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContextService;
use App\Support\Auth;

abstract class AuthenticatedController
{
    protected Auth $auth;
    protected ContextService $context;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->context = new ContextService();
    }

    /**
     * Require an authenticated user with a valid application context.
     *
     * @return array{
     *     user: array,
     *     tenant: array,
     *     business: array,
     *     availableTenants: array,
     *     availableBusinesses: array
     * }
     */
    protected function requireContext(): array
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

        return $context;
    }
}
