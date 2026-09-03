<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use RuntimeException;

class UserService
{
    private User $users;
    private Tenant $tenants;
    private AuthorizationService $authorization;

    public function __construct()
    {
        $this->users = new User();
        $this->tenants = new Tenant();
        $this->authorization = new AuthorizationService();
    }

    /**
     * Get users belonging to a tenant.
     */
    public function forTenant(
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

        return $this->users->forTenant($tenantId);
    }

    /**
     * Add an existing application user to a tenant.
     *
     * Only tenant owners and administrators may add users.
     */
    public function addToTenant(
        int $currentUserId,
        int $tenantId,
        string $email,
        string $role = 'member'
    ): void {
        $this->requireManagementAccess(
            $currentUserId,
            $tenantId
        );

        $email = strtolower(trim($email));

        if ($email === '') {
            throw new RuntimeException(
                'Email address is required.'
            );
        }

        $user = $this->users->findByEmail($email);

        if ($user === null) {
            throw new RuntimeException(
                'No user exists with that email address.'
            );
        }

        $existingUsers = $this->users->forTenant($tenantId);

        foreach ($existingUsers as $existingUser) {
            if ((int) $existingUser['id'] === (int) $user['id']) {
                throw new RuntimeException(
                    'That user already belongs to this account.'
                );
            }
        }

        $this->validateRole($role);

        $this->tenants->addUser(
            $tenantId,
            (int) $user['id'],
            $role
        );
    }

    /**
     * Change a user's role within a tenant.
     *
     * Owners may manage any role.
     * Administrators may manage members only.
     */
    public function updateRole(
        int $currentUserId,
        int $tenantId,
        int $targetUserId,
        string $role
    ): void {
        $this->requireManagementAccess(
            $currentUserId,
            $tenantId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'Valid user is required.'
            );
        }

        $this->validateRole($role);

        $currentRole = $this->authorization->tenantRole(
            $currentUserId,
            $tenantId
        );

        $targetRole = $this->authorization->tenantRole(
            $targetUserId,
            $tenantId
        );

        if ($targetRole === null) {
            throw new RuntimeException(
                'That user does not belong to this account.'
            );
        }

        if (
            $currentRole === 'admin'
            && $targetRole !== 'member'
        ) {
            throw new RuntimeException(
                'Administrators cannot change owner or administrator roles.'
            );
        }

        if (
            $targetRole === 'owner'
            && $role !== 'owner'
        ) {
            $this->preventLastOwner(
                $tenantId,
                $targetUserId
            );

            if ($currentRole !== 'owner') {
                throw new RuntimeException(
                    'Only the account owner can change an owner role.'
                );
            }
        }

        $this->tenants->updateUserRole(
            $tenantId,
            $targetUserId,
            $role
        );
    }

    /**
     * Remove a user from a tenant.
     *
     * Owners may remove any user.
     * Administrators may remove members only.
     */
    public function removeFromTenant(
        int $currentUserId,
        int $tenantId,
        int $targetUserId
    ): void {
        $this->requireManagementAccess(
            $currentUserId,
            $tenantId
        );

        if ($targetUserId <= 0) {
            throw new RuntimeException(
                'Valid user is required.'
            );
        }

        $currentRole = $this->authorization->tenantRole(
            $currentUserId,
            $tenantId
        );

        $targetRole = $this->authorization->tenantRole(
            $targetUserId,
            $tenantId
        );

        if ($targetRole === null) {
            throw new RuntimeException(
                'That user does not belong to this account.'
            );
        }

        if (
            $currentRole === 'admin'
            && $targetRole !== 'member'
        ) {
            throw new RuntimeException(
                'Administrators cannot remove owners or administrators.'
            );
        }

        if ($targetRole === 'owner') {
            $this->preventLastOwner(
                $tenantId,
                $targetUserId
            );

            if ($currentRole !== 'owner') {
                throw new RuntimeException(
                    'Only the account owner can remove an owner.'
                );
            }
        }

        $this->tenants->removeUser(
            $tenantId,
            $targetUserId
        );
    }

    /**
     * Require owner or administrator access.
     */
    private function requireManagementAccess(
        int $userId,
        int $tenantId
    ): void {
        if ($userId <= 0 || $tenantId <= 0) {
            throw new RuntimeException(
                'Valid account information is required.'
            );
        }

        if (!$this->authorization->canManageTenant(
            $userId,
            $tenantId
        )) {
            throw new RuntimeException(
                'You do not have permission to manage users in this account.'
            );
        }
    }

    /**
     * Validate a tenant role.
     */
    private function validateRole(string $role): void
    {
        if (!in_array(
            $role,
            ['owner', 'admin', 'member'],
            true
        )) {
            throw new RuntimeException(
                'Invalid user role.'
            );
        }
    }

    /**
     * Prevent removal or demotion of the last owner.
     */
    private function preventLastOwner(
        int $tenantId,
        int $targetUserId
    ): void {
        $owners = $this->users->ownersForTenant($tenantId);

        if (
            count($owners) === 1
            && (int) $owners[0]['id'] === $targetUserId
        ) {
            throw new RuntimeException(
                'The account must have at least one owner.'
            );
        }
    }
}
