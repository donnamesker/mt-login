<?php

namespace App\Support;

use RuntimeException;

class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Get the current CSRF token.
     *
     * Creates one if the session does not already have one.
     */
    public function token(): string
    {
        $this->ensureSession();

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Verify a submitted CSRF token.
     */
    public function verify(?string $token): bool
    {
        $this->ensureSession();

        if (
            $token === null
            || empty($_SESSION[self::SESSION_KEY])
        ) {
            return false;
        }

        return hash_equals(
            $_SESSION[self::SESSION_KEY],
            $token
        );
    }

    /**
     * Verify a token and throw an exception if invalid.
     */
    public function verifyOrFail(?string $token): void
    {
        if (!$this->verify($token)) {
            throw new RuntimeException(
                'Invalid CSRF token.'
            );
        }
    }

    /**
     * Remove the current CSRF token.
     */
    public function regenerate(): string
    {
        $this->ensureSession();

        $_SESSION[self::SESSION_KEY] = bin2hex(
            random_bytes(32)
        );

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Make sure the application session is active.
     */
    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $session = new \App\Services\SessionService();

        $session->start();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException(
                'Unable to initialize session.'
            );
        }
    }
}