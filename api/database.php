<?php

declare(strict_types=1);

function getConfig(): array
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function getDb(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = getConfig()['db'];
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $db['host'],
        $db['port'],
        $db['name']
    );

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    initSchema($pdo);

    return $pdo;
}

function initSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS join_applications (
            id SERIAL PRIMARY KEY,
            fullname VARCHAR(120) NOT NULL,
            roll VARCHAR(20) NOT NULL UNIQUE,
            department VARCHAR(80) NOT NULL,
            batch VARCHAR(20) NOT NULL,
            semester VARCHAR(10),
            blood_group VARCHAR(10),
            email VARCHAR(120) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            facebook VARCHAR(300),
            hall VARCHAR(120),
            why_join TEXT NOT NULL,
            experience TEXT,
            skills JSONB,
            other_skills VARCHAR(300),
            weekly_hours VARCHAR(20) NOT NULL,
            meetings VARCHAR(20) NOT NULL,
            emergency_name VARCHAR(120),
            emergency_phone VARCHAR(20),
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );
}
