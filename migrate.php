<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Database\Migrator;

try {
    $migrator = new Migrator();
    $migrator->run();

    echo 'Migrations complete.' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(
        STDERR,
        'Migration failed: ' . $e->getMessage() . PHP_EOL
    );

    exit(1);
}