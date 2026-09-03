<?php

declare(strict_types=1);

$tests = [
    'auth-helper-test.php',
    'auth-test.php',
    'authorization-test.php',
    'context-switch-test.php',
    'csrf-test.php',
    'onboarding-test.php',
    'session-context-test.php',
    'session-test.php',
    'user-test.php',
];

$php = PHP_BINARY;
$failed = false;

foreach ($tests as $test) {
    echo PHP_EOL;
    echo str_repeat('=', 72) . PHP_EOL;
    echo "Running {$test}" . PHP_EOL;
    echo str_repeat('=', 72) . PHP_EOL;

    $command = escapeshellarg($php) . ' ' . escapeshellarg(__DIR__ . '/' . $test);
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        $failed = true;
        echo "FAILED: {$test}" . PHP_EOL;
        break;
    }

    echo "PASSED: {$test}" . PHP_EOL;
}

echo PHP_EOL;

if ($failed) {
    echo "Test suite failed." . PHP_EOL;
    exit(1);
}

echo "All tests passed." . PHP_EOL;
