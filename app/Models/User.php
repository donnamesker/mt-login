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
     * Get users belonging to a tenant.
     */
    public function forTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email,
                tu.role,
                tu.created_at
             FROM users u
             INNER JOIN tenant_users tu
                 ON tu.user_id = u.id
             WHERE tu.tenant_id = :tenant_id
             ORDER BY u.name'
        );

        $stmt->execute([
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get owners belonging to a tenant.
     */
    public function ownersForTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email
             FROM users u
             INNER JOIN tenant_users tu
                 ON tu.user_id = u.id
             WHERE tu.tenant_id = :tenant_id
               AND tu.role = :role
             ORDER BY u.name'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'role' => 'owner'
        ]);

        return $stmt->fetchAll();
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
