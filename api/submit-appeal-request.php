<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/appeals.php';
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

$requesterName = postString('requester_name', 120);
$requesterPhone = postString('requester_phone', 20);
$requesterEmail = postString('requester_email', 120);
$beneficiaryName = postString('beneficiary_name', 120);
$caseType = postString('case_type', 30);
$targetAmount = postString('target_amount', 80);
$location = postString('location', 120);
$description = postString('description', 5000);

if ($requesterName === '') {
    jsonResponse(false, 'Your name is required.', 422);
}
if ($requesterPhone === '' || !preg_match('/^01\d{9}$/', $requesterPhone)) {
    jsonResponse(false, 'A valid 11-digit Bangladesh mobile number is required.', 422);
}
if ($requesterEmail !== '' && !filter_var($requesterEmail, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.', 422);
}
if ($beneficiaryName === '') {
    jsonResponse(false, 'Beneficiary name is required.', 422);
}
if (!isValidAppealCaseType($caseType)) {
    jsonResponse(false, 'Please select a valid case type.', 422);
}
if ($description === '') {
    jsonResponse(false, 'Please describe the situation and why help is needed.', 422);
}
if (empty($_POST['consent_public'])) {
    jsonResponse(false, 'You must agree that TRY may publish this appeal after verification.', 422);
}
if (empty($_POST['confirm_accurate'])) {
    jsonResponse(false, 'You must confirm the information provided is accurate.', 422);
}

$photoPath = null;

try {
    $photoPath = saveAppealPhoto($_FILES['photo'] ?? [], $beneficiaryName);
} catch (InvalidArgumentException $e) {
    jsonResponse(false, $e->getMessage(), 422);
} catch (RuntimeException $e) {
    jsonResponse(false, 'Could not save photo. Please try again.', 500);
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare(
        'INSERT INTO appeal_requests (
            requester_name, requester_phone, requester_email, beneficiary_name,
            case_type, target_amount, location, description, photo_path, consent_public
        ) VALUES (
            :requester_name, :requester_phone, :requester_email, :beneficiary_name,
            :case_type, :target_amount, :location, :description, :photo_path, :consent_public
        )'
    );
    $stmt->execute([
        ':requester_name' => $requesterName,
        ':requester_phone' => $requesterPhone,
        ':requester_email' => $requesterEmail !== '' ? $requesterEmail : null,
        ':beneficiary_name' => $beneficiaryName,
        ':case_type' => $caseType,
        ':target_amount' => $targetAmount !== '' ? $targetAmount : null,
        ':location' => $location !== '' ? $location : null,
        ':description' => $description,
        ':photo_path' => $photoPath,
        ':consent_public' => true,
    ]);

    jsonResponse(
        true,
        'Appeal request submitted. TRY will review your case and contact you if more information is needed.'
    );
} catch (PDOException $e) {
    deleteAppealPhoto($photoPath);
    $config = getConfig();
    if (($config['app_env'] ?? '') === 'development') {
        jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
    }
    jsonResponse(false, 'Could not submit appeal request. Please try again later.', 500);
}
