<?php
require_once __DIR__ . '/../auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || !verify_csrf($_GET['csrf_token'] ?? null) || !$pdo) {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
} else {
    $stmt = $pdo->prepare('SELECT foto FROM cerita WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $galleryStmt = $pdo->prepare('SELECT foto FROM cerita_foto WHERE cerita_id = ?');
        $galleryStmt->execute([$id]);
        $galleryPhotos = $galleryStmt->fetchAll();

        $delStmt = $pdo->prepare('DELETE FROM cerita WHERE id = ?');
        $delStmt->execute([$id]);

        if ($item['foto']) {
            delete_photo($item['foto'], 'cerita');
        }
        foreach ($galleryPhotos as $gPhoto) {
            delete_photo($gPhoto['foto'], 'cerita');
        }
        set_flash('success', 'Data berhasil dihapus.');
    } else {
        set_flash('error', 'Data tidak ditemukan.');
    }
}
redirect('index.php');
