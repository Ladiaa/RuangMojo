<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function potensi_form_data(): array
{
    return [
        'kategori_id' => (int)($_POST['kategori_id'] ?? 0),
        'nama' => trim((string)($_POST['nama'] ?? '')),
        'deskripsi' => trim((string)($_POST['deskripsi'] ?? '')),
        'lokasi' => trim((string)($_POST['lokasi'] ?? '')) ?: null,
        'alamat' => trim((string)($_POST['alamat'] ?? '')) ?: null,
        'link_maps' => trim((string)($_POST['link_maps'] ?? '')) ?: null,
        'latitude' => is_numeric($_POST['latitude'] ?? null) ? (float)$_POST['latitude'] : null,
        'longitude' => is_numeric($_POST['longitude'] ?? null) ? (float)$_POST['longitude'] : null,
        'pengelola' => trim((string)($_POST['pengelola'] ?? '')) ?: null,
        'kontak' => trim((string)($_POST['kontak'] ?? '')) ?: null,
        'status' => ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
    ];
}

function cerita_form_data(): array
{
    return [
        'judul' => trim((string)($_POST['judul'] ?? '')),
        'kategori_cerita' => null,
        'tanggal' => trim((string)($_POST['tanggal'] ?? date('Y-m-d'))),
        'ringkasan' => trim((string)($_POST['ringkasan'] ?? '')) ?: null,
        'deskripsi' => trim((string)($_POST['deskripsi'] ?? '')),
        'status' => ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif',
    ];
}

function format_indo_date(string $date): string
{
    $timestamp = strtotime($date);
    if (!$timestamp) return $date;
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $day = (int)date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)] ?? date('F', $timestamp);
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash_' . $key] = $message;
}

function consume_flash(string $key): ?string
{
    $sessionKey = 'flash_' . $key;
    $message = $_SESSION[$sessionKey] ?? null;
    unset($_SESSION[$sessionKey]);
    return is_string($message) ? $message : null;
}

function upload_photo(array $file, string $subfolder = 'potensi'): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['name' => null, 'error' => null];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['name' => null, 'error' => 'Upload foto gagal.'];
    }
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
        return ['name' => null, 'error' => 'Ukuran foto maksimal 10 MB.'];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['name' => null, 'error' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.'];
    }

    $cleanSubfolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $subfolder) ?: 'potensi';
    $directory = dirname(__DIR__) . '/uploads/' . $cleanSubfolder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        return ['name' => null, 'error' => 'Folder upload tidak dapat dibuat.'];
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $name)) {
        return ['name' => null, 'error' => 'Foto tidak dapat disimpan.'];
    }
    return ['name' => $name, 'error' => null];
}

function upload_photos(array $files, string $subfolder = 'potensi'): array
{
    $uploaded = [];
    $errors = [];
    $names = $files['name'] ?? [];
    $tmpNames = $files['tmp_name'] ?? [];
    $errorsByFile = $files['error'] ?? [];
    $sizes = $files['size'] ?? [];
    foreach ($names as $index => $originalName) {
        $upload = upload_photo([
            'name' => $originalName,
            'tmp_name' => $tmpNames[$index] ?? '',
            'error' => $errorsByFile[$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $sizes[$index] ?? 0,
        ], $subfolder);
        if ($upload['error']) {
            $errors[] = $originalName . ': ' . $upload['error'];
        } elseif ($upload['name']) {
            $uploaded[] = $upload['name'];
        }
    }
    return ['names' => $uploaded, 'errors' => $errors];
}

function delete_photo(?string $name, string $subfolder = 'potensi'): void
{
    if ($name && basename($name) === $name) {
        $cleanSubfolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $subfolder) ?: 'potensi';
        $path = dirname(__DIR__) . '/uploads/' . $cleanSubfolder . '/' . $name;
        if (is_file($path)) {
            unlink($path);
        }
    }
}

function get_kontak_info(?PDO $pdo = null): array
{
    $default = [
        'nama_padukuhan' => 'Padukuhan Mojo',
        'alamat' => "Kalurahan Ngeposari, Kapanewon Semanu,\nKabupaten Gunungkidul, Daerah Istimewa Yogyakarta",
        'nama_pengelola' => 'Dukuh Mojo & Pengelola',
        'nomor_whatsapp' => '',
        'link_maps' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('Padukuhan Mojo, Ngeposari, Semanu, Gunungkidul, Yogyakarta'),
        'deskripsi' => 'Ingin mengenal lebih dekat potensi Mojo? Temukan informasi dan berbagai cerita tentang Mojo melalui Ruang Mojo, atau hubungi kami untuk mendapatkan informasi lebih lanjut.',
    ];

    if (!$pdo) {
        global $pdo;
    }

    if (!$pdo) {
        return $default;
    }

    try {
        $stmt = $pdo->query('SELECT * FROM kontak_info WHERE id = 1 LIMIT 1');
        $row = $stmt ? $stmt->fetch() : null;
        if ($row) {
            return [
                'nama_padukuhan' => trim((string)($row['nama_padukuhan'] ?? '')) ?: $default['nama_padukuhan'],
                'alamat' => trim((string)($row['alamat'] ?? '')) ?: $default['alamat'],
                'nama_pengelola' => trim((string)($row['nama_pengelola'] ?? '')),
                'nomor_whatsapp' => trim((string)($row['nomor_whatsapp'] ?? '')),
                'link_maps' => trim((string)($row['link_maps'] ?? '')),
                'deskripsi' => trim((string)($row['deskripsi'] ?? '')) ?: $default['deskripsi'],
            ];
        }
    } catch (Throwable $e) {
        // Fallback on table absence or error
    }

    return $default;
}

function format_whatsapp_url(?string $number, ?string $text = ''): ?string
{
    if (!$number) {
        return null;
    }
    $clean = preg_replace('/[^\d+]/', '', $number);
    if (!$clean) {
        return null;
    }
    if (str_starts_with($clean, '+')) {
        $clean = substr($clean, 1);
    }
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    } elseif (!str_starts_with($clean, '62') && strlen($clean) <= 12) {
        $clean = '62' . $clean;
    }
    
    $url = 'https://wa.me/' . $clean;
    if ($text !== '' && $text !== null) {
        $url .= '?text=' . rawurlencode($text);
    }
    return $url;
}

function kontak_form_data(): array
{
    return [
        'nama_padukuhan' => trim((string)($_POST['nama_padukuhan'] ?? '')) ?: 'Padukuhan Mojo',
        'alamat' => trim((string)($_POST['alamat'] ?? '')) ?: "Kalurahan Ngeposari, Kapanewon Semanu,\nKabupaten Gunungkidul, Daerah Istimewa Yogyakarta",
        'nama_pengelola' => trim((string)($_POST['nama_pengelola'] ?? '')),
        'nomor_whatsapp' => trim((string)($_POST['nomor_whatsapp'] ?? '')),
        'link_maps' => trim((string)($_POST['link_maps'] ?? '')),
        'deskripsi' => trim((string)($_POST['deskripsi'] ?? '')),
    ];
}
