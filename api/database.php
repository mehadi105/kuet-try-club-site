<?php

declare(strict_types=1);

require_once __DIR__ . '/settings.php';

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
    seedDefaultContent($pdo);

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
            photo_path VARCHAR(500),
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
            status VARCHAR(30) NOT NULL DEFAULT \'pending\',
            admin_notes TEXT,
            reviewed_at TIMESTAMPTZ,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS posts (
            id SERIAL PRIMARY KEY,
            tag VARCHAR(50) NOT NULL,
            title VARCHAR(200) NOT NULL,
            excerpt TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT \'\',
            image_url VARCHAR(500),
            link_url VARCHAR(500),
            link_label VARCHAR(100) NOT NULL DEFAULT \'Read more →\',
            is_published BOOLEAN NOT NULL DEFAULT TRUE,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS spotlight_items (
            id SERIAL PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            summary TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT \'\',
            image_url VARCHAR(500),
            link_url VARCHAR(500),
            is_published BOOLEAN NOT NULL DEFAULT TRUE,
            sort_order INT NOT NULL DEFAULT 0,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );

    migrateSchema($pdo);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id SERIAL PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS subscribers (
            id SERIAL PRIMARY KEY,
            email VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS site_settings (
            key VARCHAR(100) PRIMARY KEY,
            value TEXT NOT NULL,
            updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );
}

function defaultSeedPosts(): array
{
    return [
        [
            'tag' => 'Announcement',
            'title' => 'KUET Vice-Chancellor greeting',
            'excerpt' => 'Congratulatory message for the newly appointed Vice-Chancellor, wishing strong leadership and continued progress for KUET.',
            'content' => "TRY KUET shared a congratulatory message for the newly appointed Vice-Chancellor of Khulna University of Engineering & Technology.\n\nThe club expressed hope for strong leadership, academic excellence, and continued support for student-led social welfare initiatives on campus.\n\nMembers noted that collaborative engagement between the administration and voluntary organizations like TRY can help scale community outreach, transparency, and impact.",
            'link_url' => '#contact',
            'link_label' => 'View official post →',
            'sort_order' => 1,
        ],
        [
            'tag' => 'Scholarship',
            'title' => 'Project: Scholarship support',
            'excerpt' => 'Monthly education support for deserving students through sponsorship and continued follow-up.',
            'content' => "TRY's scholarship project provides monthly education support for deserving students through sponsorship and follow-up.\n\nVolunteers coordinate with donors, verify needs, and track academic progress while maintaining dignity and privacy for beneficiaries.\n\nThe program focuses on sustained support rather than one-time aid, helping students stay in class and plan for the future.",
            'link_url' => '#contact',
            'link_label' => 'View official post →',
            'sort_order' => 2,
        ],
        [
            'tag' => 'Emergency aid',
            'title' => 'Medical support appeal',
            'excerpt' => 'Emergency fundraising appeal for treatment support. Link only to verified sources for the latest details.',
            'content' => "TRY periodically coordinates emergency medical support appeals for individuals and families in urgent need.\n\nEach appeal is shared with clear context, verified references, and transparent updates on fund collection and usage.\n\nCommunity members are encouraged to contribute only through official TRY posts and approved payment channels.",
            'link_url' => '#contact',
            'link_label' => 'View official post →',
            'sort_order' => 3,
        ],
        [
            'tag' => 'Seasonal support',
            'title' => 'Eid gift distribution',
            'excerpt' => 'Coordinating volunteers and distributions so families can celebrate Eid with dignity.',
            'content' => "Before Eid, TRY volunteers plan gift and essential item distributions for families who need support.\n\nTeams handle sourcing, packing, routing, and on-ground delivery with respect and care.\n\nThe goal is simple: help families celebrate Eid with dignity while building a culture of service among KUET students.",
            'link_url' => '#contact',
            'link_label' => 'View official post →',
            'sort_order' => 4,
        ],
        [
            'tag' => 'Volunteer',
            'title' => 'Join as a volunteer',
            'excerpt' => 'A simple path for students to contribute time, skills, and energy to social welfare work.',
            'content' => "KUET students can join TRY to contribute time, skills, and energy to social welfare work on and off campus.\n\nVolunteers support fundraising, field work, event management, design, writing, teaching, and more.\n\nIf you want to serve with purpose and learn teamwork through real community projects, apply through the membership form.",
            'link_url' => './join.html',
            'link_label' => 'Get involved →',
            'sort_order' => 5,
        ],
        [
            'tag' => 'Transparency',
            'title' => 'Updates & reporting',
            'excerpt' => 'Share verified links, photos, and short reporting notes after each activity for accountability.',
            'content' => "After each activity, TRY shares short reporting notes with photos, outcomes, and verified references whenever possible.\n\nThis helps donors, volunteers, and the wider KUET community see how resources were used and what changed on the ground.\n\nTransparency is part of TRY's commitment to responsible social service.",
            'link_url' => '#contact',
            'link_label' => 'How to report →',
            'sort_order' => 6,
        ],
    ];
}

function defaultSeedSpotlight(): array
{
    return [
        [
            'title' => 'Scholarship impact',
            'summary' => 'Short beneficiary progress updates with consent.',
            'content' => "TRY shares periodic scholarship impact updates when beneficiaries and families consent to publication.\n\nThese snapshots highlight attendance, exam progress, and personal milestones — always shared with care and respect.\n\nThe spotlight reminds donors that small monthly support can create long-term change.",
            'sort_order' => 1,
        ],
        [
            'title' => 'Volunteer highlights',
            'summary' => 'Team work, coordination, and distribution planning.',
            'content' => "Behind every distribution is a team: planners, fundraisers, packers, drivers, and on-ground volunteers.\n\nSpotlight stories recognize the coordination required to turn an idea into organized action.\n\nTRY celebrates students who show up consistently and support each other during demanding field work.",
            'sort_order' => 2,
        ],
        [
            'title' => 'Seasonal distributions',
            'summary' => 'Eid gifts, food support, and targeted community help.',
            'content' => "Seasonal drives focus on Eid gifts, food support, and targeted help for families facing sudden hardship.\n\nVolunteers map needs, prepare packages, and deliver with empathy.\n\nThese stories capture the human side of TRY's work beyond numbers and lists.",
            'sort_order' => 3,
        ],
    ];
}

function migrateSchema(PDO $pdo): void
{
    $pdo->exec('ALTER TABLE posts ADD COLUMN IF NOT EXISTS content TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('ALTER TABLE spotlight_items ADD COLUMN IF NOT EXISTS content TEXT NOT NULL DEFAULT \'\'');
    $pdo->exec('UPDATE posts SET content = excerpt WHERE content = \'\'');
    $pdo->exec('UPDATE spotlight_items SET content = summary WHERE content = \'\'');

    $pdo->exec('ALTER TABLE join_applications ADD COLUMN IF NOT EXISTS status VARCHAR(30) NOT NULL DEFAULT \'pending\'');
    $pdo->exec('ALTER TABLE join_applications ADD COLUMN IF NOT EXISTS admin_notes TEXT');
    $pdo->exec('ALTER TABLE join_applications ADD COLUMN IF NOT EXISTS reviewed_at TIMESTAMPTZ');
    $pdo->exec('UPDATE join_applications SET status = \'pending\' WHERE status IS NULL OR status = \'\'');
    $pdo->exec('ALTER TABLE join_applications ADD COLUMN IF NOT EXISTS photo_path VARCHAR(500)');

    $pdo->exec(
        "UPDATE site_settings SET value = './appeal-request.html'
         WHERE key = 'donation_url' AND value IN ('#contact', '')"
    );

    $pdo->exec(
        "UPDATE site_settings SET value = '#updates'
         WHERE key = 'work_cta_url' AND value IN ('#contact', '')"
    );

    $pdo->exec(
        "UPDATE site_settings SET value = 'See recent stories'
         WHERE key = 'work_cta_label' AND value = 'Discover more'"
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS appeal_requests (
            id SERIAL PRIMARY KEY,
            requester_name VARCHAR(120) NOT NULL,
            requester_phone VARCHAR(20) NOT NULL,
            requester_email VARCHAR(120),
            beneficiary_name VARCHAR(120) NOT NULL,
            case_type VARCHAR(30) NOT NULL,
            target_amount VARCHAR(80),
            location VARCHAR(120),
            description TEXT NOT NULL,
            photo_path VARCHAR(500),
            consent_public BOOLEAN NOT NULL DEFAULT FALSE,
            status VARCHAR(30) NOT NULL DEFAULT \'pending\',
            admin_notes TEXT,
            post_id INT,
            reviewed_at TIMESTAMPTZ,
            created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
    );
}

function seedDefaultContent(PDO $pdo): void
{
    $postCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    if ($postCount === 0) {
        $posts = defaultSeedPosts();

        $stmt = $pdo->prepare(
            'INSERT INTO posts (tag, title, excerpt, content, link_url, link_label, sort_order)
             VALUES (:tag, :title, :excerpt, :content, :link_url, :link_label, :sort_order)'
        );

        foreach ($posts as $post) {
            $stmt->execute([
                ':tag' => $post['tag'],
                ':title' => $post['title'],
                ':excerpt' => $post['excerpt'],
                ':content' => $post['content'],
                ':link_url' => $post['link_url'],
                ':link_label' => $post['link_label'],
                ':sort_order' => $post['sort_order'],
            ]);
        }
    }

    $spotlightCount = (int) $pdo->query('SELECT COUNT(*) FROM spotlight_items')->fetchColumn();
    if ($spotlightCount === 0) {
        $items = defaultSeedSpotlight();

        $stmt = $pdo->prepare(
            'INSERT INTO spotlight_items (title, summary, content, sort_order)
             VALUES (:title, :summary, :content, :sort_order)'
        );

        foreach ($items as $item) {
            $stmt->execute([
                ':title' => $item['title'],
                ':summary' => $item['summary'],
                ':content' => $item['content'],
                ':sort_order' => $item['sort_order'],
            ]);
        }
    }

    $settingsCount = (int) $pdo->query('SELECT COUNT(*) FROM site_settings')->fetchColumn();
    if ($settingsCount === 0) {
        foreach (defaultSiteSettings() as $key => $value) {
            setSetting($pdo, $key, $value);
        }
    }
}
