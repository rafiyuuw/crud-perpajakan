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

$wp = ambil_satu("SELECT nama_wajib_pajak FROM wajib_pajak WHERE id_wajib_pajak = ?", [$id]);
if (!$wp) {
    set_flash('danger', 'Data wajib pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM wajib_pajak WHERE id_wajib_pajak = ?", [$id]);
    set_flash('success', 'Wajib Pajak <strong>' . escape($wp['nama_wajib_pajak']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Wajib Pajak tidak dapat dihapus karena masih memiliki relasi transaksi aktif (SPT, Objek Pajak, atau Permohonan). Gunakan opsi status "Nonaktif" atau "Suspen" untuk menonaktifkan wajib pajak.');
    } else {
        set_flash('danger', 'Gagal menghapus data: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
