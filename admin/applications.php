<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/applications.php';
require_once __DIR__ . '/../api/uploads.php';
requireAdmin();

$pdo = getDb();
$statuses = applicationStatuses();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        $photoStmt = $pdo->prepare('SELECT photo_path FROM join_applications WHERE id = :id');
        $photoStmt->execute([':id' => $id]);
        $photoPath = $photoStmt->fetchColumn();

        $pdo->prepare('DELETE FROM join_applications WHERE id = :id')->execute([':id' => $id]);
        deleteApplicationPhoto(is_string($photoPath) ? $photoPath : null);
        flashSet('success', 'Application deleted.');
        header('Location: ./applications.php');
        exit;
    }

    if ($action === 'update' && $id > 0) {
        $status = trim((string) ($_POST['status'] ?? 'pending'));
        if (!isValidApplicationStatus($status)) {
            flashSet('error', 'Invalid status selected.');
            header('Location: ./applications.php?view=' . $id);
            exit;
        }

        $notes = trim((string) ($_POST['admin_notes'] ?? ''));
        if (mb_strlen($notes) > 5000) {
            $notes = mb_substr($notes, 0, 5000);
        }

        $stmt = $pdo->prepare(
            'UPDATE join_applications
             SET status = :status, admin_notes = :admin_notes, reviewed_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            ':status' => $status,
            ':admin_notes' => $notes,
            ':id' => $id,
        ]);

        flashSet('success', 'Application updated.');
        header('Location: ./applications.php?view=' . $id);
        exit;
    }

    header('Location: ./applications.php');
    exit;
}

$statusFilter = trim((string) ($_GET['status'] ?? ''));
$search = trim((string) ($_GET['q'] ?? ''));
$viewId = isset($_GET['view']) ? (int) $_GET['view'] : 0;
$viewing = null;

if ($viewId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM join_applications WHERE id = :id');
    $stmt->execute([':id' => $viewId]);
    $viewing = $stmt->fetch() ?: null;
}

$listSql = 'SELECT id, fullname, roll, department, batch, email, phone, status, created_at
            FROM join_applications WHERE 1=1';
$listParams = [];

if ($statusFilter !== '' && isValidApplicationStatus($statusFilter)) {
    $listSql .= ' AND status = :status';
    $listParams[':status'] = $statusFilter;
}

if ($search !== '') {
    $listSql .= ' AND (fullname ILIKE :q OR roll ILIKE :q OR email ILIKE :q OR department ILIKE :q)';
    $listParams[':q'] = '%' . $search . '%';
}

$listSql .= ' ORDER BY created_at DESC';
$listStmt = $pdo->prepare($listSql);
$listStmt->execute($listParams);
$applications = $listStmt->fetchAll();

$exportQuery = http_build_query(array_filter([
    'status' => $statusFilter,
    'q' => $search,
]));

renderAdminHeader('Applications', 'applications.php');
?>
<div class="admin-toolbar">
  <p>Review volunteer applications, update status, and export for committee meetings.</p>
  <a class="admin-btn primary" href="./applications-export.php<?= $exportQuery !== '' ? '?' . e($exportQuery) : '' ?>">Export CSV</a>
</div>

<?php if ($viewing): ?>
  <?php
    $status = (string) ($viewing['status'] ?? 'pending');
    $skillsText = formatSkillsDisplay($viewing['skills']);
    if (($viewing['other_skills'] ?? '') !== '') {
        $skillsText = $skillsText === '—'
            ? (string) $viewing['other_skills']
            : $skillsText . '; ' . $viewing['other_skills'];
    }
  ?>
  <section class="admin-panel">
    <div class="admin-toolbar">
      <div>
        <h2><?= e((string) $viewing['fullname']) ?></h2>
        <p class="admin-meta">Roll <?= e((string) $viewing['roll']) ?> · <?= e(formatAdminDate((string) $viewing['created_at'])) ?></p>
      </div>
      <a class="admin-btn" href="./applications.php<?= $exportQuery !== '' ? '?' . e($exportQuery) : '' ?>">Back to list</a>
    </div>

    <p style="margin:0 0 16px">
      <span class="badge <?= e(statusBadgeClass($status)) ?>"><?= e(formatApplicationStatus($status)) ?></span>
    </p>

    <?php if (!empty($viewing['photo_path'])): ?>
      <figure class="admin-applicant-photo">
        <img src="<?= e('../' . ltrim((string) $viewing['photo_path'], './')) ?>" alt="Applicant profile photo" />
        <figcaption class="admin-meta">Submitted profile photo</figcaption>
      </figure>
    <?php endif; ?>

    <form class="admin-form" method="post" style="margin-bottom:20px">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="update" />
      <input type="hidden" name="id" value="<?= (int) $viewing['id'] ?>" />
      <div class="admin-form-row">
        <label>
          Status
          <select name="status" required>
            <?php foreach ($statuses as $value => $label): ?>
              <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Reviewed at
          <input type="text" value="<?= e(formatAdminDate((string) ($viewing['reviewed_at'] ?? ''))) ?>" disabled />
        </label>
      </div>
      <label>
        Admin notes (internal only)
        <textarea name="admin_notes" rows="4" placeholder="Interview notes, committee comments…"><?= e((string) ($viewing['admin_notes'] ?? '')) ?></textarea>
      </label>
      <button class="primary" type="submit">Save status & notes</button>
    </form>

    <div class="admin-detail-grid">
      <section class="admin-detail-card">
        <h3>Personal</h3>
        <dl class="admin-dl">
          <dt>Full name</dt><dd><?= e((string) $viewing['fullname']) ?></dd>
          <dt>Roll</dt><dd><?= e((string) $viewing['roll']) ?></dd>
          <dt>Department</dt><dd><?= e((string) $viewing['department']) ?></dd>
          <dt>Batch</dt><dd><?= e((string) $viewing['batch']) ?></dd>
          <dt>Semester</dt><dd><?= e((string) ($viewing['semester'] ?: '—')) ?></dd>
          <dt>Blood group</dt><dd><?= e((string) ($viewing['blood_group'] ?: '—')) ?></dd>
          <dt>Hall</dt><dd><?= e((string) ($viewing['hall'] ?: '—')) ?></dd>
        </dl>
      </section>

      <section class="admin-detail-card">
        <h3>Contact</h3>
        <dl class="admin-dl">
          <dt>Email</dt><dd><a href="mailto:<?= e((string) $viewing['email']) ?>"><?= e((string) $viewing['email']) ?></a></dd>
          <dt>Phone</dt><dd><?= e((string) $viewing['phone']) ?></dd>
          <dt>Facebook</dt><dd><?= e((string) ($viewing['facebook'] ?: '—')) ?></dd>
          <dt>Emergency contact</dt><dd><?= e((string) ($viewing['emergency_name'] ?: '—')) ?><?= ($viewing['emergency_phone'] ?? '') !== '' ? ' · ' . e((string) $viewing['emergency_phone']) : '' ?></dd>
        </dl>
      </section>

      <section class="admin-detail-card admin-detail-wide">
        <h3>Motivation & experience</h3>
        <dl class="admin-dl">
          <dt>Why join</dt><dd><?= nl2br(e((string) $viewing['why_join'])) ?></dd>
          <dt>Experience</dt><dd><?= nl2br(e((string) ($viewing['experience'] ?: '—'))) ?></dd>
        </dl>
      </section>

      <section class="admin-detail-card">
        <h3>Skills & availability</h3>
        <dl class="admin-dl">
          <dt>Skills</dt><dd><?= e($skillsText) ?></dd>
          <dt>Weekly hours</dt><dd><?= e((string) $viewing['weekly_hours']) ?></dd>
          <dt>Meetings</dt><dd><?= e(ucfirst((string) $viewing['meetings'])) ?></dd>
        </dl>
      </section>
    </div>

    <form method="post" style="margin-top:16px" onsubmit="return confirm('Delete this application permanently?');">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>" />
      <input type="hidden" name="action" value="delete" />
      <input type="hidden" name="id" value="<?= (int) $viewing['id'] ?>" />
      <button class="admin-btn danger" type="submit">Delete application</button>
    </form>
  </section>
<?php endif; ?>

<section class="admin-panel">
  <form class="admin-filter-bar" method="get">
    <label>
      Status
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach ($statuses as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= $statusFilter === $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="admin-filter-search">
      Search
      <input type="search" name="q" value="<?= e($search) ?>" placeholder="Name, roll, email, department" />
    </label>
    <button class="admin-btn" type="submit">Filter</button>
    <?php if ($statusFilter !== '' || $search !== ''): ?>
      <a class="admin-btn" href="./applications.php">Clear</a>
    <?php endif; ?>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Roll</th>
          <th>Department</th>
          <th>Batch</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($applications === []): ?>
          <tr><td colspan="7">No applications found.</td></tr>
        <?php else: ?>
          <?php foreach ($applications as $row): ?>
            <?php $rowStatus = (string) ($row['status'] ?? 'pending'); ?>
            <tr>
              <td><?= e($row['fullname']) ?></td>
              <td><?= e($row['roll']) ?></td>
              <td><?= e($row['department']) ?></td>
              <td><?= e($row['batch']) ?></td>
              <td><span class="badge <?= e(statusBadgeClass($rowStatus)) ?>"><?= e(formatApplicationStatus($rowStatus)) ?></span></td>
              <td><?= e(formatAdminDate((string) $row['created_at'])) ?></td>
              <td><a class="admin-btn" href="./applications.php?view=<?= (int) $row['id'] ?><?= $exportQuery !== '' ? '&' . e($exportQuery) : '' ?>">Review</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php renderAdminFooter(); ?>
