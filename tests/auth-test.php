<?php

require __DIR__ . '/../bootstrap.php';

use App\Services\AuthService;

$auth = new AuthService();

$email = 'test@example.com';
$password = 'ThisIsATestPassword123!';

echo "Testing registration...\n";

try {
    $userId = $auth->register(
        'Test User',
        $email,
        $password
    );

    echo "User created with ID: {$userId}\n";
} catch (Throwable $e) {
    echo "Registration: {$e->getMessage()}\n";
}

echo "\nTesting authentication...\n";

$user = $auth->authenticate(
    $email,
    $password
);

if ($user !== null) {
    echo "Authentication successful!\n";
    echo "User ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Email: {$user['email']}\n";
} else {
    echo "Authentication failed.\n";
}

echo "\nTesting incorrect password...\n";

$user = $auth->authenticate(
    $email,
    'WrongPassword123!'
);

if ($user === null) {
    echo "Incorrect password rejected correctly.\n";
} else {
    echo "ERROR: Incorrect password was accepted!\n";
}