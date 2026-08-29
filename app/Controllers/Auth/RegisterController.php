<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\AuthService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class RegisterController
{
    private AuthService $auth;
    private SessionService $session;
    private Csrf $csrf;

    public function __construct()
    {
        $this->auth = new AuthService();
        $this->session = new SessionService();
        $this->csrf = new Csrf();
    }

    /**
     * Display the registration form.
     */
    public function show(): void
    {
        $this->session->start();

        View::render('auth/register', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'name' => '',
            'email' => ''
        ]);
    }

    /**
     * Process the registration form.
     */
    public function register(): void
    {
        $this->session->start();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid. Please try again.'
            );

            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) (
            $_POST['password_confirmation'] ?? ''
        );

        if ($password !== $passwordConfirmation) {
            $this->showError(
                'The passwords do not match.',
                $name,
                $email
            );

            return;
        }

        try {
            $userId = $this->auth->register(
                $name,
                $email,
                $password
            );
        } catch (RuntimeException $e) {
            $this->showError(
                $e->getMessage(),
                $name,
                $email
            );

            return;
        }

        $this->session->login($userId);

        header('Location: /onboarding');
        exit;
    }

    /**
     * Display a registration error.
     */
    private function showError(
        string $message,
        string $name = '',
        string $email = ''
    ): void {
        View::render('auth/register', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'name' => $name,
            'email' => $email
        ]);
    }
}