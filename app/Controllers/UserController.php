<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class UserController extends AuthenticatedController
{
    private UserService $users;
    private Csrf $csrf;

    public function __construct()
    {
        parent::__construct();

        $this->users = new UserService();
        $this->csrf = new Csrf();
    }

    /**
     * Display users belonging to the current account.
     */
    public function index(): void
    {
        $context = $this->requireContext();

        $tenantId = (int) $context['tenant']['id'];
        $userId = (int) $this->auth->id();

        try {
            $users = $this->users->forTenant(
                $userId,
                $tenantId
            );
        } catch (RuntimeException $e) {
            http_response_code(403);
            echo htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
            return;
        }

        View::render('users/index', [
            'context' => $context,
            'users' => $users
        ]);
    }

    /**
     * Display the add-user form.
     */
    public function create(): void
    {
        $context = $this->requireContext();

        View::render('users/create', [
            'context' => $context,
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'email' => '',
            'role' => 'member'
        ]);
    }

    /**
     * Add an existing user to the current account.
     */
    public function store(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showCreateError(
                $context,
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $email = trim(
            (string) ($_POST['email'] ?? '')
        );

        $role = trim(
            (string) ($_POST['role'] ?? 'member')
        );

        try {
            $this->users->addToTenant(
                (int) $this->auth->id(),
                (int) $context['tenant']['id'],
                $email,
                $role
            );
        } catch (RuntimeException $e) {
            $this->showCreateError(
                $context,
                $e->getMessage(),
                $email,
                $role
            );
            return;
        }

        header('Location: /users');
        exit;
    }

    /**
     * Display an add-user error.
     */
    private function showCreateError(
        array $context,
        string $message,
        string $email = '',
        string $role = 'member'
    ): void {
        View::render('users/create', [
            'context' => $context,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'email' => $email,
            'role' => $role
        ]);
    }
}
