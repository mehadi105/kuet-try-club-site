<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';
require_once __DIR__ . '/../api/applications.php';
requireAdmin();

$pdo = getDb();
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$search = trim((string) ($_GET['q'] ?? ''));

$sql = 'SELECT * FROM join_applications WHERE 1=1';
$params = [];

if ($statusFilter !== '' && isValidApplicationStatus($statusFilter)) {
    $sql .= ' AND status = :status';
    $params[':status'] = $statusFilter;
}

if ($search !== '') {
    $sql .= ' AND (fullname ILIKE :q OR roll ILIKE :q OR email ILIKE :q OR department ILIKE :q)';
    $params[':q'] = '%' . $search . '%';
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$filename = 'try-applications-' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fputcsv($out, [
    'ID', 'Status', 'Full name', 'Roll', 'Department', 'Batch', 'Semester', 'Blood group', 'Photo path',
    'Email', 'Phone', 'Facebook', 'Hall', 'Why join', 'Experience', 'Skills', 'Other skills',
    'Weekly hours', 'Meetings', 'Emergency name', 'Emergency phone', 'Admin notes',
    'Submitted at', 'Reviewed at',
]);

foreach ($rows as $row) {
    fputcsv($out, [
        $row['id'],
        formatApplicationStatus((string) $row['status']),
        $row['fullname'],
        $row['roll'],
        $row['department'],
        $row['batch'],
        $row['semester'],
        $row['blood_group'],
        $row['photo_path'],
        $row['email'],
        $row['phone'],
        $row['facebook'],
        $row['hall'],
        $row['why_join'],
        $row['experience'],
        formatSkillsDisplay($row['skills']),
        $row['other_skills'],
        $row['weekly_hours'],
        $row['meetings'],
        $row['emergency_name'],
        $row['emergency_phone'],
        $row['admin_notes'],
        $row['created_at'],
        $row['reviewed_at'],
    ]);
}

fclose($out);
exit;
