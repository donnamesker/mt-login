<?php

declare(strict_types=1);

return [
    'name' => $_ENV['APP_NAME'] ?? 'My Application',
    'environment' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var(
        $_ENV['APP_DEBUG'] ?? false,
        FILTER_VALIDATE_BOOLEAN
    ),
];