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

$email = trim((string) ($_POST['email'] ?? ''));
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Please enter a valid email address.', 422);
}

try {
    $pdo = getDb();
    $stmt = $pdo->prepare('INSERT INTO subscribers (email) VALUES (:email)');
    $stmt->execute([':email' => mb_substr($email, 0, 120)]);

    jsonResponse(true, 'Thanks for subscribing! You will receive TRY updates.');
} catch (PDOException $e) {
    if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'duplicate key')) {
        jsonResponse(true, 'You are already subscribed.');
    }
    jsonResponse(false, 'Could not subscribe. Please try again later.', 500);
}
