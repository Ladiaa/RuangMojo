<?php
require_once __DIR__ . '/../auth.php';

$items = $pdo ? $pdo->query('SELECT k.*, (SELECT COUNT(*) FROM potensi p WHERE p.kategori_id = k.id) AS total_potensi FROM kategori k ORDER BY k.nama_kategori')->fetchAll() : [];
$flashSuccess = consume_flash('success');
$flashError = consume_flash('error');

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kategori - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<div class="admin-backdrop" id="adminBackdrop"></div>
<div class="admin-layout">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-top">
      <div class="brand-row">
        <a class="brand" href="../../index.php">
          <img class="brand-logo" src="../../assets/images/logo-clean.png" alt="Logo Ruang Mojo">
          <div class="brand-text">
            <span class="brand-name">Ruang Mojo</span>
            <span class="brand-tag">Panel Admin</span>
          </div>
        </a>
        <button class="sidebar-close-btn" id="adminSidebarClose" aria-label="Tutup Menu">&times;</button>
      </div>
      <nav>
        <a href="../dashboard.php">
          <span>Dasbor</span>
        </a>
        <a href="../potensi/index.php">
          <span>Data Potensi</span>
        </a>
        <a class="active" href="index.php">
          <span>Kategori</span>
        </a>
        <a href="../cerita/index.php">
          <span>Cerita Mojo</span>
        </a>
        <a href="../profile.php">
          <span>Ubah Profil</span>
        </a>
        <a class="admin-logout-trigger" data-logout-trigger="true" href="../logout.php" title="Keluar dari Panel Admin">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          <span>Keluar</span>
        </a>
      </nav>
    </div>
    <div class="sidebar-footer">
      <a class="user-badge" href="../profile.php" style="text-decoration: none;">
        <div class="user-avatar"><?= e($adminInitial) ?></div>
        <div class="user-details">
          <span class="user-name"><?= e($adminName) ?></span>
          <span class="user-role">Administrator</span>
        </div>
      </a>
    </div>
  </aside>
  <main class="admin-main">
    <header class="admin-top">
      <div class="top-left">
        <button class="admin-burger-btn" id="adminBurgerBtn" aria-label="Buka Menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <a class="admin-mobile-brand" href="../../index.php">
          <img src="../../assets/images/logo-dark.png" alt="Logo">
          <span>Ruang Mojo</span>
        </a>
        <span class="top-title-desktop">Kelola Kategori Potensi</span>
      </div>
      <a class="top-btn-site" href="../../index.php" target="_blank" rel="noopener">
        <span>Lihat Website</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
    </header>
    <section class="admin-content">
      <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><?= e($flashSuccess) ?></div>
      <?php endif; ?>
      <?php if ($flashError): ?>
        <div class="alert alert-error"><?= e($flashError) ?></div>
      <?php endif; ?>

      <div class="panel-heading" style="padding: 0; background: transparent; border: 0; margin-bottom: 1.5rem;">
        <div>
          <h1>Kategori Potensi</h1>
          <p class="section-desc">Kelola pengelompokan klasifikasi potensi Padukuhan Mojo.</p>
        </div>
        <a class="btn btn-primary" href="tambah.php">+ Tambah Kategori</a>
      </div>

      <div class="admin-panel table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama Kategori</th>
              <th>Deskripsi</th>
              <th>Total Potensi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td data-label="Nama Kategori"><strong><?= e($item['nama_kategori']) ?></strong></td>
                <td data-label="Deskripsi" style="color: #7A695A;"><?= e($item['deskripsi'] ?: '-') ?></td>
                <td data-label="Total Potensi">
                  <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 24px; padding: 0 0.5rem; border-radius: 999px; background: rgba(107,79,56,0.1); color: #5C4330; font-weight: 700; font-size: 0.82rem;">
                    <?= (int)$item['total_potensi'] ?>
                  </span>
                </td>
                <td data-label="Aksi">
                  <div class="action-group">
                    <a class="btn-action btn-action-edit" href="edit.php?id=<?= (int)$item['id'] ?>">Edit</a>
                    <a class="btn-action btn-action-delete" href="hapus.php?id=<?= (int)$item['id'] ?>&csrf_token=<?= e(csrf_token()) ?>" data-confirm="Apakah Anda yakin ingin menghapus data ini? Kategori yang masih digunakan oleh potensi tidak dapat dihapus.">Hapus</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?>
              <tr class="empty-search-row">
                <td colspan="4" style="text-align: center; padding: 2rem; color: #7A695A;">Belum ada kategori yang ditambahkan.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>
<?php require_once __DIR__ . '/../modal-logout.php'; ?>
<script src="../../assets/js/script.js?v=<?= filemtime(__DIR__ . '/../../assets/js/script.js') ?>"></script>
</body>
</html>
