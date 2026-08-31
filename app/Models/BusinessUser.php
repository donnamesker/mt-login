<?php

declare(strict_types=1);

namespace App\Models;

use App\Database\Database;
use PDO;

class BusinessUser
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Get users assigned to a business.
     */
    public function forBusiness(
        int $businessId,
        int $tenantId
    ): array {
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
             INNER JOIN businesses b
                 ON b.id = bu.business_id
             WHERE bu.business_id = :business_id
               AND b.tenant_id = :tenant_id
             ORDER BY u.name'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get tenant users who are not assigned to a business.
     */
    public function availableForBusiness(
        int $businessId,
        int $tenantId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email
             FROM tenant_users tu
             INNER JOIN users u
                 ON u.id = tu.user_id
             WHERE tu.tenant_id = :tenant_id
               AND NOT EXISTS (
                   SELECT 1
                   FROM business_users bu
                   WHERE bu.business_id = :business_id
                     AND bu.user_id = u.id
               )
             ORDER BY u.name'
        );

        $stmt->execute([
            'tenant_id' => $tenantId,
            'business_id' => $businessId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get a user's role within a business.
     */
    public function role(
        int $businessId,
        int $userId
    ): ?string {
        $stmt = $this->db->prepare(
            'SELECT role
             FROM business_users
             WHERE business_id = :business_id
               AND user_id = :user_id
             LIMIT 1'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'user_id' => $userId
        ]);

        $role = $stmt->fetchColumn();

        return $role !== false
            ? (string) $role
            : null;
    }

    /**
     * Get business owners.
     */
    public function owners(
        int $businessId
    ): array {
        $stmt = $this->db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email
             FROM business_users bu
             INNER JOIN users u
                 ON u.id = bu.user_id
             WHERE bu.business_id = :business_id
               AND bu.role = :role
             ORDER BY u.name'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'role' => 'owner'
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Add a user to a business.
     */
    public function add(
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
     * Update a business user's role.
     */
    public function updateRole(
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
    public function remove(
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
}