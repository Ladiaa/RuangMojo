<?php
require_once __DIR__ . '/../auth.php';

$categories = $pdo ? $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll() : [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = potensi_form_data();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif ($data['nama'] === '' || $data['deskripsi'] === '' || !$data['kategori_id']) {
        $error = 'Silakan lengkapi data terlebih dahulu. Nama, kategori, dan deskripsi wajib diisi.';
    } elseif (!$pdo) {
        $error = 'Database belum tersedia.';
    } else {
        $upload = upload_photo($_FILES['foto'] ?? [], 'potensi');
        $galleryUpload = upload_photos($_FILES['galeri'] ?? [], 'potensi');
        if ($upload['error']) {
            $error = $upload['error'];
            foreach ($galleryUpload['names'] as $name) { delete_photo($name, 'potensi'); }
        } else {
            try {
                $pdo->beginTransaction();
                $statement = $pdo->prepare('INSERT INTO potensi (kategori_id,nama,deskripsi,lokasi,alamat,link_maps,latitude,longitude,pengelola,kontak,foto,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
                $statement->execute([
                    $data['kategori_id'], $data['nama'], $data['deskripsi'], $data['lokasi'],
                    $data['alamat'], $data['link_maps'], $data['latitude'], $data['longitude'], $data['pengelola'],
                    $data['kontak'], $upload['name'], $data['status'],
                ]);
                $potensiId = (int)$pdo->lastInsertId();
                if ($galleryUpload['names']) {
                    $galleryStatement = $pdo->prepare('INSERT INTO potensi_foto (potensi_id, foto, urutan) VALUES (?, ?, ?)');
                    foreach ($galleryUpload['names'] as $index => $name) { $galleryStatement->execute([$potensiId, $name, $index + 1]); }
                }
                $pdo->commit();
                set_flash('success', $galleryUpload['errors'] ? 'Data berhasil ditambahkan, namun beberapa foto galeri gagal diunggah.' : 'Data berhasil ditambahkan.');
                foreach ($galleryUpload['errors'] as $galleryError) { error_log($galleryError); }
                redirect('index.php');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                if ($upload['name']) {
                    delete_photo($upload['name'], 'potensi');
                }
                foreach ($galleryUpload['names'] as $name) { delete_photo($name, 'potensi'); }
                $error = 'Terjadi kesalahan saat menambahkan data. Periksa kategori dan isian formulir.';
            }
        }
    }
}

function tambah_value(string $key): string
{
    return e($_POST[$key] ?? '');
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Potensi - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="admin-body">
<main class="form-page">
  <div class="breadcrumb"><a href="index.php">&larr; Kembali ke Data Potensi</a></div>
  <h1>Tambah Potensi Baru</h1>
  <p class="section-desc">Dokumentasikan potensi padukuhan, UMKM, kerajinan, seni, pertanian, atau wisata.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    
    <div class="form-grid">
      <label class="wide">
        Nama Potensi *
        <input type="text" name="nama" required maxlength="150" value="<?= tambah_value('nama') ?>" placeholder="Contoh: Sanggar Batik Tulis Tradisional">
      </label>

      <label>
        Kategori *
        <select name="kategori_id" required>
          <option value="">-- Pilih Kategori --</option>
          <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['id'] ?>" <?= ((int)($_POST['kategori_id'] ?? 0) === (int)$category['id']) ? 'selected' : '' ?>><?= e($category['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Status Publikasi
        <select name="status">
          <option value="aktif" <?= (($_POST['status'] ?? 'aktif') === 'aktif') ? 'selected' : '' ?>>Aktif (Ditampilkan ke publik)</option>
          <option value="nonaktif" <?= (($_POST['status'] ?? '') === 'nonaktif') ? 'selected' : '' ?>>Tidak Aktif (Disembunyikan)</option>
        </select>
      </label>

      <label class="wide">
        Deskripsi Lengkap *
        <textarea name="deskripsi" rows="7" required placeholder="Ceritakan latar belakang, keunikan, produk, dan potensi ini secara lengkap..."><?= tambah_value('deskripsi') ?></textarea>
      </label>

      <label>
        Wilayah / Dusun (Lokasi Singkat)
        <input type="text" name="lokasi" maxlength="150" value="<?= tambah_value('lokasi') ?>" placeholder="Contoh: RT 03 / RW 02, Mojo">
      </label>

      <label>
        Alamat Lengkap
        <input type="text" name="alamat" maxlength="255" value="<?= tambah_value('alamat') ?>" placeholder="Contoh: Padukuhan Mojo, Ngeposari, Semanu">
      </label>

      <div class="wide">
        <label style="display: flex; flex-direction: column; gap: 0.45rem;">
          Link Google Maps
          <div class="maps-input-wrapper">
            <input type="text" id="linkMapsInput" name="link_maps" maxlength="500" value="<?= tambah_value('link_maps') ?>" placeholder="Tempel link Google Maps lokasi potensi">
            <div class="maps-btn-group">
              <a id="btnTestMapLink" class="btn-map-action btn-map-test" href="<?= !empty($_POST['link_maps']) ? e($_POST['link_maps']) : '#' ?>" target="_blank" rel="noopener" style="<?= empty($_POST['link_maps']) ? 'display: none;' : 'display: inline-flex;' ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                  <polyline points="15 3 21 3 21 9"></polyline>
                  <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
                Buka Link
              </a>
              <a class="btn-map-action btn-map-open" href="https://www.google.com/maps/search/?api=1&query=Padukuhan+Mojo+Ngeposari+Semanu" target="_blank" rel="noopener">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                  <line x1="8" y1="2" x2="8" y2="18"></line>
                  <line x1="16" y1="6" x2="16" y2="22"></line>
                </svg>
                Buka Google Maps
              </a>
            </div>
          </div>
          <span style="font-size: 0.8rem; font-weight: 400; color: #7A695A; margin-top: 0.2rem;">Opsional. Isi jika lokasi potensi memiliki tautan Google Maps.</span>
        </label>
      </div>

      <label>
        Nama Pengelola / Pemilik
        <input type="text" name="pengelola" maxlength="150" value="<?= tambah_value('pengelola') ?>" placeholder="Contoh: Ibu Siti &amp; Kelompok Pengrajin">
      </label>

      <label>
        Kontak / WhatsApp
        <input type="text" name="kontak" maxlength="100" value="<?= tambah_value('kontak') ?>" placeholder="Contoh: 0812-3456-7890">
      </label>

      <div>
        <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
          Foto Utama (JPG, PNG, WEBP, Maks. 10MB)
        </span>
        <div class="custom-file-upload">
          <input type="file" name="foto" id="fotoUtamaTambah" class="custom-file-input" accept=".jpg,.jpeg,.png,.webp" data-photo-preview="#mainPhotoPreview">
          <label for="fotoUtamaTambah" class="custom-file-label">
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
        <div id="mainPhotoPreview" class="photo-preview" aria-live="polite"></div>
      </div>

      <div>
        <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
          Galeri Foto Tambahan (Bisa pilih beberapa)
        </span>
        <div class="custom-file-upload">
          <input type="file" name="galeri[]" id="galeriFotoTambah" class="custom-file-input" accept=".jpg,.jpeg,.png,.webp" multiple data-gallery-preview="#galleryPreview">
          <label for="galeriFotoTambah" class="custom-file-label">
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
      <button class="btn btn-primary" type="submit">Simpan Potensi</button>
      <a class="btn" href="index.php" style="background: #E2D8CB; color: #3B2B20;">Batal</a>
    </div>
  </form>
</main>
<script src="../../assets/js/script.js"></script>
</body>
</html>
