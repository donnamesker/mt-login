<?php

ob_start();

require __DIR__ . '/../bootstrap.php';

use App\Support\Auth;
use App\Services\SessionService;

$session = new SessionService();
$auth = new Auth();

$session->login(1);

echo "Authentication check: ";

if ($auth->check()) {
    echo "PASS\n";
} else {
    echo "FAIL\n";
}

echo "Authenticated user ID: ";

echo $auth->id() . "\n";

$user = $auth->user();

echo "Authenticated user name: ";

echo $user['name'] . "\n";

echo "Authenticated user email: ";

echo $user['email'] . "\n";

$auth->logout();

echo "After logout: ";

if (!$auth->check()) {
    echo "PASS\n";
} else {
    echo "FAIL\n";
}

ob_end_flush();