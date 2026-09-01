<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AccountService;
use App\Services\AuthorizationService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class AccountController extends AuthenticatedController
{
    private AccountService $account;
    private AuthorizationService $authorization;
    private Csrf $csrf;

    public function __construct()
    {
        parent::__construct();

        $this->account = new AccountService();
        $this->authorization = new AuthorizationService();
        $this->csrf = new Csrf();
    }

    /**
     * Display the current account.
     */
    public function index(): void
    {
        $context = $this->requireContext();

        $userId = (int) $this->auth->id();
        $tenantId = (int) $context['tenant']['id'];

        $role = $this->authorization->tenantRole(
            $userId,
            $tenantId
        );

        View::render('account/index', [
            'context' => $context,
            'role' => $role,
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'success' => $_GET['success'] ?? null,
            'name' => $context['tenant']['name']
        ]);
    }

    /**
     * Update the current account.
     */
    public function update(): void
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
            $this->account->updateName(
                (int) $this->auth->id(),
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

        header(
            'Location: /account?success=Account+updated+successfully.'
        );
        exit;
    }

    /**
     * Display an account update error.
     */
    private function showError(
        array $context,
        string $message,
        string $name = ''
    ): void {
        $userId = (int) $this->auth->id();
        $tenantId = (int) $context['tenant']['id'];

        $role = $this->authorization->tenantRole(
            $userId,
            $tenantId
        );

        View::render('account/index', [
            'context' => $context,
            'role' => $role,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'success' => null,
            'name' => $name
        ]);
    }
}
