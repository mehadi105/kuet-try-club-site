<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/helpers.php';
requireAdmin();

$pdo = getDb();
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM spotlight_items WHERE id = :id')->execute([':id' => $id]);
        flashSet('success', 'Event deleted.');
        header('Location: ./spotlight.php');
        exit;
    }

    $galleryJson = encodeGalleryImages(
        parseGalleryImagesInput((string) ($_POST['gallery_images'] ?? ''))
    );

    $data = [
        ':title' => trim((string) ($_POST['title'] ?? '')),
        ':summary' => trim((string) ($_POST['summary'] ?? '')),
        ':content' => trim((string) ($_POST['content'] ?? '')),
        ':image_url' => trim((string) ($_POST['image_url'] ?? '')),
        ':gallery_images' => $galleryJson,
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
                gallery_images = :gallery_images::jsonb, link_url = :link_url, is_published = :is_published,
                sort_order = :sort_order, updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute($data + [':id' => $id]);
        flashSet('success', 'Event updated.');
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO spotlight_items (title, summary, content, image_url, gallery_images, link_url, is_published, sort_order)
             VALUES (:title, :summary, :content, :image_url, :gallery_images::jsonb, :link_url, :is_published, :sort_order)'
        );
        $stmt->execute($data);
        flashSet('success', 'Event created.');
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

renderAdminHeader('Recent events', 'spotlight.php');
?>
<div class="admin-toolbar">
  <p>
    Manage <strong>Recent events</strong> on the homepage (#recent-events). Each event has a featured hero image,
    a highlights grid (gallery), and a full detail page.
  </p>
  <?php if (!$showForm): ?>
    <a class="admin-btn primary" href="./spotlight.php?new=1">Add event</a>
  <?php else: ?>
    <a class="admin-btn" href="./spotlight.php">Back to list</a>
  <?php endif; ?>
</div>

<?php if ($showForm): ?>
  <section class="admin-panel">
    <h2><?= $editing ? 'Edit event' : 'New event' ?></h2>
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
      <label>Summary (homepage card + intro on detail page)
        <textarea name="summary" rows="3" required><?= e($editing['summary'] ?? '') ?></textarea>
      </label>
      <label>Full content (detail page — separate paragraphs with a blank line)
        <textarea name="content" rows="10"><?= e($editing['content'] ?? '') ?></textarea>
      </label>
      <label>Featured image URL (hero banner + homepage thumbnail)
        <input type="text" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>" placeholder="./public/event-your-photo.png" />
      </label>
      <label>Event highlights gallery (one per line; optional caption after <code>|</code> — shown in the 3-column grid)
        <textarea name="gallery_images" rows="8" placeholder="./public/photo-one.png | Volunteers pack supplies&#10;./public/photo-two.png | Delivery team"><?= e(galleryImagesToInput($editing['gallery_images'] ?? '')) ?></textarea>
      </label>
      <label>External link URL (optional — e.g. Facebook album)
        <input type="text" name="link_url" value="<?= e($editing['link_url'] ?? '') ?>" placeholder="https://www.facebook.com/try.kuet" />
      </label>
      <?php if ($editing): ?>
        <p><a class="admin-btn" href="../spotlight.php?id=<?= (int) $editing['id'] ?>" target="_blank" rel="noopener">Preview event page</a></p>
      <?php endif; ?>
      <label class="admin-check">
        <input type="checkbox" name="is_published" <?= ($editing['is_published'] ?? true) ? 'checked' : '' ?> />
        Published on homepage
      </label>
      <button class="primary" type="submit"><?= $editing ? 'Save changes' : 'Create event' ?></button>
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
            <th>Gallery</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <?php
              $galleryCount = count(eventGalleryTiles($item['gallery_images'] ?? null));
              $hasFeatured = trim((string) ($item['image_url'] ?? '')) !== '';
            ?>
            <tr>
              <td><?= (int) $item['sort_order'] ?></td>
              <td><?= e($item['title']) ?></td>
              <td>
                <?php if ($hasFeatured): ?>
                  <span class="badge badge-success">Hero</span>
                <?php endif; ?>
                <?php if ($galleryCount > 0): ?>
                  <span class="badge badge-muted"><?= $galleryCount ?> grid</span>
                <?php elseif (!$hasFeatured): ?>
                  <span class="badge badge-muted">No images</span>
                <?php endif; ?>
              </td>
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
                <form method="post" onsubmit="return confirm('Delete this event?');">
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
