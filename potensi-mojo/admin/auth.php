<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$loginPath = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/potensi/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/kategori/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/cerita/') ? '../login.php' : 'login.php';
require_admin($loginPath);
