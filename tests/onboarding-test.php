<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\OnboardingService;

$db = Database::connection();

echo "Testing onboarding...\n";

$userId = 1;
$tenantId = null;
$businessId = null;

try {
    $onboarding = new OnboardingService();

    $result = $onboarding->createAccount(
        $userId,
        'Test Customer Account',
        'Test Business'
    );

    $tenantId = $result['tenant_id'];
    $businessId = $result['business_id'];

    echo "Onboarding successful.\n";
    echo "Tenant ID: {$tenantId}\n";
    echo "Business ID: {$businessId}\n";

    echo "\nVerifying tenant owner...\n";

    $statement = $db->prepare(
        'SELECT role
         FROM tenant_users
         WHERE tenant_id = :tenant_id
           AND user_id = :user_id
         LIMIT 1'
    );

    $statement->execute([
        'tenant_id' => $tenantId,
        'user_id' => $userId
    ]);

    $tenantRole = $statement->fetchColumn();

    if ($tenantRole !== 'owner') {
        throw new RuntimeException(
            "Expected tenant owner, got: {$tenantRole}"
        );
    }

    echo "Tenant owner: PASS\n";

    echo "\nVerifying business owner...\n";

    $statement = $db->prepare(
        'SELECT role
         FROM business_users
         WHERE business_id = :business_id
           AND user_id = :user_id
         LIMIT 1'
    );

    $statement->execute([
        'business_id' => $businessId,
        'user_id' => $userId
    ]);

    $businessRole = $statement->fetchColumn();

    if ($businessRole !== 'owner') {
        throw new RuntimeException(
            "Expected business owner, got: {$businessRole}"
        );
    }

    echo "Business owner: PASS\n";

    echo "\nVerifying business belongs to tenant...\n";

    $statement = $db->prepare(
        'SELECT tenant_id
         FROM businesses
         WHERE id = :business_id
         LIMIT 1'
    );

    $statement->execute([
        'business_id' => $businessId
    ]);

    $businessTenantId = $statement->fetchColumn();

    if ((int) $businessTenantId !== $tenantId) {
        throw new RuntimeException(
            'Business is assigned to the wrong tenant.'
        );
    }

    echo "Business tenant relationship: PASS\n";

    echo "\nOnboarding tests passed.\n";

} finally {

    if ($tenantId !== null) {
        $statement = $db->prepare(
            'DELETE FROM tenants
             WHERE id = :tenant_id'
        );

        $statement->execute([
            'tenant_id' => $tenantId
        ]);

        echo "Test data cleaned up.\n";
    }
}