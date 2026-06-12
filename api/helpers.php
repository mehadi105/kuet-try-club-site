<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatArticleContent(string $content): string
{
    $content = trim($content);
    if ($content === '') {
        return '';
    }

    $blocks = preg_split('/\n\s*\n/', $content) ?: [];
    $html = '';

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }

        if (isArticleQuoteBlock($block)) {
            $html .= '<blockquote class="article-quote"><p>' . nl2br(e($block)) . '</p></blockquote>';
            continue;
        }

        if (isArticleSubheading($block)) {
            $html .= '<h2 class="article-subheading">' . e($block) . '</h2>';
            continue;
        }

        if (isArticleInfoBlock($block)) {
            $html .= formatArticleInfoBlock($block);
            continue;
        }

        if (isArticleContactLine($block)) {
            $html .= '<div class="article-callout"><p>' . nl2br(e($block)) . '</p></div>';
            continue;
        }

        $html .= '<p>' . nl2br(e($block)) . '</p>';
    }

    return $html;
}

function isArticleQuoteBlock(string $block): bool
{
    if (str_contains($block, "\n")) {
        return false;
    }

    return str_starts_with($block, '‘')
        || str_starts_with($block, "'")
        || str_starts_with($block, '"')
        || str_contains($block, '(সূরা');
}

function isArticleSubheading(string $block): bool
{
    if (str_contains($block, "\n")) {
        return false;
    }

    if (mb_strlen($block) > 72) {
        return false;
    }

    if (preg_match('/\+?\d{7,}|@|Account|Bank|bKash|Nagad|SWIFT/i', $block)) {
        return false;
    }

    return (bool) preg_match(
        '/^(একটি মানবিক আবেদন|মানবিক সহায়তায় এগিয়ে আসুন)/u',
        $block
    );
}

function isArticleContactLine(string $block): bool
{
    return !str_contains($block, "\n")
        && (bool) preg_match('/Mobile Banking|bKash|Nagad/i', $block);
}

function isArticleInfoBlock(string $block): bool
{
    $lines = array_values(array_filter(array_map('trim', explode("\n", $block))));
    if (count($lines) < 2) {
        return false;
    }

    $kvLines = 0;
    foreach ($lines as $line) {
        if (preg_match('/^[^:]+:\s*.+$/', $line)) {
            $kvLines++;
        }
    }

    if ($kvLines < 2) {
        return false;
    }

    return (bool) preg_match(
        '/Account|Bank|Branch|Routing|SWIFT|bKash|Nagad|Mobile Banking/i',
        $block
    );
}

function formatArticleInfoBlock(string $block): string
{
    $lines = array_values(array_filter(array_map('trim', explode("\n", $block))));
    $title = '';

    if ($lines !== [] && preg_match('/^([^:]+):\s*$/u', $lines[0], $matches)) {
        $title = trim($matches[1]);
        array_shift($lines);
    }

    $html = '<div class="article-info-box">';
    if ($title !== '') {
        $html .= '<h3 class="article-info-title">' . e($title) . '</h3>';
    }
    $html .= '<dl class="article-dl">';

    foreach ($lines as $line) {
        if (preg_match('/^([^:]+):\s*(.+)$/u', $line, $matches)) {
            $html .= '<div class="article-dl-row">';
            $html .= '<dt>' . e(trim($matches[1])) . '</dt>';
            $html .= '<dd>' . e(trim($matches[2])) . '</dd>';
            $html .= '</div>';
            continue;
        }
        $html .= '<div class="article-dl-row article-dl-row-full"><dd>' . e($line) . '</dd></div>';
    }

    $html .= '</dl></div>';

    return $html;
}

function formatArticleDate(?string $timestamp): string
{
    if ($timestamp === null || $timestamp === '') {
        return '';
    }

    try {
        $dt = new DateTimeImmutable($timestamp);
        return $dt->format('F j, Y');
    } catch (Exception) {
        return '';
    }
}

function isExternalUrl(string $url): bool
{
    return (bool) preg_match('/^https?:\/\//i', $url);
}

function articleImageAlt(string $title, string $tag): string
{
    $title = trim($title);
    if ($title !== '') {
        return $title;
    }

    return trim($tag) !== '' ? $tag : 'TRY KUET story image';
}

/**
 * @return list<array{url: string, caption: string}>
 */
function eventGalleryTiles(mixed $galleryRaw): array
{
    return postDisplayImages('', $galleryRaw);
}

function postDisplayImages(string $imageUrl, mixed $galleryRaw): array
{
    $images = [];
    $seen = [];

    $add = static function (string $url, string $caption = '') use (&$images, &$seen): void {
        $url = trim($url);
        if ($url === '' || isset($seen[$url])) {
            return;
        }
        $seen[$url] = true;
        $images[] = ['url' => $url, 'caption' => trim($caption)];
    };

    if (is_string($galleryRaw) && $galleryRaw !== '') {
        $decoded = json_decode($galleryRaw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $add($item);
                    continue;
                }
                if (is_array($item)) {
                    $add((string) ($item['url'] ?? ''), (string) ($item['caption'] ?? ''));
                }
            }
        }
    } elseif (is_array($galleryRaw)) {
        foreach ($galleryRaw as $item) {
            if (is_string($item)) {
                $add($item);
                continue;
            }
            if (is_array($item)) {
                $add((string) ($item['url'] ?? ''), (string) ($item['caption'] ?? ''));
            }
        }
    }

    if ($images === [] && $imageUrl !== '') {
        $add($imageUrl);
    }

    return $images;
}

function encodeGalleryImages(array $items): string
{
    $normalized = [];
    foreach ($items as $item) {
        if (is_string($item)) {
            $url = trim($item);
            if ($url !== '') {
                $normalized[] = ['url' => $url, 'caption' => ''];
            }
            continue;
        }
        if (!is_array($item)) {
            continue;
        }
        $url = trim((string) ($item['url'] ?? ''));
        if ($url === '') {
            continue;
        }
        $normalized[] = [
            'url' => $url,
            'caption' => trim((string) ($item['caption'] ?? '')),
        ];
    }

    return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
}

function parseGalleryImagesInput(string $input): array
{
    $items = [];
    foreach (preg_split('/\r\n|\r|\n/', $input) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (str_contains($line, '|')) {
            [$url, $caption] = array_map('trim', explode('|', $line, 2));
            $items[] = ['url' => $url, 'caption' => $caption];
            continue;
        }
        $items[] = ['url' => $line, 'caption' => ''];
    }

    return $items;
}

function galleryImagesToInput(mixed $galleryRaw): string
{
    $lines = [];
    foreach (postDisplayImages('', $galleryRaw) as $item) {
        $line = $item['url'];
        if ($item['caption'] !== '') {
            $line .= ' | ' . $item['caption'];
        }
        $lines[] = $line;
    }

    return implode("\n", $lines);
}
