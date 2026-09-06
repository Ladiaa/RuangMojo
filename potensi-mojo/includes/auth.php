<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_admin(string $loginPath = 'login.php'): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . $loginPath);
        exit;
    }
}
