<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Database;
use App\Models\Business;
use PDO;
use RuntimeException;

class BusinessService
{
    private PDO $db;
    private Business $businesses;
    private AuthorizationService $authorization;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->businesses = new Business();
        $this->authorization = new AuthorizationService();
    }

    /**
     * Get businesses accessible to the user within the tenant.
     */
    public function forUser(
        int $userId,
        int $tenantId
    ): array {
        if ($userId <= 0 || $tenantId <= 0) {
            throw new RuntimeException(
                'Valid user and account are required.'
            );
        }

        if (!$this->authorization->canAccessTenant(
            $userId,
            $tenantId
        )) {
            throw new RuntimeException(
                'Unauthorized account access.'
            );
        }

        return $this->businesses->forUser(
            $userId,
            $tenantId
        );
    }

    /**
     * Create a business and make the user its owner.
     *
     * Only tenant owners and administrators may create
     * businesses within the account.
     */
    public function create(
        int $userId,
        int $tenantId,
        string $name
    ): int {
        $name = trim($name);

        if ($userId <= 0) {
            throw new RuntimeException(
                'A valid user is required.'
            );
        }

        if ($tenantId <= 0) {
            throw new RuntimeException(
                'A valid account is required.'
            );
        }

        if ($name === '') {
            throw new RuntimeException(
                'Business name is required.'
            );
        }

        if (!$this->authorization->canManageTenant(
            $userId,
            $tenantId
        )) {
            throw new RuntimeException(
                'You do not have permission to create businesses in this account.'
            );
        }

        try {
            $this->db->beginTransaction();

            $businessId = $this->businesses->create(
                $tenantId,
                $name
            );

            $statement = $this->db->prepare(
                'INSERT INTO business_users
                    (business_id, user_id, role)
                 VALUES
                    (:business_id, :user_id, :role)'
            );

            $statement->execute([
                'business_id' => $businessId,
                'user_id' => $userId,
                'role' => 'owner'
            ]);

            $this->db->commit();

            return $businessId;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw new RuntimeException(
                'Unable to create business.',
                0,
                $e
            );
        }
    }
}