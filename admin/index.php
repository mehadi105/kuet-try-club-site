<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/applications.php';
requireAdmin();

$pdo = getDb();

$stats = [
    'posts' => (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'spotlight' => (int) $pdo->query('SELECT COUNT(*) FROM spotlight_items')->fetchColumn(),
    'applications' => (int) $pdo->query('SELECT COUNT(*) FROM join_applications')->fetchColumn(),
    'pending_applications' => (int) $pdo->query("SELECT COUNT(*) FROM join_applications WHERE status = 'pending'")->fetchColumn(),
    'messages' => (int) $pdo->query('SELECT COUNT(*) FROM contact_messages WHERE is_read = FALSE')->fetchColumn(),
    'pending_appeals' => (int) $pdo->query("SELECT COUNT(*) FROM appeal_requests WHERE status = 'pending'")->fetchColumn(),
    'subscribers' => (int) $pdo->query('SELECT COUNT(*) FROM subscribers')->fetchColumn(),
];

$recentApplications = $pdo->query(
    'SELECT id, fullname, roll, department, status, created_at
     FROM join_applications
     ORDER BY created_at DESC
     LIMIT 5'
)->fetchAll();

$recentMessages = $pdo->query(
    'SELECT id, name, email, message, is_read, created_at
     FROM contact_messages
     ORDER BY created_at DESC
     LIMIT 5'
)->fetchAll();

renderAdminHeader('Dashboard', 'index.php');
?>
<section class="admin-grid">
  <article class="admin-card">
    <h2>Posts</h2>
    <p class="admin-stat"><?= $stats['posts'] ?></p>
    <a class="admin-btn" href="./posts.php">Manage posts</a>
  </article>
  <article class="admin-card">
    <h2>Recent events</h2>
    <p class="admin-stat"><?= $stats['spotlight'] ?></p>
    <a class="admin-btn" href="./spotlight.php">Manage events</a>
  </article>
  <article class="admin-card">
    <h2>Pending applications</h2>
    <p class="admin-stat"><?= $stats['pending_applications'] ?></p>
    <p class="admin-meta"><?= $stats['applications'] ?> total</p>
    <a class="admin-btn" href="./applications.php?status=pending">Review pending</a>
  </article>
  <article class="admin-card">
    <h2>Unread messages</h2>
    <p class="admin-stat"><?= $stats['messages'] ?></p>
    <a class="admin-btn" href="./messages.php">Open inbox</a>
  </article>
  <article class="admin-card">
    <h2>Pending appeals</h2>
    <p class="admin-stat"><?= $stats['pending_appeals'] ?></p>
    <a class="admin-btn" href="./appeals.php?status=pending">Review appeals</a>
  </article>
  <article class="admin-card">
    <h2>Subscribers</h2>
    <p class="admin-stat"><?= $stats['subscribers'] ?></p>
    <a class="admin-btn" href="./subscribers.php">View list</a>
  </article>
</section>

<section class="admin-panel">
  <h2>Recent volunteer applications</h2>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Roll</th>
          <th>Department</th>
          <th>Status</th>
          <th>Submitted</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recentApplications === []): ?>
          <tr><td colspan="5">No applications yet.</td></tr>
        <?php else: ?>
          <?php foreach ($recentApplications as $row): ?>
            <?php $rowStatus = (string) ($row['status'] ?? 'pending'); ?>
            <tr>
              <td><?= e($row['fullname']) ?></td>
              <td><?= e($row['roll']) ?></td>
              <td><?= e($row['department']) ?></td>
              <td><span class="badge <?= e(statusBadgeClass($rowStatus)) ?>"><?= e(formatApplicationStatus($rowStatus)) ?></span></td>
              <td><?= e(formatAdminDate((string) $row['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<section class="admin-panel">
  <h2>Recent contact messages</h2>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Message</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recentMessages === []): ?>
          <tr><td colspan="4">No messages yet.</td></tr>
        <?php else: ?>
          <?php foreach ($recentMessages as $row): ?>
            <tr>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['email']) ?></td>
              <td><?= e(mb_strimwidth($row['message'], 0, 80, '…')) ?></td>
              <td>
                <?php if ($row['is_read']): ?>
                  <span class="badge badge-muted">Read</span>
                <?php else: ?>
                  <span class="badge badge-unread">Unread</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php
renderAdminFooter();
