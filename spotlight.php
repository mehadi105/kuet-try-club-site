<?php

declare(strict_types=1);

require_once __DIR__ . '/api/database.php';
require_once __DIR__ . '/api/helpers.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    exit('Spotlight item not found.');
}

$pdo = getDb();
$stmt = $pdo->prepare(
    'SELECT id, title, summary, content, image_url, gallery_images, link_url, created_at
     FROM spotlight_items
     WHERE id = :id AND is_published = TRUE'
);
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Spotlight item not found.');
}

$content = trim((string) $item['content']);
if ($content === '') {
    $content = (string) $item['summary'];
}

$bodyHtml = formatArticleContent($content);
if ($bodyHtml === '') {
    $bodyHtml = '<p>' . nl2br(e((string) $item['summary'])) . '</p>';
}

$pageTitle = (string) $item['title'];
$metaDescription = (string) $item['summary'];
$eyebrow = 'Recent Events';
$title = (string) $item['title'];
$subtitle = (string) $item['summary'];
$backUrl = './index.html#recent-events';
$backLabel = '← Back to recent events';
$articleLayout = 'event';
$breadcrumbSection = 'Recent events';
$breadcrumbSectionUrl = './index.html#recent-events';
$imageUrl = trim((string) ($item['image_url'] ?? ''));
$gridImages = eventGalleryTiles($item['gallery_images'] ?? null);
$featuredImageUrl = $imageUrl !== '' ? $imageUrl : ($gridImages[0]['url'] ?? '');
$externalUrl = trim((string) ($item['link_url'] ?? ''));
$externalLabel = 'View on Facebook →';
$publishedDate = formatArticleDate((string) $item['created_at']);
$imageAlt = articleImageAlt($title, $eyebrow);

$relatedEventsStmt = $pdo->prepare(
    'SELECT id, title, image_url, created_at
     FROM spotlight_items
     WHERE is_published = TRUE AND id != :id
     ORDER BY created_at DESC, sort_order ASC
     LIMIT 5'
);
$relatedEventsStmt->execute([':id' => $id]);
$relatedEvents = $relatedEventsStmt->fetchAll() ?: [];

require __DIR__ . '/includes/article-detail.php';
