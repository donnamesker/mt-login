<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\AuthService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class LoginController
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
     * Display the login form.
     */
    public function show(): void
    {
        $this->session->start();

        View::render('auth/login', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'email' => ''
        ]);
    }

    /**
     * Process the login form.
     */
    public function login(): void
    {
        $this->session->start();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid. Please try again.'
            );

            return;
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->showError(
                'Please enter your email address and password.',
                $email
            );

            return;
        }

        $user = $this->auth->authenticate($email, $password);

        if ($user === null) {
            $this->showError(
                'The email address or password you entered is incorrect.',
                $email
            );

            return;
        }

        $this->session->login((int) $user['id']);

        header('Location: /dashboard');
        exit;
    }

    /**
     * Display a login error.
     */
    private function showError(
        string $message,
        string $email = ''
    ): void {
        View::render('auth/login', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'email' => $email
        ]);
    }
}