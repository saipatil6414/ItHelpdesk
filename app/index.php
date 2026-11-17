<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$pdo = db();

$allowedStatuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
$allowedCategories = [
    'Hardware', 'Software', 'Network', 'Access', 'Email', 'Other'
];

$search = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$category = (string) ($_GET['category'] ?? '');

if (mb_strlen($search) > 150) {
    $search = mb_substr($search, 0, 150);
}

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if (!in_array($category, $allowedCategories, true)) {
    $category = '';
}

$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(status = 'Open'), 0) AS open_count,
        COALESCE(SUM(status = 'In Progress'), 0) AS progress_count,
        COALESCE(SUM(status = 'Resolved'), 0) AS resolved_count
     FROM tickets"
)->fetch();

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(title LIKE ? OR requester LIKE ? OR email LIKE ?
                 OR CAST(id AS CHAR) = ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like, $search);
}

if ($status !== '') {
    $where[] = 'status = ?';
    $params[] = $status;
}

if ($category !== '') {
    $where[] = 'category = ?';
    $params[] = $category;
}

$sql = 'SELECT id, title, requester, email, category, priority,
               status, created_at
        FROM tickets';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 200';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

$priorities = ['Low', 'Medium', 'High', 'Critical'];
$statuses = $allowedStatuses;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | IT HelpDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">
        <span class="brand-mark">HD</span>
        <span>HelpDesk <small>IT SUPPORT</small></span>
    </a>

    <div class="top-actions">
        <span class="muted welcome">
            Admin: <?= e($_SESSION['admin_username'] ?? 'Administrator') ?>
        </span>
        <a class="btn primary" href="create_ticket.php">+ New ticket</a>
        <form method="post" action="logout.php" class="inline-form">
            <input type="hidden" name="csrf_token"
                   value="<?= e(csrf_token()) ?>">
            <button class="btn secondary" type="submit">Logout</button>
        </form>
    </div>
</header>

<main class="container">
    <section class="page-heading">
        <p class="eyebrow">OVERVIEW / SUPPORT OPERATIONS</p>
        <h1>IT Helpdesk Dashboard</h1>
        <p class="muted">Monitor, prioritize and resolve employee IT issues.</p>
    </section>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert success">Ticket created successfully.</div>
    <?php elseif (isset($_GET['updated'])): ?>
        <div class="alert success">Ticket updated successfully.</div>
    <?php endif; ?>

    <section class="stats-grid">
        <article class="stat-card">
            <span class="stat-label">Total tickets</span>
            <strong><?= e($stats['total']) ?></strong>
            <span class="stat-note">All recorded issues</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Open</span>
            <strong><?= e($stats['open_count']) ?></strong>
            <span class="stat-note">Awaiting action</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">In progress</span>
            <strong><?= e($stats['progress_count']) ?></strong>
            <span class="stat-note">Being investigated</span>
        </article>
        <article class="stat-card">
            <span class="stat-label">Resolved</span>
            <strong><?= e($stats['resolved_count']) ?></strong>
            <span class="stat-note">Successfully resolved</span>
        </article>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Support tickets</h2>
                <p class="muted">Showing up to 200 matching records.</p>
            </div>
            <span class="result-count"><?= count($tickets) ?> results</span>
        </div>

        <form method="get" action="index.php" class="filters">
            <input name="q" value="<?= e($search) ?>"
                   placeholder="Search ticket, employee or email..."
                   aria-label="Search tickets">

            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                <?php foreach ($statuses as $item): ?>
                    <option value="<?= e($item) ?>"
                        <?= $status === $item ? 'selected' : '' ?>>
                        <?= e($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="category" aria-label="Filter by category">
                <option value="">All categories</option>
                <?php foreach ($allowedCategories as $item): ?>
                    <option value="<?= e($item) ?>"
                        <?= $category === $item ? 'selected' : '' ?>>
                        <?= e($item) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn primary" type="submit">Apply filters</button>
            <a class="btn secondary" href="index.php">Reset</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Requester</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Update</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!$tickets): ?>
                    <tr>
                        <td colspan="7" class="empty">
                            No tickets found. Try different filters or create a ticket.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <strong>#HD-<?= e($ticket['id']) ?></strong>
                            <span class="ticket-title">
                                <?= e($ticket['title']) ?>
                            </span>
                        </td>
                        <td>
                            <?= e($ticket['requester']) ?>
                            <span class="ticket-title">
                                <?= e($ticket['email']) ?>
                            </span>
                        </td>
                        <td><?= e($ticket['category']) ?></td>
                        <td>
                            <span class="priority <?= strtolower(e($ticket['priority'])) ?>">
                                <?= e($ticket['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status <?= strtolower(str_replace(' ', '-', e($ticket['status']))) ?>">
                                <?= e($ticket['status']) ?>
                            </span>
                        </td>
                        <td><?= e(date('d M Y, H:i', strtotime($ticket['created_at']))) ?></td>
                        <td>
                            <form method="post" action="update_ticket.php"
                                  class="update-form">
                                <input type="hidden" name="csrf_token"
                                       value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="id"
                                       value="<?= e($ticket['id']) ?>">

                                <select name="status" aria-label="Ticket status">
                                    <?php foreach ($statuses as $item): ?>
                                        <option value="<?= e($item) ?>"
                                            <?= $ticket['status'] === $item
                                                ? 'selected' : '' ?>>
                                            <?= e($item) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="priority" aria-label="Ticket priority">
                                    <?php foreach ($priorities as $item): ?>
                                        <option value="<?= e($item) ?>"
                                            <?= $ticket['priority'] === $item
                                                ? 'selected' : '' ?>>
                                            <?= e($item) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button class="btn small primary" type="submit">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="footer">
        IT Helpdesk & Ticket Management System · PHP · MySQL · Docker
    </footer>
</main>
</body>
</html>