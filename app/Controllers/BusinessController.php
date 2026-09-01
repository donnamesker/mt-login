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

}