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

$petugas = ambil_satu("SELECT nama_petugas FROM petugas_pajak WHERE id_petugas = ?", [$id]);
if (!$petugas) {
    set_flash('danger', 'Data petugas pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM petugas_pajak WHERE id_petugas = ?", [$id]);
    set_flash('success', 'Petugas pajak <strong>' . escape($petugas['nama_petugas']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Petugas tidak dapat dihapus karena masih tercatat dalam transaksi SPT atau riwayat permohonan. Gunakan opsi status "Nonaktif" jika petugas sudah tidak bertugas.');
    } else {
        set_flash('danger', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
