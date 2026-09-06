<?php
require_once __DIR__ . '/../auth.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && verify_csrf($_GET['csrf_token'] ?? null) && $pdo) {
    try {
        $stmt = $pdo->prepare('DELETE FROM kategori WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Data berhasil dihapus.');
    } catch (PDOException $exception) {
        set_flash('error', 'Kategori yang masih digunakan oleh potensi tidak dapat dihapus.');
    }
} else {
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
}
redirect('index.php');
