<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);

$dotenv->safeLoad();

/*
 * Configure application session storage.
 *
 * PHP's default session.save_path may be unset in some local
 * development environments. Store sessions inside the application
 * so the application does not depend on the machine's global PHP
 * session configuration.
 */
$sessionPath = __DIR__ . '/storage/sessions';

if (!is_dir($sessionPath)) {
    if (!mkdir($sessionPath, 0700, true) && !is_dir($sessionPath)) {
        throw new RuntimeException(
            'Unable to create session storage directory.'
        );
    }
}

if (!is_writable($sessionPath)) {
    throw new RuntimeException(
        'Session storage directory is not writable.'
    );
}

session_save_path($sessionPath);
