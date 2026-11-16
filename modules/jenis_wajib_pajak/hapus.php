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

$jenis = ambil_satu("SELECT nama_jenis FROM jenis_wajib_pajak WHERE id_jenis_wajib_pajak = ?", [$id]);
if (!$jenis) {
    set_flash('danger', 'Data jenis wajib pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM jenis_wajib_pajak WHERE id_jenis_wajib_pajak = ?", [$id]);
    set_flash('success', 'Jenis wajib pajak <strong>' . escape($jenis['nama_jenis']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Jenis wajib pajak tidak dapat dihapus karena masih digunakan oleh data Wajib Pajak yang terdaftar.');
    } else {
        set_flash('danger', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
