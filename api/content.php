<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/database.php';

try {
    $pdo = getDb();

    $posts = $pdo->query(
        'SELECT id, tag, title, excerpt, image_url, link_url, link_label, sort_order, created_at
         FROM posts
         WHERE is_published = TRUE
         ORDER BY sort_order ASC, created_at DESC'
    )->fetchAll();

    $spotlight = $pdo->query(
        'SELECT id, title, summary, image_url, link_url, sort_order, created_at
         FROM spotlight_items
         WHERE is_published = TRUE
         ORDER BY sort_order ASC, created_at DESC'
    )->fetchAll();

    echo json_encode([
        'success' => true,
        'settings' => getAllSettings($pdo),
        'posts' => $posts,
        'spotlight' => $spotlight,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not load site content.',
    ]);
}
