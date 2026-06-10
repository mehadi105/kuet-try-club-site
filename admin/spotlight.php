<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
requireAdmin();

$pdo = getDb();
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM spotlight_items WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Spotlight item deleted.');
        header('Location: ./spotlight.php');
        exit;
    }

    $data = [
        ':title' => trim((string) ($_POST['title'] ?? '')),
        ':summary' => trim((string) ($_POST['summary'] ?? '')),
        ':content' => trim((string) ($_POST['content'] ?? '')),
        ':image_url' => trim((string) ($_POST['image_url'] ?? '')),
        ':link_url' => trim((string) ($_POST['link_url'] ?? '')),
        ':is_published' => !empty($_POST['is_published']),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($data[':title'] === '' || $data[':summary'] === '') {
        flashSet('error', 'Title and summary are required.');
        header('Location: ./spotlight.php' . (!empty($_POST['id']) ? '?edit=' . (int) $_POST['id'] : '?new=1'));
        exit;
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare(
            'UPDATE spotlight_items SET
                title = :title, summary = :summary, content = :content, image_url = :image_url,
                link_url = :link_url, is_published = :is_published,
                sort_order = :sort_order, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($data + [':id' => $id]);
        flashSet('success', 'Spotlight item updated.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO spotlight_items (title, summary, content, image_url, link_url, is_published, sort_order)
             VALUES (:title, :summary, :content, :image_url, :link_url, :is_published, :sort_order)'
        );
        $stmt->execute($data);
        flashSet('success', 'Spotlight item created.');
    }

    header('Location: ./spotlight.php');
    exit;
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM spotlight_items WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$showForm = isset($_GET['new']) || $editing !== null;
$items = $pdo->query('SELECT * FROM spotlight_items ORDER BY sort_order ASC, created_at DESC')->fetchAll();

renderAdminHeader('Spotlight', 'spotlight.php');
?>
<div class="admin-toolbar">
  <p>Manage spotlight cards on the homepage.</p>
  <?php if (!$showForm): ?>
    <a class="admin-btn primary" href="./spotlight.php?new=1">Add item</a>
  <?php else: ?>
    <a class="admin-btn" href="./spotlight.php">Back to list</a>
  <?php endif; ?>
</div>

<?php if ($showForm): ?>
  <section class="admin-panel">
    <h2><?= $editing ? 'Edit spotlight item' : 'New spotlight item' ?></h2>
    <form class="admin-form" method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>" />
      <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>" />
      <?php endif; ?>
      <div class="admin-form-row">
        <label>Title <input type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required /></label>
        <label>Sort order <input type="number" name="sort_order" value="<?= e((string) ($editing['sort_order'] ?? 0)) ?>" /></label>
      </div>
      <label>Summary (short text for cards) <textarea name="summary" required><?= e($editing['summary'] ?? '') ?></textarea></label>
      <label>Full content (detail page — separate paragraphs with a blank line) <textarea name="content" rows="10"><?= e($editing['content'] ?? '') ?></textarea></label>
      <div class="admin-form-row">
        <label>Image URL <input type="text" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>" /></label>
        <label>External link URL (optional) <input type="text" name="link_url" value="<?= e($editing['link_url'] ?? '') ?>" placeholder="https://..." /></label>
      </div>
      <?php if ($editing): ?>
        <p><a class="admin-btn" href="../spotlight.php?id=<?= (int) $editing['id'] ?>" target="_blank" rel="noopener">Preview spotlight page</a></p>
      <?php endif; ?>
      <label class="admin-check">
        <input type="checkbox" name="is_published" <?= ($editing['is_published'] ?? true) ? 'checked' : '' ?> />
        Published
      </label>
      <button class="primary" type="submit"><?= $editing ? 'Save changes' : 'Create item' ?></button>
    </form>
  </section>
<?php else: ?>
  <section class="admin-panel">
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= (int) $item['sort_order'] ?></td>
              <td><?= e($item['title']) ?></td>
              <td>
                <?php if ($item['is_published']): ?>
                  <span class="badge badge-success">Published</span>
                <?php else: ?>
                  <span class="badge badge-muted">Draft</span>
                <?php endif; ?>
              </td>
              <td class="admin-actions">
                <a class="admin-btn" href="../spotlight.php?id=<?= (int) $item['id'] ?>" target="_blank" rel="noopener">View</a>
                <a class="admin-btn" href="./spotlight.php?edit=<?= (int) $item['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this item?');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $item['id'] ?>" />
                  <button class="admin-btn danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>
<?php renderAdminFooter(); ?>
