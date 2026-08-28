<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Find a user by their ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, password_hash, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, email, password_hash, created_at, updated_at
             FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute([
            'email' => strtolower(trim($email))
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Create a new user.
     */
    public function create(
        string $name,
        string $email,
        string $passwordHash
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash)
             VALUES (:name, :email, :password_hash)'
        );

        $stmt->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password_hash' => $passwordHash
        ]);

        return (int) $this->db->lastInsertId();
    }
}