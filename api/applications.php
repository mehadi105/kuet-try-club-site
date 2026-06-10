<?php

declare(strict_types=1);

function applicationStatuses(): array
{
    return [
        'pending' => 'Pending',
        'interview_scheduled' => 'Interview scheduled',
        'waitlisted' => 'Waitlisted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];
}

function isValidApplicationStatus(string $status): bool
{
    return array_key_exists($status, applicationStatuses());
}

function formatApplicationStatus(string $status): string
{
    return applicationStatuses()[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function statusBadgeClass(string $status): string
{
    return match ($status) {
        'approved' => 'badge-success',
        'rejected' => 'badge-danger',
        'interview_scheduled' => 'badge-info',
        'waitlisted' => 'badge-warning',
        default => 'badge-unread',
    };
}

function skillLabels(): array
{
    return [
        'field_work' => 'Field work & distribution',
        'fundraising' => 'Fundraising & donations',
        'event' => 'Event management',
        'photography' => 'Photography / videography',
        'design' => 'Poster / graphic design',
        'writing' => 'Writing & reporting',
        'social_media' => 'Social media',
        'teaching' => 'Teaching / mentoring',
    ];
}

function formatSkillsDisplay($skills): string
{
    if ($skills === null || $skills === '') {
        return '—';
    }

    $decoded = is_string($skills) ? json_decode($skills, true) : $skills;
    if (!is_array($decoded) || $decoded === []) {
        return '—';
    }

    $labels = skillLabels();
    $parts = [];
    foreach ($decoded as $skill) {
        $skill = (string) $skill;
        $parts[] = $labels[$skill] ?? $skill;
    }

    return implode(', ', $parts);
}

function formatAdminDate(?string $timestamp): string
{
    if ($timestamp === null || $timestamp === '') {
        return '—';
    }

    try {
        return (new DateTimeImmutable($timestamp))->format('M j, Y g:i A');
    } catch (Exception) {
        return (string) $timestamp;
    }
}
