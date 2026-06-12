<?php

declare(strict_types=1);

require_once __DIR__ . '/_init.php';

if (isAdminLoggedIn()) {
    header('Location: ./index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (attemptAdminLogin($username, $password)) {
        $redirect = (string) ($_GET['redirect'] ?? './index.php');
        if (!str_starts_with($redirect, '/admin/')) {
            $redirect = './index.php';
        }
        header('Location: ' . $redirect);
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin login — TRY KUET</title>
    <link rel="icon" href="../public/try-logo.png" type="image/png" />
    <link rel="apple-touch-icon" href="../public/try-logo.png" />
    <link rel="stylesheet" href="./admin.css" />
  </head>
  <body class="login-page">
    <div class="login-card">
      <img class="login-logo" src="../public/try-logo.png" alt="" width="56" height="56" decoding="async" />
      <h1>Admin login</h1>
      <p>Sign in to manage posts, applications, and site content.</p>
      <?php if ($error !== ''): ?>
        <div class="login-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>
      <form class="admin-form" method="post">
        <label>
          Username
          <input type="text" name="username" autocomplete="username" required />
        </label>
        <label>
          Password
          <input type="password" name="password" autocomplete="current-password" required />
        </label>
        <button class="primary" type="submit">Sign in</button>
      </form>
      <p style="margin-top:16px;font-size:13px;color:#64748b">
        Default: <code>admin</code> / <code>trykuet123</code> — change in <code>.env</code>
      </p>
    </div>
  </body>
</html>
