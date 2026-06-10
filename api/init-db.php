<?php

declare(strict_types=1);

/**
 * One-time setup: creates the PostgreSQL database and tables.
 * Run: php api/init-db.php
 */

$config = require __DIR__ . '/config.php';
$db = $config['db'];

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=postgres', $db['host'], $db['port']);

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $dbName = $db['name'];
    $stmt = $pdo->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
    $stmt->execute([':name' => $dbName]);

    if (!$stmt->fetchColumn()) {
        $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);
        $pdo->exec("CREATE DATABASE {$safeName} ENCODING 'UTF8'");
        echo "Database '{$safeName}' created.\n";
    } else {
        echo "Database '{$dbName}' already exists.\n";
    }

    require_once __DIR__ . '/database.php';
    getDb();

    echo "Tables are ready (applications, posts, spotlight, messages, subscribers, settings).\n";
    echo "Admin panel: http://localhost:8000/admin/login.php\n";
    echo "PostgreSQL setup complete.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "Setup failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Start PostgreSQL: brew services start postgresql@17\n");
    exit(1);
}
