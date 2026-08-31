<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthorizationService;
use App\Services\BusinessUserService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class BusinessUserController extends AuthenticatedController
{
    private BusinessUserService $businessUsers;
    private AuthorizationService $authorization;
    private Csrf $csrf;

    public function __construct()
    {
        parent::__construct();

        $this->businessUsers = new BusinessUserService();
        $this->authorization = new AuthorizationService();
        $this->csrf = new Csrf();
    }

    /**
     * Display users assigned to a business.
     */
    public function index(): void
    {
        $context = $this->requireContext();

        $businessId = (int) ($_GET['id'] ?? 0);

        try {
            $business = $this->getBusiness(
                $context,
                $businessId
            );

            $userId = (int) $context['user']['id'];
            $tenantId = (int) $context['tenant']['id'];

            $users = $this->businessUsers->forBusiness(
                $userId,
                $tenantId,
                $businessId
            );

            $canManage = $this->authorization->canManageBusiness(
                $userId,
                $tenantId,
                $businessId
            );

            $availableUsers = [];

            if ($canManage) {
                $availableUsers = $this->businessUsers
                    ->availableForBusiness(
                        $userId,
                        $tenantId,
                        $businessId
                    );
            }

            View::render('businesses/users', [
                'context' => $context,
                'business' => $business,
                'users' => $users,
                'availableUsers' => $availableUsers,
                'canManage' => $canManage,
                'csrfToken' => $this->csrf->token(),
                'errors' => []
            ]);
        } catch (RuntimeException $e) {
            $this->showError(
                $e->getMessage()
            );
        }
    }

    /**
     * Add a tenant user to the business.
     */
    public function add(): void
    {
        $context = $this->requireContext();

        if (!$this->verifyCsrf()) {
            $this->showBusinessError(
                $context,
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $businessId = (int) ($_POST['business_id'] ?? 0);
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $role = trim(
            (string) ($_POST['role'] ?? 'member')
        );

        try {
            $this->businessUsers->add(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId,
                $role
            );

            header(
                'Location: /businesses/users?id='
                . $businessId
            );
            exit;
        } catch (RuntimeException $e) {
            $this->showBusinessError(
                $context,
                $e->getMessage(),
                $businessId
            );
        }
    }

    /**
     * Update a business user's role.
     */
    public function update(): void
    {
        $context = $this->requireContext();

        if (!$this->verifyCsrf()) {
            $this->showBusinessError(
                $context,
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $businessId = (int) ($_POST['business_id'] ?? 0);
        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $role = trim(
            (string) ($_POST['role'] ?? 'member')
        );

        try {
            $this->businessUsers->updateRole(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId,
                $role
            );

            header(
                'Location: /businesses/users?id='
                . $businessId
            );
            exit;
        } catch (RuntimeException $e) {
            $this->showBusinessError(
                $context,
                $e->getMessage(),
                $businessId
            );
        }
    }

    /**
     * Remove a user from the business.
     */
    public function remove(): void
    {
        $context = $this->requireContext();

        if (!$this->verifyCsrf()) {
            $this->showBusinessError(
                $context,
                'Your session has expired or the form is invalid.'
            );
            return;
        }

        $businessId = (int) ($_POST['business_id'] ?? 0);
        $targetUserId = (int) ($_POST['user_id'] ?? 0);

        try {
            $this->businessUsers->remove(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId
            );

            header(
                'Location: /businesses/users?id='
                . $businessId
            );
            exit;
        } catch (RuntimeException $e) {
            $this->showBusinessError(
                $context,
                $e->getMessage(),
                $businessId
            );
        }
    }

    /**
     * Get an accessible business in the current tenant.
     */
    private function getBusiness(
        array $context,
        int $businessId
    ): array {
        if ($businessId <= 0) {
            throw new RuntimeException(
                'Business not found.'
            );
        }

        $userId = (int) $context['user']['id'];
        $tenantId = (int) $context['tenant']['id'];

        $businesses = $context['availableBusinesses'];

        foreach ($businesses as $business) {
            if ((int) $business['id'] !== $businessId) {
                continue;
            }

            if (!$this->authorization->canAccessBusiness(
                $userId,
                $tenantId,
                $businessId
            )) {
                throw new RuntimeException(
                    'You do not have access to this business.'
                );
            }

            return $business;
        }

        throw new RuntimeException(
            'Business not found.'
        );
    }

    /**
     * Verify the CSRF token.
     */
    private function verifyCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? null;

        return $this->csrf->verify($token);
    }

    /**
     * Display an error page.
     */
    private function showError(
        string $message
    ): void {
        http_response_code(403);

        echo htmlspecialchars(
            $message,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Re-render the business users page with an error.
     */
    private function showBusinessError(
        array $context,
        string $message,
        int $businessId = 0
    ): void {
        try {
            $business = $this->getBusiness(
                $context,
                $businessId
            );

            $userId = (int) $context['user']['id'];
            $tenantId = (int) $context['tenant']['id'];

            $users = $this->businessUsers->forBusiness(
                $userId,
                $tenantId,
                $businessId
            );

            $canManage = $this->authorization->canManageBusiness(
                $userId,
                $tenantId,
                $businessId
            );

            $availableUsers = [];

            if ($canManage) {
                $availableUsers = $this->businessUsers
                    ->availableForBusiness(
                        $userId,
                        $tenantId,
                        $businessId
                    );
            }

            View::render('businesses/users', [
                'context' => $context,
                'business' => $business,
                'users' => $users,
                'availableUsers' => $availableUsers,
                'canManage' => $canManage,
                'csrfToken' => $this->csrf->token(),
                'errors' => [$message]
            ]);
        } catch (RuntimeException $e) {
            $this->showError(
                $e->getMessage()
            );
        }
    }
}