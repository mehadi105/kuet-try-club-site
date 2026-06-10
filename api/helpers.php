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
        $html .= '<p>' . nl2br(e($block)) . '</p>';
    }

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
