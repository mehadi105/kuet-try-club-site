<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function defaultSiteSettings(): array
{
    return [
        'hero_eyebrow' => 'Featured',
        'hero_title' => 'Emergency response & community support',
        'hero_subtitle' => 'A simple structure inspired by the reference layout. Replace this text with a verified TRY update headline and short summary.',
        'hero_image' => './public/639984083_1360235046146231_4377309201098488597_n.jpg',
        'hero_cta_primary_label' => 'Join TRY',
        'hero_cta_primary_url' => './join.html',
        'hero_cta_secondary_label' => 'Read more',
        'hero_cta_secondary_url' => '#updates',
        'updates_title' => 'TRY, for the community',
        'updates_subtitle' => 'Latest posts and activities (structured as cards for quick updates).',
        'work_title' => 'What TRY does',
        'work_text' => 'KUET students volunteer through TRY to support education, emergency aid, seasonal relief, and on-campus community service — planned, verified, and reported with transparency.',
        'work_cta_label' => 'See recent stories',
        'work_cta_url' => '#updates',
        'spotlight_title' => 'Recent events',
        'spotlight_subtitle' => 'TRY activities, distributions, and campus events — photo highlights and short reports.',
        'inspiration_title' => 'Inspirational stories',
        'inspiration_subtitle' => 'Watch stories of service, compassion, and community impact from TRY and related work.',
        'youtube_playlist_id' => 'PLMV2DVHuQ9Ii1-yeok6bpsto4gOh3k7c_',
        'youtube_title' => 'TRY story playlist',
        'youtube_caption' => 'A curated playlist of inspirational moments and humanitarian work. Use the player controls to browse videos in the series.',
        'subscribe_title' => 'Subscribe to updates',
        'subscribe_text' => 'Get notified about TRY activities, volunteer calls, and community updates.',
        'subscribe_image' => './public/subscribe-community.png',
        'join_cta_title' => 'Want to volunteer with TRY?',
        'join_cta_text' => 'KUET students can apply through our membership form.',
        'contact_title' => 'Contact & official links',
        'contact_subtitle' => 'Add verified links (Facebook page/group, forms, and official post URLs).',
        'facebook_page_url' => 'https://www.facebook.com/try.kuet',
        'facebook_group_url' => 'https://www.facebook.com/groups/try.general',
        'donation_url' => './appeal-request.html',
    ];
}

/**
 * Grouped field definitions for the admin settings UI.
 *
 * @return list<array<string, mixed>>
 */
function siteSettingsGroups(): array
{
    return [
        [
            'id' => 'hero',
            'title' => 'Hero banner',
            'description' => 'The large featured area at the top of the homepage — image, headline, and two action buttons.',
            'preview' => '../index.html#main',
            'managed_elsewhere' => 'Story cards in the hero area come from Admin → Posts, not from these fields.',
            'fields' => [
                [
                    'row' => [
                        field('hero_eyebrow', 'Eyebrow label', 'text', 'Small label above the main headline.', 'Homepage → hero eyebrow', 'Featured'),
                        field('hero_image', 'Hero image path', 'url', 'Path or URL to the banner image. Use ./public/your-file.jpg for files in the public folder.', 'Homepage → hero background image', './public/your-photo.jpg'),
                    ],
                ],
                field('hero_title', 'Main headline', 'text', 'Large title visitors see first.', 'Homepage → hero title (h1)', 'Scholarship drive 2026'),
                field('hero_subtitle', 'Subtitle', 'textarea', 'Short summary under the headline. Keep to 1–3 sentences.', 'Homepage → hero subtitle', ''),
                [
                    'row' => [
                        field('hero_cta_primary_label', 'Primary button text', 'text', 'Main call-to-action (filled button).', 'Homepage → hero primary button label', 'Join TRY'),
                        field('hero_cta_primary_url', 'Primary button link', 'url', 'Where the primary button goes. Use ./join.html for pages or #updates for sections.', 'Homepage → hero primary button URL', './join.html'),
                    ],
                ],
                [
                    'row' => [
                        field('hero_cta_secondary_label', 'Secondary button text', 'text', 'Outline button next to the primary action.', 'Homepage → hero secondary button label', 'Read more'),
                        field('hero_cta_secondary_url', 'Secondary button link', 'url', 'Usually a section anchor like #updates or #work.', 'Homepage → hero secondary button URL', '#updates'),
                    ],
                ],
            ],
        ],
        [
            'id' => 'updates',
            'title' => 'News & stories section',
            'description' => 'Heading text above the post cards on the homepage. The site shows the latest 6 posts; the “See more” button uses your Facebook page URL from Contact & links.',
            'preview' => '../index.html#updates',
            'managed_elsewhere' => 'Individual news cards are managed under Admin → Posts (only the 6 most recent appear on the homepage).',
            'fields' => [
                field('updates_title', 'Section title', 'text', 'Heading for the updates / news area.', 'Homepage → #updates title', 'TRY, for the community'),
                field('updates_subtitle', 'Section subtitle', 'textarea', 'Short line under the updates heading.', 'Homepage → #updates subtitle', ''),
            ],
        ],
        [
            'id' => 'work',
            'title' => 'What TRY does section',
            'description' => 'Intro text and main button in the work / programs area.',
            'preview' => '../index.html#work',
            'managed_elsewhere' => 'The four program cards (scholarship, medical aid, etc.) are fixed in the site template. Only the title, intro paragraph, and first button are editable here.',
            'fields' => [
                field('work_title', 'Section title', 'text', 'Heading for the work area.', 'Homepage → #work title', 'What TRY does'),
                field('work_text', 'Intro paragraph', 'textarea', 'Short overview of TRY’s activities shown above the program cards.', 'Homepage → #work intro text', ''),
                [
                    'row' => [
                        field('work_cta_label', 'Main button text', 'text', 'Label for the primary button below the program cards.', 'Homepage → #work “See recent stories” button', 'See recent stories'),
                        field('work_cta_url', 'Main button link', 'url', 'Where that button goes. #updates shows the news cards.', 'Homepage → #work main button URL', '#updates'),
                    ],
                ],
            ],
        ],
        [
            'id' => 'spotlight',
            'title' => 'Recent events section',
            'description' => 'Heading text above recent event cards on the homepage.',
            'preview' => '../index.html#recent-events',
            'managed_elsewhere' => 'Individual events are managed under Admin → Recent events.',
            'fields' => [
                field('spotlight_title', 'Section title', 'text', 'Heading for the recent events grid.', 'Homepage → #recent-events title', 'Recent events'),
                field('spotlight_subtitle', 'Section subtitle', 'textarea', 'Short line under the recent events heading.', 'Homepage → #recent-events subtitle', ''),
            ],
        ],
        [
            'id' => 'inspiration',
            'title' => 'Inspirational stories (YouTube)',
            'description' => 'YouTube playlist embed and captions in the video section.',
            'preview' => '../index.html#inspiration',
            'fields' => [
                field('inspiration_title', 'Section title', 'text', 'Heading above the video player.', 'Homepage → #inspiration title', 'Inspirational stories'),
                field('inspiration_subtitle', 'Section subtitle', 'textarea', 'Short line under the inspiration heading.', 'Homepage → #inspiration subtitle', ''),
                field('youtube_playlist_id', 'YouTube playlist ID', 'text', 'The ID after list= in a YouTube playlist URL. Example: youtube.com/playlist?list=PLxxx → use PLxxx', 'Homepage → embedded playlist', 'PLMV2DVHuQ9Ii1-yeok6bpsto4gOh3k7c_'),
                field('youtube_title', 'Video card title', 'text', 'Title shown under the player.', 'Homepage → video card title', 'TRY story playlist'),
                field('youtube_caption', 'Video card caption', 'textarea', 'Description text under the video title.', 'Homepage → video card caption', ''),
            ],
        ],
        [
            'id' => 'subscribe',
            'title' => 'Subscribe & volunteer CTA',
            'description' => 'Email subscribe block and the “Want to volunteer?” call-to-action before contact.',
            'preview' => '../index.html#subscribe',
            'managed_elsewhere' => 'Subscribe emails are stored under Admin → Subscribers. The join form itself is at join.html.',
            'fields' => [
                field('subscribe_title', 'Subscribe heading', 'text', 'Title for the email signup area.', 'Homepage → #subscribe title', 'Subscribe to updates'),
                field('subscribe_text', 'Subscribe description', 'textarea', 'Text above the email input.', 'Homepage → #subscribe text', ''),
                field('subscribe_image', 'Subscribe section image', 'url', 'Wide photo beside the email signup panel. Use ./public/your-file.png for files in public/.', 'Homepage → #subscribe image', './public/subscribe-community.png'),
                field('join_cta_title', 'Volunteer CTA heading', 'text', 'Title in the join call-to-action band.', 'Homepage → volunteer CTA title', 'Want to volunteer with TRY?'),
                field('join_cta_text', 'Volunteer CTA text', 'textarea', 'Supporting text above the Join TRY button (button link is fixed to join.html).', 'Homepage → volunteer CTA text', ''),
            ],
        ],
        [
            'id' => 'contact',
            'title' => 'Contact & official links',
            'description' => 'Contact section headings and the official links list (Facebook, appeal form).',
            'preview' => '../index.html#contact',
            'managed_elsewhere' => 'Contact form messages go to Admin → Messages. Appeal submissions go to Admin → Appeal requests.',
            'fields' => [
                field('contact_title', 'Section title', 'text', 'Heading for the contact area.', 'Homepage → #contact title', 'Contact & official links'),
                field('contact_subtitle', 'Section subtitle', 'textarea', 'Short line under the contact heading.', 'Homepage → #contact subtitle', ''),
                field('facebook_page_url', 'Facebook page URL', 'url', 'Full link to TRY’s Facebook page. Opens in a new tab when external.', 'Homepage → contact links → Facebook page', 'https://www.facebook.com/try.kuet'),
                field('facebook_group_url', 'Facebook group URL', 'url', 'Link to TRY’s Facebook group, if you have one.', 'Homepage → contact links → Facebook group', 'https://www.facebook.com/groups/try.general'),
                field('donation_url', 'Appeal request form URL', 'url', 'Link for “Request donation/appeal” in the contact section.', 'Homepage → contact links → Request donation/appeal', './appeal-request.html'),
            ],
        ],
    ];
}

/**
 * @return list<string>
 */
function siteSettingKeys(): array
{
    $keys = [];
    foreach (siteSettingsGroups() as $group) {
        foreach (flattenSettingsFields($group['fields']) as $field) {
            $keys[] = $field['key'];
        }
    }

    return $keys;
}

/**
 * @param list<array<string, mixed>> $fields
 * @return list<array<string, mixed>>
 */
function flattenSettingsFields(array $fields): array
{
    $flat = [];
    foreach ($fields as $item) {
        if (isset($item['row']) && is_array($item['row'])) {
            foreach ($item['row'] as $rowField) {
                $flat[] = $rowField;
            }
            continue;
        }
        $flat[] = $item;
    }

    return $flat;
}

/**
 * @return array<string, mixed>
 */
function field(
    string $key,
    string $label,
    string $type,
    string $help,
    string $affects,
    string $placeholder = ''
): array {
    return [
        'key' => $key,
        'label' => $label,
        'type' => $type,
        'help' => $help,
        'affects' => $affects,
        'placeholder' => $placeholder,
    ];
}

function getAllSettings(PDO $pdo): array
{
    $settings = defaultSiteSettings();
    $stmt = $pdo->query('SELECT key, value FROM site_settings');
    foreach ($stmt->fetchAll() as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function getSetting(PDO $pdo, string $key, ?string $default = null): string
{
    $defaults = defaultSiteSettings();
    $fallback = $default ?? ($defaults[$key] ?? '');

    $stmt = $pdo->prepare('SELECT value FROM site_settings WHERE key = :key');
    $stmt->execute([':key' => $key]);
    $value = $stmt->fetchColumn();

    if ($value === false) {
        return $fallback;
    }

    return (string) $value;
}

function setSetting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO site_settings (key, value, updated_at)
         VALUES (:key, :value, NOW())
         ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = NOW()'
    );
    $stmt->execute([':key' => $key, ':value' => $value]);
}

function saveSettings(PDO $pdo, array $data): void
{
    $allowed = array_fill_keys(siteSettingKeys(), true);

    foreach ($data as $key => $value) {
        if (!isset($allowed[$key])) {
            continue;
        }
        setSetting($pdo, (string) $key, trim((string) $value));
    }
}

function resetSettingsGroup(PDO $pdo, string $groupId): bool
{
    $defaults = defaultSiteSettings();

    foreach (siteSettingsGroups() as $group) {
        if (($group['id'] ?? '') !== $groupId) {
            continue;
        }

        foreach (flattenSettingsFields($group['fields']) as $field) {
            $key = (string) $field['key'];
            if (array_key_exists($key, $defaults)) {
                setSetting($pdo, $key, $defaults[$key]);
            }
        }

        return true;
    }

    return false;
}

function resetAllSiteSettings(PDO $pdo): void
{
    foreach (defaultSiteSettings() as $key => $value) {
        setSetting($pdo, $key, $value);
    }
}

function findSettingsGroup(string $groupId): ?array
{
    foreach (siteSettingsGroups() as $group) {
        if (($group['id'] ?? '') === $groupId) {
            return $group;
        }
    }

    return null;
}
