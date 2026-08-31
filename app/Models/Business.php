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
     */
    public function forUser(
        int $userId,
        int $tenantId
    ): array {
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

    /**
     * Get users assigned to a business.
     */
    public function users(int $businessId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email,
                bu.role,
                bu.created_at
             FROM business_users bu
             INNER JOIN users u
                 ON u.id = bu.user_id
             WHERE bu.business_id = :business_id
             ORDER BY u.name, u.email'
        );

        $stmt->execute([
            'business_id' => $businessId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get users belonging to a tenant who are not
     * currently assigned to the business.
     */
    public function availableUsers(
        int $tenantId,
        int $businessId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email
             FROM tenant_users tu
             INNER JOIN users u
                 ON u.id = tu.user_id
             LEFT JOIN business_users bu
                 ON bu.user_id = tu.user_id
                AND bu.business_id = :business_id
             WHERE tu.tenant_id = :tenant_id
               AND bu.user_id IS NULL
             ORDER BY u.name, u.email'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Determine whether a user is assigned to a business.
     */
    public function hasUser(
        int $businessId,
        int $userId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM business_users
             WHERE business_id = :business_id
               AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'user_id' => $userId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Determine whether a user belongs to a tenant.
     */
    public function userBelongsToTenant(
        int $tenantId,
        int $userId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM tenant_users
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'user_id' => $userId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Add a user to a business.
     */
    public function addUser(
        int $businessId,
        int $userId,
        string $role
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO business_users
                (business_id, user_id, role)
             VALUES
                (:business_id, :user_id, :role)'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'user_id' => $userId,
            'role' => $role
        ]);
    }

    /**
     * Update a user's business role.
     */
    public function updateUserRole(
        int $businessId,
        int $userId,
        string $role
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE business_users
             SET role = :role
             WHERE business_id = :business_id
               AND user_id = :user_id'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'user_id' => $userId,
            'role' => $role
        ]);
    }

    /**
     * Remove a user from a business.
     */
    public function removeUser(
        int $businessId,
        int $userId
    ): void {
        $stmt = $this->db->prepare(
            'DELETE FROM business_users
             WHERE business_id = :business_id
               AND user_id = :user_id'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'user_id' => $userId
        ]);
    }

    /**
     * Count business owners.
     */
    public function ownerCount(int $businessId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM business_users
             WHERE business_id = :business_id
               AND role = :role'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'role' => 'owner'
        ]);

        return (int) $stmt->fetchColumn();
    }
}