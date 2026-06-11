<?php

declare(strict_types=1);

require_once __DIR__ . '/api/database.php';
require_once __DIR__ . '/api/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Post not found.');
}

$pdo = getDb();
$stmt = $pdo->prepare(
    'SELECT id, tag, title, excerpt, content, image_url, gallery_images, link_url, link_label, created_at
     FROM posts
     WHERE id = :id AND is_published = TRUE'
);
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}

$content = trim((string) $post['content']);
if ($content === '') {
    $content = (string) $post['excerpt'];
}

$bodyHtml = formatArticleContent($content);
if ($bodyHtml === '') {
    $bodyHtml = '<p>' . nl2br(e((string) $post['excerpt'])) . '</p>';
}

$pageTitle = (string) $post['title'];
$metaDescription = (string) $post['excerpt'];
$eyebrow = (string) $post['tag'];
$title = (string) $post['title'];
$subtitle = (string) $post['excerpt'];
$backUrl = './index.html#updates';
$backLabel = '← Back to news & stories';
$imageUrl = trim((string) ($post['image_url'] ?? ''));
$displayImages = postDisplayImages($imageUrl, $post['gallery_images'] ?? null);
$externalUrl = trim((string) ($post['link_url'] ?? ''));
$externalLabel = trim((string) ($post['link_label'] ?? 'Related link →'));
$publishedDate = formatArticleDate((string) $post['created_at']);
$imageAlt = articleImageAlt($title, $eyebrow);

$relatedStmt = $pdo->prepare(
    'SELECT id, tag, title, excerpt, image_url, created_at
     FROM posts
     WHERE is_published = TRUE AND id != :id
     ORDER BY sort_order ASC, created_at DESC
     LIMIT 3'
);
$relatedStmt->execute([':id' => $id]);
$relatedPosts = $relatedStmt->fetchAll() ?: [];

require __DIR__ . '/includes/article-detail.php';
