<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/applications.php';

function jsonResponse(bool $success, string $message, array $extra = [], int $code = 200): void
{
    http_response_code($code);
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

$roll = trim((string) ($_POST['roll'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));

if (!preg_match('/^\d{7}$/', $roll)) {
    jsonResponse(false, 'Please enter a valid 7-digit roll number.', [], 422);
}

if (!preg_match('/^01\d{9}$/', $phone)) {
    jsonResponse(false, 'Please enter a valid 11-digit mobile number.', [], 422);
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare(
        'SELECT fullname, roll, department, batch, status, created_at
         FROM join_applications
         WHERE roll = :roll AND phone = :phone
         LIMIT 1'
    );
    $stmt->execute([':roll' => $roll, ':phone' => $phone]);
    $row = $stmt->fetch();

    if (!$row) {
        jsonResponse(false, 'No application found for this roll and phone number.', [], 404);
    }

    $status = (string) $row['status'];
    $statusMessage = match ($status) {
        'approved' => 'Congratulations! Your application has been approved by the committee.',
        'rejected' => 'Your application was not accepted in this recruitment cycle.',
        'waitlisted' => 'You are on the waitlist. The committee may contact you if a spot opens.',
        'interview_scheduled' => 'Your application is shortlisted. Interview details will be shared by the committee.',
        default => 'Your application has been received and is under review.',
    };

    jsonResponse(true, $statusMessage, [
        'application' => [
            'fullname' => $row['fullname'],
            'roll' => $row['roll'],
            'department' => $row['department'],
            'batch' => $row['batch'],
            'status' => $status,
            'status_label' => formatApplicationStatus($status),
            'submitted_at' => formatAdminDate((string) $row['created_at']),
        ],
    ]);
} catch (PDOException $e) {
    jsonResponse(false, 'Could not check status. Please try again later.', [], 500);
}
