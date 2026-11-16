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

$kantor = ambil_satu("SELECT nama_kantor FROM kantor_pajak WHERE id_kantor = ?", [$id]);
if (!$kantor) {
    set_flash('danger', 'Data kantor pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM kantor_pajak WHERE id_kantor = ?", [$id]);
    set_flash('success', 'Data kantor pajak <strong>' . escape($kantor['nama_kantor']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Kantor pajak tidak dapat dihapus karena masih memiliki data Petugas Pajak atau Wajib Pajak yang terhubung. Hapus atau pindahkan relasi terlebih dahulu demi integritas data.');
    } else {
        set_flash('danger', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
