<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$status = (string) ($_POST['status'] ?? '');
$priority = (string) ($_POST['priority'] ?? '');

$statuses = ['Open', 'In Progress', 'Resolved', 'Closed'];
$priorities = ['Low', 'Medium', 'High', 'Critical'];

if (
    !$id ||
    !in_array($status, $statuses, true) ||
    !in_array($priority, $priorities, true)
) {
    http_response_code(422);
    exit('Invalid ticket update.');
}

$stmt = db()->prepare(
    'UPDATE tickets SET status = ?, priority = ? WHERE id = ?'
);
$stmt->execute([$status, $priority, $id]);

redirect('index.php?updated=1');