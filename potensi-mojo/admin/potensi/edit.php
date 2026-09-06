<?php
require_once __DIR__ . '/../auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$error = '';
$item = null;
$gallery = [];

if (!$id) {
    $error = 'ID potensi tidak valid.';
} elseif (!$pdo) {
    $error = 'Database belum tersedia.';
} else {
    $statement = $pdo->prepare('SELECT * FROM potensi WHERE id = ? LIMIT 1');
    $statement->execute([$id]);
    $item = $statement->fetch();
    if (!$item) {
        $error = 'Data tidak ditemukan.';
    } else {
        $statement = $pdo->prepare('SELECT * FROM potensi_foto WHERE potensi_id = ? ORDER BY urutan, id');
        $statement->execute([$id]);
        $gallery = $statement->fetchAll();
    }
}

$categories = $pdo ? $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll() : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item) {
    $data = potensi_form_data();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } elseif ($data['nama'] === '' || $data['deskripsi'] === '' || !$data['kategori_id']) {
        $error = 'Silakan lengkapi data terlebih dahulu. Nama, kategori, dan deskripsi wajib diisi.';
    } else {
        $upload = upload_photo($_FILES['foto'] ?? [], 'potensi');
        $galleryUpload = upload_photos($_FILES['galeri'] ?? [], 'potensi');
        if ($upload['error']) {
            $error = $upload['error'];
            foreach ($galleryUpload['names'] as $name) { delete_photo($name, 'potensi'); }
        } else {
            $photo = $upload['name'] ?: $item['foto'];
            try {
                $pdo->beginTransaction();
                $statement = $pdo->prepare('UPDATE potensi SET kategori_id=?, nama=?, deskripsi=?, lokasi=?, alamat=?, link_maps=?, pengelola=?, kontak=?, foto=?, status=? WHERE id=?');
                $statement->execute([$data['kategori_id'], $data['nama'], $data['deskripsi'], $data['lokasi'], $data['alamat'], $data['link_maps'], $data['pengelola'], $data['kontak'], $photo, $data['status'], $id]);
                if ($galleryUpload['names']) {
                    $last = $pdo->prepare('SELECT COALESCE(MAX(urutan), 0) FROM potensi_foto WHERE potensi_id = ?');
                    $last->execute([$id]);
                    $order = (int)$last->fetchColumn();
                    $galleryStatement = $pdo->prepare('INSERT INTO potensi_foto (potensi_id, foto, urutan) VALUES (?, ?, ?)');
                    foreach ($galleryUpload['names'] as $name) {
                        $galleryStatement->execute([$id, $name, ++$order]);
                    }
                }
                $pdo->commit();
                if ($upload['name'] && $item['foto'] && $item['foto'] !== $upload['name']) {
                    delete_photo($item['foto'], 'potensi');
                }
                foreach ($galleryUpload['errors'] as $galleryError) { error_log($galleryError); }
                set_flash('success', $galleryUpload['errors'] ? 'Data berhasil diperbarui, namun beberapa foto galeri gagal diunggah.' : 'Data berhasil diperbarui.');
                redirect('index.php');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) { $pdo->rollBack(); }
                if ($upload['name']) { delete_photo($upload['name'], 'potensi'); }
                foreach ($galleryUpload['names'] as $name) { delete_photo($name, 'potensi'); }
                $error = 'Terjadi kesalahan saat memperbarui data. Pastikan isian formulir valid.';
            }
        }
    }
}

function edit_value(string $key, ?array $item): string { return e($_POST[$key] ?? $item[$key] ?? ''); }
function edit_selected(string $key, string $value, ?array $item): string { return (string)($_POST[$key] ?? $item[$key] ?? '') === $value ? 'selected' : ''; }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ubah Data Potensi - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="admin-body">
<main class="form-page">
  <div class="breadcrumb"><a href="index.php">&larr; Kembali ke Data Potensi</a></div>
  <h1>Ubah Data Potensi</h1>
  <p class="section-desc">Perbarui data, lokasi, pengelola, atau kelola dokumentasi foto potensi.</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if ($item): ?>
    <form class="admin-form" method="post" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      
      <div class="form-grid">
        <label class="wide">
          Nama Potensi *
          <input type="text" name="nama" required maxlength="150" value="<?= edit_value('nama', $item) ?>" placeholder="Contoh: Sanggar Batik Tulis Tradisional">
        </label>

        <label>
          Kategori *
          <select name="kategori_id" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= (int)$category['id'] ?>" <?= ((int)($_POST['kategori_id'] ?? $item['kategori_id']) === (int)$category['id']) ? 'selected' : '' ?>><?= e($category['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Status Publikasi
          <select name="status">
            <option value="aktif" <?= edit_selected('status', 'aktif', $item) ?>>Aktif (Ditampilkan ke publik)</option>
            <option value="nonaktif" <?= edit_selected('status', 'nonaktif', $item) ?>>Tidak Aktif (Disembunyikan)</option>
          </select>
        </label>

        <label class="wide">
          Deskripsi Lengkap *
          <textarea name="deskripsi" rows="7" required placeholder="Tuliskan deskripsi lengkap mengenai potensi ini..."><?= edit_value('deskripsi', $item) ?></textarea>
        </label>

        <label>
          Wilayah / Dusun (Lokasi Singkat)
          <input type="text" name="lokasi" maxlength="150" value="<?= edit_value('lokasi', $item) ?>" placeholder="Contoh: RT 03 / RW 02, Mojo">
        </label>

        <label>
          Alamat Lengkap
          <input type="text" name="alamat" maxlength="255" value="<?= edit_value('alamat', $item) ?>" placeholder="Contoh: Padukuhan Mojo, Ngeposari, Semanu">
        </label>

        <div class="wide">
          <label style="display: flex; flex-direction: column; gap: 0.45rem;">
            Link Google Maps
            <div class="maps-input-wrapper">
              <input type="text" id="linkMapsInput" name="link_maps" maxlength="500" value="<?= edit_value('link_maps', $item) ?>" placeholder="Tempel link Google Maps lokasi potensi">
              <div class="maps-btn-group">
                <a id="btnTestMapLink" class="btn-map-action btn-map-test" href="<?= !empty($item['link_maps']) ? e($item['link_maps']) : '#' ?>" target="_blank" rel="noopener" style="<?= empty($item['link_maps']) ? 'display: none;' : 'display: inline-flex;' ?>">
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
          <input type="text" name="pengelola" maxlength="150" value="<?= edit_value('pengelola', $item) ?>" placeholder="Contoh: Ibu Siti &amp; Kelompok Pengrajin">
        </label>

        <label>
          Kontak / WhatsApp
          <input type="text" name="kontak" maxlength="100" value="<?= edit_value('kontak', $item) ?>" placeholder="Contoh: 0812-3456-7890">
        </label>

        <div class="wide">
          <label>Foto Utama Saat Ini</label>
          <?php if ($item['foto']): ?>
            <div class="current-photo">
              <img src="../../uploads/potensi/<?= rawurlencode($item['foto']) ?>" alt="Foto utama <?= e($item['nama']) ?>">
            </div>
          <?php else: ?>
            <p style="font-size: 0.85rem; color: #888;">Belum ada foto utama.</p>
          <?php endif; ?>
          <div style="margin-top: 0.85rem;">
            <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
              Ganti Foto Utama (Biarkan kosong jika tidak ingin diubah)
            </span>
            <div class="custom-file-upload">
              <input type="file" name="foto" id="fotoUtamaInput" class="custom-file-input" accept=".jpg,.jpeg,.png,.webp" data-photo-preview="#mainPhotoPreview">
              <label for="fotoUtamaInput" class="custom-file-label">
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
              <?php foreach ($gallery as $photo): ?>
                <div class="gallery-admin-item">
                  <img src="../../uploads/potensi/<?= rawurlencode($photo['foto']) ?>" alt="Foto galeri <?= e($item['nama']) ?>">
                  <a class="danger-link" href="hapus-foto.php?id=<?= (int)$photo['id'] ?>&potensi_id=<?= $id ?>&csrf_token=<?= e(csrf_token()) ?>" data-confirm="Apakah Anda yakin ingin menghapus foto ini?">Hapus Foto</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="font-size: 0.88rem; color: #888;">Belum ada foto galeri tambahan.</p>
          <?php endif; ?>

          <div style="margin-top: 1rem;">
            <span style="font-size: 0.88rem; font-weight: 600; color: #3B2B20; display: block; margin-bottom: 0.45rem;">
              Tambah Foto Galeri Baru (Bisa pilih beberapa)
            </span>
            <div class="custom-file-upload">
              <input type="file" name="galeri[]" id="galeriFotoInput" class="custom-file-input" accept=".jpg,.jpeg,.png,.webp" multiple data-gallery-preview="#galleryPreview">
              <label for="galeriFotoInput" class="custom-file-label">
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
          <div id="galleryPreview" class="photo-preview photo-preview-grid"></div>
        </div>
      </div>

      <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap;">
        <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
        <a class="btn" href="index.php" style="background: #E2D8CB; color: #3B2B20;">Batal</a>
      </div>
    </form>
  <?php endif; ?>
</main>
<script src="../../assets/js/script.js"></script>
</body>
</html>
