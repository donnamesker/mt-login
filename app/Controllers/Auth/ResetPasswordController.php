<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Services\PasswordResetService;
use App\Services\SessionService;
use App\Support\Csrf;
use App\Support\View;
use Throwable;

class ResetPasswordController
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
     * Display the reset-password form when the token is valid.
     */
    public function show(): void
    {
        $this->session->start();

        $token = trim(
            (string) ($_GET['token'] ?? '')
        );

        if (!$this->passwordResets->tokenIsValid($token)) {
            View::render('auth/reset-password', [
                'csrfToken' => $this->csrf->token(),
                'errors' => [
                    'This password reset link is invalid or has expired. Please request a new one.'
                ],
                'token' => $token,
                'validToken' => false,
                'passwordReset' => false
            ], null);

            return;
        }

        View::render('auth/reset-password', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [],
            'token' => $token,
            'validToken' => true,
            'passwordReset' => false
        ], null);
    }

    /**
     * Process a new password.
     */
    public function reset(): void
    {
        $this->session->start();

        $csrfToken = $_POST['_csrf_token'] ?? null;
        $token = trim(
            (string) ($_POST['token'] ?? '')
        );
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) (
            $_POST['password_confirmation'] ?? ''
        );

        if (!$this->csrf->verify($csrfToken)) {
            $this->showError(
                'Your session has expired or the form is invalid. Please try again.',
                $token
            );

            return;
        }

        if (!$this->passwordResets->tokenIsValid($token)) {
            $this->showError(
                'This password reset link is invalid or has expired. Please request a new one.',
                $token
            );

            return;
        }

        if (strlen($password) < 12) {
            $this->showError(
                'Password must be at least 12 characters.',
                $token
            );

            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->showError(
                'The passwords do not match.',
                $token
            );

            return;
        }

        try {
            $this->passwordResets->resetPassword(
                $token,
                $password
            );
        } catch (Throwable $e) {
            error_log(
                'Password reset failed: '
                . $e->getMessage()
            );

            $this->showError(
                'We could not reset your password. Please request a new password reset link and try again.',
                $token
            );

            return;
        }

        // Ensure the browser using the reset link must authenticate again.
        $this->session->logout();

        header('Location: /login?reset=success');
        exit;
    }

    private function showError(
        string $message,
        string $token
    ): void {
        View::render('auth/reset-password', [
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'token' => $token,
            'validToken' => $this->passwordResets->tokenIsValid($token),
            'passwordReset' => false
        ], null);
    }
}