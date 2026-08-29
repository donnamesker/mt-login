<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Business;
use App\Models\Tenant;
use App\Services\AuthorizationService;
use App\Services\SessionService;
use App\Support\Auth;
use App\Support\View;

class DashboardController
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

    public function index(): void
    {
        if (!$this->auth->check()) {
            header('Location: /login');
            exit;
        }

        $user = $this->auth->user();
        $userId = $this->auth->id();

        if ($user === null || $userId === null) {
            header('Location: /login');
            exit;
        }

        $tenantId = $this->session->tenantId();
        $businessId = $this->session->businessId();

        /*
         * A user must have a selected tenant and business
         * before accessing the dashboard.
         */
        if ($tenantId === null || $businessId === null) {
            header('Location: /onboarding');
            exit;
        }

        /*
         * Verify that the user actually belongs to the
         * selected tenant.
         */
        if (!$this->authorization->canAccessTenant($userId, $tenantId)) {
            header('Location: /onboarding');
            exit;
        }

        /*
         * Verify that the user can access the selected
         * business and that the business belongs to the
         * selected tenant.
         */
        if (
            !$this->authorization->canAccessBusiness(
                $userId,
                $tenantId,
                $businessId
            )
        ) {
            header('Location: /onboarding');
            exit;
        }

        $tenant = $this->tenants->find($tenantId);
        $business = $this->businesses->find($businessId);

        if ($tenant === null || $business === null) {
            header('Location: /onboarding');
            exit;
        }

        /*
         * Final defense: make sure the selected business
         * actually belongs to the selected tenant.
         */
        if ((int) $business['tenant_id'] !== $tenantId) {
            header('Location: /onboarding');
            exit;
        }

        $availableTenants = $this->tenants->forUser($userId);
        $availableBusinesses = $this->businesses->forUser(
            $userId,
            $tenantId
        );

        View::render('dashboard/index', [
            'user' => $user,
            'tenant' => $tenant,
            'business' => $business,
            'availableTenants' => $availableTenants,
            'availableBusinesses' => $availableBusinesses
        ]);
    }
}