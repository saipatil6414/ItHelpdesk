<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function require_admin(): void
{
    if (empty($_SESSION['is_admin'])) {
        redirect('login.php');
    }
}

function admin_credentials_valid(string $username, string $password): bool
{
    $expectedUser = getenv('ADMIN_USERNAME') ?: '';
    $expectedPassword = getenv('ADMIN_PASSWORD') ?: '';

    return $expectedUser !== ''
        && $expectedPassword !== ''
        && hash_equals($expectedUser, $username)
        && hash_equals($expectedPassword, $password);
}