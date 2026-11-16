<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/fungsi.php';

cek_login();

$id = (int)($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? '';

if (!verifikasi_token_sederhana($token)) {
    set_flash('danger', 'Token keamanan tidak valid atau telah kedaluwarsa!');
    header('Location: index.php');
    exit;
}

$data = ambil_satu("SELECT * FROM bukti_pembayaran WHERE id_bukti = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data bukti pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    // Hapus file fisik jika ada
    $file_path = __DIR__ . '/../../' . $data['lokasi_file'];
    if (!empty($data['lokasi_file']) && file_exists($file_path) && is_file($file_path)) {
        @unlink($file_path);
    }

    eksekusi("DELETE FROM bukti_pembayaran WHERE id_bukti = ?", [$id]);
    set_flash('success', 'Bukti pembayaran ' . escape($data['nomor_bukti']) . ' berhasil dihapus!');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Bukti pembayaran tidak dapat dihapus karena masih terkait dengan data lain!');
    } else {
        set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
