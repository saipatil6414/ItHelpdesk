<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['is_admin'])) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (admin_credentials_valid($username, $password)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_username'] = $username;

        redirect('index.php');
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | HelpDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
<main class="login-card">
    <div class="brand-mark">HD</div>
    <h1>HelpDesk</h1>
    <p class="muted">Sign in to manage support tickets.</p>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token"
               value="<?= e(csrf_token()) ?>">

        <label for="username">Username</label>
        <input id="username" name="username"
               autocomplete="username" required maxlength="100">

        <label for="password">Password</label>
        <input id="password" name="password" type="password"
               autocomplete="current-password" required>

        <button class="btn primary full" type="submit">Sign in</button>
    </form>
</main>
</body>
</html>