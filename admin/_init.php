<?php

declare(strict_types=1);

require_once __DIR__ . '/../api/database.php';
require_once __DIR__ . '/../api/auth.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}

function flashSet(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flashGet(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function adminNavItems(): array
{
    return [
        'index.php' => 'Dashboard',
        'posts.php' => 'Posts',
        'spotlight.php' => 'Spotlight',
        'applications.php' => 'Applications',
        'messages.php' => 'Messages',
        'appeals.php' => 'Appeal requests',
        'subscribers.php' => 'Subscribers',
        'settings.php' => 'Site settings',
    ];
}

function renderAdminHeader(string $title, string $active = ''): void
{
    $flash = flashGet();
    ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($title) ?> — TRY KUET Admin</title>
    <link rel="stylesheet" href="./admin.css" />
  </head>
  <body>
    <div class="admin-shell">
      <aside class="admin-sidebar">
        <a class="admin-brand" href="./index.php">
          <span class="admin-brand-mark">TRY</span>
          <span>Admin panel</span>
        </a>
        <nav class="admin-nav" aria-label="Admin navigation">
          <?php foreach (adminNavItems() as $file => $label): ?>
            <a href="./<?= e($file) ?>" class="<?= $active === $file ? 'is-active' : '' ?>"><?= e($label) ?></a>
          <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-foot">
          <a href="../index.html" target="_blank" rel="noopener">View website</a>
          <a href="./logout.php">Log out</a>
        </div>
      </aside>
      <main class="admin-main">
        <header class="admin-top">
          <div>
            <p class="admin-eyebrow">TRY KUET</p>
            <h1><?= e($title) ?></h1>
          </div>
          <p class="admin-user">Signed in as <?= e(adminUsername()) ?></p>
        </header>
        <?php if ($flash): ?>
          <div class="admin-alert admin-alert-<?= e($flash['type']) ?>" role="status">
            <?= e($flash['message']) ?>
          </div>
        <?php endif; ?>
    <?php
}

function renderAdminFooter(): void
{
    ?>
      </main>
    </div>
  </body>
</html>
    <?php
}
