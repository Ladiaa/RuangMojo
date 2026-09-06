<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = null;
$gallery = [];

if ($pdo && $id) {
    $stmt = $pdo->prepare("SELECT * FROM cerita WHERE id = ? AND status = 'aktif'");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if ($item) {
        $galleryStmt = $pdo->prepare('SELECT foto FROM cerita_foto WHERE cerita_id = ? ORDER BY urutan, id');
        $galleryStmt->execute([$id]);
        $gallery = $galleryStmt->fetchAll();
    }
}

if (!$item) {
    http_response_code(404);
}

$title = $item ? e($item['judul']) . ' - Cerita Mojo' : 'Cerita Tidak Ditemukan - Ruang Mojo';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="container detail-page">
  <nav class="breadcrumb">
    <a href="index.php">Beranda</a> / 
    <a href="tentang.php#cerita">Tentang Mojo</a> / 
    <span><?= e($item['judul'] ?? 'Tidak ditemukan') ?></span>
  </nav>

  <?php if (!$item): ?>
    <div class="empty-state">
      <span class="empty-mark">&#10022;</span>
      <h1>Cerita tidak ditemukan</h1>
      <p>Data cerita yang Anda cari tidak tersedia atau belum dipublikasikan.</p>
      <p style="margin-top: 1.5rem;"><a class="btn btn-primary" href="tentang.php#cerita">&larr; Kembali ke Tentang Mojo</a></p>
    </div>
  <?php else: 
    $images = [];
    if ($item['foto']) {
        $images[] = 'uploads/cerita/' . rawurlencode($item['foto']);
    }
    foreach ($gallery as $photo) {
        $images[] = 'uploads/cerita/' . rawurlencode($photo['foto']);
    }
    if (!$images) {
        $images[] = 'assets/images/placeholder-hero.svg';
    }
  ?>
    <article class="detail">
      <div class="detail-media">
        <div class="carousel" data-carousel>
          <div class="carousel-stage">
            <img data-carousel-image src="<?= e($images[0]) ?>" alt="<?= e($item['judul']) ?>">
            <?php if (count($images) > 1): ?>
              <button class="carousel-control carousel-prev" type="button" data-carousel-prev aria-label="Foto sebelumnya">&larr;</button>
              <button class="carousel-control carousel-next" type="button" data-carousel-next aria-label="Foto berikutnya">&rarr;</button>
            <?php endif; ?>
          </div>
          <div class="carousel-info">
            <span data-carousel-counter>1 / <?= count($images) ?></span>
            <span>Dokumentasi Kegiatan</span>
          </div>
          <?php if (count($images) > 1): ?>
            <div class="carousel-thumbs">
              <?php foreach ($images as $index => $carouselImage): ?>
                <button type="button" data-carousel-thumb="<?= $index ?>" aria-label="Tampilkan foto <?= $index + 1 ?>">
                  <img src="<?= e($carouselImage) ?>" alt="">
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <script type="application/json" data-carousel-images><?= json_encode($images, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        </div>
      </div>

      <div class="detail-body">
        <div style="display: flex; align-items: center; gap: .75rem; margin-bottom: .5rem; flex-wrap: wrap;">
          <span style="font-size: .88rem; color: var(--muted); letter-spacing: .05em; font-weight: 500;"><?= e(format_indo_date($item['tanggal'])) ?></span>
        </div>

        <h1 style="font-size: clamp(2.4rem, 5vw, 4rem); margin: 0.5rem 0 1.5rem;"><?= e($item['judul']) ?></h1>

        <?php if (!empty($item['ringkasan'])): ?>
          <p class="intro-copy" style="font-size: 1.25rem; margin-bottom: 1.5rem;"><?= nl2br(e($item['ringkasan'])) ?></p>
        <?php endif; ?>

        <div style="color: var(--text); font-size: 1.05rem; line-height: 1.8; margin: 2rem 0; border-top: 1px solid var(--soft-beige); padding-top: 1.5rem;">
          <?= nl2br(e($item['deskripsi'])) ?>
        </div>

        <div style="margin-top: 2.5rem;">
          <a class="btn btn-secondary" href="tentang.php#cerita">&larr; Kembali ke Cerita Mojo</a>
        </div>
      </div>
    </article>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
