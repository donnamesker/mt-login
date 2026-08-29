<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\AuthorizationService;

$db = Database::connection();
$authorization = new AuthorizationService();

echo "Starting authorization tests...\n";

$db->beginTransaction();

try {
    echo "\nCreating test tenant...\n";

    $stmt = $db->prepare(
        "INSERT INTO tenants (name)
         VALUES (:name)"
    );

    $stmt->execute([
        'name' => 'Authorization Test Tenant'
    ]);

    $tenantId = (int) $db->lastInsertId();

    echo "Tenant ID: {$tenantId}\n";

    echo "\nAdding User #1 to tenant...\n";

    $stmt = $db->prepare(
        "INSERT INTO tenant_users (tenant_id, user_id, role)
         VALUES (:tenant_id, :user_id, :role)"
    );

    $stmt->execute([
        'tenant_id' => $tenantId,
        'user_id' => 1,
        'role' => 'owner'
    ]);

    echo "Tenant membership created.\n";

    echo "\nCreating test business...\n";

    $stmt = $db->prepare(
        "INSERT INTO businesses (tenant_id, name)
         VALUES (:tenant_id, :name)"
    );

    $stmt->execute([
        'tenant_id' => $tenantId,
        'name' => 'Authorization Test Business'
    ]);

    $businessId = (int) $db->lastInsertId();

    echo "Business ID: {$businessId}\n";

    echo "\nAdding User #1 to business...\n";

    $stmt = $db->prepare(
        "INSERT INTO business_users (business_id, user_id, role)
         VALUES (:business_id, :user_id, :role)"
    );

    $stmt->execute([
        'business_id' => $businessId,
        'user_id' => 1,
        'role' => 'owner'
    ]);

    echo "Business membership created.\n";

    echo "\nTesting tenant access...\n";

    if (!$authorization->canAccessTenant(1, $tenantId)) {
        throw new RuntimeException(
            'Tenant access was incorrectly denied.'
        );
    }

    echo "Tenant access granted correctly.\n";

    echo "\nTesting business access...\n";

    if (
        !$authorization->canAccessBusiness(
            1,
            $tenantId,
            $businessId
        )
    ) {
        throw new RuntimeException(
            'Business access was incorrectly denied.'
        );
    }

    echo "Business access granted correctly.\n";

    echo "\nTesting invalid business access...\n";

    if (
        $authorization->canAccessBusiness(
            1,
            $tenantId,
            999999
        )
    ) {
        throw new RuntimeException(
            'Invalid business access was incorrectly granted.'
        );
    }

    echo "Invalid business access rejected correctly.\n";

    $db->rollBack();

    echo "\nAuthorization tests passed.\n";
} catch (\Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    throw $e;
}