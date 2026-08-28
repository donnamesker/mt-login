<?php

require __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\AuthorizationService;

$db = Database::connection();

echo "Creating test tenant...\n";

$db->exec(
    "INSERT INTO tenants (name)
     VALUES ('Test Tenant')"
);

$tenantId = (int) $db->lastInsertId();

echo "Tenant ID: {$tenantId}\n";

echo "\nAdding User #1 to tenant...\n";

$stmt = $db->prepare(
    "INSERT INTO tenant_users (tenant_id, user_id)
     VALUES (:tenant_id, :user_id)"
);

$stmt->execute([
    'tenant_id' => $tenantId,
    'user_id' => 1
]);

echo "Tenant membership created.\n";

echo "\nCreating test business...\n";

$stmt = $db->prepare(
    "INSERT INTO businesses (tenant_id, name)
     VALUES (:tenant_id, :name)"
);

$stmt->execute([
    'tenant_id' => $tenantId,
    'name' => 'Test Business'
]);

$businessId = (int) $db->lastInsertId();

echo "Business ID: {$businessId}\n";

echo "\nAdding User #1 to business...\n";

$stmt = $db->prepare(
    "INSERT INTO business_users (business_id, user_id)
     VALUES (:business_id, :user_id)"
);

$stmt->execute([
    'business_id' => $businessId,
    'user_id' => 1
]);

echo "Business membership created.\n";

$authorization = new AuthorizationService();

echo "\nTesting tenant access...\n";

if ($authorization->canAccessTenant(1, $tenantId)) {
    echo "Tenant access granted correctly.\n";
} else {
    echo "ERROR: Tenant access denied.\n";
}

echo "\nTesting business access...\n";

if ($authorization->canAccessBusiness(1, $tenantId, $businessId)) {
    echo "Business access granted correctly.\n";
} else {
    echo "ERROR: Business access denied.\n";
}

echo "\nTesting invalid business access...\n";

if (!$authorization->canAccessBusiness(1, $tenantId, 999999)) {
    echo "Invalid business access rejected correctly.\n";
} else {
    echo "ERROR: Invalid business access was granted.\n";
}