<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Services\SessionService;

$session = new SessionService();

$session->start();

$session->setTenantId(2);
$session->setBusinessId(2);

$tenantId = $session->tenantId();
$businessId = $session->businessId();

echo "Session started.\n";

if ($tenantId !== 2) {
    echo "Tenant context test FAILED.\n";
    exit(1);
}

if ($businessId !== 2) {
    echo "Business context test FAILED.\n";
    exit(1);
}

echo "Tenant context: PASS\n";
echo "Tenant ID: {$tenantId}\n";

echo "Business context: PASS\n";
echo "Business ID: {$businessId}\n";