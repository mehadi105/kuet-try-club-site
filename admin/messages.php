<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM contact_messages WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Message deleted.');
    }

    if ($action === 'read' && $id > 0) {
        $pdo->prepare('UPDATE contact_messages SET is_read = TRUE WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Message marked as read.');
    }

    header('Location: ./messages.php' . ($id > 0 ? '?view=' . $id : ''));
    exit;
}

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$viewing = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = :id');
    $stmt->execute([':id' => $viewId]);
    $viewing = $stmt->fetch() ?: null;

    if ($viewing && !$viewing['is_read']) {
        $pdo->prepare('UPDATE contact_messages SET is_read = TRUE WHERE id = :id')->execute([':id' => $viewId]);
        $viewing['is_read'] = true;
    }
}

$messages = $pdo->query(
    'SELECT id, name, email, message, is_read, created_at
     FROM contact_messages
     ORDER BY created_at DESC'
)->fetchAll();

renderAdminHeader('Messages', 'messages.php');
?>
<?php if ($viewing): ?>
  <section class="admin-panel">
    <div class="admin-toolbar">
      <h2>Message from <?= e($viewing['name']) ?></h2>
      <a class="admin-btn" href="./messages.php">Back to inbox</a>
    </div>
    <p><strong>Email:</strong> <?= e($viewing['email']) ?></p>
    <p><strong>Received:</strong> <?= e($viewing['created_at']) ?></p>
    <div style="margin-top:14px;padding:14px;border:1px solid var(--admin-border);border-radius:12px;background:#fff">
      <?= nl2br(e($viewing['message'])) ?>
    </div>
    <form method="post" style="margin-top:16px" onsubmit="return confirm('Delete this message?');">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="delete" />
      <input type="hidden" name="id" value="<?= (int) $viewing['id'] ?>" />
      <button class="admin-btn danger" type="submit">Delete message</button>
    </form>
  </section>
<?php endif; ?>

<section class="admin-panel">
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Message</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($messages === []): ?>
          <tr><td colspan="5">No messages yet.</td></tr>
        <?php else: ?>
          <?php foreach ($messages as $row): ?>
            <tr>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['email']) ?></td>
              <td><?= e(mb_strimwidth($row['message'], 0, 70, '…')) ?></td>
              <td>
                <?php if ($row['is_read']): ?>
                  <span class="badge badge-muted">Read</span>
                <?php else: ?>
                  <span class="badge badge-unread">Unread</span>
                <?php endif; ?>
              </td>
              <td><a class="admin-btn" href="./messages.php?view=<?= (int) $row['id'] ?>">Open</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderAdminFooter(); ?>
