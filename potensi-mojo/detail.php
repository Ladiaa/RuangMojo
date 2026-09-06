<?php
$title = 'Detail Potensi - Potensi Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$item = null;
if ($pdo && $id) { $stmt = $pdo->prepare("SELECT p.*, k.nama_kategori FROM potensi p JOIN kategori k ON k.id = p.kategori_id WHERE p.id = ? AND p.status = 'aktif'"); $stmt->execute([$id]); $item = $stmt->fetch(); }
$gallery = [];
if ($item && $pdo) { $stmt = $pdo->prepare('SELECT foto FROM potensi_foto WHERE potensi_id = ? ORDER BY urutan, id'); $stmt->execute([$id]); $gallery = $stmt->fetchAll(); }
if (!$item) { http_response_code(404); }
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="container detail-page">
  <nav class="breadcrumb"><a href="index.php">Beranda</a> / <a href="potensi.php">Potensi</a> / <?= e($item['nama'] ?? 'Tidak ditemukan') ?></nav>
  <?php if (!$item): ?><h1>Potensi tidak ditemukan</h1><p>Data tidak tersedia atau belum aktif.</p><?php else: $images = []; if ($item['foto']) { $images[] = 'uploads/potensi/' . rawurlencode($item['foto']); } foreach ($gallery as $photo) { $images[] = 'uploads/potensi/' . rawurlencode($photo['foto']); } if (!$images) { $images[] = 'assets/images/placeholder-hero.svg'; } ?>
  <article class="detail">
    <div class="detail-media">
      <div class="carousel" data-carousel>
        <div class="carousel-stage"><img data-carousel-image src="<?= e($images[0]) ?>" alt="<?= $item['foto'] ? 'Foto ' . e($item['nama']) : 'Dokumentasi belum tersedia' ?>"><?php if (count($images) > 1): ?><button class="carousel-control carousel-prev" type="button" data-carousel-prev aria-label="Foto sebelumnya">&larr;</button><button class="carousel-control carousel-next" type="button" data-carousel-next aria-label="Foto berikutnya">&rarr;</button><?php endif; ?></div>
        <div class="carousel-info"><span data-carousel-counter>1 / <?= count($images) ?></span><span>Dokumentasi</span></div>
        <?php if (count($images) > 1): ?><div class="carousel-thumbs"><?php foreach ($images as $index => $carouselImage): ?><button type="button" data-carousel-thumb="<?= $index ?>" aria-label="Tampilkan foto <?= $index + 1 ?>"><img src="<?= e($carouselImage) ?>" alt=""></button><?php endforeach; ?></div><?php endif; ?>
        <script type="application/json" data-carousel-images><?= json_encode($images, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
      </div>
    </div>
    <div class="detail-body">
      <div class="card-meta"><?= e($item['nama_kategori']) ?></div><h1><?= e($item['nama']) ?></h1><p class="lead"><?= nl2br(e($item['deskripsi'])) ?></p>

      <dl class="info-list">
        <dt>Lokasi</dt><dd><?= e($item['lokasi'] ?: '-') ?></dd><dt>Alamat</dt><dd><?= e($item['alamat'] ?: '-') ?></dd><dt>Pengelola</dt><dd><?= e($item['pengelola'] ?: '-') ?></dd><dt>Kontak</dt><dd><?= e($item['kontak'] ?: '-') ?></dd>
      </dl>
      <?php 
        $rawMapUrl = trim((string)($item['link_maps'] ?? ''));
        if ($rawMapUrl !== ''): 
          $mapUrl = preg_match('~^https?://~i', $rawMapUrl) ? $rawMapUrl : 'https://' . $rawMapUrl;
      ?>
        <p style="margin-top: 1.5rem;">
          <a class="btn btn-secondary" target="_blank" rel="noopener" href="<?= e($mapUrl) ?>">
            <span>Lihat Lokasi di Google Maps</span>
            <span aria-hidden="true">&rarr;</span>
          </a>
        </p>
      <?php endif; ?>
    </div>
  </article>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
