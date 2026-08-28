<?php

namespace App\Support;

use App\Models\User;
use App\Services\SessionService;

class Auth
{
    private SessionService $session;

    private User $users;

    public function __construct()
    {
        $this->session = new SessionService();
        $this->users = new User();
    }

    /**
     * Determine whether a user is authenticated.
     */
    public function check(): bool
    {
        return $this->session->isAuthenticated();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?array
    {
        $userId = $this->session->userId();

        if ($userId === null) {
            return null;
        }

        return $this->users->find($userId);
    }

    /**
     * Get the authenticated user's ID.
     */
    public function id(): ?int
    {
        return $this->session->userId();
    }

    /**
     * Log the user out.
     */
    public function logout(): void
    {
        $this->session->logout();
    }
}