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

$bayar = ambil_satu("SELECT id_tagihan, nomor_transaksi FROM pembayaran_pajak WHERE id_pembayaran = ?", [$id]);
if (!$bayar) {
    set_flash('danger', 'Data pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$tagihan_id = $bayar['id_tagihan'];

try {
    eksekusi("DELETE FROM pembayaran_pajak WHERE id_pembayaran = ?", [$id]);
    
    // Evaluasi Ulang Status Tagihan
    $tagihan_row = ambil_satu("SELECT jumlah_tagihan FROM tagihan_pajak WHERE id_tagihan = ?", [$tagihan_id]);
    $total_masuk = ambil_satu("SELECT COALESCE(SUM(jumlah_pembayaran), 0) as total FROM pembayaran_pajak WHERE id_tagihan = ? AND status_pembayaran = 'Berhasil'", [$tagihan_id])['total'] ?? 0;
    
    if ($total_masuk >= (float)$tagihan_row['jumlah_tagihan'] && $total_masuk > 0) {
        eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Lunas' WHERE id_tagihan = ?", [$tagihan_id]);
    } elseif ($total_masuk > 0) {
        eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Dibayar Sebagian' WHERE id_tagihan = ?", [$tagihan_id]);
    } else {
        eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Belum Bayar' WHERE id_tagihan = ?", [$tagihan_id]);
    }
    
    set_flash('success', 'Transaksi pembayaran <strong>' . escape($bayar['nomor_transaksi']) . '</strong> berhasil dihapus dan status tagihan disinkronkan.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: index.php');
exit;
