<?php

declare(strict_types=1);

function appealCaseTypes(): array
{
    return [
        'medical' => 'Medical treatment',
        'education' => 'Education support',
        'emergency' => 'Emergency relief',
        'other' => 'Other',
    ];
}

function appealStatuses(): array
{
    return [
        'pending' => 'Pending review',
        'under_review' => 'Under review',
        'approved' => 'Approved (not published)',
        'rejected' => 'Rejected',
        'published' => 'Published as post',
    ];
}

function isValidAppealCaseType(string $type): bool
{
    return array_key_exists($type, appealCaseTypes());
}

function isValidAppealStatus(string $status): bool
{
    return array_key_exists($status, appealStatuses());
}

function formatAppealCaseType(string $type): string
{
    return appealCaseTypes()[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

function formatAppealStatus(string $status): string
{
    return appealStatuses()[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function appealStatusBadgeClass(string $status): string
{
    return match ($status) {
        'published' => 'badge-success',
        'approved' => 'badge-info',
        'rejected' => 'badge-danger',
        'under_review' => 'badge-warning',
        default => 'badge-unread',
    };
}

function appealTagForCaseType(string $caseType): string
{
    return match ($caseType) {
        'education' => 'Scholarship',
        'medical', 'emergency' => 'Emergency aid',
        default => 'Appeal',
    };
}

function buildPostDraftFromAppeal(array $appeal): array
{
    $beneficiary = trim((string) ($appeal['beneficiary_name'] ?? ''));
    $caseType = (string) ($appeal['case_type'] ?? 'other');
    $description = trim((string) ($appeal['description'] ?? ''));
    $target = trim((string) ($appeal['target_amount'] ?? ''));
    $location = trim((string) ($appeal['location'] ?? ''));

    $caseLabel = formatAppealCaseType($caseType);
    $title = $beneficiary !== ''
        ? $caseLabel . ' for ' . $beneficiary
        : $caseLabel . ' appeal';

    $excerpt = mb_strimwidth($description, 0, 220, '…');
    if ($excerpt === '') {
        $excerpt = 'Fundraising appeal submitted to TRY KUET for review.';
    }

    $contentParts = [$description];
    if ($target !== '') {
        $contentParts[] = 'Target amount: ' . $target;
    }
    if ($location !== '') {
        $contentParts[] = 'Location: ' . $location;
    }
    $contentParts[] = 'Payment and donation details are shared only through TRY’s verified official posts.';

    return [
        'tag' => appealTagForCaseType($caseType),
        'title' => $title,
        'excerpt' => $excerpt,
        'content' => implode("\n\n", $contentParts),
        'image_url' => trim((string) ($appeal['photo_path'] ?? '')),
        'link_url' => '',
        'link_label' => 'Donate via official post →',
        'sort_order' => 0,
        'is_published' => false,
    ];
}
