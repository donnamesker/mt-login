<?php

require __DIR__ . '/../bootstrap.php';

use App\Models\User;

$userModel = new User();

$user = $userModel->findByEmail('nobody@example.com');

if ($user === null) {
    echo "User model is working. No matching user found.\n";
} else {
    echo "User found: {$user['name']}\n";
}