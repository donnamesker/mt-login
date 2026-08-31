<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessUser;
use RuntimeException;

class BusinessUserService
{
    private Business $businesses;
    private BusinessUser $businessUsers;
    private AuthorizationService $authorization;

    public function __construct()
    {
        $this->businesses = new Business();
        $this->businessUsers = new BusinessUser();
        $this->authorization = new AuthorizationService();
    }

    /**
     * Get users assigned to a business.
     */
    public function forBusiness(
        int $currentUserId,
        int $tenantId,
        int $businessId
    ): array {
        $this->requireBusinessAccess(
            $currentUserId,
            $tenantId,
            $businessId
        );

        return $this->businessUsers->forBusiness(
            $businessId,
            $tenantId
        );
    }

    /**
     * Get tenant users available to add to a business.
     */
    public function availableForBusiness(
        int $currentUserId,
        int $tenantId,
        int $businessId
    ): array {
        $this->requireBusinessManagement(
            $currentUserId,
            $tenantId,
            $businessId
        );

        return $this->businessUsers->availableForBusiness(
            $businessId,
            $tenantId
        );
    }

    /**
     * Add a tenant user to a business.
     *
     * Business owners may assign any valid business role.
     * Business administrators may add members only.
     *
     * Tenant owners and tenant administrators are treated as
     * having full business-management authority within their tenant.
     */
    public function add(
        int $currentUserId,
        int $tenantId,
        int $businessId,
        int $targetUserId,
        string $role = 'member'
    ): void {
        $this->requireBusinessManagement(
            $currentUserId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'Valid user is required.'
            );
        }

        $role = trim($role);

        $this->validateRole($role);

        $this->requireBusinessBelongsToTenant(
            $businessId,
            $tenantId
        );

        if (
            !$this->authorization->canAccessTenant(
                $targetUserId,
                $tenantId
            )
        ) {
            throw new RuntimeException(
                'That user does not belong to this account.'
            );
        }

        if (
            $this->businessUsers->role(
                $businessId,
                $targetUserId
            ) !== null
        ) {
            throw new RuntimeException(
                'That user is already assigned to this business.'
            );
        }

        $currentRole = $this->businessUsers->role(
            $businessId,
            $currentUserId
        );

        $tenantRole = $this->authorization->tenantRole(
            $currentUserId,
            $tenantId
        );

        /*
         * Tenant owners and tenant admins can manage all
         * businesses in their tenant.
         *
         * Business admins can manage members only.
         */
        $hasTenantManagement =
            in_array(
                $tenantRole,
                ['owner', 'admin'],
                true
            );

        if (
            !$hasTenantManagement
            && $currentRole === 'admin'
            && $role !== 'member'
        ) {
            throw new RuntimeException(
                'Business administrators can add members only.'
            );
        }

        /*
         * A user who has business-management access should
         * always have a business role unless their authority
         * comes from the tenant level.
         */
        if (
            !$hasTenantManagement
            && !in_array(
                $currentRole,
                ['owner', 'admin'],
                true
            )
        ) {
            throw new RuntimeException(
                'You do not have permission to add users to this business.'
            );
        }

        $this->businessUsers->add(
            $businessId,
            $targetUserId,
            $role
        );
    }

    /**
     * Change a business user's role.
     *
     * Business owners may manage any business role.
     * Business administrators may manage members only.
     * Tenant owners and tenant administrators may manage
     * business roles within their tenant.
     */
    public function updateRole(
        int $currentUserId,
        int $tenantId,
        int $businessId,
        int $targetUserId,
        string $role
    ): void {
        $this->requireBusinessManagement(
            $currentUserId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'Valid user is required.'
            );
        }

        $role = trim($role);

        $this->validateRole($role);

        $currentRole = $this->businessUsers->role(
            $businessId,
            $currentUserId
        );

        $targetRole = $this->businessUsers->role(
            $businessId,
            $targetUserId
        );

        if ($targetRole === null) {
            throw new RuntimeException(
                'That user is not assigned to this business.'
            );
        }

        $tenantRole = $this->authorization->tenantRole(
            $currentUserId,
            $tenantId
        );

        $hasTenantManagement =
            in_array(
                $tenantRole,
                ['owner', 'admin'],
                true
            );

        /*
         * Business administrators may manage members only.
         * This check must apply to the NEW role as well as the
         * existing target role. Otherwise an admin could promote
         * a member to owner or administrator.
         */
        if (
            !$hasTenantManagement
            && $currentRole === 'admin'
        ) {
            if ($targetRole !== 'member') {
                throw new RuntimeException(
                    'Business administrators cannot change owner or administrator roles.'
                );
            }

            if ($role !== 'member') {
                throw new RuntimeException(
                    'Business administrators can assign members only.'
                );
            }
        }

        /*
         * Only an owner or a tenant-level administrator/owner
         * can change an existing owner to another role.
         */
        if (
            $targetRole === 'owner'
            && $role !== 'owner'
        ) {
            if (
                !$hasTenantManagement
                && $currentRole !== 'owner'
            ) {
                throw new RuntimeException(
                    'Only a business owner can change an owner role.'
                );
            }

            $this->preventLastOwner(
                $businessId,
                $targetUserId
            );
        }

        $this->businessUsers->updateRole(
            $businessId,
            $targetUserId,
            $role
        );
    }

    /**
     * Remove a user from the business.
     *
     * Business owners may remove anyone.
     * Business administrators may remove members only.
     * Tenant owners and tenant administrators may manage
     * business membership within their tenant.
     */
    public function remove(
        int $currentUserId,
        int $tenantId,
        int $businessId,
        int $targetUserId
    ): void {
        $this->requireBusinessManagement(
            $currentUserId,
            $tenantId,
            $businessId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'Valid user is required.'
            );
        }

        $currentRole = $this->businessUsers->role(
            $businessId,
            $currentUserId
        );

        $targetRole = $this->businessUsers->role(
            $businessId,
            $targetUserId
        );

        if ($targetRole === null) {
            throw new RuntimeException(
                'That user is not assigned to this business.'
            );
        }

        $tenantRole = $this->authorization->tenantRole(
            $currentUserId,
            $tenantId
        );

        $hasTenantManagement =
            in_array(
                $tenantRole,
                ['owner', 'admin'],
                true
            );

        if (
            !$hasTenantManagement
            && $currentRole === 'admin'
            && $targetRole !== 'member'
        ) {
            throw new RuntimeException(
                'Business administrators cannot remove owners or administrators.'
            );
        }

        if ($targetRole === 'owner') {
            if (
                !$hasTenantManagement
                && $currentRole !== 'owner'
            ) {
                throw new RuntimeException(
                    'Only a business owner can remove an owner.'
                );
            }

            $this->preventLastOwner(
                $businessId,
                $targetUserId
            );
        }

        $this->businessUsers->remove(
            $businessId,
            $targetUserId
        );
    }

    /**
     * Require access to the business.
     */
    private function requireBusinessAccess(
        int $userId,
        int $tenantId,
        int $businessId
    ): void {
        if (
            $userId <= 0
            || $tenantId <= 0
            || $businessId <= 0
        ) {
            throw new RuntimeException(
                'Valid business information is required.'
            );
        }

        if (
            !$this->authorization->canAccessBusiness(
                $userId,
                $tenantId,
                $businessId
            )
        ) {
            throw new RuntimeException(
                'You do not have access to this business.'
            );
        }
    }

    /**
     * Require business management access.
     */
    private function requireBusinessManagement(
        int $userId,
        int $tenantId,
        int $businessId
    ): void {
        if (
            $userId <= 0
            || $tenantId <= 0
            || $businessId <= 0
        ) {
            throw new RuntimeException(
                'Valid business information is required.'
            );
        }

        if (
            !$this->authorization->canManageBusiness(
                $userId,
                $tenantId,
                $businessId
            )
        ) {
            throw new RuntimeException(
                'You do not have permission to manage users in this business.'
            );
        }
    }

    /**
     * Confirm that the business belongs to the tenant.
     */
    private function requireBusinessBelongsToTenant(
        int $businessId,
        int $tenantId
    ): void {
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

    /**
     * Validate a business role.
     */
    private function validateRole(string $role): void
    {
        if (!in_array(
            $role,
            ['owner', 'admin', 'member'],
            true
        )) {
            throw new RuntimeException(
                'Invalid business role.'
            );
        }
    }

    /**
     * Prevent removal or demotion of the last owner.
     */
    private function preventLastOwner(
        int $businessId,
        int $targetUserId
    ): void {
        $owners = $this->businessUsers->owners(
            $businessId
        );

        if (
            count($owners) <= 1
            && isset($owners[0])
            && (int) $owners[0]['id'] === $targetUserId
        ) {
            throw new RuntimeException(
                'The business must have at least one owner.'
            );
        }
    }
}