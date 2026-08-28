<?php

ob_start();

require __DIR__ . '/../bootstrap.php';

use App\Support\Csrf;

$csrf = new Csrf();

echo "Generating CSRF token...\n";

$token = $csrf->token();

if (strlen($token) === 64) {
    echo "Token generated correctly.\n";
} else {
    echo "ERROR: Token has unexpected length.\n";
}

echo "\nTesting valid token...\n";

if ($csrf->verify($token)) {
    echo "Valid token accepted correctly.\n";
} else {
    echo "ERROR: Valid token rejected.\n";
}

echo "\nTesting invalid token...\n";

if (!$csrf->verify('invalid-token')) {
    echo "Invalid token rejected correctly.\n";
} else {
    echo "ERROR: Invalid token accepted.\n";
}

echo "\nTesting missing token...\n";

if (!$csrf->verify(null)) {
    echo "Missing token rejected correctly.\n";
} else {
    echo "ERROR: Missing token accepted.\n";
}

echo "\nTesting regenerated token...\n";

$newToken = $csrf->regenerate();

if (
    $newToken !== $token
    && $csrf->verify($newToken)
    && !$csrf->verify($token)
) {
    echo "Token regeneration works correctly.\n";
} else {
    echo "ERROR: Token regeneration failed.\n";
}

ob_end_flush();