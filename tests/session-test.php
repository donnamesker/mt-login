<?php

ob_start();

require __DIR__ . '/../bootstrap.php';

use App\Services\SessionService;

$session = new SessionService();

$session->start();

echo "Session started.\n";

$session->login(1);

if (!$session->isAuthenticated()) {
    ob_end_clean();
    exit("ERROR: User was not authenticated.\n");
}

echo "User is authenticated.\n";
echo "User ID: " . $session->userId() . "\n";

$session->logout();

if ($session->isAuthenticated()) {
    ob_end_clean();
    exit("ERROR: User is still authenticated.\n");
}

echo "User logged out successfully.\n";

ob_end_flush();