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
}