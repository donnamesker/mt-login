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

    /**
     * Get users assigned to a business.
     */
    public function users(
        int $userId,
        int $tenantId,
        int $businessId
    ): array {
        $this->assertCanManageBusiness(
            $userId,
            $tenantId,
            $businessId
        );

        return $this->businesses->users($businessId);
    }

    /**
     * Get tenant users who can be added to a business.
     */
    public function availableUsers(
        int $userId,
        int $tenantId,
        int $businessId
    ): array {
        $this->assertCanManageBusiness(
            $userId,
            $tenantId,
            $businessId
        );

        return $this->businesses->availableUsers(
            $tenantId,
            $businessId
        );
    }

    /**
     * Add a tenant user to a business.
     */
    public function addUser(
        int $userId,
        int $tenantId,
        int $businessId,
        int $targetUserId,
        string $role
    ): void {
        $this->assertCanManageBusiness(
            $userId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'A valid user is required.'
            );
        }

        $role = strtolower(trim($role));

        if (!in_array(
            $role,
            ['owner', 'admin', 'member'],
            true
        )) {
            throw new RuntimeException(
                'Invalid business role.'
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

        if (!$this->businesses->userBelongsToTenant(
            $tenantId,
            $targetUserId
        )) {
            throw new RuntimeException(
                'User does not belong to this account.'
            );
        }

        if ($this->businesses->hasUser(
            $businessId,
            $targetUserId
        )) {
            throw new RuntimeException(
                'User is already assigned to this business.'
            );
        }

        try {
            $this->businesses->addUser(
                $businessId,
                $targetUserId,
                $role
            );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Unable to add user to business.',
                0,
                $e
            );
        }
    }

    /**
     * Change a user's business role.
     */
    public function updateUserRole(
        int $userId,
        int $tenantId,
        int $businessId,
        int $targetUserId,
        string $role
    ): void {
        $this->assertCanManageBusiness(
            $userId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'A valid user is required.'
            );
        }

        $role = strtolower(trim($role));

        if (!in_array(
            $role,
            ['owner', 'admin', 'member'],
            true
        )) {
            throw new RuntimeException(
                'Invalid business role.'
            );
        }

        if (!$this->businesses->hasUser(
            $businessId,
            $targetUserId
        )) {
            throw new RuntimeException(
                'User is not assigned to this business.'
            );
        }

        $currentRole = $this->getUserRole(
            $businessId,
            $targetUserId
        );

        if (
            $currentRole === 'owner'
            && $role !== 'owner'
            && $this->businesses->ownerCount($businessId) <= 1
        ) {
            throw new RuntimeException(
                'A business must have at least one owner.'
            );
        }

        $this->businesses->updateUserRole(
            $businessId,
            $targetUserId,
            $role
        );
    }

    /**
     * Remove a user from a business.
     */
    public function removeUser(
        int $userId,
        int $tenantId,
        int $businessId,
        int $targetUserId
    ): void {
        $this->assertCanManageBusiness(
            $userId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'A valid user is required.'
            );
        }

        if (!$this->businesses->hasUser(
            $businessId,
            $targetUserId
        )) {
            throw new RuntimeException(
                'User is not assigned to this business.'
            );
        }

        $currentRole = $this->getUserRole(
            $businessId,
            $targetUserId
        );

        if (
            $currentRole === 'owner'
            && $this->businesses->ownerCount($businessId) <= 1
        ) {
            throw new RuntimeException(
                'The last business owner cannot be removed.'
            );
        }

        $this->businesses->removeUser(
            $businessId,
            $targetUserId
        );
    }

    /**
     * Get a user's current business role.
     */
    private function getUserRole(
        int $businessId,
        int $userId
    ): ?string {
        $statement = $this->db->prepare(
            'SELECT role
             FROM business_users
             WHERE business_id = :business_id
               AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'business_id' => $businessId,
            'user_id' => $userId
        ]);

        $role = $statement->fetchColumn();

        return $role !== false
            ? (string) $role
            : null;
    }

    /**
     * Confirm the current user can manage the business.
     */
    private function assertCanManageBusiness(
        int $userId,
        int $tenantId,
        int $businessId
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
    }
}