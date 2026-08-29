<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Services\OnboardingService;

echo "Testing onboarding...\n";

$userId = 1;

$onboarding = new OnboardingService();

try {
    $result = $onboarding->createAccount(
        $userId,
        'Test Customer Account',
        'Test Business'
    );

    echo "Onboarding successful.\n";
    echo "Tenant ID: {$result['tenant_id']}\n";
    echo "Business ID: {$result['business_id']}\n";
} catch (Throwable $e) {
    echo "Onboarding failed: " . $e->getMessage() . "\n";
    exit(1);
}