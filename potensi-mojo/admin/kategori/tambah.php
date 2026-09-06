<?php
require_once __DIR__ . '/../auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['nama_kategori'] ?? ''));
    $description = trim((string)($_POST['deskripsi'] ?? ''));
    if (!verify_csrf($_POST['csrf_token'] ?? null) || $name === '') {
        $error = 'Silakan lengkapi data terlebih dahulu. Nama kategori wajib diisi.';
    } elseif (!$pdo) {
        $error = 'Database belum tersedia.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (?, ?)');
            $stmt->execute([$name, $description ?: null]);
            set_flash('success', 'Data berhasil ditambahkan.');
            redirect('index.php');
        } catch (PDOException $exception) {
            $error = 'Kategori dengan nama tersebut sudah ada atau tidak dapat disimpan.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Kategori - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="admin-body">
<main class="form-page">
  <div class="breadcrumb"><a href="index.php">&larr; Kembali ke Kategori</a></div>
  <h1>Tambah Kategori Baru</h1>
  <p class="section-desc">Tambahkan kelompok kategori untuk mengklasifikasikan ragam potensi di Padukuhan Mojo.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    
    <label>
      Nama Kategori *
      <input name="nama_kategori" required maxlength="100" value="<?= e($_POST['nama_kategori'] ?? '') ?>" placeholder="Contoh: Kuliner & Olahan Pangan">
    </label>

    <label>
      Deskripsi Singkat Kategori
      <textarea name="deskripsi" rows="4" placeholder="Penjelasan mengenai cakupan kelompok potensi ini..."><?= e($_POST['deskripsi'] ?? '') ?></textarea>
    </label>

    <div style="display: flex; gap: 0.75rem; margin-top: 1.25rem; flex-wrap: wrap;">
      <button class="btn btn-primary" type="submit">Simpan Kategori</button>
      <a class="btn" href="index.php" style="background: #E2D8CB; color: #3B2B20;">Batal</a>
    </div>
  </form>
</main>
<script src="../../assets/js/script.js"></script>
</body>
</html>
