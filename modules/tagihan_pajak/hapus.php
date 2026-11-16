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

$tagihan = ambil_satu("SELECT nomor_tagihan FROM tagihan_pajak WHERE id_tagihan = ?", [$id]);
if (!$tagihan) {
    set_flash('danger', 'Data tagihan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM tagihan_pajak WHERE id_tagihan = ?", [$id]);
    set_flash('success', 'Surat Tagihan Pajak <strong>' . escape($tagihan['nomor_tagihan']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Tagihan tidak dapat dihapus karena sudah memiliki riwayat transaksi Pembayaran Pajak atau Denda Pajak.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
