<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\SessionService;

$db = Database::connection();
$session = new SessionService();

$session->start();
$session->login(1);

$statement = $db->prepare(
    'SELECT tu.tenant_id
     FROM tenant_users tu
     INNER JOIN businesses b
         ON b.tenant_id = tu.tenant_id
     WHERE tu.user_id = :user_id
     ORDER BY tu.tenant_id, b.id
     LIMIT 1'
);

$statement->execute(['user_id' => 1]);
$tenantId = $statement->fetchColumn();

if ($tenantId === false) {
    throw new RuntimeException(
        'Test user has no tenant with an accessible business.'
    );
}

$tenantId = (int) $tenantId;

$statement = $db->prepare(
    'SELECT b.id
     FROM businesses b
     INNER JOIN tenant_users tu
         ON tu.tenant_id = b.tenant_id
     LEFT JOIN business_users bu
         ON bu.business_id = b.id
        AND bu.user_id = :business_user_id
     WHERE tu.user_id = :tenant_user_id
       AND b.tenant_id = :tenant_id
       AND (
           tu.role IN (\'owner\', \'admin\')
           OR bu.user_id IS NOT NULL
       )
     ORDER BY b.id
     LIMIT 1'
);

$statement->execute([
    'business_user_id' => 1,
    'tenant_user_id' => 1,
    'tenant_id' => $tenantId
]);

$businessId = $statement->fetchColumn();

if ($businessId === false) {
    throw new RuntimeException(
        'Test user has no accessible business.'
    );
}

$businessId = (int) $businessId;

$session->setTenantId($tenantId);
$session->setBusinessId($businessId);

if ($session->tenantId() !== $tenantId) {
    throw new RuntimeException('Tenant context was not stored correctly.');
}

if ($session->businessId() !== $businessId) {
    throw new RuntimeException('Business context was not stored correctly.');
}

echo "Session started.\n";
echo "Tenant context: PASS ({$tenantId})\n";
echo "Business context: PASS ({$businessId})\n";
echo "Session context tests passed.\n";
