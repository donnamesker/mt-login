<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\ContextService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class ContextController
{
    private ContextService $context;
    private SessionService $session;
    private Csrf $csrf;

    public function __construct()
    {
        $this->context = new ContextService();
        $this->session = new SessionService();
        $this->csrf = new Csrf();
    }

    /**
     * Switch the current tenant/account.
     */
    public function switchTenant(): void
    {
        $this->session->start();

        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $tenantId = (int) ($_POST['tenant_id'] ?? 0);

        try {
            $this->context->switchTenant($tenantId);
        } catch (RuntimeException $e) {
            $this->showError($e->getMessage());
            return;
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * Switch the current business.
     */
    public function switchBusiness(): void
    {
        $this->session->start();

        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $businessId = (int) ($_POST['business_id'] ?? 0);

        try {
            $this->context->switchBusiness($businessId);
        } catch (RuntimeException $e) {
            $this->showError($e->getMessage());
            return;
        }

        header('Location: /dashboard');
        exit;
    }

    /**
     * Display a context-switching error.
     */
    private function showError(string $message): void
    {
        header('Location: /dashboard?context_error=' . urlencode($message));
        exit;
    }
}