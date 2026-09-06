<?php
require_once __DIR__ . '/../auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || !verify_csrf($_GET['csrf_token'] ?? null) || !$pdo) {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
} else {
    $stmt = $pdo->prepare('SELECT foto FROM potensi WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $galleryStatement = $pdo->prepare('SELECT foto FROM potensi_foto WHERE potensi_id = ?');
        $galleryStatement->execute([$id]);
        $galleryPhotos = $galleryStatement->fetchAll();
        $stmt = $pdo->prepare('DELETE FROM potensi WHERE id = ?');
        $stmt->execute([$id]);
        delete_photo($item['foto']);
        foreach ($galleryPhotos as $galleryPhoto) {
            delete_photo($galleryPhoto['foto']);
        }
        set_flash('success', 'Data berhasil dihapus.');
    } else {
        set_flash('error', 'Data tidak ditemukan.');
    }
}
redirect('index.php');
