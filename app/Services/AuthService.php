<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class AuthService
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    /**
     * Create a new user account.
     */
    public function register(
        string $name,
        string $email,
        string $password
    ): int {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new RuntimeException('Name is required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('A valid email address is required.');
        }

        if (strlen($password) < 12) {
            throw new RuntimeException(
                'Password must be at least 12 characters.'
            );
        }

        if ($this->users->findByEmail($email) !== null) {
            throw new RuntimeException(
                'An account with that email address already exists.'
            );
        }

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Unable to create password hash.'
            );
        }

        return $this->users->create(
            $name,
            $email,
            $passwordHash
        );
    }

    /**
     * Authenticate a user with an email address and password.
     */
    public function authenticate(
        string $email,
        string $password
    ): ?array {
        $email = strtolower(trim($email));

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }
}