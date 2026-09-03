<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database\Database;
use App\Services\AuthorizationService;

$db = Database::connection();
$authorization = new AuthorizationService();

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertFalse(bool $condition, string $message): void
{
    assertTrue(!$condition, $message);
}

function createTestUser(PDO $db, string $label): int
{
    $statement = $db->prepare(
        'INSERT INTO users (name, email, password_hash)
         VALUES (:name, :email, :password_hash)'
    );

    $email = 'authorization-' . $label . '-' . bin2hex(random_bytes(6)) . '@example.test';

    $statement->execute([
        'name' => 'Authorization ' . $label,
        'email' => $email,
        'password_hash' => password_hash('TestPassword123!', PASSWORD_DEFAULT)
    ]);

    return (int) $db->lastInsertId();
}

function createTenant(PDO $db, string $name): int
{
    $statement = $db->prepare(
        'INSERT INTO tenants (name) VALUES (:name)'
    );

    $statement->execute(['name' => $name]);

    return (int) $db->lastInsertId();
}

function addTenantUser(PDO $db, int $tenantId, int $userId, string $role): void
{
    $statement = $db->prepare(
        'INSERT INTO tenant_users (tenant_id, user_id, role)
         VALUES (:tenant_id, :user_id, :role)'
    );

    $statement->execute([
        'tenant_id' => $tenantId,
        'user_id' => $userId,
        'role' => $role
    ]);
}

function createBusiness(PDO $db, int $tenantId, string $name): int
{
    $statement = $db->prepare(
        'INSERT INTO businesses (tenant_id, name)
         VALUES (:tenant_id, :name)'
    );

    $statement->execute([
        'tenant_id' => $tenantId,
        'name' => $name
    ]);

    return (int) $db->lastInsertId();
}

function addBusinessUser(
    PDO $db,
    int $businessId,
    int $userId,
    string $role
): void {
    $statement = $db->prepare(
        'INSERT INTO business_users (business_id, user_id, role)
         VALUES (:business_id, :user_id, :role)'
    );

    $statement->execute([
        'business_id' => $businessId,
        'user_id' => $userId,
        'role' => $role
    ]);
}

echo "Starting authorization tests...\n";

$db->beginTransaction();

try {
    $ownerId = createTestUser($db, 'owner');
    $memberId = createTestUser($db, 'member');
    $otherOwnerId = createTestUser($db, 'other-owner');

    $tenantOneId = createTenant($db, 'Authorization Tenant One');
    $tenantTwoId = createTenant($db, 'Authorization Tenant Two');

    addTenantUser($db, $tenantOneId, $ownerId, 'owner');
    addTenantUser($db, $tenantOneId, $memberId, 'member');
    addTenantUser($db, $tenantTwoId, $otherOwnerId, 'owner');

    $businessOneId = createBusiness(
        $db,
        $tenantOneId,
        'Authorization Business One'
    );

    $businessTwoId = createBusiness(
        $db,
        $tenantTwoId,
        'Authorization Business Two'
    );

    addBusinessUser(
        $db,
        $businessOneId,
        $ownerId,
        'owner'
    );

    addBusinessUser(
        $db,
        $businessOneId,
        $memberId,
        'member'
    );

    addBusinessUser(
        $db,
        $businessTwoId,
        $otherOwnerId,
        'owner'
    );

    echo "Testing tenant isolation...\n";
    assertTrue(
        $authorization->canAccessTenant($ownerId, $tenantOneId),
        'Tenant owner should access their tenant.'
    );
    assertFalse(
        $authorization->canAccessTenant($ownerId, $tenantTwoId),
        'User must not access another tenant.'
    );

    echo "Testing tenant-level business access...\n";
    assertTrue(
        $authorization->canAccessBusiness(
            $ownerId,
            $tenantOneId,
            $businessOneId
        ),
        'Tenant owner should access businesses in their tenant.'
    );
    assertTrue(
        $authorization->canManageBusiness(
            $ownerId,
            $tenantOneId,
            $businessOneId
        ),
        'Tenant owner should manage businesses in their tenant.'
    );
    assertFalse(
        $authorization->canAccessBusiness(
            $ownerId,
            $tenantOneId,
            $businessTwoId
        ),
        'Tenant owner must not access a business in another tenant.'
    );

    echo "Testing explicit business membership...\n";
    assertTrue(
        $authorization->canAccessBusiness(
            $memberId,
            $tenantOneId,
            $businessOneId
        ),
        'Tenant member with business membership should have access.'
    );
    assertFalse(
        $authorization->canManageBusiness(
            $memberId,
            $tenantOneId,
            $businessOneId
        ),
        'Business member should not have management access.'
    );

    echo "Testing cross-tenant business ID protection...\n";
    assertFalse(
        $authorization->canAccessBusiness(
            $otherOwnerId,
            $tenantOneId,
            $businessOneId
        ),
        'A user from another tenant must not access this business.'
    );
    assertFalse(
        $authorization->canManageBusiness(
            $otherOwnerId,
            $tenantOneId,
            $businessOneId
        ),
        'A user from another tenant must not manage this business.'
    );

    echo "Testing stale business membership protection...\n";
    $statement = $db->prepare(
        'DELETE FROM tenant_users
         WHERE tenant_id = :tenant_id
           AND user_id = :user_id'
    );

    $statement->execute([
        'tenant_id' => $tenantOneId,
        'user_id' => $memberId
    ]);

    assertFalse(
        $authorization->canAccessBusiness(
            $memberId,
            $tenantOneId,
            $businessOneId
        ),
        'Business membership must not survive loss of tenant membership.'
    );
    assertFalse(
        $authorization->canManageBusiness(
            $memberId,
            $tenantOneId,
            $businessOneId
        ),
        'Stale business membership must not grant management access.'
    );

    echo "Testing business role lookup isolation...\n";
    assertTrue(
        $authorization->businessRole(
            $ownerId,
            $tenantOneId,
            $businessOneId
        ) === 'owner',
        'Owner business role should be returned correctly.'
    );
    assertTrue(
        $authorization->businessRole(
            $ownerId,
            $tenantTwoId,
            $businessTwoId
        ) === null,
        'Business role lookup must respect tenant boundaries.'
    );

    $db->rollBack();

    echo "Authorization tests passed.\n";
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    throw $e;
}
