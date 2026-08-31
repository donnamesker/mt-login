<?php

namespace App\Services;

use App\Database\Database;
use PDO;

class AuthorizationService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Determine whether a user belongs to a tenant.
     */
    public function canAccessTenant(
        int $userId,
        int $tenantId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM tenant_users
             WHERE user_id = :user_id
               AND tenant_id = :tenant_id
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Get a user's role within a tenant.
     */
    public function tenantRole(
        int $userId,
        int $tenantId
    ): ?string {
        $stmt = $this->db->prepare(
            'SELECT role
             FROM tenant_users
             WHERE user_id = :user_id
               AND tenant_id = :tenant_id
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'tenant_id' => $tenantId
        ]);

        $role = $stmt->fetchColumn();

        return $role !== false
            ? (string) $role
            : null;
    }

    /**
     * Determine whether a user can manage a tenant.
     *
     * Owners and admins can manage the account.
     */
    public function canManageTenant(
        int $userId,
        int $tenantId
    ): bool {
        $role = $this->tenantRole(
            $userId,
            $tenantId
        );

        return in_array(
            $role,
            ['owner', 'admin'],
            true
        );
    }

    /**
     * Get a user's role within a business.
     */
    public function businessRole(
        int $userId,
        int $tenantId,
        int $businessId
    ): ?string {
        $stmt = $this->db->prepare(
            'SELECT bu.role
             FROM business_users bu
             INNER JOIN businesses b
                 ON b.id = bu.business_id
             WHERE bu.user_id = :user_id
               AND bu.business_id = :business_id
               AND b.tenant_id = :tenant_id
             LIMIT 1'
        );

        $stmt->execute([
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'business_id' => $businessId
        ]);

        $role = $stmt->fetchColumn();

        return $role !== false
            ? (string) $role
            : null;
    }

    /**
     * Determine whether a user can access a business.
     *
     * Tenant owners and admins can access every business
     * within their tenant.
     *
     * Tenant members must have explicit business membership.
     */
    public function canAccessBusiness(
        int $userId,
        int $tenantId,
        int $businessId
    ): bool {
        $tenantRole = $this->tenantRole(
            $userId,
            $tenantId
        );

        if (in_array(
            $tenantRole,
            ['owner', 'admin'],
            true
        )) {
            return $this->businessBelongsToTenant(
                $tenantId,
                $businessId
            );
        }

        return $this->businessRole(
            $userId,
            $tenantId,
            $businessId
        ) !== null;
    }

    /**
     * Determine whether a user can manage a business.
     *
     * Tenant owners and admins can manage every business
     * within their tenant.
     *
     * Business owners and admins can manage their business.
     */
    public function canManageBusiness(
        int $userId,
        int $tenantId,
        int $businessId
    ): bool {
        $tenantRole = $this->tenantRole(
            $userId,
            $tenantId
        );

        if (in_array(
            $tenantRole,
            ['owner', 'admin'],
            true
        )) {
            return $this->businessBelongsToTenant(
                $tenantId,
                $businessId
            );
        }

        $businessRole = $this->businessRole(
            $userId,
            $tenantId,
            $businessId
        );

        return in_array(
            $businessRole,
            ['owner', 'admin'],
            true
        );
    }

    /**
     * Determine whether a business belongs to a tenant.
     */
    private function businessBelongsToTenant(
        int $tenantId,
        int $businessId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
             FROM businesses
             WHERE id = :business_id
               AND tenant_id = :tenant_id
             LIMIT 1'
        );

        $stmt->execute([
            'business_id' => $businessId,
            'tenant_id' => $tenantId
        ]);

        return $stmt->fetchColumn() !== false;
    }
}