<?php

declare(strict_types=1);

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
     *
     * Tenant owners and administrators can access every
     * business within their tenant.
     *
     * Tenant members must have explicit business membership.
     */
    public function forUser(
        int $userId,
        int $tenantId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT
                b.id,
                b.tenant_id,
                b.name,
                b.created_at,
                b.updated_at
             FROM businesses b
             INNER JOIN tenant_users tu
                 ON tu.tenant_id = b.tenant_id
             LEFT JOIN business_users bu
                 ON bu.business_id = b.id
                 AND bu.user_id = :business_user_id
             WHERE tu.user_id = :tenant_user_id
               AND b.tenant_id = :tenant_id
               AND (
                   tu.role IN (\'owner\', \'admin\')
                   OR bu.user_id IS NOT NULL
               )
             ORDER BY b.name'
        );

        $stmt->execute([
            'business_user_id' => $userId,
            'tenant_user_id' => $userId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Create a business within a tenant.
     */
    public function create(
        int $tenantId,
        string $name
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO businesses (tenant_id, name)
             VALUES (:tenant_id, :name)'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'name' => trim($name)
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a business name.
     */
    public function updateName(
        int $businessId,
        string $name
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE businesses
             SET name = :name
             WHERE id = :business_id'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'name' => trim($name)
        ]);
    }
}