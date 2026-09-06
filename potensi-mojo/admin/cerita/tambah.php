<?php
require_once __DIR__ . '/../auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = cerita_form_data();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif ($data['judul'] === '' || $data['deskripsi'] === '' || $data['tanggal'] === '') {
        $error = 'Silakan lengkapi data terlebih dahulu. Judul, tanggal, dan deskripsi cerita wajib diisi.';
    } elseif (!$pdo) {
        $error = 'Database belum tersedia.';
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
                $statement = $pdo->prepare('INSERT INTO cerita (judul, kategori_cerita, tanggal, ringkasan, deskripsi, foto, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([
                    $data['judul'],
                    $data['kategori_cerita'],
                    $data['tanggal'],
                    $data['ringkasan'],
                    $data['deskripsi'],
                    $upload['name'],
                    $data['status'],
                ]);
                $ceritaId = (int)$pdo->lastInsertId();

                if ($galleryUpload['names']) {
                    $galleryStatement = $pdo->prepare('INSERT INTO cerita_foto (cerita_id, foto, urutan) VALUES (?, ?, ?)');
                    foreach ($galleryUpload['names'] as $index => $name) {
                        $galleryStatement->execute([$ceritaId, $name, $index + 1]);
                    }
                }

                $pdo->commit();
                set_flash('success', $galleryUpload['errors'] ? 'Data berhasil ditambahkan, namun beberapa foto galeri gagal diunggah.' : 'Data berhasil ditambahkan.');
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
                $error = 'Terjadi kesalahan saat menambahkan data. Silakan periksa isian formulir.';
            }
        }
    }
}

function tambah_cerita_value(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Cerita Mojo - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="admin-body">
<main class="form-page">
  <div class="breadcrumb"><a href="index.php">&larr; Kembali ke Cerita Mojo</a></div>
  <h1>Tambah Cerita Mojo</h1>
  <p class="section-desc">Tambahkan cerita, kegiatan padukuhan, dokumentasi, atau kabar dari Padukuhan Mojo.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

    <div class="form-grid">
      <label class="wide">
        Judul Cerita / Kegiatan *
        <input type="text" name="judul" value="<?= tambah_cerita_value('judul') ?>" required placeholder="Contoh: Kegiatan Pendataan Potensi Mojo">
      </label>

      <label>
        Tanggal Kegiatan / Publikasi *
        <input type="date" name="tanggal" value="<?= tambah_cerita_value('tanggal', date('Y-m-d')) ?>" required>
      </label>

      <label>
        Status Publikasi
        <select name="status">
          <option value="aktif" <?= ($_POST['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif (Ditampilkan ke publik)</option>
          <option value="nonaktif" <?= ($_POST['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Tidak Aktif (Disembunyikan)</option>
        </select>
      </label>

      <label class="wide">
        Ringkasan Singkat (Opsional - ditampilkan pada kartu)
        <textarea name="ringkasan" rows="2" placeholder="Penjelasan singkat 1-2 kalimat"><?= tambah_cerita_value('ringkasan') ?></textarea>
      </label>

      <label class="wide">
        Deskripsi Lengkap *
        <textarea name="deskripsi" rows="7" required placeholder="Tuliskan cerita, jalannya kegiatan, atau informasi lengkap di sini..."><?= tambah_cerita_value('deskripsi') ?></textarea>
      </label>

      <div>
        <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
          Foto Utama (JPG, PNG, WEBP, Maks. 10MB)
        </span>
        <div class="custom-file-upload">
          <input type="file" name="foto" id="fotoUtamaCeritaTambah" class="custom-file-input" accept="image/jpeg,image/png,image/webp" data-photo-preview="#mainPhotoPreview">
          <label for="fotoUtamaCeritaTambah" class="custom-file-label">
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
        <div id="mainPhotoPreview" class="photo-preview"></div>
      </div>

      <div>
        <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
          Galeri Foto Tambahan (Bisa pilih beberapa)
        </span>
        <div class="custom-file-upload">
          <input type="file" name="galeri[]" id="galeriFotoCeritaTambah" class="custom-file-input" accept="image/jpeg,image/png,image/webp" multiple data-gallery-preview="#galleryPreview">
          <label for="galeriFotoCeritaTambah" class="custom-file-label">
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
        <div id="galleryPreview" class="photo-preview"></div>
      </div>
    </div>

    <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
      <button class="btn btn-primary" type="submit">Simpan Cerita</button>
      <a class="btn" href="index.php" style="background: #e2d8cb; color: #3B2B20;">Batal</a>
    </div>
  </form>
</main>
<script src="../../assets/js/script.js"></script>
</body>
</html>
