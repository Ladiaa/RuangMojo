<?php
require_once __DIR__ . '/auth.php';

$totalPotensi = $totalKategori = $totalCerita = $aktif = $nonaktif = 0;
$terbaru = [];

if ($pdo) {
    $totalPotensi = (int)$pdo->query('SELECT COUNT(*) FROM potensi')->fetchColumn();
    $totalKategori = (int)$pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
    $totalCerita = (int)$pdo->query('SELECT COUNT(*) FROM cerita')->fetchColumn();
    $aktif = (int)$pdo->query("SELECT COUNT(*) FROM potensi WHERE status = 'aktif'")->fetchColumn();
    $nonaktif = (int)$pdo->query("SELECT COUNT(*) FROM potensi WHERE status = 'nonaktif'")->fetchColumn();
    $terbaru = $pdo->query('SELECT p.id, p.nama, p.status, p.created_at, k.nama_kategori FROM potensi p JOIN kategori k ON k.id = p.kategori_id ORDER BY p.created_at DESC LIMIT 6')->fetchAll();
}

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminInitial = strtoupper(substr($adminName, 0, 1));

function admin_page_start(string $title, string $activePage = 'dashboard'): void {
    global $adminName, $adminInitial;
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<div class="admin-backdrop" id="adminBackdrop"></div>
<div class="admin-layout">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-top">
      <div class="brand-row">
        <a class="brand" href="../index.php">
          <img class="brand-logo" src="../assets/images/logo-clean.png" alt="Logo Ruang Mojo">
          <div class="brand-text">
            <span class="brand-name">Ruang Mojo</span>
            <span class="brand-tag">Panel Admin</span>
          </div>
        </a>
        <button class="sidebar-close-btn" id="adminSidebarClose" aria-label="Tutup Menu">&times;</button>
      </div>
      <nav>
        <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
          <span>Dasbor</span>
        </a>
        <a class="<?= $activePage === 'potensi' ? 'active' : '' ?>" href="potensi/index.php">
          <span>Data Potensi</span>
        </a>
        <a class="<?= $activePage === 'kategori' ? 'active' : '' ?>" href="kategori/index.php">
          <span>Kategori</span>
        </a>
        <a class="<?= $activePage === 'cerita' ? 'active' : '' ?>" href="cerita/index.php">
          <span>Cerita Mojo</span>
        </a>
        <a class="<?= $activePage === 'profile' ? 'active' : '' ?>" href="profile.php">
          <span>Ubah Profil</span>
        </a>
        <a class="admin-logout-trigger" data-logout-trigger="true" href="logout.php" title="Keluar dari Panel Admin">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          <span>Keluar</span>
        </a>
      </nav>
    </div>
    <div class="sidebar-footer">
      <a class="user-badge" href="profile.php" style="text-decoration: none;">
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
        <a class="admin-mobile-brand" href="../index.php">
          <img src="../assets/images/logo-dark.png" alt="Logo">
          <span>Ruang Mojo</span>
        </a>
        <span class="top-title-desktop">Dasbor</span>
      </div>
      <a class="top-btn-site" href="../index.php" target="_blank" rel="noopener">
        <span>Lihat Website</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
    </header>
<?php }

function admin_page_end(): void { ?>
  </main>
</div>
<?php require_once __DIR__ . '/modal-logout.php'; ?>
<script src="../assets/js/script.js?v=<?= filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
<?php }

admin_page_start('Dasbor', 'dashboard');
?>
<section class="admin-content">
  <div style="margin-bottom: 2rem;">
    <h1>Selamat Datang, <?= e($adminName) ?></h1>
    <p class="section-desc">Ringkasan sistem informasi dokumentasi dan potensi Padukuhan Mojo.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <span class="stat-label">Total Potensi</span>
      <strong><?= $totalPotensi ?></strong>
    </div>

    <div class="stat-card">
      <span class="stat-label">Total Kategori</span>
      <strong><?= $totalKategori ?></strong>
    </div>

    <div class="stat-card">
      <span class="stat-label">Cerita Mojo</span>
      <strong><?= $totalCerita ?></strong>
    </div>

    <div class="stat-card">
      <span class="stat-label">Potensi Aktif</span>
      <strong style="color: #1E6B38;"><?= $aktif ?></strong>
    </div>

    <div class="stat-card">
      <span class="stat-label">Potensi Tidak Aktif</span>
      <strong style="color: #8C2E2E;"><?= $nonaktif ?></strong>
    </div>
  </div>

  <div class="admin-panel">
    <div class="panel-heading">
      <div>
        <h2>Potensi Terbaru</h2>
        <p class="section-desc">Daftar potensi lokal yang baru saja didokumentasikan.</p>
      </div>
      <a class="btn btn-primary" href="potensi/tambah.php">+ Tambah Potensi</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Nama Potensi</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($terbaru as $item): ?>
            <tr>
              <td data-label="Nama Potensi"><strong><?= e($item['nama']) ?></strong></td>
              <td data-label="Kategori"><span class="badge-kategori"><?= e($item['nama_kategori']) ?></span></td>
              <td data-label="Status">
                <span class="status status-<?= e($item['status']) ?>">
                  <?= $item['status'] === 'aktif' ? 'Aktif' : 'Tidak Aktif' ?>
                </span>
              </td>
              <td data-label="Tanggal Dibuat"><?= e(format_indo_date($item['created_at'])) ?></td>
              <td data-label="Aksi">
                <div class="action-group">
                  <a class="btn-action btn-action-view" href="../detail.php?id=<?= (int)$item['id'] ?>" target="_blank" rel="noopener">Lihat</a>
                  <a class="btn-action btn-action-edit" href="potensi/edit.php?id=<?= (int)$item['id'] ?>">Edit</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$terbaru): ?>
            <tr class="empty-search-row">
              <td colspan="5" style="text-align: center; padding: 2rem; color: #7A695A;">Belum ada data potensi yang ditambahkan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php admin_page_end(); ?>
