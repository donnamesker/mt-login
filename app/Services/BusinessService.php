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

        if (mb_strlen($name) > 255) {
            throw new RuntimeException(
                'Business name cannot exceed 255 characters.'
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

    /**
     * Update a business name.
     *
     * Tenant owners/admins and business owners/admins
     * may update the business.
     */
    public function updateName(
        int $userId,
        int $tenantId,
        int $businessId,
        string $name
    ): void {
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

        if ($businessId <= 0) {
            throw new RuntimeException(
                'A valid business is required.'
            );
        }

        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException(
                'Business name is required.'
            );
        }

        if (mb_strlen($name) > 255) {
            throw new RuntimeException(
                'Business name cannot exceed 255 characters.'
            );
        }

        if (!$this->authorization->canManageBusiness(
            $userId,
            $tenantId,
            $businessId
        )) {
            throw new RuntimeException(
                'You do not have permission to manage this business.'
            );
        }

        $business = $this->businesses->find($businessId);

        if ($business === null) {
            throw new RuntimeException(
                'Business not found.'
            );
        }

        if ((int) $business['tenant_id'] !== $tenantId) {
            throw new RuntimeException(
                'Business does not belong to this account.'
            );
        }

        $this->businesses->updateName(
            $businessId,
            $name
        );
    }
}