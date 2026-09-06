<?php
require_once __DIR__ . '/../auth.php';

$q = trim((string)($_GET['q'] ?? ''));
$items = [];

if ($pdo) {
    $sql = 'SELECT c.*, (SELECT COUNT(*) FROM cerita_foto cf WHERE cf.cerita_id = c.id) AS gallery_count 
            FROM cerita c 
            WHERE 1=1';
    $params = [];

    if ($q !== '') {
        $sql .= ' AND (c.judul LIKE ? OR c.ringkasan LIKE ? OR c.deskripsi LIKE ?)';
        $term = '%' . $q . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    $sql .= ' ORDER BY c.tanggal DESC, c.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
}

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
  <title>Kelola Cerita Mojo - Ruang Mojo Admin</title>
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
        <a href="../kategori/index.php">
          <span>Kategori</span>
        </a>
        <a class="active" href="index.php">
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
        <span class="top-title-desktop">Kelola Cerita Mojo</span>
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

      <div style="margin-bottom: 1.5rem;">
        <h1>Cerita &amp; Kegiatan Mojo</h1>
        <p class="section-desc">Kelola dokumentasi kegiatan, cerita masyarakat, dan kabar seputar Padukuhan Mojo.</p>
      </div>

      <form class="admin-toolbar" method="get" action="index.php">
        <div class="admin-toolbar-left">
          <div class="admin-search-box">
            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="adminSearchInput" name="q" value="<?= e($q) ?>" placeholder="Cari cerita atau kegiatan..." data-empty-message="Data tidak ditemukan." autocomplete="off">
            <a class="admin-search-clear" id="adminSearchClear" href="index.php" title="Hapus pencarian" style="<?= ($q !== '') ? 'display:block;' : '' ?>">&times;</a>
          </div>
        </div>

        <div class="admin-toolbar-right">
          <a class="btn btn-primary" href="tambah.php">+ Tambah Cerita</a>
        </div>
      </form>

      <div class="admin-panel table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width: 70px;">Foto</th>
              <th>Judul Cerita</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th style="width: 1%; white-space: nowrap;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): 
              $photoCount = ($item['foto'] ? 1 : 0) + (int)$item['gallery_count'];
              $thumb = $item['foto'] ? '../../uploads/cerita/' . rawurlencode($item['foto']) : null;
            ?>
              <tr>
                <td data-label="Foto">
                  <?php if ($thumb): ?>
                    <img src="<?= e($thumb) ?>" alt="" style="width: 60px; height: 45px; object-fit: cover; border-radius: 8px; display: block;">
                  <?php else: ?>
                    <span style="font-size: 0.75rem; color: #888;">Tanpa foto</span>
                  <?php endif; ?>
                </td>
                <td data-label="Judul Cerita">
                  <div class="potensi-nama"><?= e($item['judul']) ?></div>
                  <?php if ($photoCount > 1): ?>
                    <span class="potensi-pengelola">+<?= $photoCount - 1 ?> foto galeri</span>
                  <?php endif; ?>
                </td>
                <td data-label="Tanggal"><?= e(format_indo_date($item['tanggal'])) ?></td>
                <td data-label="Status">
                  <span class="status status-<?= e($item['status']) ?>">
                    <?= $item['status'] === 'aktif' ? 'Aktif' : 'Tidak Aktif' ?>
                  </span>
                </td>
                <td data-label="Aksi">
                  <div class="action-group">
                    <a class="btn-action btn-action-view" href="../../cerita-detail.php?id=<?= (int)$item['id'] ?>" target="_blank" rel="noopener">Lihat</a>
                    <a class="btn-action btn-action-edit" href="edit.php?id=<?= (int)$item['id'] ?>">Edit</a>
                    <a class="btn-action btn-action-delete" href="hapus.php?id=<?= (int)$item['id'] ?>&csrf_token=<?= e(csrf_token()) ?>" data-confirm="Apakah Anda yakin ingin menghapus data ini?">Hapus</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?>
              <tr class="empty-search-row">
                <td colspan="5" style="text-align: center; padding: 2.5rem 1.5rem; color: #7A695A;">
                  <?= ($q !== '') ? 'Data tidak ditemukan.' : 'Belum ada cerita atau kegiatan yang ditambahkan.' ?>
                </td>
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
