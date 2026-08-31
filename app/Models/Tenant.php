<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Tenant
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Find a tenant by ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, created_at, updated_at
             FROM tenants
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $tenant = $stmt->fetch();

        return $tenant ?: null;
    }

    /**
     * Get tenants belonging to a user.
     */
    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT t.id, t.name, t.created_at, t.updated_at
             FROM tenants t
             INNER JOIN tenant_users tu
                 ON tu.tenant_id = t.id
             WHERE tu.user_id = :user_id
             ORDER BY t.name'
        );

        $stmt->execute([
            'user_id' => $userId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Add a user to a tenant.
     */
    public function addUser(
        int $tenantId,
        int $userId,
        string $role = 'member'
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO tenant_users (
                tenant_id,
                user_id,
                role
            )
            VALUES (
                :tenant_id,
                :user_id,
                :role
            )'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'role' => $role
        ]);
    }

    /**
     * Update a tenant name.
     */
    public function updateName(
        int $tenantId,
        string $name
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE tenants
             SET name = :name
             WHERE id = :tenant_id'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => $name
        ]);
    }
}