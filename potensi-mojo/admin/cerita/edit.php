<?php
require_once __DIR__ . '/../auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || !$pdo) {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
    redirect('index.php');
}

$stmt = $pdo->prepare('SELECT * FROM cerita WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('error', 'Data tidak ditemukan.');
    redirect('index.php');
}

$galleryStmt = $pdo->prepare('SELECT * FROM cerita_foto WHERE cerita_id = ? ORDER BY urutan, id');
$galleryStmt->execute([$id]);
$gallery = $galleryStmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = cerita_form_data();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif ($data['judul'] === '' || $data['deskripsi'] === '' || $data['tanggal'] === '') {
        $error = 'Silakan lengkapi data terlebih dahulu. Judul, tanggal, dan deskripsi cerita wajib diisi.';
    } else {
        $upload = upload_photo($_FILES['foto'] ?? [], 'cerita');
        $galleryUpload = upload_photos($_FILES['galeri'] ?? [], 'cerita');

        if ($upload['error']) {
            $error = $upload['error'];
            foreach ($galleryUpload['names'] as $name) {
                delete_photo($name, 'cerita');
            }
        } else {
            try {
                $pdo->beginTransaction();
                $photoName = $upload['name'] ?? $item['foto'];

                $updateStmt = $pdo->prepare('UPDATE cerita SET judul = ?, tanggal = ?, ringkasan = ?, deskripsi = ?, foto = ?, status = ? WHERE id = ?');
                $updateStmt->execute([
                    $data['judul'],
                    $data['tanggal'],
                    $data['ringkasan'],
                    $data['deskripsi'],
                    $photoName,
                    $data['status'],
                    $id,
                ]);

                if ($upload['name'] && $item['foto'] && $item['foto'] !== $upload['name']) {
                    delete_photo($item['foto'], 'cerita');
                }

                if ($galleryUpload['names']) {
                    $maxOrder = (int)$pdo->query("SELECT COALESCE(MAX(urutan), 0) FROM cerita_foto WHERE cerita_id = $id")->fetchColumn();
                    $insertGallery = $pdo->prepare('INSERT INTO cerita_foto (cerita_id, foto, urutan) VALUES (?, ?, ?)');
                    foreach ($galleryUpload['names'] as $index => $name) {
                        $insertGallery->execute([$id, $name, $maxOrder + $index + 1]);
                    }
                }

                $pdo->commit();
                set_flash('success', $galleryUpload['errors'] ? 'Data berhasil diperbarui, namun beberapa foto galeri baru gagal diunggah.' : 'Data berhasil diperbarui.');
                foreach ($galleryUpload['errors'] as $galleryError) {
                    error_log($galleryError);
                }
                redirect('index.php');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($upload['name']) {
                    delete_photo($upload['name'], 'cerita');
                }
                foreach ($galleryUpload['names'] as $name) {
                    delete_photo($name, 'cerita');
                }
                $error = 'Terjadi kesalahan saat memperbarui data. Silakan periksa isian formulir.';
            }
        }
    }
}

function edit_cerita_value(string $key, array $item): string
{
    return e($_POST[$key] ?? $item[$key] ?? '');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ubah Cerita Mojo - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="admin-body">
<main class="form-page">
  <div class="breadcrumb"><a href="index.php">&larr; Kembali ke Cerita Mojo</a></div>
  <h1>Ubah Cerita Mojo</h1>
  <p class="section-desc">Ubah data cerita, perbarui foto, atau kelola galeri dokumentasi.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-grid">
      <label class="wide">
        Judul Cerita / Kegiatan *
        <input type="text" name="judul" value="<?= edit_cerita_value('judul', $item) ?>" required placeholder="Contoh: Kegiatan Pendataan Potensi Mojo">
      </label>

      <label>
        Tanggal Kegiatan / Publikasi *
        <input type="date" name="tanggal" value="<?= edit_cerita_value('tanggal', $item) ?>" required>
      </label>

      <label>
        Status Publikasi
        <select name="status">
          <?php $currentStatus = $_POST['status'] ?? $item['status'] ?? 'aktif'; ?>
          <option value="aktif" <?= $currentStatus === 'aktif' ? 'selected' : '' ?>>Aktif (Ditampilkan ke publik)</option>
          <option value="nonaktif" <?= $currentStatus === 'nonaktif' ? 'selected' : '' ?>>Tidak Aktif (Disembunyikan)</option>
        </select>
      </label>

      <label class="wide">
        Ringkasan Singkat (Opsional - ditampilkan pada kartu)
        <textarea name="ringkasan" rows="2" placeholder="Penjelasan singkat 1-2 kalimat"><?= edit_cerita_value('ringkasan', $item) ?></textarea>
      </label>

      <label class="wide">
        Deskripsi Lengkap *
        <textarea name="deskripsi" rows="7" required placeholder="Tuliskan cerita, jalannya kegiatan, atau informasi lengkap di sini..."><?= edit_cerita_value('deskripsi', $item) ?></textarea>
      </label>

      <div class="wide">
        <label>Foto Utama Saat Ini</label>
        <?php if ($item['foto']): ?>
          <div class="current-photo">
            <img src="../../uploads/cerita/<?= rawurlencode($item['foto']) ?>" alt="Foto Utama" style="width: 200px; height: 130px; object-fit: cover; border-radius: 8px; display: block; margin-bottom: 0.5rem;">
          </div>
        <?php else: ?>
          <p style="font-size: 0.88rem; color: #777;">Belum ada foto utama.</p>
        <?php endif; ?>
        <div style="margin-top: 0.85rem;">
          <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
            Ganti Foto Utama (Biarkan kosong jika tidak ingin mengubah)
          </span>
          <div class="custom-file-upload">
            <input type="file" name="foto" id="fotoUtamaCeritaEdit" class="custom-file-input" accept="image/jpeg,image/png,image/webp" data-photo-preview="#mainPhotoPreview">
            <label for="fotoUtamaCeritaEdit" class="custom-file-label">
              <span class="custom-file-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Pilih Foto
              </span>
              <span class="custom-file-name" data-empty-text="Belum ada foto dipilih">Belum ada foto dipilih</span>
            </label>
          </div>
        </div>
        <div id="mainPhotoPreview" class="photo-preview"></div>
      </div>

      <div class="wide gallery-section">
        <h2>Galeri Foto Tambahan</h2>
        <?php if ($gallery): ?>
          <div class="gallery-admin-grid">
            <?php foreach ($gallery as $gPhoto): ?>
              <div class="gallery-admin-item">
                <img src="../../uploads/cerita/<?= rawurlencode($gPhoto['foto']) ?>" alt="">
                <a class="danger-link" href="hapus-foto.php?id=<?= (int)$gPhoto['id'] ?>&cerita_id=<?= (int)$id ?>&csrf_token=<?= e(csrf_token()) ?>" data-confirm="Apakah Anda yakin ingin menghapus foto ini?">Hapus Foto</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p style="font-size: 0.88rem; color: #777;">Belum ada foto tambahan di galeri.</p>
        <?php endif; ?>

        <div style="margin-top: 1rem;">
          <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
            Tambah Foto Galeri Baru (Bisa pilih beberapa)
          </span>
          <div class="custom-file-upload">
            <input type="file" name="galeri[]" id="galeriFotoCeritaEdit" class="custom-file-input" accept="image/jpeg,image/png,image/webp" multiple data-gallery-preview="#galleryPreview">
            <label for="galeriFotoCeritaEdit" class="custom-file-label">
              <span class="custom-file-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Pilih Foto
              </span>
              <span class="custom-file-name" data-empty-text="Belum ada foto dipilih">Belum ada foto dipilih</span>
            </label>
          </div>
        </div>
        <div id="galleryPreview" class="photo-preview"></div>
      </div>
    </div>

    <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
      <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
      <a class="btn" href="index.php" style="background: #e2d8cb; color: #3B2B20;">Batal</a>
    </div>
  </form>
</main>
<script src="../../assets/js/script.js"></script>
</body>
</html>
