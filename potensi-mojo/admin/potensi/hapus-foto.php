<?php
require_once __DIR__ . '/../auth.php';

$photoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$potensiId = filter_input(INPUT_GET, 'potensi_id', FILTER_VALIDATE_INT);
if (!$photoId || !$potensiId || !verify_csrf($_GET['csrf_token'] ?? null) || !$pdo) {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
    redirect('index.php');
}
$statement = $pdo->prepare('SELECT foto FROM potensi_foto WHERE id = ? AND potensi_id = ? LIMIT 1');
$statement->execute([$photoId, $potensiId]);
$photo = $statement->fetch();
if ($photo) {
    $statement = $pdo->prepare('DELETE FROM potensi_foto WHERE id = ? AND potensi_id = ?');
    $statement->execute([$photoId, $potensiId]);
    delete_photo($photo['foto']);
    set_flash('success', 'Foto berhasil dihapus.');
} else {
    set_flash('error', 'Data tidak ditemukan.');
}
redirect('edit.php?id=' . $potensiId);
