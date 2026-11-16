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

$riwayat = ambil_satu("SELECT id_spt FROM riwayat_status_spt WHERE id_riwayat_spt = ?", [$id]);
if (!$riwayat) {
    set_flash('danger', 'Data riwayat tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM riwayat_status_spt WHERE id_riwayat_spt = ?", [$id]);
    set_flash('success', 'Catatan riwayat status SPT berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: index.php' . ($riwayat['id_spt'] ? '?spt=' . $riwayat['id_spt'] : ''));
exit;
