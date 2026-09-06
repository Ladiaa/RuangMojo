<?php
require_once __DIR__ . '/../auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ceritaId = filter_input(INPUT_GET, 'cerita_id', FILTER_VALIDATE_INT);

if (!$id || !$ceritaId || !verify_csrf($_GET['csrf_token'] ?? null) || !$pdo) {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
} else {
    $stmt = $pdo->prepare('SELECT foto FROM cerita_foto WHERE id = ? AND cerita_id = ?');
    $stmt->execute([$id, $ceritaId]);
    $photo = $stmt->fetch();
    if ($photo) {
        $del = $pdo->prepare('DELETE FROM cerita_foto WHERE id = ?');
        $del->execute([$id]);
        delete_photo($photo['foto'], 'cerita');
        set_flash('success', 'Foto berhasil dihapus.');
    } else {
        set_flash('error', 'Data tidak ditemukan.');
    }
}
redirect('edit.php?id=' . (int)$ceritaId);
