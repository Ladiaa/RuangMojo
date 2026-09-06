<?php
require_once __DIR__ . '/auth.php';

$adminId = (int)($_SESSION['admin_id'] ?? 0);
$error = '';
$success = consume_flash('success');

if (!$adminId || !$pdo) {
    set_flash('error', 'Sesi tidak valid atau database belum tersedia.');
    redirect('dashboard.php');
}

// Fetch current user data
$stmt = $pdo->prepare('SELECT id, nama, username, email, role FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'Data pengguna tidak ditemukan.');
    redirect('dashboard.php');
}

// Fetch current contact data
$kontak = get_kontak_info($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan coba lagi.';
    } else {
        $formAction = (string)($_POST['form_action'] ?? '');

        if ($formAction === 'kontak') {
            $dataKontak = kontak_form_data();

            if ($dataKontak['nama_padukuhan'] === '' || $dataKontak['alamat'] === '') {
                $error = 'Nama padukuhan dan alamat lengkap wajib diisi.';
            } else {
                try {
                    // Update or insert into kontak_info
                    $stmtCheck = $pdo->query('SELECT COUNT(*) FROM kontak_info WHERE id = 1');
                    $exists = (int)$stmtCheck->fetchColumn() > 0;

                    if ($exists) {
                        $updateKontak = $pdo->prepare('UPDATE kontak_info SET nama_padukuhan = ?, alamat = ?, nama_pengelola = NULL, nomor_whatsapp = ?, link_maps = ?, deskripsi = ? WHERE id = 1');
                        $updateKontak->execute([
                            $dataKontak['nama_padukuhan'],
                            $dataKontak['alamat'],
                            $dataKontak['nomor_whatsapp'],
                            $dataKontak['link_maps'],
                            $dataKontak['deskripsi'],
                        ]);
                    } else {
                        $insertKontak = $pdo->prepare('INSERT INTO kontak_info (id, nama_padukuhan, alamat, nama_pengelola, nomor_whatsapp, link_maps, deskripsi) VALUES (1, ?, ?, NULL, ?, ?, ?)');
                        $insertKontak->execute([
                            $dataKontak['nama_padukuhan'],
                            $dataKontak['alamat'],
                            $dataKontak['nomor_whatsapp'],
                            $dataKontak['link_maps'],
                            $dataKontak['deskripsi'],
                        ]);
                    }

                    set_flash('success', 'Informasi kontak berhasil diperbarui.');
                    redirect('profile.php');
                } catch (PDOException $e) {
                    $error = 'Terjadi kesalahan saat menyimpan informasi kontak. Silakan coba lagi.';
                }
            }
        } elseif ($formAction === 'akun') {
            $nama = trim((string)($_POST['nama'] ?? ''));
            $username = trim((string)($_POST['username'] ?? ''));
            $passwordLama = (string)($_POST['password_lama'] ?? '');
            $passwordBaru = (string)($_POST['password_baru'] ?? '');
            $konfirmasiPassword = (string)($_POST['konfirmasi_password'] ?? '');

            if ($nama === '' || $username === '') {
                $error = 'Nama lengkap administrator dan username wajib diisi.';
            } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
                $error = 'Username harus terdiri dari 3-50 karakter alfanumerik (huruf, angka, titik, strip, atau garis bawah).';
            } else {
                // Check if username is used by another user
                $checkUsername = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1');
                $checkUsername->execute([$username, $adminId]);
                if ($checkUsername->fetch()) {
                    $error = 'Username tersebut sudah digunakan oleh akun lain.';
                } else {
                    $updatePassword = false;
                    $newPasswordHash = null;

                    if ($passwordBaru !== '' || $passwordLama !== '' || $konfirmasiPassword !== '') {
                        // Fetch password hash from DB
                        $passStmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                        $passStmt->execute([$adminId]);
                        $currentHash = (string)$passStmt->fetchColumn();

                        if (!password_verify($passwordLama, $currentHash)) {
                            $error = 'Kata sandi saat ini yang Anda masukkan salah.';
                        } elseif (strlen($passwordBaru) < 6) {
                            $error = 'Kata sandi baru minimal harus terdiri dari 6 karakter.';
                        } elseif ($passwordBaru !== $konfirmasiPassword) {
                            $error = 'Konfirmasi kata sandi baru tidak cocok.';
                        } else {
                            $updatePassword = true;
                            $newPasswordHash = password_hash($passwordBaru, PASSWORD_DEFAULT);
                        }
                    }

                    if (!$error) {
                        try {
                            if ($updatePassword && $newPasswordHash) {
                                $updateStmt = $pdo->prepare('UPDATE users SET nama = ?, username = ?, password = ? WHERE id = ?');
                                $updateStmt->execute([$nama, $username, $newPasswordHash, $adminId]);
                                $msg = 'Profil dan kata sandi berhasil diperbarui.';
                            } else {
                                $updateStmt = $pdo->prepare('UPDATE users SET nama = ?, username = ? WHERE id = ?');
                                $updateStmt->execute([$nama, $username, $adminId]);
                                $msg = 'Informasi profil berhasil diperbarui.';
                            }

                            $_SESSION['admin_name'] = $nama;
                            set_flash('success', $msg);
                            redirect('profile.php');
                        } catch (PDOException $e) {
                            $error = 'Terjadi kesalahan saat menyimpan perubahan. Silakan coba lagi.';
                        }
                    }
                }
            }
        }
    }
}

$adminName = $_SESSION['admin_name'] ?? $user['nama'];
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil &amp; Kontak - Ruang Mojo Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="admin-body">
<div class="admin-backdrop" id="adminBackdrop"></div>
<div class="admin-layout">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-top">
      <div class="brand-row">
        <a class="brand" href="../index.php">
          <img class="brand-logo" src="../assets/images/logo-clean.png" alt="Logo Ruang Mojo">
          <div class="brand-text">
            <span class="brand-name">Ruang Mojo</span>
            <span class="brand-tag">Panel Admin</span>
          </div>
        </a>
        <button class="sidebar-close-btn" id="adminSidebarClose" aria-label="Tutup Menu">&times;</button>
      </div>
      <nav>
        <a href="dashboard.php">
          <span>Dasbor</span>
        </a>
        <a href="potensi/index.php">
          <span>Data Potensi</span>
        </a>
        <a href="kategori/index.php">
          <span>Kategori</span>
        </a>
        <a href="cerita/index.php">
          <span>Cerita Mojo</span>
        </a>
        <a class="active" href="profile.php">
          <span>Ubah Profil</span>
        </a>
        <a class="admin-logout-trigger" data-logout-trigger="true" href="logout.php" title="Keluar dari Panel Admin">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
          <span>Keluar</span>
        </a>
      </nav>
    </div>
    <div class="sidebar-footer">
      <a class="user-badge" href="profile.php" style="text-decoration: none;">
        <div class="user-avatar"><?= e($adminInitial) ?></div>
        <div class="user-details">
          <span class="user-name"><?= e($adminName) ?></span>
          <span class="user-role">Administrator</span>
        </div>
      </a>
    </div>
  </aside>

  <main class="admin-main">
    <header class="admin-top">
      <div class="top-left">
        <button class="admin-burger-btn" id="adminBurgerBtn" aria-label="Buka Menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <a class="admin-mobile-brand" href="../index.php">
          <img src="../assets/images/logo-dark.png" alt="Logo">
          <span>Ruang Mojo</span>
        </a>
        <span class="top-title-desktop">Pengaturan Profil &amp; Informasi Kontak</span>
      </div>
      <a class="top-btn-site" href="../index.php" target="_blank" rel="noopener">
        <span>Lihat Website</span>
        <span aria-hidden="true">&rarr;</span>
      </a>
    </header>

    <section class="admin-content">
      <div style="margin-bottom: 2rem;">
        <h1>Pengaturan Profil &amp; Kontak</h1>
        <p class="section-desc">Kelola informasi kontak publik Padukuhan Mojo dan pengaturan akun administrator.</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
      <?php endif; ?>

      <div style="display: flex; flex-direction: column; gap: 2rem; max-width: 860px;">
        <!-- PANEL 1: INFORMASI KONTAK PUBLIK -->
        <div class="admin-panel" style="padding: 2.25rem 2.5rem; box-sizing: border-box;">
          <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="kontak">

            <div style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(107,79,56,0.12); padding-bottom: 1rem;">
              <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #2B211A; margin: 0 0 0.4rem;">Informasi Kontak Padukuhan</h2>
              <p style="color: #7A695A; font-size: 0.88rem; margin: 0;">Konten yang akan ditampilkan secara otomatis pada halaman <strong>Kontak</strong> website publik.</p>
            </div>

            <div class="form-grid">
              <label class="wide">
                Nama Padukuhan *
                <input type="text" name="nama_padukuhan" required maxlength="150" value="<?= e($_POST['nama_padukuhan'] ?? $kontak['nama_padukuhan']) ?>" placeholder="Contoh: Padukuhan Mojo">
              </label>

              <label class="wide">
                Alamat Lengkap *
                <textarea name="alamat" rows="3" required placeholder="Contoh: Kalurahan Ngeposari, Kapanewon Semanu, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta"><?= e($_POST['alamat'] ?? $kontak['alamat']) ?></textarea>
              </label>

              <label class="wide">
                Nomor WhatsApp
                <input type="text" name="nomor_whatsapp" maxlength="50" value="<?= e($_POST['nomor_whatsapp'] ?? $kontak['nomor_whatsapp']) ?>" placeholder="Contoh: 0812-3456-7890">
                <span style="font-size: 0.8rem; font-weight: 400; color: #7A695A; margin-top: 0.2rem;">Format nomor ponsel / WhatsApp (misal: 08123456789).</span>
              </label>

              <div class="wide">
                <label style="display: flex; flex-direction: column; gap: 0.45rem;">
                  Link Google Maps
                  <div class="maps-input-wrapper">
                    <input type="text" id="linkMapsInput" name="link_maps" maxlength="500" value="<?= e($_POST['link_maps'] ?? $kontak['link_maps']) ?>" placeholder="Tempel link Google Maps lokasi Padukuhan Mojo">
                    <div class="maps-btn-group">
                      <a id="btnTestMapLink" class="btn-map-action btn-map-test" href="<?= !empty($kontak['link_maps']) ? e($kontak['link_maps']) : '#' ?>" target="_blank" rel="noopener" style="<?= empty($kontak['link_maps']) ? 'display: none;' : 'display: inline-flex;' ?>">
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
                  <span style="font-size: 0.8rem; font-weight: 400; color: #7A695A; margin-top: 0.2rem;">Tautan menuju lokasi Padukuhan Mojo di Google Maps.</span>
                </label>
              </div>

              <label class="wide">
                Deskripsi Singkat Kontak
                <textarea name="deskripsi" rows="2" placeholder="Contoh: Ingin mengenal lebih dekat potensi Mojo? Temukan informasi dan berbagai cerita tentang Mojo melalui Ruang Mojo, atau hubungi kami untuk mendapatkan informasi lebih lanjut."><?= e($_POST['deskripsi'] ?? $kontak['deskripsi']) ?></textarea>
              </label>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.75rem; flex-wrap: wrap;">
              <button class="btn btn-primary" type="submit">Simpan Perubahan Kontak</button>
            </div>
          </form>
        </div>

        <!-- PANEL 2: AKUN ADMINISTRATOR & KATA SANDI -->
        <div class="admin-panel" style="padding: 2.25rem 2.5rem; box-sizing: border-box;">
          <form class="admin-form" method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_action" value="akun">

            <div style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(107,79,56,0.12); padding-bottom: 1rem;">
              <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #2B211A; margin: 0 0 0.4rem;">Informasi Akun Administrator</h2>
              <p style="color: #7A695A; font-size: 0.88rem; margin: 0;">Data identitas yang digunakan untuk masuk ke panel admin.</p>
            </div>

            <div class="form-grid">
              <label class="wide">
                Nama Lengkap Administrator *
                <input type="text" name="nama" required maxlength="100" value="<?= e($_POST['nama'] ?? $user['nama']) ?>" placeholder="Nama Administrator">
              </label>

              <label class="wide">
                Username *
                <input type="text" name="username" required maxlength="50" autocomplete="username" value="<?= e($_POST['username'] ?? $user['username']) ?>" placeholder="Masukkan username admin">
                <span style="font-size: 0.8rem; font-weight: 400; color: #7A695A; margin-top: 0.2rem;">Username digunakan untuk masuk ke panel admin.</span>
              </label>
            </div>

            <div style="margin: 2rem 0 1rem; border-top: 1px solid rgba(107,79,56,0.12); padding-top: 1.75rem;">
              <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; color: #2B211A; margin: 0 0 0.4rem;">Ubah Kata Sandi</h2>
              <p style="color: #7A695A; font-size: 0.88rem; margin: 0 0 1.25rem;">Biarkan seluruh kolom kata sandi kosong jika tidak ingin mengubah kata sandi.</p>

              <div class="form-grid">
                <label class="wide">
                  Kata Sandi Saat Ini
                  <input type="password" name="password_lama" autocomplete="current-password" placeholder="Masukkan kata sandi saat ini">
                </label>

                <label>
                  Kata Sandi Baru (Minimal 6 karakter)
                  <input type="password" name="password_baru" autocomplete="new-password" placeholder="Kata sandi baru">
                </label>

                <label>
                  Konfirmasi Kata Sandi Baru
                  <input type="password" name="konfirmasi_password" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
                </label>
              </div>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.75rem; flex-wrap: wrap;">
              <button class="btn btn-primary" type="submit">Simpan Perubahan Akun</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
</div>
<?php require_once __DIR__ . '/modal-logout.php'; ?>
<script src="../assets/js/script.js?v=<?= filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
