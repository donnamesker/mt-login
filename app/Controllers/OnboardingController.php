<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\OnboardingService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class OnboardingController
{
    private OnboardingService $onboarding;
    private SessionService $session;
    private Csrf $csrf;

    public function __construct()
    {
        $this->onboarding = new OnboardingService();
        $this->session = new SessionService();
        $this->csrf = new Csrf();
    }

    /**
     * Display the initial account setup form.
     */
    public function show(): void
    {
        $this->session->start();

        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        View::render('onboarding/index', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'tenantName' => '',
            'businessName' => ''
        ]);
    }

    /**
     * Process the initial account setup form.
     */
    public function create(): void
    {
        $this->session->start();

        if (!$this->session->isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid. Please try again.'
            );

            return;
        }

        $tenantName = trim((string) ($_POST['tenant_name'] ?? ''));
        $businessName = trim((string) ($_POST['business_name'] ?? ''));

        try {
            $result = $this->onboarding->createAccount(
                $this->session->userId(),
                $tenantName,
                $businessName
            );
        } catch (RuntimeException $e) {
            $this->showError(
                $e->getMessage(),
                $tenantName,
                $businessName
            );

            return;
        }

        $this->session->setTenantId($result['tenant_id']);
        $this->session->setBusinessId($result['business_id']);

        header('Location: /dashboard');
        exit;
    }

    /**
     * Display an onboarding error.
     */
    private function showError(
        string $message,
        string $tenantName = '',
        string $businessName = ''
    ): void {
        View::render('onboarding/index', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'tenantName' => $tenantName,
            'businessName' => $businessName
        ]);
    }
}