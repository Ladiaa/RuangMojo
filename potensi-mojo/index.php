<?php
$title = 'Beranda - Potensi Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$categories = $pdo ? $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY id')->fetchAll() : [];
$activeCount = $pdo ? (int)$pdo->query("SELECT COUNT(*) FROM potensi WHERE status = 'aktif'")->fetchColumn() : 0;
$heroImage = file_exists(__DIR__ . '/assets/images/hero-mojo.png') ? 'assets/images/hero-mojo.png' : 'assets/images/placeholder-hero.svg';
$featured = $pdo ? $pdo->query("SELECT p.id, p.nama, p.deskripsi, p.lokasi, p.foto, k.nama_kategori FROM potensi p JOIN kategori k ON k.id = p.kategori_id WHERE p.status = 'aktif' ORDER BY p.created_at DESC LIMIT 3")->fetchAll() : [];
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="site-main">
  <!-- 1. Hero Section: Unified Global Template -->
  <section class="hero">
    <img src="<?= e($heroImage) ?>" alt="Padukuhan Mojo" class="hero-image hero-image-home" loading="eager">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <p class="hero-eyebrow">POTENSI PADUKUHAN</p>
      <h1 class="hero-title">Padukuhan Mojo</h1>
      <p class="hero-description">Potensi yang tumbuh dari tanah, karya, dan masyarakat. Temukan berbagai potensi lokal, hasil karya, dan cerita masyarakat Padukuhan Mojo.</p>
      <div class="hero-actions">
        <a class="hero-button" href="potensi.php">
          <span>Jelajahi Potensi</span>
          <span aria-hidden="true">&rarr;</span>
        </a>
        <a class="hero-button-outline" href="tentang.php">Kenali Mojo</a>
      </div>
    </div>
  </section>

  <section class="stats-strip container" aria-label="Informasi singkat tentang Mojo">
    <div><strong><?= str_pad((string)$activeCount, 2, '0', STR_PAD_LEFT) ?></strong><span>Potensi terdata</span></div>
    <div><strong><?= str_pad((string)count($categories), 2, '0', STR_PAD_LEFT) ?></strong><span>Kategori potensi</span></div>
  </section>

  <section class="categories container" id="discover">
    <div class="section-heading">
      <p class="eyebrow">Jelajahi berdasarkan bidang</p>
      <h2>Beragam potensi, satu cerita.</h2>
      <p class="section-desc">Temukan berbagai potensi Mojo berdasarkan bidangnya.</p>
    </div>
    <div class="category-grid">
      <?php foreach ($categories as $number => $category): ?>
        <a class="category-card" href="potensi.php?kategori=<?= (int)$category['id'] ?>">
          <div class="cat-index"><?= str_pad((string)($number + 1), 2, '0', STR_PAD_LEFT) ?></div>
          <h3><?= e($category['nama_kategori']) ?></h3>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="featured container">
    <div class="section-heading">
      <p class="eyebrow">Potensi Mojo</p>
      <h2>Kenali potensi di sekitar kita.</h2>
      <p class="section-desc">Berbagai potensi lokal yang tumbuh dan berkembang bersama masyarakat Mojo.</p>
    </div>
    <div class="cards-grid featured-gallery">
      <?php foreach ($featured as $item):
        $image = $item['foto'] ? 'uploads/potensi/' . rawurlencode($item['foto']) : 'assets/images/placeholder-hero.svg';
      ?>
      <article class="card featured-card">
        <div class="card-media"><img src="<?= e($image) ?>" alt="Foto <?= e($item['nama']) ?>"></div>
        <div class="card-body">
          <div class="card-meta"><?= e($item['nama_kategori']) ?></div>
          <h3 class="card-title"><?= e($item['nama']) ?></h3>
          <p class="card-excerpt"><?= e(mb_strimwidth($item['deskripsi'], 0, 120, '...')) ?></p>
          <div class="card-actions">
            <a class="btn btn-ghost" href="detail.php?id=<?= (int)$item['id'] ?>">Lihat Detail <span aria-hidden="true">→</span></a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$featured): ?><p>Belum ada potensi aktif yang dapat ditampilkan.</p><?php endif; ?>
    </div>
    <div class="more-link-wrap">
      <a class="btn btn-secondary" href="potensi.php">Lihat Semua Potensi <span aria-hidden="true">→</span></a>
    </div>
  </section>

  <section class="about container">
    <div class="about-inner">
      <div class="about-left">
        <p class="eyebrow">Tentang Mojo</p>
        <h2>Setiap wilayah memiliki cerita.</h2>
      </div>
      <div class="about-right">
        <p>Padukuhan Mojo merupakan bagian dari Kalurahan Ngeposari, Kapanewon Semanu, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta.</p>
        <p>Mojo memiliki berbagai potensi yang tumbuh dari aktivitas dan karya masyarakat, mulai dari kerajinan, pertanian, produk lokal, hingga sumber daya alam.</p>
        <p>Website ini menjadi ruang untuk mengenal, mendokumentasikan, dan memperkenalkan berbagai potensi tersebut.</p>
        <a class="btn btn-secondary" href="tentang.php">Selengkapnya tentang Mojo <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
