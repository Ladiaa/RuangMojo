<?php
$title = 'Potensi - Ruang Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$heroImage = 'assets/images/banner-potensi-mojo.jpg';
if (!file_exists(__DIR__ . '/' . $heroImage)) {
    $heroImage = file_exists(__DIR__ . '/public/images/banner-potensi-mojo.jpg')
        ? 'public/images/banner-potensi-mojo.jpg'
        : 'assets/images/cta-potensi-mojo.jpg';
}

$query = trim((string)($_GET['q'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT);
$items = [];
$categories = [];

if ($pdo) {
    // Kategori untuk dropdown filter
    $categories = $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();

    // Query data potensi aktif
    $sql = "SELECT p.*, k.nama_kategori FROM potensi p JOIN kategori k ON k.id = p.kategori_id WHERE p.status = 'aktif'";
    $params = [];
    if ($query !== '') {
        $sql .= ' AND (p.nama LIKE ? OR p.deskripsi LIKE ?)';
        $params[] = "%{$query}%";
        $params[] = "%{$query}%";
    }
    if ($categoryId) {
        $sql .= ' AND p.kategori_id = ?';
        $params[] = $categoryId;
    }
    $sql .= ' ORDER BY p.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="public-site potensi-page-wrapper">
  <!-- 1. HERO SECTION: UNIFIED GLOBAL TEMPLATE -->
  <section class="hero">
    <img src="<?= e($heroImage) ?>" alt="Aktivitas masyarakat Mojo" class="hero-image hero-image-potensi" loading="eager">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">POTENSI TUMBUH DARI MASYARAKAT</p>
      <h1 class="hero-title">Keterampilan lokal,<br>keberkahan untuk bersama.</h1>
      <p class="hero-description">Berbagai potensi di Mojo lahir dari aktivitas, ketekunan, dan kreativitas masyarakatnya.</p>
      
      <div class="hero-actions">
        <a class="hero-button" href="#daftar-potensi">
          <span>Jelajahi Potensi</span>
          <span aria-hidden="true">&rarr;</span>
        </a>
      </div>

      <div class="hero-scroll">
        <a href="#daftar-potensi" class="hero-scroll-link">
          <span class="scroll-icon-circle" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="8 12 12 16 16 12"></polyline>
              <line x1="12" y1="8" x2="12" y2="16"></line>
            </svg>
          </span>
          <span>Scroll untuk menjelajah</span>
        </a>
      </div>
    </div>

  </section>

  <!-- 2. SECTION DAFTAR POTENSI -->
  <section class="potensi-section-editorial" id="daftar-potensi">
    <div class="container potensi-editorial-container">
      
      <!-- Section Heading -->
      <header class="potensi-editorial-header">
        <p class="eyebrow potensi-section-eyebrow">POTENSI MOJO</p>
        <h2 class="potensi-section-title">Beragam potensi lokal Mojo.</h2>
        <p class="potensi-section-desc">Kenali berbagai potensi yang tumbuh dari kehidupan dan aktivitas masyarakat Padukuhan Mojo.</p>
      </header>

      <!-- 3. SEARCH & FILTER BAR -->
      <div class="potensi-search-filter-wrap">
        <div class="potensi-search-filter-card">
          <div class="potensi-search-input-col">
            <svg class="potensi-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="search" placeholder="Cari potensi..." id="searchInput" value="<?= e($query) ?>" autocomplete="off" aria-label="Cari potensi">
          </div>
          <div class="potensi-filter-select-col">
            <select id="filterCategory" aria-label="Filter kategori potensi">
              <option value="">Semua kategori</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= ((string)($categoryId ?: '') === (string)$category['id']) ? 'selected' : '' ?>>
                  <?= e($category['nama_kategori']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <!-- 4. GRID DATA POTENSI -->
      <div id="list" class="cards-grid potensi-cards-grid">
        <?php foreach ($items as $item): 
          $image = $item['foto'] ? 'uploads/potensi/' . rawurlencode($item['foto']) : 'assets/images/placeholder-hero.svg'; 
        ?>
        <article class="card potensi-card">
          <div class="card-media potensi-card-media">
            <img src="<?= e($image) ?>" alt="<?= $item['foto'] ? 'Foto ' . e($item['nama']) : 'Dokumentasi belum tersedia' ?>" loading="lazy">
          </div>
          <div class="card-body potensi-card-body" data-category="<?= (int)$item['kategori_id'] ?>">
            <div class="card-meta potensi-card-meta"><?= e($item['nama_kategori']) ?></div>
            <h3 class="card-title potensi-card-title"><?= e($item['nama']) ?></h3>
            <p class="card-excerpt potensi-card-desc"><?= e(mb_strimwidth($item['deskripsi'], 0, 120, '...')) ?></p>
            <div class="card-actions potensi-card-actions">
              <a class="btn-detail-link" href="detail.php?id=<?= (int)$item['id'] ?>">
                <span>Lihat Detail</span>
                <span class="link-arrow" aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>

        <?php if (!$items): ?>
        <div class="potensi-empty-message" id="potensiEmptyState">
          <p>Belum ada potensi aktif yang sesuai pencarian.</p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
