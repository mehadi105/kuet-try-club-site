<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/database.php';

try {
    $pdo = getDb();
    $pdo->query('SELECT 1');
    echo json_encode([
        'success' => true,
        'message' => 'PHP backend is running.',
        'php_version' => PHP_VERSION,
        'database' => 'connected',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Backend error.',
        'php_version' => PHP_VERSION,
    ]);
}
