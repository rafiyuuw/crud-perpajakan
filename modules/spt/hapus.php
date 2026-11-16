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

$spt = ambil_satu("SELECT nomor_spt FROM spt WHERE id_spt = ?", [$id]);
if (!$spt) {
    set_flash('danger', 'Data SPT tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM spt WHERE id_spt = ?", [$id]);
    set_flash('success', 'Pelaporan SPT <strong>' . escape($spt['nomor_spt']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'SPT tidak dapat dihapus karena sudah memiliki Surat Tagihan Pajak yang berelasi dengan riwayat pembayaran.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
