<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthorizationService;
use App\Services\BusinessService;
use App\Support\Csrf;
use App\Support\View;
use RuntimeException;

class BusinessController extends AuthenticatedController
{
    private BusinessService $businesses;
    private AuthorizationService $authorization;
    private Csrf $csrf;

    public function __construct()
    {
        parent::__construct();

        $this->businesses = new BusinessService();
        $this->authorization = new AuthorizationService();
        $this->csrf = new Csrf();
    }

    /**
     * Display businesses for the current account.
     */
    public function index(): void
    {
        $context = $this->requireContext();

        $userId = (int) $context['user']['id'];
        $tenantId = (int) $context['tenant']['id'];

        $manageableBusinesses = [];

        foreach ($context['availableBusinesses'] as $business) {
            $manageableBusinesses[(int) $business['id']] =
                $this->authorization->canManageBusiness(
                    $userId,
                    $tenantId,
                    (int) $business['id']
                );
        }

        View::render('businesses/index', [
            'context' => $context,
            'businesses' => $context['availableBusinesses'],
            'canManage' => $this->authorization->canManageTenant(
                $userId,
                $tenantId
            ),
            'manageableBusinesses' => $manageableBusinesses,
            'csrfToken' => $this->csrf->token(),
            'errors' => []
        ]);
    }

    /**
     * Create a new business.
     */
    public function create(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showIndexError(
                $context,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        try {
            $this->businesses->create(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $name
            );
        } catch (RuntimeException $e) {
            $this->showIndexError(
                $context,
                $e->getMessage(),
                $name
            );

            return;
        }

        header('Location: /businesses');
        exit;
    }

    /**
     * Display the business edit form.
     */
    public function edit(): void
    {
        $context = $this->requireContext();

        $businessId = (int) ($_GET['id'] ?? 0);

        $business = $this->getManageableBusiness(
            $context,
            $businessId
        );

        View::render('businesses/edit', [
            'context' => $context,
            'business' => $business,
            'csrfToken' => $this->csrf->token(),
            'errors' => []
        ]);
    }

    /**
     * Update a business.
     */
    public function update(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        if (!$this->csrf->verify($token)) {
            $this->showEditError(
                $context,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $businessId = (int) (
            $_POST['business_id'] ?? 0
        );

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        try {
            $this->businesses->updateName(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $name
            );
        } catch (RuntimeException $e) {
            $this->showEditError(
                $context,
                $e->getMessage(),
                $businessId,
                $name
            );

            return;
        }

        header('Location: /businesses');
        exit;
    }

    /**
     * Display users assigned to a business.
     */
    public function users(): void
    {
        $context = $this->requireContext();

        $businessId = (int) ($_GET['id'] ?? 0);

        $business = $this->getManageableBusiness(
            $context,
            $businessId
        );

        try {
            $users = $this->businesses->users(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId
            );

            $availableUsers = $this->businesses->availableUsers(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId
            );
        } catch (RuntimeException $e) {
            http_response_code(403);
            echo htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
            return;
        }

        View::render('businesses/users', [
            'context' => $context,
            'business' => $business,
            'users' => $users,
            'availableUsers' => $availableUsers,
            'csrfToken' => $this->csrf->token(),
            'errors' => []
        ]);
    }

    /**
     * Add a user to a business.
     */
    public function addUser(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        $businessId = (int) (
            $_POST['business_id'] ?? 0
        );

        if (!$this->csrf->verify($token)) {
            $this->showUsersError(
                $context,
                $businessId,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $targetUserId = (int) (
            $_POST['user_id'] ?? 0
        );

        $role = trim(
            (string) ($_POST['role'] ?? 'member')
        );

        try {
            $this->businesses->addUser(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId,
                $role
            );
        } catch (RuntimeException $e) {
            $this->showUsersError(
                $context,
                $businessId,
                $e->getMessage()
            );

            return;
        }

        header(
            'Location: /businesses/users?id='
            . $businessId
        );

        exit;
    }

    /**
     * Update a user's business role.
     */
    public function updateUserRole(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        $businessId = (int) (
            $_POST['business_id'] ?? 0
        );

        if (!$this->csrf->verify($token)) {
            $this->showUsersError(
                $context,
                $businessId,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $targetUserId = (int) (
            $_POST['user_id'] ?? 0
        );

        $role = trim(
            (string) ($_POST['role'] ?? '')
        );

        try {
            $this->businesses->updateUserRole(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId,
                $role
            );
        } catch (RuntimeException $e) {
            $this->showUsersError(
                $context,
                $businessId,
                $e->getMessage()
            );

            return;
        }

        header(
            'Location: /businesses/users?id='
            . $businessId
        );

        exit;
    }

    /**
     * Remove a user from a business.
     */
    public function removeUser(): void
    {
        $context = $this->requireContext();

        $token = $_POST['_csrf_token'] ?? null;

        $businessId = (int) (
            $_POST['business_id'] ?? 0
        );

        if (!$this->csrf->verify($token)) {
            $this->showUsersError(
                $context,
                $businessId,
                'Your session has expired or the form is invalid.'
            );

            return;
        }

        $targetUserId = (int) (
            $_POST['user_id'] ?? 0
        );

        try {
            $this->businesses->removeUser(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId,
                $targetUserId
            );
        } catch (RuntimeException $e) {
            $this->showUsersError(
                $context,
                $businessId,
                $e->getMessage()
            );

            return;
        }

        header(
            'Location: /businesses/users?id='
            . $businessId
        );

        exit;
    }

    /**
     * Get a business the current user is allowed to manage.
     */
    private function getManageableBusiness(
        array $context,
        int $businessId
    ): array {
        if ($businessId <= 0) {
            http_response_code(404);
            echo 'Business not found.';
            exit;
        }

        $businesses = $context['availableBusinesses'];

        foreach ($businesses as $business) {
            if (
                (int) $business['id']
                !== $businessId
            ) {
                continue;
            }

            if (!$this->authorization->canManageBusiness(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId
            )) {
                http_response_code(403);
                echo 'You do not have permission to manage this business.';
                exit;
            }

            return $business;
        }

        http_response_code(404);
        echo 'Business not found.';
        exit;
    }

    /**
     * Display a business list error.
     */
    private function showIndexError(
        array $context,
        string $message,
        string $name = ''
    ): void {
        $userId = (int) $context['user']['id'];
        $tenantId = (int) $context['tenant']['id'];

        $canManage = $this->authorization->canManageTenant(
            $userId,
            $tenantId
        );

        View::render('businesses/index', [
            'context' => $context,
            'businesses' => $context['availableBusinesses'],
            'canManage' => $canManage,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'name' => $name
        ]);
    }

    /**
     * Display a business edit error.
     */
    private function showEditError(
        array $context,
        string $message,
        int $businessId = 0,
        string $name = ''
    ): void {
        $business = null;

        foreach (
            $context['availableBusinesses']
            as $availableBusiness
        ) {
            if (
                (int) $availableBusiness['id']
                === $businessId
            ) {
                $business = $availableBusiness;
                break;
            }
        }

        if ($business === null) {
            http_response_code(404);
            echo 'Business not found.';
            return;
        }

        View::render('businesses/edit', [
            'context' => $context,
            'business' => $business,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message],
            'name' => $name
        ]);
    }

    /**
     * Display a business-user management error.
     */
    private function showUsersError(
        array $context,
        int $businessId,
        string $message
    ): void {
        try {
            $business = $this->getManageableBusiness(
                $context,
                $businessId
            );

            $users = $this->businesses->users(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId
            );

            $availableUsers = $this->businesses->availableUsers(
                (int) $context['user']['id'],
                (int) $context['tenant']['id'],
                $businessId
            );
        } catch (RuntimeException $e) {
            http_response_code(403);
            echo htmlspecialchars(
                $e->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
            return;
        }

        View::render('businesses/users', [
            'context' => $context,
            'business' => $business,
            'users' => $users,
            'availableUsers' => $availableUsers,
            'csrfToken' => $this->csrf->token(),
            'errors' => [$message]
        ]);
    }
}