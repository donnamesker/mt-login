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
     * Determine whether a user can access a business
     * within a specific tenant.
     */
    public function canAccessBusiness(
        int $userId,
        int $tenantId,
        int $businessId
    ): bool {
        $stmt = $this->db->prepare(
            'SELECT 1
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

        return $stmt->fetchColumn() !== false;
    }
}