<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/uploads.php';

function jsonResponse(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function postString(string $key, int $maxLen = 500): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if (mb_strlen($value) > $maxLen) {
        $value = mb_substr($value, 0, $maxLen);
    }
    return $value;
}

$required = [
    'fullname' => 'Full name is required.',
    'roll' => 'Roll number is required.',
    'department' => 'Department is required.',
    'batch' => 'Batch is required.',
    'email' => 'Email is required.',
    'phone' => 'Phone number is required.',
    'why_join' => 'Please explain why you want to join.',
    'weekly_hours' => 'Weekly availability is required.',
    'meetings' => 'Meeting availability is required.',
];

foreach ($required as $field => $msg) {
    if (postString($field) === '') {
        jsonResponse(false, $msg, 422);
    }
}

if (empty($_POST['confirm_info']) || empty($_POST['agree_rules'])) {
    jsonResponse(false, 'You must accept the declaration checkboxes.', 422);
}

$roll = postString('roll', 20);
if (!preg_match('/^\d{7}$/', $roll)) {
    jsonResponse(false, 'Roll number must be 7 digits.', 422);
}

$phone = postString('phone', 20);
if (!preg_match('/^01\d{9}$/', $phone)) {
    jsonResponse(false, 'Phone must be a valid 11-digit Bangladesh mobile number.', 422);
}

$email = postString('email', 120);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.', 422);
}

$skills = [];
if (isset($_POST['skills']) && is_array($_POST['skills'])) {
    $skills = array_values(array_filter(array_map('trim', $_POST['skills'])));
}

$photoPath = null;

try {
    $photoPath = saveApplicationPhoto($_FILES['photo'] ?? [], $roll);
} catch (InvalidArgumentException $e) {
    jsonResponse(false, $e->getMessage(), 422);
} catch (RuntimeException $e) {
    jsonResponse(false, 'Could not save photo. Please try again.', 500);
}

try {
    $pdo = getDb();

    $duplicate = $pdo->prepare('SELECT 1 FROM join_applications WHERE roll = :roll');
    $duplicate->execute([':roll' => $roll]);
    if ($duplicate->fetchColumn()) {
        deleteApplicationPhoto($photoPath);
        jsonResponse(false, 'An application with this roll number already exists.', 409);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO join_applications (
            fullname, roll, department, batch, semester, blood_group, photo_path,
            email, phone, facebook, hall, why_join, experience,
            skills, other_skills, weekly_hours, meetings,
            emergency_name, emergency_phone
        ) VALUES (
            :fullname, :roll, :department, :batch, :semester, :blood_group, :photo_path,
            :email, :phone, :facebook, :hall, :why_join, :experience,
            :skills, :other_skills, :weekly_hours, :meetings,
            :emergency_name, :emergency_phone
        )'
    );

    $stmt->execute([
        ':fullname' => postString('fullname', 120),
        ':roll' => $roll,
        ':department' => postString('department', 80),
        ':batch' => postString('batch', 20),
        ':semester' => postString('semester', 10),
        ':blood_group' => postString('blood_group', 10),
        ':photo_path' => $photoPath,
        ':email' => $email,
        ':phone' => $phone,
        ':facebook' => postString('facebook', 300),
        ':hall' => postString('hall', 120),
        ':why_join' => postString('why_join', 5000),
        ':experience' => postString('experience', 5000),
        ':skills' => json_encode($skills, JSON_UNESCAPED_UNICODE),
        ':other_skills' => postString('other_skills', 300),
        ':weekly_hours' => postString('weekly_hours', 20),
        ':meetings' => postString('meetings', 20),
        ':emergency_name' => postString('emergency_name', 120),
        ':emergency_phone' => postString('emergency_phone', 20),
    ]);

    jsonResponse(true, 'Application submitted successfully.');
} catch (PDOException $e) {
    deleteApplicationPhoto($photoPath);

    if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'duplicate key')) {
        jsonResponse(false, 'An application with this roll number already exists.', 409);
    }
    $config = getConfig();
    if (($config['app_env'] ?? '') === 'development') {
        jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
    }
    jsonResponse(false, 'Could not save application. Please try again later.', 500);
}
