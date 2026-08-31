<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\ContextService;
use App\Services\SessionService;

$db = Database::connection();
$session = new SessionService();
$context = new ContextService();

$session->start();

$userId = 1;

$session->login($userId);

echo "Testing context switching..." . PHP_EOL;
echo "Authenticated User ID: {$userId}" . PHP_EOL;

// Find a tenant belonging to the test user.
$statement = $db->prepare(
    'SELECT tenant_id
     FROM tenant_users
     WHERE user_id = :user_id
     ORDER BY tenant_id
     LIMIT 1'
);

$statement->execute([
    'user_id' => $userId
]);

$tenantId = $statement->fetchColumn();

if ($tenantId === false) {
    throw new RuntimeException(
        'Test user has no tenant membership.'
    );
}

$tenantId = (int) $tenantId;

echo PHP_EOL;
echo "Testing valid tenant switch..." . PHP_EOL;

$context->switchTenant($tenantId);

if ($session->tenantId() !== $tenantId) {
    throw new RuntimeException(
        'Tenant switch failed.'
    );
}

if ($session->businessId() === null) {
    throw new RuntimeException(
        'Tenant switch did not select a business.'
    );
}

echo "Valid tenant switch: PASS" . PHP_EOL;
echo "Tenant ID: " . $session->tenantId() . PHP_EOL;
echo "Business ID: " . $session->businessId() . PHP_EOL;

// Create a tenant that the test user does not belong to.
echo PHP_EOL;
echo "Creating unauthorized tenant..." . PHP_EOL;

$statement = $db->prepare(
    'INSERT INTO tenants (name)
     VALUES (:name)'
);

$statement->execute([
    'name' => 'Context Switch Test Unauthorized Tenant'
]);

$unauthorizedTenantId = (int) $db->lastInsertId();

echo "Unauthorized Tenant ID: {$unauthorizedTenantId}" . PHP_EOL;

echo "Testing invalid tenant switch..." . PHP_EOL;

try {
    $context->switchTenant($unauthorizedTenantId);

    throw new RuntimeException(
        'Unauthorized tenant switch was allowed.'
    );
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'Unauthorized tenant access.') {
        throw $e;
    }
}

if ($session->tenantId() !== $tenantId) {
    throw new RuntimeException(
        'Unauthorized tenant switch changed the session.'
    );
}

echo "Invalid tenant switch: PASS" . PHP_EOL;

// Create a business in the unauthorized tenant.
// The test user does not belong to the tenant and therefore
// must not be able to switch to this business.
echo PHP_EOL;
echo "Creating unauthorized business..." . PHP_EOL;

$statement = $db->prepare(
    'INSERT INTO businesses (tenant_id, name)
     VALUES (:tenant_id, :name)'
);

$statement->execute([
    'tenant_id' => $unauthorizedTenantId,
    'name' => 'Context Switch Test Unauthorized Business'
]);

$unauthorizedBusinessId = (int) $db->lastInsertId();

echo "Unauthorized Business ID: {$unauthorizedBusinessId}" . PHP_EOL;

$currentBusinessId = $session->businessId();

echo "Testing invalid business switch..." . PHP_EOL;

try {
    $context->switchBusiness($unauthorizedBusinessId);

    throw new RuntimeException(
        'Unauthorized business switch was allowed.'
    );
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'Unauthorized business access.') {
        throw $e;
    }
}

if ($session->businessId() !== $currentBusinessId) {
    throw new RuntimeException(
        'Unauthorized business switch changed the session.'
    );
}

echo "Invalid business switch: PASS" . PHP_EOL;

// Clean up the test records.
$db->prepare(
    'DELETE FROM businesses WHERE id = :id'
)->execute([
    'id' => $unauthorizedBusinessId
]);

$db->prepare(
    'DELETE FROM tenants WHERE id = :id'
)->execute([
    'id' => $unauthorizedTenantId
]);

echo PHP_EOL;
echo "Context switching tests passed." . PHP_EOL;
?>
