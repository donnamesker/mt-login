<?php

namespace App\Services;

use RuntimeException;

class SessionService
{
    private const SESSION_TIMEOUT = 1800;

    /**
     * Start the application session.
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            throw new RuntimeException(
                'Cannot start session because headers have already been sent.'
            );
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $this->isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (!session_start()) {
            throw new RuntimeException(
                'Unable to start session.'
            );
        }

        $this->enforceTimeout();
    }

    /**
     * Log a user into the application.
     */
    public function login(int $userId): void
    {
        $this->start();

        if (headers_sent()) {
            throw new RuntimeException(
                'Cannot regenerate session ID because headers have already been sent.'
            );
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $userId;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Determine whether a user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        $this->start();

        return isset($_SESSION['user_id']);
    }

    /**
     * Get the authenticated user's ID.
     */
    public function userId(): ?int
    {
        $this->start();

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (int) $_SESSION['user_id'];
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        $this->start();

        if (headers_sent()) {
            throw new RuntimeException(
                'Cannot log out because headers have already been sent.'
            );
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'] ?? '',
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }

    /**
     * Enforce inactivity timeout.
     */
    private function enforceTimeout(): void
    {
        if (!isset($_SESSION['last_activity'])) {
            return;
        }

        if (time() - $_SESSION['last_activity'] > self::SESSION_TIMEOUT) {
            $this->destroyCurrentSession();
            return;
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * Destroy the current session without starting it again.
     */
    private function destroyCurrentSession(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Determine whether the current request uses HTTPS.
     */
    private function isHttps(): bool
    {
        return isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off';
    }
}