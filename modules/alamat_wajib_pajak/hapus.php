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

$alamat = ambil_satu("SELECT id_wajib_pajak, jenis_alamat FROM alamat_wajib_pajak WHERE id_alamat = ?", [$id]);
if (!$alamat) {
    set_flash('danger', 'Data alamat tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM alamat_wajib_pajak WHERE id_alamat = ?", [$id]);
    set_flash('success', 'Alamat wajib pajak (' . escape($alamat['jenis_alamat']) . ') berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus alamat: ' . $e->getMessage());
}

header('Location: index.php' . ($alamat['id_wajib_pajak'] ? '?wajib_pajak=' . $alamat['id_wajib_pajak'] : ''));
exit;
