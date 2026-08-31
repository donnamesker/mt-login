<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use RuntimeException;

class AccountService
{
    private Tenant $tenants;
    private AuthorizationService $authorization;

    public function __construct()
    {
        $this->tenants = new Tenant();
        $this->authorization = new AuthorizationService();
    }

    /**
     * Update the account name.
     *
     * Only account owners and admins may update the name.
     */
    public function updateName(
        int $userId,
        int $tenantId,
        string $name
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
                'You do not have permission to manage this account.'
            );
        }

        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException(
                'Account name is required.'
            );
        }

        if (mb_strlen($name) > 255) {
            throw new RuntimeException(
                'Account name cannot exceed 255 characters.'
            );
        }

        $this->tenants->updateName(
            $tenantId,
            $name
        );
    }
}
