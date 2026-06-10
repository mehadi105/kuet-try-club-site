<?php

declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

return [
    'app_env' => getenv('APP_ENV') ?: 'development',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '5432',
        'name' => getenv('DB_NAME') ?: 'try_kuet',
        'user' => getenv('DB_USER') ?: getenv('USER') ?: 'postgres',
        'pass' => getenv('DB_PASS') ?: '',
    ],
];
