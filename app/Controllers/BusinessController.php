<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\BusinessService;
use App\Services\AuthorizationService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class BusinessController extends AuthenticatedController
{
    private BusinessService $businesses;
    private AuthorizationService $authorization;
    private Csrf $csrf;

    public function __construct()
    {
        parent::__construct();

        $this->businesses = new BusinessService();
        $this->authorization = new AuthorizationService();
        $this->csrf = new Csrf();
    }

    /**
     * Display businesses for the current account.
     */
    public function index(): void
    {
        $context = $this->requireContext();

        $userId = (int) $context['user']['id'];
        $tenantId = (int) $context['tenant']['id'];

        $canManage = $this->authorization->canManageTenant(
            $userId,
            $tenantId
        );

        View::render('businesses/index', [
            'context' => $context,
            'businesses' => $context['availableBusinesses'],
            'canManage' => $canManage,
            'csrfToken' => $this->csrf->token(),
            'errors' => []
        ]);
    }

    /**
     * Create a new business.
     */
    public function create(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                $context,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        try {
            $this->businesses->create(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $name
            );
        } catch (RuntimeException $e) {
            $this->showError(
                $context,
                $e->getMessage(),
                $name
            );

            return;
        }

        header('Location: /businesses');
        exit;
    }

    /**
     * Display a business creation error.
     */
    private function showError(
        array $context,
        string $message,
        string $name = ''
    ): void {
        $userId = (int) $context['user']['id'];
        $tenantId = (int) $context['tenant']['id'];

        $canManage = $this->authorization->canManageTenant(
            $userId,
            $tenantId
        );

        View::render('businesses/index', [
            'context' => $context,
            'businesses' => $context['availableBusinesses'],
            'canManage' => $canManage,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'name' => $name
        ]);
    }
}