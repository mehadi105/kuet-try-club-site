<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
requireAdmin();

$pdo = getDb();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM subscribers WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Subscriber removed.');
    }
    header('Location: ./subscribers.php');
    exit;
}

$subscribers = $pdo->query(
    'SELECT id, email, created_at FROM subscribers ORDER BY created_at DESC'
)->fetchAll();

renderAdminHeader('Subscribers', 'subscribers.php');
?>
<section class="admin-panel">
  <p>Emails collected from the homepage subscribe form.</p>
  <div class="admin-table-wrap" style="margin-top:16px">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Email</th>
          <th>Subscribed</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($subscribers === []): ?>
          <tr><td colspan="3">No subscribers yet.</td></tr>
        <?php else: ?>
          <?php foreach ($subscribers as $row): ?>
            <tr>
              <td><?= e($row['email']) ?></td>
              <td><?= e($row['created_at']) ?></td>
              <td>
                <form method="post" onsubmit="return confirm('Remove this subscriber?');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
                  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>" />
                  <button class="admin-btn danger" type="submit">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderAdminFooter(); ?>
