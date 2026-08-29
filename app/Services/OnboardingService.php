<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use PDO;
use RuntimeException;

class OnboardingService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * Create a tenant, its owner membership, the first business,
     * and the business owner membership in one transaction.
     *
     * @return array{tenant_id: int, business_id: int}
     */
    public function createAccount(
        int $userId,
        string $tenantName,
        string $businessName
    ): array {
        $tenantName = trim($tenantName);
        $businessName = trim($businessName);

        if ($userId <= 0) {
            throw new RuntimeException('A valid user is required.');
        }

        if ($tenantName === '') {
            throw new RuntimeException('Account name is required.');
        }

        if ($businessName === '') {
            throw new RuntimeException('Business name is required.');
        }

        try {
            $this->db->beginTransaction();

            // Create the tenant/account.
            $statement = $this->db->prepare(
                'INSERT INTO tenants (name)
                 VALUES (:name)'
            );

            $statement->execute([
                'name' => $tenantName
            ]);

            $tenantId = (int) $this->db->lastInsertId();

            // Make the user the tenant owner.
            $statement = $this->db->prepare(
                'INSERT INTO tenant_users (tenant_id, user_id, role)
                 VALUES (:tenant_id, :user_id, :role)'
            );

            $statement->execute([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'role' => 'owner'
            ]);

            // Create the first business.
            $statement = $this->db->prepare(
                'INSERT INTO businesses (tenant_id, name)
                 VALUES (:tenant_id, :name)'
            );

            $statement->execute([
                'tenant_id' => $tenantId,
                'name' => $businessName
            ]);

            $businessId = (int) $this->db->lastInsertId();

            // Make the user the business owner.
            $statement = $this->db->prepare(
                'INSERT INTO business_users (business_id, user_id, role)
                 VALUES (:business_id, :user_id, :role)'
            );

            $statement->execute([
                'business_id' => $businessId,
                'user_id' => $userId,
                'role' => 'owner'
            ]);

            $this->db->commit();

            return [
                'tenant_id' => $tenantId,
                'business_id' => $businessId
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw new RuntimeException(
                'Unable to create account.',
                0,
                $e
            );
        }
    }
}