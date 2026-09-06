<?php
$title = 'Dokumentasi - Potensi Mojo';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$items = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT p.id, p.nama, p.deskripsi, p.foto, p.created_at, k.nama_kategori FROM potensi p JOIN kategori k ON k.id = p.kategori_id WHERE p.status = 'aktif' ORDER BY p.created_at DESC");
    $items = $stmt->fetchAll();
}
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<main class="container">
  <section class="documentasi-page">
    <p class="eyebrow">Dokumentasi</p>
    <h1>Cerita dan kegiatan di Mojo.</h1>
    <p class="section-desc">Potensi, kegiatan masyarakat, dan kisah yang terus berkembang di Padukuhan Mojo.</p>

    <div class="cards-grid stories-grid">
      <?php foreach ($items as $item):
        $image = $item['foto'] ? 'uploads/potensi/' . rawurlencode($item['foto']) : 'assets/images/placeholder-hero.svg';
      ?>
      <article class="card story-card">
        <div class="card-media"><img src="<?= e($image) ?>" alt="Dokumentasi <?= e($item['nama']) ?>"></div>
        <div class="card-body">
          <div class="card-meta"><?= e($item['nama_kategori']) ?></div>
          <h3 class="card-title"><?= e($item['nama']) ?></h3>
          <p class="card-excerpt"><?= e(mb_strimwidth($item['deskripsi'], 0, 120, '...')) ?></p>
          <div class="card-actions">
            <a class="btn btn-ghost" href="detail.php?id=<?= (int)$item['id'] ?>">Lihat Detail</a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$items): ?>
        <div class="empty-state"><span class="empty-mark">✦</span><h2>Dokumentasi sedang dikumpulkan</h2><p>Konten baru akan segera hadir seiring dengan proses pendataan potensi di Padukuhan Mojo.</p></div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
