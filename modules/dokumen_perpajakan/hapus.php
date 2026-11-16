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

$dok = ambil_satu("SELECT id_wajib_pajak, nama_file, lokasi_file FROM dokumen_perpajakan WHERE id_dokumen = ?", [$id]);
if (!$dok) {
    set_flash('danger', 'Data dokumen tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    // Hapus file fisik jika ada di folder
    $file_path = __DIR__ . '/../../' . $dok['lokasi_file'];
    if (file_exists($file_path) && is_file($file_path)) {
        @unlink($file_path);
    }
    
    eksekusi("DELETE FROM dokumen_perpajakan WHERE id_dokumen = ?", [$id]);
    set_flash('success', 'Berkas dokumen <strong>' . escape($dok['nama_file']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus dokumen: ' . $e->getMessage());
}

header('Location: index.php' . ($dok['id_wajib_pajak'] ? '?wajib_pajak=' . $dok['id_wajib_pajak'] : ''));
exit;
