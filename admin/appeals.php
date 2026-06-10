<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/appeals.php';
require_once __DIR__ . '/../api/uploads.php';
requireAdmin();

$pdo = getDb();
$statusFilter = (string) ($_GET['status'] ?? 'all');
$allowedFilters = ['all', 'pending', 'under_review', 'approved', 'rejected', 'published'];
if (!in_array($statusFilter, $allowedFilters, true)) {
    $statusFilter = 'all';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $photoStmt = $pdo->prepare('SELECT photo_path FROM appeal_requests WHERE id = :id');
        $photoStmt->execute([':id' => $id]);
        $photoPath = $photoStmt->fetchColumn();

        $pdo->prepare('DELETE FROM appeal_requests WHERE id = :id')->execute([':id' => $id]);
        deleteAppealPhoto(is_string($photoPath) ? $photoPath : null);
        flashSet('success', 'Appeal request deleted.');
        header('Location: ./appeals.php');
        exit;
    }

    if ($action === 'update' && $id > 0) {
        $status = (string) ($_POST['status'] ?? 'pending');
        $adminNotes = trim((string) ($_POST['admin_notes'] ?? ''));

        if (!isValidAppealStatus($status)) {
            flashSet('error', 'Invalid status.');
            header('Location: ./appeals.php?view=' . $id);
            exit;
        }

        $reviewedAt = in_array($status, ['approved', 'rejected', 'published', 'under_review'], true)
            ? date('c')
            : null;

        $pdo->prepare(
            'UPDATE appeal_requests
             SET status = :status, admin_notes = :admin_notes, reviewed_at = COALESCE(:reviewed_at, reviewed_at)
             WHERE id = :id'
        )->execute([
            ':status' => $status,
            ':admin_notes' => $adminNotes !== '' ? $adminNotes : null,
            ':reviewed_at' => $reviewedAt,
            ':id' => $id,
        ]);

        flashSet('success', 'Appeal request updated.');
        header('Location: ./appeals.php?view=' . $id);
        exit;
    }
}

$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$viewing = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM appeal_requests WHERE id = :id');
    $stmt->execute([':id' => $viewId]);
    $viewing = $stmt->fetch() ?: null;
}

$sql = 'SELECT id, requester_name, beneficiary_name, case_type, status, created_at FROM appeal_requests';
$params = [];
if ($statusFilter !== 'all') {
    $sql .= ' WHERE status = :status';
    $params[':status'] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appeals = $stmt->fetchAll();

require_once __DIR__ . '/../api/applications.php';

renderAdminHeader('Appeal requests', 'appeals.php');
?>
<?php if ($viewing): ?>
  <?php $status = (string) ($viewing['status'] ?? 'pending'); ?>
  <section class="admin-panel">
    <div class="admin-toolbar">
      <h2>Appeal #<?= (int) $viewing['id'] ?> — <?= e($viewing['beneficiary_name']) ?></h2>
      <a class="admin-btn" href="./appeals.php<?= $statusFilter !== 'all' ? '?status=' . e($statusFilter) : '' ?>">Back to list</a>
    </div>

    <p style="margin:0 0 16px">
      <span class="badge <?= e(appealStatusBadgeClass($status)) ?>"><?= e(formatAppealStatus($status)) ?></span>
    </p>

    <?php if (!empty($viewing['photo_path'])): ?>
      <figure class="admin-applicant-photo">
        <img src="<?= e('../' . ltrim((string) $viewing['photo_path'], './')) ?>" alt="Appeal supporting photo" />
        <figcaption class="admin-meta">Submitted photo</figcaption>
      </figure>
    <?php endif; ?>

    <dl class="admin-dl">
      <div><dt>Requester</dt><dd><?= e($viewing['requester_name']) ?></dd></div>
      <div><dt>Phone</dt><dd><?= e($viewing['requester_phone']) ?></dd></div>
      <div><dt>Email</dt><dd><?= e($viewing['requester_email'] ?: '—') ?></dd></div>
      <div><dt>Beneficiary</dt><dd><?= e($viewing['beneficiary_name']) ?></dd></div>
      <div><dt>Case type</dt><dd><?= e(formatAppealCaseType((string) $viewing['case_type'])) ?></dd></div>
      <div><dt>Target amount</dt><dd><?= e($viewing['target_amount'] ?: '—') ?></dd></div>
      <div><dt>Location</dt><dd><?= e($viewing['location'] ?: '—') ?></dd></div>
      <div><dt>Submitted</dt><dd><?= e(formatAdminDate((string) $viewing['created_at'])) ?></dd></div>
      <?php if (!empty($viewing['post_id'])): ?>
        <div><dt>Published post</dt><dd><a href="../post.php?id=<?= (int) $viewing['post_id'] ?>" target="_blank" rel="noopener">View post #<?= (int) $viewing['post_id'] ?></a></dd></div>
      <?php endif; ?>
    </dl>

    <div style="margin:16px 0;padding:14px;border:1px solid var(--admin-border);border-radius:12px;background:#fff">
      <?= nl2br(e((string) $viewing['description'])) ?>
    </div>

    <form class="admin-form" method="post" style="margin-bottom:20px">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="id" value="<?= (int) $viewing['id'] ?>" />
      <label>Status
        <select name="status">
          <?php foreach (appealStatuses() as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Internal notes <textarea name="admin_notes" rows="4"><?= e((string) ($viewing['admin_notes'] ?? '')) ?></textarea></label>
      <button class="primary" type="submit">Save review</button>
    </form>

    <div class="admin-toolbar" style="margin-top:8px">
      <?php if (empty($viewing['post_id'])): ?>
        <a class="admin-btn primary" href="./posts.php?from_appeal=<?= (int) $viewing['id'] ?>">Create post from appeal</a>
      <?php else: ?>
        <a class="admin-btn" href="./posts.php?edit=<?= (int) $viewing['post_id'] ?>">Edit published post</a>
      <?php endif; ?>
      <form method="post" onsubmit="return confirm('Delete this appeal request?');">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
        <input type="hidden" name="action" value="delete" />
        <input type="hidden" name="id" value="<?= (int) $viewing['id'] ?>" />
        <button class="admin-btn danger" type="submit">Delete request</button>
      </form>
    </div>
  </section>
<?php endif; ?>

<section class="admin-panel">
  <div class="admin-toolbar">
    <p>Incoming donation and fundraising appeal requests from the public form.</p>
    <form class="admin-filter-bar" method="get">
      <label>
        Status
        <select name="status" onchange="this.form.submit()">
          <?php foreach ($allowedFilters as $filter): ?>
            <option value="<?= e($filter) ?>" <?= $statusFilter === $filter ? 'selected' : '' ?>>
              <?= e($filter === 'all' ? 'All' : formatAppealStatus($filter)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </form>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Beneficiary</th>
          <th>Requester</th>
          <th>Type</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($appeals === []): ?>
          <tr><td colspan="6">No appeal requests yet.</td></tr>
        <?php else: ?>
          <?php foreach ($appeals as $row): ?>
            <?php $rowStatus = (string) ($row['status'] ?? 'pending'); ?>
            <tr>
              <td><?= e($row['beneficiary_name']) ?></td>
              <td><?= e($row['requester_name']) ?></td>
              <td><?= e(formatAppealCaseType((string) $row['case_type'])) ?></td>
              <td><span class="badge <?= e(appealStatusBadgeClass($rowStatus)) ?>"><?= e(formatAppealStatus($rowStatus)) ?></span></td>
              <td><?= e(formatAdminDate((string) $row['created_at'])) ?></td>
              <td><a class="admin-btn" href="./appeals.php?view=<?= (int) $row['id'] ?>">Review</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderAdminFooter(); ?>
