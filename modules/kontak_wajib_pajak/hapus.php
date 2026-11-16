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

$kontak = ambil_satu("SELECT id_wajib_pajak, jenis_kontak, nomor_telepon FROM kontak_wajib_pajak WHERE id_kontak = ?", [$id]);
if (!$kontak) {
    set_flash('danger', 'Data kontak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM kontak_wajib_pajak WHERE id_kontak = ?", [$id]);
    set_flash('success', 'Kontak (' . escape($kontak['jenis_kontak']) . ' - ' . escape($kontak['nomor_telepon']) . ') berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus kontak: ' . $e->getMessage());
}

header('Location: index.php' . ($kontak['id_wajib_pajak'] ? '?wajib_pajak=' . $kontak['id_wajib_pajak'] : ''));
exit;
