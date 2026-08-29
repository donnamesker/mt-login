<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Business;
use App\Models\Tenant;
use App\Support\Auth;

class ContextService
{
    private Auth $auth;
    private Tenant $tenants;
    private Business $businesses;
    private AuthorizationService $authorization;
    private SessionService $session;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->tenants = new Tenant();
        $this->businesses = new Business();
        $this->authorization = new AuthorizationService();
        $this->session = new SessionService();
    }

    /**
     * Get the current application context.
     *
     * Returns null if the user is not authenticated or
     * the selected tenant/business context is invalid.
     *
     * @return array{
     *     user: array,
     *     tenant: array,
     *     business: array,
     *     availableTenants: array,
     *     availableBusinesses: array
     * }|null
     */
    public function current(): ?array
    {
        if (!$this->auth->check()) {
            return null;
        }

        $user = $this->auth->user();
        $userId = $this->auth->id();

        if ($user === null || $userId === null) {
            return null;
        }

        $tenantId = $this->session->tenantId();
        $businessId = $this->session->businessId();

        if ($tenantId === null || $businessId === null) {
            return null;
        }

        if (!$this->authorization->canAccessTenant(
            $userId,
            $tenantId
        )) {
            return null;
        }

        if (!$this->authorization->canAccessBusiness(
            $userId,
            $tenantId,
            $businessId
        )) {
            return null;
        }

        $tenant = $this->tenants->find($tenantId);
        $business = $this->businesses->find($businessId);

        if ($tenant === null || $business === null) {
            return null;
        }

        if ((int) $business['tenant_id'] !== $tenantId) {
            return null;
        }

        $availableTenants = $this->tenants->forUser($userId);

        $availableBusinesses = $this->businesses->forUser(
            $userId,
            $tenantId
        );

        return [
            'user' => $user,
            'tenant' => $tenant,
            'business' => $business,
            'availableTenants' => $availableTenants,
            'availableBusinesses' => $availableBusinesses
        ];
    }

    /**
     * Establish an initial context for the authenticated user.
     *
     * If the user has no selected tenant/business, select the
     * first tenant and its first accessible business.
     *
     * @return bool True if a valid context was established.
     */
    public function initialize(): bool
    {
        $userId = $this->auth->id();

        if ($userId === null) {
            return false;
        }

        $tenantId = $this->session->tenantId();
        $businessId = $this->session->businessId();

        if ($tenantId !== null && $businessId !== null) {
            if (
                $this->authorization->canAccessTenant(
                    $userId,
                    $tenantId
                )
                && $this->authorization->canAccessBusiness(
                    $userId,
                    $tenantId,
                    $businessId
                )
            ) {
                return true;
            }
        }

        $tenants = $this->tenants->forUser($userId);

        if ($tenants === []) {
            return false;
        }

        $tenantId = (int) $tenants[0]['id'];

        $businesses = $this->businesses->forUser(
            $userId,
            $tenantId
        );

        if ($businesses === []) {
            return false;
        }

        $this->session->setTenantId($tenantId);
        $this->session->setBusinessId(
            (int) $businesses[0]['id']
        );

        return true;
    }

    /**
     * Switch the current tenant/account.
     *
     * Verifies that the authenticated user belongs to the
     * tenant before changing the session context.
     */
    public function switchTenant(int $tenantId): void
    {
        $userId = $this->auth->id();

        if ($userId === null) {
            throw new \RuntimeException('Authentication required.');
        }

        if (!$this->authorization->canAccessTenant(
            $userId,
            $tenantId
        )) {
            throw new \RuntimeException('Unauthorized tenant access.');
        }

        $businesses = $this->businesses->forUser(
            $userId,
            $tenantId
        );

        if ($businesses === []) {
            throw new \RuntimeException(
                'No accessible businesses exist in this account.'
            );
        }

        $this->session->setTenantId($tenantId);
        $this->session->setBusinessId(
            (int) $businesses[0]['id']
        );
    }

    /**
     * Switch the current business.
     *
     * Verifies that the authenticated user can access the
     * business within the currently selected tenant.
     */
    public function switchBusiness(int $businessId): void
    {
        $userId = $this->auth->id();
        $tenantId = $this->session->tenantId();

        if ($userId === null || $tenantId === null) {
            throw new \RuntimeException(
                'Authentication and account context are required.'
            );
        }

        if (!$this->authorization->canAccessBusiness(
            $userId,
            $tenantId,
            $businessId
        )) {
            throw new \RuntimeException('Unauthorized business access.');
        }

        $this->session->setBusinessId($businessId);
    }
}