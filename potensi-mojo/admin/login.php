<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif (!$pdo) {
        $error = 'Database belum tersedia. Silakan hubungkan database MySQL.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if ($username === '' || $password === '') {
            $error = 'Username dan kata sandi wajib diisi.';
        } else {
            $statement = $pdo->prepare('SELECT id, nama, username, password, role FROM users WHERE username = ? LIMIT 1');
            $statement->execute([$username]);
            $user = $statement->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int)$user['id'];
                $_SESSION['admin_name'] = $user['nama'];
                $_SESSION['admin_role'] = $user['role'];
                redirect('dashboard.php');
            }
            $error = 'Username atau kata sandi tidak sesuai.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk Admin - Ruang Mojo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-login">
  <main class="login-box">
    <div class="brand-area">
      <img class="brand-logo" src="../assets/images/logo-clean.png" alt="Logo Ruang Mojo">
      <span class="brand-name">Ruang Mojo</span>
      <span class="brand-tag">Administrator</span>
    </div>

    <h1>Masuk ke Panel Admin</h1>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      
      <label>
        Username
        <input type="text" name="username" required autocomplete="username" placeholder="Masukkan username" value="<?= e($_POST['username'] ?? '') ?>">
      </label>

      <label>
        Kata Sandi
        <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">
      </label>

      <button class="btn btn-primary" type="submit">Masuk ke Panel Admin &rarr;</button>
    </form>

    <div class="login-back">
      <a href="../index.php">&larr; Kembali ke Website Utama</a>
    </div>
  </main>
</body>
</html>
