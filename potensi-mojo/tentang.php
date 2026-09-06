<?php
$title = 'Tentang Mojo - Ruang Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Foto dokumentasi utama untuk cinematic full-bleed hero
$aboutHeroImage = 'assets/images/ibu-petani.jpg';
if (!file_exists(__DIR__ . '/' . $aboutHeroImage)) {
    $aboutHeroImage = file_exists(__DIR__ . '/public/images/ibu-petani.jpg')
        ? 'public/images/ibu-petani.jpg'
        : 'assets/images/about-mojo.jpg';
}

$mapLink = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Padukuhan Mojo, Ngeposari, Semanu, Gunungkidul, Yogyakarta');


// Dynamic stories from database (only active ones)
$stories = [];
if ($pdo) {
    $stories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM cerita_foto cf WHERE cf.cerita_id = c.id) AS gallery_count FROM cerita c WHERE c.status = 'aktif' ORDER BY c.tanggal DESC, c.created_at DESC")->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="public-site about-page-wrapper">
  <!-- 1. Hero Section: Unified Global Template -->
  <section class="hero">
    <img src="<?= e($aboutHeroImage) ?>" alt="Masyarakat Padukuhan Mojo" class="hero-image hero-image-about" loading="eager">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">TENTANG PADUKUHAN MOJO</p>
      <h1 class="hero-title">Setiap wilayah<br>memiliki cerita.</h1>
      <p class="hero-description">Padukuhan Mojo merupakan bagian dari Kalurahan Ngeposari, Kapanewon Semanu, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta. Mojo dikenal sebagai kawasan yang kaya akan alam, budaya, dan potensi masyarakat yang terus berkembang.</p>
      <div class="hero-actions">
        <a class="hero-button" href="#mengenal">Mengenal Mojo</a>
        <a class="hero-button-outline" href="#cerita">Cerita &amp; Kegiatan</a>
      </div>
      <div class="hero-scroll">
        <a href="#mengenal" class="hero-scroll-link">
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

  <!-- 2. Section Mengenal Mojo -->
  <section class="about-section about-mengenal" id="mengenal">
    <div class="container">
      <div class="about-editorial-header">
        <p class="eyebrow">TENTANG MOJO</p>
        <h2>Mengenal Mojo lebih dekat.</h2>
      </div>

      <div class="about-editorial-grid">
        <div class="about-editorial-col">
          <div class="about-editorial-card has-media">
            <div class="about-editorial-media">
              <img src="assets/images/gotong-royong-mojo.jpg" alt="Aktivitas gotong royong warga Padukuhan Mojo" loading="lazy">
            </div>
            <div class="about-editorial-content">
              <h3>Bersama Menjaga Mojo</h3>
              <p>Gotong royong menjadi bagian dari keseharian warga dalam menjaga lingkungan dan memperkuat kebersamaan.</p>
            </div>
          </div>
          <div class="about-editorial-card has-media">
            <div class="about-editorial-media">
              <img src="assets/images/masyarakat-aktivitas-mojo.jpg" alt="Kebersamaan warga dan kegiatan masyarakat Padukuhan Mojo" loading="lazy">
            </div>
            <div class="about-editorial-content">
              <h3>Kebersamaan Warga</h3>
              <p>Berbagai kegiatan bersama menjadi ruang bagi warga untuk berkumpul, berinteraksi, dan mempererat hubungan antar masyarakat.</p>
            </div>
          </div>
        </div>

        <div class="about-editorial-col">
          <div class="about-editorial-card has-media">
            <div class="about-editorial-media">
              <img src="assets/images/alam-budaya-mojo.jpg" alt="Bentang alam karst dan batuan alam khas Padukuhan Mojo" loading="lazy">
            </div>
            <div class="about-editorial-content">
              <h3>Kekayaan Alam Mojo</h3>
              <p>Bentang batuan dan karakter alam karst menjadi bagian dari kekayaan alam yang membentuk wajah Padukuhan Mojo.</p>
            </div>
          </div>
          <div class="about-editorial-card has-media">
            <div class="about-editorial-media">
              <img src="assets/images/dokumentasi-informasi-mojo.jpg" alt="Musyawarah dan pencatatan informasi Padukuhan Mojo" loading="lazy">
            </div>
            <div class="about-editorial-content">
              <h3>Musyawarah Warga</h3>
              <p>Ruang bersama untuk berdiskusi, berbagi informasi, dan membangun kesepakatan bagi kehidupan Padukuhan Mojo.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 3. Section Lokasi -->
  <section class="about-section about-location-section">
    <div class="container">
      <div class="about-location-card">
        <div class="about-location-info">
          <p class="eyebrow">LOKASI PADUKUHAN</p>
          <h2>Wilayah yang Asri &amp; Strategis</h2>
          <div class="location-address-box">
            <strong>PADUKUHAN MOJO</strong>
            <p>Kalurahan Ngeposari, Kapanewon Semanu<br>Kabupaten Gunungkidul<br>Daerah Istimewa Yogyakarta</p>
          </div>
          <a class="btn btn-primary about-map-btn" href="<?= e($mapLink) ?>" target="_blank" rel="noopener">
            <span>Lihat Lokasi di Google Maps</span>
            <span aria-hidden="true">&rarr;</span>
          </a>
        </div>
        <div class="about-location-badge-box">
          <div class="location-badge-item">
            <span class="badge-num">01</span>
            <span class="badge-label">Kalurahan Ngeposari</span>
          </div>
          <div class="location-badge-item">
            <span class="badge-num">02</span>
            <span class="badge-label">Kapanewon Semanu</span>
          </div>
          <div class="location-badge-item">
            <span class="badge-num">03</span>
            <span class="badge-label">Kabupaten Gunungkidul</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 5. Section Cerita Mojo (Dinamis dari Database) -->
  <section class="about-section about-cerita-section" id="cerita">
    <div class="container">
      <div class="section-heading">
        <p class="eyebrow">CERITA MOJO</p>
        <h2>Cerita yang terus tumbuh.</h2>
        <p class="section-desc">Kegiatan, aktivitas masyarakat, perkembangan potensi, dan berbagai cerita dari Padukuhan Mojo.</p>
      </div>

      <div class="cerita-grid">
        <?php foreach ($stories as $story): 
          $storyPhoto = $story['foto'] ? 'uploads/cerita/' . rawurlencode($story['foto']) : 'assets/images/placeholder-hero.svg';
          $photoCount = ($story['foto'] ? 1 : 0) + (int)($story['gallery_count'] ?? 0);
          $excerpt = !empty($story['ringkasan']) ? $story['ringkasan'] : $story['deskripsi'];
        ?>
          <article class="cerita-card">
            <div class="cerita-card-media">
              <img src="<?= e($storyPhoto) ?>" alt="<?= e($story['judul']) ?>" loading="lazy">
              <?php if ($photoCount > 1): ?>
                <span class="cerita-gallery-indicator">&#128247; <?= $photoCount ?></span>
              <?php endif; ?>
            </div>
            <div class="cerita-card-body">
              <div class="cerita-card-date"><?= e(format_indo_date($story['tanggal'])) ?></div>
              <h3 class="cerita-card-title"><?= e($story['judul']) ?></h3>
              <p class="cerita-card-excerpt"><?= e($excerpt) ?></p>
              <div class="cerita-card-actions">
                <a class="btn btn-secondary cerita-detail-btn" href="cerita-detail.php?id=<?= (int)$story['id'] ?>">
                  <span>Lihat Detail</span>
                  <span aria-hidden="true">&rarr;</span>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>

        <?php if (!$stories): ?>
          <div class="empty-state wide" style="grid-column: 1 / -1;">
            <span class="empty-mark">&#10022;</span>
            <h3>Belum ada cerita yang dipublikasikan</h3>
            <p>Dokumentasi kegiatan dan cerita terbaru masyarakat Padukuhan Mojo akan segera hadir di sini.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
