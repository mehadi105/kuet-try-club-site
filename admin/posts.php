<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/appeals.php';
requireAdmin();

$pdo = getDb();
$editing = null;
$fromAppeal = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM posts WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Post deleted.');
        header('Location: ./posts.php');
        exit;
    }

    $data = [
        ':tag' => trim((string) ($_POST['tag'] ?? '')),
        ':title' => trim((string) ($_POST['title'] ?? '')),
        ':excerpt' => trim((string) ($_POST['excerpt'] ?? '')),
        ':content' => trim((string) ($_POST['content'] ?? '')),
        ':image_url' => trim((string) ($_POST['image_url'] ?? '')),
        ':link_url' => trim((string) ($_POST['link_url'] ?? '')),
        ':link_label' => trim((string) ($_POST['link_label'] ?? 'Read more →')),
        ':is_published' => !empty($_POST['is_published']),
        ':sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($data[':tag'] === '' || $data[':title'] === '' || $data[':excerpt'] === '') {
        flashSet('error', 'Tag, title, and excerpt are required.');
        header('Location: ./posts.php' . (!empty($_POST['id']) ? '?edit=' . (int) $_POST['id'] : '?new=1'));
        exit;
    }

    if ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare(
            'UPDATE posts SET
                tag = :tag, title = :title, excerpt = :excerpt, content = :content, image_url = :image_url,
                link_url = :link_url, link_label = :link_label, is_published = :is_published,
                sort_order = :sort_order, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($data + [':id' => $id]);
        flashSet('success', 'Post updated.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO posts (tag, title, excerpt, content, image_url, link_url, link_label, is_published, sort_order)
             VALUES (:tag, :title, :excerpt, :content, :image_url, :link_url, :link_label, :is_published, :sort_order)
             RETURNING id'
        );
        $stmt->execute($data);
        $newPostId = (int) $stmt->fetchColumn();

        $appealId = (int) ($_POST['appeal_request_id'] ?? 0);
        if ($appealId > 0) {
            $pdo->prepare(
                'UPDATE appeal_requests
                 SET status = \'published\', post_id = :post_id, reviewed_at = NOW()
                 WHERE id = :id'
            )->execute([
                ':post_id' => $newPostId,
                ':id' => $appealId,
            ]);
            flashSet('success', 'Post created and linked to appeal request.');
            header('Location: ./appeals.php?view=' . $appealId);
            exit;
        }

        flashSet('success', 'Post created.');
    }

    header('Location: ./posts.php');
    exit;
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
    $stmt->execute([':id' => (int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

if (isset($_GET['from_appeal'])) {
    $appealId = (int) $_GET['from_appeal'];
    $stmt = $pdo->prepare('SELECT * FROM appeal_requests WHERE id = :id');
    $stmt->execute([':id' => $appealId]);
    $fromAppeal = $stmt->fetch() ?: null;

    if (!$fromAppeal) {
        flashSet('error', 'Appeal request not found.');
        header('Location: ./appeals.php');
        exit;
    }

    if (!empty($fromAppeal['post_id'])) {
        flashSet('error', 'This appeal already has a published post.');
        header('Location: ./appeals.php?view=' . $appealId);
        exit;
    }

    $editing = buildPostDraftFromAppeal($fromAppeal);
    $editing['appeal_request_id'] = $appealId;
}

$showForm = isset($_GET['new']) || $editing !== null;
$posts = $pdo->query('SELECT * FROM posts ORDER BY sort_order ASC, created_at DESC')->fetchAll();

renderAdminHeader('Posts', 'posts.php');
?>
<div class="admin-toolbar">
  <p>Manage story cards shown on the homepage.</p>
  <?php if (!$showForm): ?>
    <a class="admin-btn primary" href="./posts.php?new=1">Add post</a>
  <?php else: ?>
    <a class="admin-btn" href="./posts.php">Back to list</a>
  <?php endif; ?>
</div>

<?php if ($showForm): ?>
  <section class="admin-panel">
    <h2><?= !empty($editing['id']) ? 'Edit post' : 'New post' ?></h2>
    <?php if (!empty($fromAppeal) && empty($fromAppeal['post_id'])): ?>
      <p class="admin-meta">Pre-filled from appeal request #<?= (int) $fromAppeal['id'] ?> (<?= e($fromAppeal['beneficiary_name']) ?>). Add the official Facebook donation link before publishing.</p>
    <?php endif; ?>
    <form class="admin-form" method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="<?= !empty($editing['id']) ? 'update' : 'create' ?>" />
      <?php if (!empty($editing['id'])): ?>
        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>" />
      <?php endif; ?>
      <?php if (!empty($editing['appeal_request_id'])): ?>
        <input type="hidden" name="appeal_request_id" value="<?= (int) $editing['appeal_request_id'] ?>" />
      <?php endif; ?>
      <div class="admin-form-row">
        <label>Tag <input type="text" name="tag" value="<?= e($editing['tag'] ?? '') ?>" required /></label>
        <label>Sort order <input type="number" name="sort_order" value="<?= e((string) ($editing['sort_order'] ?? 0)) ?>" /></label>
      </div>
      <label>Title <input type="text" name="title" value="<?= e($editing['title'] ?? '') ?>" required /></label>
      <label>Excerpt (short summary for cards) <textarea name="excerpt" required><?= e($editing['excerpt'] ?? '') ?></textarea></label>
      <label>Full content (shown on detail page — separate paragraphs with a blank line) <textarea name="content" rows="10"><?= e($editing['content'] ?? '') ?></textarea></label>
      <label>Image URL <input type="text" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>" placeholder="./public/your-image.jpg" /></label>
      <div class="admin-form-row">
        <label>External link URL (optional) <input type="text" name="link_url" value="<?= e($editing['link_url'] ?? '') ?>" placeholder="https://facebook.com/..." /></label>
        <label>External link label <input type="text" name="link_label" value="<?= e($editing['link_label'] ?? 'Official post →') ?>" /></label>
      </div>
      <?php if (!empty($editing['id'])): ?>
        <p><a class="admin-btn" href="../post.php?id=<?= (int) $editing['id'] ?>" target="_blank" rel="noopener">Preview post page</a></p>
      <?php endif; ?>
      <label class="admin-check">
        <input type="checkbox" name="is_published" <?= ($editing['is_published'] ?? true) ? 'checked' : '' ?> />
        Published
      </label>
      <button class="primary" type="submit"><?= !empty($editing['id']) ? 'Save changes' : 'Create post' ?></button>
    </form>
  </section>
<?php else: ?>
  <section class="admin-panel">
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order</th>
            <th>Tag</th>
            <th>Title</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td><?= (int) $post['sort_order'] ?></td>
              <td><?= e($post['tag']) ?></td>
              <td><?= e($post['title']) ?></td>
              <td>
                <?php if ($post['is_published']): ?>
                  <span class="badge badge-success">Published</span>
                <?php else: ?>
                  <span class="badge badge-muted">Draft</span>
                <?php endif; ?>
              </td>
              <td class="admin-actions">
                <a class="admin-btn" href="../post.php?id=<?= (int) $post['id'] ?>" target="_blank" rel="noopener">View</a>
                <a class="admin-btn" href="./posts.php?edit=<?= (int) $post['id'] ?>">Edit</a>
                <form method="post" onsubmit="return confirm('Delete this post?');">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
                  <input type="hidden" name="action" value="delete" />
                  <input type="hidden" name="id" value="<?= (int) $post['id'] ?>" />
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
