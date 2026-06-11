<?php

declare(strict_types=1);

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/helpers.php';

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

function newTryPosts2026(): array
{
    return [
        [
            'tag' => 'Condolence',
            'title' => 'আর্কিটেকচার ২৪ ব্যাচের সায়েম ফেরদৌসের প্রয়াণে শোক প্রকাশ',
            'excerpt' => '১৭ মে ২০২৬, রবিবার কুয়েট ক্যাম্পাস সংলগ্ন পুকুরে দুর্ঘটনায় ইন্তেকাল করেছেন সায়েম ফেরদৌস — TRY পরিবার গভীর শোকাহত।',
            'content' => "আজ ১৭/৫/২০২৬ তারিখ, রবিবার, কুয়েটের আর্কিটেকচার বিভাগের ২৪ ব্যাচের শিক্ষার্থী সায়েম ফেরদৌস খানজাহান আলী হল সংলগ্ন পুকুরে ডুবে যেয়ে ইন্তেকাল করেছেন। তার এই আকস্মিক মৃত্যুতে ট্রাই পরিবারের প্রতিটি ভলেন্টিয়ার গভীরভাবে ব্যথিত ও শোকাহত।\n\nআমরা তার বিদেহী আত্মার শান্তি কামনা করছি। সৃষ্টিকর্তা তার শোকাবহ পরিবারকে এই কঠিন সময়ে ধৈর্য্য ধারণের তৌফিক দান করুক।\n\nআমরা সকলেও দূর্ঘটনামূলক পরিস্থিতিগুলোতে সচেতনতা অবলম্বন করি।",
            'image_url' => './public/post-condolence-sayem-ferdous.png',
            'link_url' => 'https://www.facebook.com/try.kuet',
            'link_label' => 'Official Facebook post →',
            'sort_order' => 1,
            'created_at' => '2026-05-17 10:00:00+06',
        ],
        [
            'tag' => 'Emergency aid',
            'title' => 'মানবিক আবেদন — রেয়ান আরাফ শিহরন ভাইয়ের বাবার চিকিৎসায় জরুরি সহায়তা',
            'excerpt' => 'লেদার ইঞ্জিনিয়ারিং ২ক১২ ব্যাচের শিক্ষার্থী রেয়ান আরাফ শিহরনের বাবা আইসিইউতে ভর্তি — ধারাবাহিক চিকিৎসার জন্য আর্থিক সহযোগিতা প্রয়োজন।',
            'content' => "একটি মানবিক আবেদন\n\nকুয়েটের ২ক১২ ব্যাচের লেদার ইঞ্জিনিয়ারিং বিভাগের শিক্ষার্থী, রেয়ান আরাফ শিহরন ভাইয়ের বাবা দীর্ঘদিন ধরে হার্ট, কিডনি ও অন্যান্য জটিল রোগে ভুগে বর্তমানে গুরুতর অসুস্থ অবস্থায় আইসিইউতে ভর্তি রয়েছেন। আঙ্কেল পূর্ব থেকেই হৃদরোগে আক্রান্ত ছিলেন এবং ২০১৮ সালে কিডনি সমস্যা দেখা দেয়। সেসময় ডায়ালাইসিসের পর কিডনি ট্রান্সপ্লান্ট করা হয়। এরপর থেকেই প্রতি মাসে প্রায় ৩০,০০০ টাকার ওষুধ ও ৫–১০ হাজার টাকার চেকআপ চালিয়ে যেতে হচ্ছিল।\n\n২০২৫ সালে কিডনির সমস্যার আরো অবনতি হলে সেসময় থেকে প্রতি সপ্তাহে ৩ বার করে ডায়ালাইসিস করতে হচ্ছে (প্রতি সেশন প্রায় ৩,০০০ টাকা)। গত ১৪ই এপ্রিল হঠাৎ অসুস্থ হয়ে পড়লে তাকে ধানমন্ডির পপুলার হাসপাতালে আইসিইউতে ভর্তি করা হয়। বর্তমানে আইসিইউ, ডায়ালাইসিস ও আনুষঙ্গিক খরচ মিলিয়ে প্রতিদিন মোটা অঙ্কের টাকা ব্যয় হচ্ছে, যা পরিবারের পক্ষে বহন করা অত্যন্ত কষ্টসাধ্য হয়ে দাঁড়িয়েছে। গত কয়েকদিনেই প্রায় ৩ লক্ষের অধিক বিল চলে আসায়, সেই খরচ যোগাতে গিয়ে শিহরণ ভাই অনেকটাই ভেঙ্গে পড়েছেন।\n\nআঙ্কেলের জীবন বাঁচাতে ব্যয়বহুল সত্ত্বেও ধারাবাহিকভাবে চিকিৎসা চালিয়ে যাওয়া অত্যন্ত জরুরি, যা একা শিহরণ ভাইয়ের পক্ষে বহন করা প্রায় অসম্ভব হয়ে পড়েছে। তাই সকলের কাছে আন্তরিক অনুরোধ, নিজ নিজ সামর্থ্য অনুযায়ী আর্থিক সহায়তা দিয়ে এগিয়ে আসুন। আঙ্কেলের চিকিৎসা চালিয়ে যাওয়া এখন কেবল আপনাদের সহযোগিতার মাধ্যমেই সম্ভব।\n\nমানবিক সহায়তায় এগিয়ে আসুন\n\nসহযোগিতার মাধ্যম:\nbKash: 01745740110 – Foysal (BME'21)\nbKash: 01624071475 – Khaled (TE'22)\nNagad: 01832491622 – Adon (BME'21)\nNagad: 01950489009 – Nazmul (LE'22)\nRocket: 017917095369 – Arnab (LE'21)\nRocket: 017722256234 – Rupam (LE'22)\nBank Name: Dutch Bangla Bank Limited\nAccount Holder: Md. Foysal Alam\nAccount Number: 2631050032552\nBranch: Thakurgaon Road Branch\nSWIFT Code: DBBLBDDH\nRouting Number: 090940970\nPhone Number: 01745740110",
            'image_url' => './public/post-appeal-riyan-shihron.png',
            'link_url' => 'https://www.facebook.com/try.kuet',
            'link_label' => 'Official Facebook post →',
            'sort_order' => 2,
            'created_at' => '2026-05-14 12:00:00+06',
        ],
        [
            'tag' => 'Greeting',
            'title' => 'শুভ নববর্ষ ১৪৩৩',
            'excerpt' => 'পহেলা বৈশাখের আনন্দ ছড়িয়ে পড়ুক সবার হৃদয়ে — TRY KUET-এর পক্ষ থেকে বর্ণিল শুভেচ্ছা।',
            'content' => "মুছে যাক সব গ্লানি, আসুক নতুনের আনন্দ। শুভ নববর্ষ ১৪৩৩!\n\nনতুন বছর সবার জীবনে বয়ে আনুক অনাবিল সুখ, শান্তি আর সমৃদ্ধি। হালখাতার নতুন পাতায় যুক্ত হোক নতুন স্বপ্ন। পহেলা বৈশাখের এই আনন্দ ছড়িয়ে পড়ুক সবার হৃদয়ে।\n\nবাঙালির প্রাণের উৎসব পহেলা বৈশাখে ‘ট্রাই’-এর পক্ষ থেকে সকল শুভাকাঙ্ক্ষীকে জানাই বর্ণিল শুভেচ্ছা।\n\n#পহেলাবৈশাখ #শুভনববর্ষ #বাংলা_নববর্ষ১৪৩৩ #নববর্ষেরশুভেচ্ছা",
            'image_url' => './public/post-pohela-boishakh-1433.png',
            'link_url' => 'https://www.facebook.com/try.kuet',
            'link_label' => 'Official Facebook post →',
            'sort_order' => 3,
            'created_at' => '2026-04-14 08:00:00+06',
        ],
    ];
}

function latestTryPosts(): array
{
    return [
        [
            'tag' => 'Emergency aid',
            'title' => 'মানবিক আবেদন — ইসমত আরা ইতির জন্য জরুরি সহায়তা',
            'excerpt' => 'গুরুতর লিভার রোগে আক্রান্ত একাদশ শ্রেণীর শিক্ষার্থী ইসমত আরা ইতির জরুরি লিভার ট্রান্সপ্লান্টের জন্য আর্থিক সহযোগিতা প্রয়োজন।',
            'content' => "একটি মানবিক আবেদন\n\nনর্থ ওয়েস্টার্ন ইউনিভার্সিটির সিএসই বিভাগের স্প্রিং-২২ ব্যাচের শিক্ষার্থী আয়েশা সিদ্দিকা আখির ছোট বোন, একাদশ শ্রেণীর শিক্ষার্থী ইসমত আরা ইতি দীর্ঘদিন যাবৎ গুরুতর লিভার রোগে আক্রান্ত এবং বর্তমানে হাসপাতালে চিকিৎসাধীন রয়েছে। এসএসসি পরীক্ষার সময়কাল থেকে তার লিভারের সমস্যা ধরা পড়ে এবং সুস্থতা অর্জনের লক্ষ্যে চিকিৎসা শুরু করা হয়।\n\nচিকিৎসকদের মতে, তার জীবন বাঁচাতে জরুরি ভিত্তিতে লিভার ট্রান্সপ্লান্ট প্রয়োজন। দীর্ঘদিনের চিকিৎসা ব্যয়ে পরিবারটি আর্থিকভাবে ভেঙে পড়েছে। ইতিমধ্যে আগামী ১৩ জুন অপারেশনের তারিখ ঠিক করা হয়েছে, তবে অপারেশনের জন্য জরুরি ভিত্তিতে প্রয়োজন ২০ লক্ষ টাকা। এমতাবস্থায়, সকলের নিজ নিজ সামর্থ্য অনুযায়ী অনুদানেই তার চিকিৎসা চালিয়ে যাওয়া সম্ভব।\n\nসবার আন্তরিক দোয়া ও সামর্থ্য অনুযায়ী আর্থিক সহযোগিতা একটি জীবন বাঁচাতে পারে।\n\nব্যাংক হিসাব:\nAccount Name: Md Alamgir Talukder\nAccount Number: 2913201028022\nBank Name: Sonali Bank PLC\nBranch Name: Rayenda Bazar Branch, Bagerhat\nRouting Number: 200011244\nSWIFT Code: BARBBDDH\nCountry: Bangladesh\n\nMobile Banking (bKash/Nagad): +8801797372786 (Personal)\n\nমানবিক সহায়তায় এগিয়ে আসুন।",
            'image_url' => './public/post-appeal-ismat-iti.png',
            'link_url' => './appeal-request.html',
            'link_label' => 'Request an appeal →',
            'sort_order' => 1,
        ],
        [
            'tag' => 'Greeting',
            'title' => 'ঈদ-উল-আযহার শুভেচ্ছা',
            'excerpt' => 'TRY KUET-এর পক্ষ থেকে ঈদ-উল-আযহার আন্তরিক শুভেচ্ছা — ত্যাগ, তাকওয়া ও মানবিকতার শিক্ষায় উদ্বুদ্ধ হোন।',
            'content' => "‘এরপর তা থেকে তোমরা আহার করো এবং আহার করাও তাকে, যে অভাব থাকা সত্ত্বেও কারও কাছে হাত পাতে না এবং তাকেও, যে নিজের অভাবের কথা প্রকাশ করে হাত পাতে।’ (সূরা হজ, আয়াত: ৩৬)\n\nমহান আল্লাহ তায়ালার সন্তুষ্টি অর্জনই প্রতিটি ত্যাগের প্রধান উদ্দেশ্য — এই উপলব্ধিই প্রতিবছর স্মরণ করিয়ে দেয় ঈদ-উল-আযহা। কোরবানির মূল লক্ষ্য তাকওয়া অর্জন, দরিদ্রদের প্রতি উদারতা প্রকাশ এবং মহান স্রষ্টার নিমিত্তে আত্মত্যাগের শিক্ষা।\n\nধনী-দরিদ্রের সেতুবন্ধন হয়ে ঈদ-উল-আযহা সবার জীবনে বয়ে আনুক অগণিত সুখ, শান্তি ও সমৃদ্ধি। TRY-এর পক্ষ থেকে সকলকে জানাই ঈদের শুভেচ্ছা।\n\nঈদ মোবারক 🌙",
            'image_url' => './public/post-eid-ul-adha.png',
            'link_url' => 'https://www.facebook.com/try.kuet',
            'link_label' => 'Official Facebook post →',
            'sort_order' => 2,
        ],
        [
            'tag' => 'Announcement',
            'title' => 'Congratulations to our advisors',
            'excerpt' => 'Heartfelt congratulations to Kaniz Fatema Ma’am and Tasnim Ahmed Ma’am on promotion to Assistant Professor.',
            'content' => "A proud moment for all of us.\n\nHeartfelt congratulations to our respected advisors:\n\nKaniz Fatema Ma’am — Department of Urban and Regional Planning, KUET\nTasnim Ahmed Ma’am — Department of Biomedical Engineering, KUET\n\non being promoted to Assistant Professor.\n\nYour dedication, sincerity, and guidance have always inspired us. Seeing your achievements makes us truly happy and proud.\n\nWishing you both many more successes, happiness, and beautiful milestones ahead.",
            'image_url' => './public/post-advisor-kaniz-fatema.png',
            'gallery_images' => [
                [
                    'url' => './public/post-advisor-kaniz-fatema.png',
                    'caption' => 'Kaniz Fatema Ma’am — Urban & Regional Planning',
                ],
                [
                    'url' => './public/post-advisor-tasnim-ahmed.png',
                    'caption' => 'Tasnim Ahmed Ma’am — Biomedical Engineering',
                ],
            ],
            'link_url' => 'https://www.facebook.com/try.kuet',
            'link_label' => 'View on Facebook →',
            'sort_order' => 3,
        ],
    ];
}

function defaultSeedPosts(): array
{
    return array_merge(newTryPosts2026(), latestTryPosts());
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
        "UPDATE site_settings SET value = 'https://www.facebook.com/try.kuet'
         WHERE key = 'facebook_page_url' AND value IN ('#contact', '')"
    );

    $pdo->exec(
        "UPDATE site_settings SET value = 'https://www.facebook.com/groups/try.general'
         WHERE key = 'facebook_group_url' AND value IN ('#contact', '')"
    );

    $pdo->exec(
        "UPDATE site_settings SET value = 'See recent stories'
         WHERE key = 'work_cta_label' AND value = 'Discover more'"
    );

    migrateLatestPosts($pdo);
    migratePostGalleries($pdo);
    migrateTryPosts2026($pdo);
    migrateRemoveVolunteerPost($pdo);

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

function insertPost(PDO $pdo, array $post): void
{
    $gallery = '[]';
    if (!empty($post['gallery_images'])) {
        $gallery = encodeGalleryImages($post['gallery_images']);
    }

    $columns = 'tag, title, excerpt, content, image_url, gallery_images, link_url, link_label, sort_order';
    $values = ':tag, :title, :excerpt, :content, :image_url, :gallery_images::jsonb, :link_url, :link_label, :sort_order';
    $params = [
        ':tag' => $post['tag'],
        ':title' => $post['title'],
        ':excerpt' => $post['excerpt'],
        ':content' => $post['content'],
        ':image_url' => $post['image_url'] ?? '',
        ':gallery_images' => $gallery,
        ':link_url' => $post['link_url'],
        ':link_label' => $post['link_label'],
        ':sort_order' => $post['sort_order'],
    ];

    if (!empty($post['created_at'])) {
        $columns .= ', created_at';
        $values .= ', :created_at';
        $params[':created_at'] = $post['created_at'];
    }

    $stmt = $pdo->prepare(
        "INSERT INTO posts ({$columns}) VALUES ({$values})"
    );
    $stmt->execute($params);
}

function migratePostGalleries(PDO $pdo): void
{
    $pdo->exec('ALTER TABLE posts ADD COLUMN IF NOT EXISTS gallery_images JSONB');

    if (getSetting($pdo, 'posts_gallery_version') === '1') {
        return;
    }

    $advisorGallery = encodeGalleryImages([
        [
            'url' => './public/post-advisor-kaniz-fatema.png',
            'caption' => 'Kaniz Fatema Ma’am — Urban & Regional Planning',
        ],
        [
            'url' => './public/post-advisor-tasnim-ahmed.png',
            'caption' => 'Tasnim Ahmed Ma’am — Biomedical Engineering',
        ],
    ]);

    $stmt = $pdo->prepare(
        'UPDATE posts
         SET gallery_images = :gallery_images::jsonb,
             image_url = COALESCE(NULLIF(image_url, \'\'), :image_url)
         WHERE title = :title'
    );
    $stmt->execute([
        ':gallery_images' => $advisorGallery,
        ':image_url' => './public/post-advisor-kaniz-fatema.png',
        ':title' => 'Congratulations to our advisors',
    ]);

    setSetting($pdo, 'posts_gallery_version', '1');
}

function migrateTryPosts2026(PDO $pdo): void
{
    if (getSetting($pdo, 'posts_try_2026_version') === '1') {
        return;
    }

    $delete = $pdo->prepare('DELETE FROM posts WHERE title = :title');
    foreach (newTryPosts2026() as $post) {
        $delete->execute([':title' => $post['title']]);
        insertPost($pdo, $post);
    }

    setSetting($pdo, 'posts_try_2026_version', '1');
}

function migrateLatestPosts(PDO $pdo): void
{
    if (getSetting($pdo, 'posts_seed_version') === '2025-try-latest') {
        return;
    }

    $placeholderTitles = [
        'KUET Vice-Chancellor greeting',
        'Project: Scholarship support',
        'Medical support appeal',
        'Eid gift distribution',
        'Updates & reporting',
    ];

    $delete = $pdo->prepare('DELETE FROM posts WHERE title = :title');
    foreach ($placeholderTitles as $title) {
        $delete->execute([':title' => $title]);
    }

    foreach (latestTryPosts() as $post) {
        $delete->execute([':title' => $post['title']]);
        insertPost($pdo, $post);
    }

    setSetting($pdo, 'posts_seed_version', '2025-try-latest');
}

function migrateRemoveVolunteerPost(PDO $pdo): void
{
    if (getSetting($pdo, 'posts_remove_volunteer') === '1') {
        return;
    }

    $pdo->prepare('DELETE FROM posts WHERE title = :title')->execute([
        ':title' => 'Join as a volunteer',
    ]);

    setSetting($pdo, 'posts_remove_volunteer', '1');
}

function seedDefaultContent(PDO $pdo): void
{
    $postCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
    if ($postCount === 0) {
        foreach (defaultSeedPosts() as $post) {
            insertPost($pdo, $post);
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
