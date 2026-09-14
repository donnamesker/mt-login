<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\PasswordResetService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use Throwable;

class ForgotPasswordController
{
    private PasswordResetService $passwordResets;
    private SessionService $session;
    private Csrf $csrf;

    public function __construct()
    {
        $this->passwordResets = new PasswordResetService();
        $this->session = new SessionService();
        $this->csrf = new Csrf();
    }

    /**
     * Display the forgot-password form.
     */
    public function show(): void
    {
        $this->session->start();

        View::render('auth/forgot-password', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'email' => '',
            'sent' => false
        ], null);
    }

    /**
     * Process a password reset request.
     *
     * The response intentionally does not reveal whether the email address
     * belongs to an account.
     */
    public function requestReset(): void
    {
        $this->session->start();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showError(
                'Your session has expired or the form is invalid. Please try again.'
            );

            return;
        }

        $email = strtolower(
            trim((string) ($_POST['email'] ?? ''))
        );

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showError(
                'Please enter a valid email address.',
                $email
            );

            return;
        }

        try {
            $this->passwordResets->requestReset($email);
        } catch (Throwable $e) {
            error_log(
                'Password reset request failed: '
                . $e->getMessage()
            );
        }

        View::render('auth/forgot-password', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'email' => $email,
            'sent' => true
        ], null);
    }

    private function showError(
        string $message,
        string $email = ''
    ): void {
        View::render('auth/forgot-password', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'email' => $email,
            'sent' => false
        ], null);
    }
}