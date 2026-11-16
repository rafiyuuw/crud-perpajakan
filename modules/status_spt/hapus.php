<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/fungsi.php';

cek_login();

$id = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    set_flash('danger', 'Validasi keamanan gagal!');
    header('Location: index.php');
    exit;
}

$status = ambil_satu("SELECT nama_status FROM status_spt WHERE id_status_spt = ?", [$id]);
if (!$status) {
    set_flash('danger', 'Data status tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM status_spt WHERE id_status_spt = ?", [$id]);
    set_flash('success', 'Status SPT <strong>' . escape($status['nama_status']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Status SPT tidak dapat dihapus karena masih digunakan pada data SPT atau Riwayat Status SPT.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
