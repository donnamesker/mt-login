<?php

ob_start();

require __DIR__ . '/../bootstrap.php';

use App\Services\SessionService;

$session = new SessionService();

try {
    $session->start();

    echo "Session started.\n";

    $session->login(1);

    if (!$session->isAuthenticated()) {
        throw new RuntimeException('User was not authenticated.');
    }

    echo "User is authenticated.\n";
    echo "User ID: " . $session->userId() . "\n";

    $session->setTenantId(123);
    $session->setBusinessId(456);

    $session->login(1);

    if ($session->tenantId() !== null) {
        throw new RuntimeException(
            'Tenant context survived a new login.'
        );
    }

    if ($session->businessId() !== null) {
        throw new RuntimeException(
            'Business context survived a new login.'
        );
    }

    echo "Old tenant/business context cleared on login.\n";

    $session->logout();

    if ($session->isAuthenticated()) {
        throw new RuntimeException('User is still authenticated.');
    }

    echo "User logged out successfully.\n";

    ob_end_flush();
} catch (Throwable $e) {
    ob_end_clean();
    throw $e;
}
