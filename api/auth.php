<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function requireAdmin(): void
{
    if (isAdminLoggedIn()) {
        return;
    }

    $login = '/admin/login.php';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if ($requestUri !== '') {
        $login .= '?redirect=' . urlencode($requestUri);
    }

    header('Location: ' . $login);
    exit;
}

function attemptAdminLogin(string $username, string $password): bool
{
    $config = require __DIR__ . '/config.php';
    $admin = $config['admin'];

    if ($username !== ($admin['username'] ?? '')) {
        return false;
    }

    if ($password !== ($admin['password'] ?? '')) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;

    return true;
}

function adminLogout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function adminUsername(): string
{
    return (string) ($_SESSION['admin_username'] ?? 'admin');
}
