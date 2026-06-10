<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/database.php';

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

$name = postString('name', 120);
$email = postString('email', 120);
$message = postString('message', 5000);

if ($name === '') {
    jsonResponse(false, 'Name is required.', 422);
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'A valid email is required.', 422);
}
if ($message === '') {
    jsonResponse(false, 'Message is required.', 422);
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare(
        'INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)'
    );
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message,
    ]);

    jsonResponse(true, 'Message sent successfully. We will get back to you soon.');
} catch (PDOException $e) {
    $config = getConfig();
    if (($config['app_env'] ?? '') === 'development') {
        jsonResponse(false, 'Database error: ' . $e->getMessage(), 500);
    }
    jsonResponse(false, 'Could not send message. Please try again later.', 500);
}
