<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Business
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Find a business by ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, tenant_id, name, created_at, updated_at
             FROM businesses
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            'id' => $id
        ]);

        $business = $stmt->fetch();

        return $business ?: null;
    }

    /**
     * Get businesses belonging to a tenant.
     */
    public function forTenant(int $tenantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, tenant_id, name, created_at, updated_at
             FROM businesses
             WHERE tenant_id = :tenant_id
             ORDER BY name'
        );

        $stmt->execute([
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get businesses the user is authorized to access.
     */
    public function forUser(int $userId, int $tenantId): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.id, b.tenant_id, b.name, b.created_at, b.updated_at
             FROM businesses b
             INNER JOIN business_users bu
                 ON bu.business_id = b.id
             WHERE bu.user_id = :user_id
               AND b.tenant_id = :tenant_id
             ORDER BY b.name'
        );

        $stmt->execute([
            'user_id' => $userId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }
}