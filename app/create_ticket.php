<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$error = '';

$categories = [
    'Hardware',
    'Software',
    'Network',
    'Access',
    'Email',
    'Other',
];

$priorities = ['Low', 'Medium', 'High', 'Critical'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim((string) ($_POST['title'] ?? ''));
    $requester = trim((string) ($_POST['requester'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $category = (string) ($_POST['category'] ?? '');
    $priority = (string) ($_POST['priority'] ?? 'Medium');
    $description = trim((string) ($_POST['description'] ?? ''));

    if (
        $title === '' || mb_strlen($title) > 150 ||
        $requester === '' || mb_strlen($requester) > 100 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        mb_strlen($email) > 190 ||
        !in_array($category, $categories, true) ||
        !in_array($priority, $priorities, true) ||
        $description === '' || mb_strlen($description) > 10000
    ) {
        $error = 'Please check all fields and try again.';
    } else {
        $stmt = db()->prepare(
            'INSERT INTO tickets
             (title, requester, email, category, priority, description)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $title,
            $requester,
            $email,
            $category,
            $priority,
            $description,
        ]);

        redirect('index.php?created=1');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Ticket | HelpDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php"><span class="brand-mark">HD</span>
        HelpDesk</a>
    <a class="btn secondary" href="index.php">Back to dashboard</a>
</header>

<main class="container narrow">
    <div class="page-heading">
        <p class="eyebrow">SUPPORT OPERATIONS</p>
        <h1>Create a ticket</h1>
        <p class="muted">Record a new employee IT issue.</p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <section class="panel">
        <form method="post" action="create_ticket.php">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">

            <label for="title">Issue title</label>
            <input id="title" name="title" maxlength="150"
                   required value="<?= e($_POST['title'] ?? '') ?>"
                   placeholder="e.g. Unable to connect to VPN">

            <div class="form-grid">
                <div>
                    <label for="requester">Employee name</label>
                    <input id="requester" name="requester"
                           maxlength="100" required
                           value="<?= e($_POST['requester'] ?? '') ?>">
                </div>
                <div>
                    <label for="email">Employee email</label>
                    <input id="email" name="email" type="email"
                           maxlength="190" required
                           value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <?php foreach ($categories as $item): ?>
                            <option value="<?= e($item) ?>"
                                <?= (($_POST['category'] ?? 'Software') === $item)
                                    ? 'selected' : '' ?>>
                                <?= e($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <?php foreach ($priorities as $item): ?>
                            <option value="<?= e($item) ?>"
                                <?= (($_POST['priority'] ?? 'Medium') === $item)
                                    ? 'selected' : '' ?>>
                                <?= e($item) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label for="description">Issue description</label>
            <textarea id="description" name="description" rows="6"
                      maxlength="10000" required
                      placeholder="Describe the problem and any error messages..."><?= e($_POST['description'] ?? '') ?></textarea>

            <button class="btn primary" type="submit">Create ticket</button>
        </form>
    </section>
</main>
</body>
</html>