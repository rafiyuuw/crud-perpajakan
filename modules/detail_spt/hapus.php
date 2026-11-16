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

$detail = ambil_satu("SELECT id_spt, jumlah_pajak FROM detail_spt WHERE id_detail_spt = ?", [$id]);
if (!$detail) {
    set_flash('danger', 'Data rincian tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$spt_id = $detail['id_spt'];

try {
    eksekusi("DELETE FROM detail_spt WHERE id_detail_spt = ?", [$id]);
    
    // Perbarui Total Pajak pada SPT Induk
    $total_baru = ambil_satu("SELECT COALESCE(SUM(jumlah_pajak), 0) as total FROM detail_spt WHERE id_spt = ?", [$spt_id])['total'] ?? 0;
    eksekusi("UPDATE spt SET total_pajak = ? WHERE id_spt = ?", [$total_baru, $spt_id]);
    eksekusi("UPDATE tagihan_pajak SET jumlah_tagihan = ? WHERE id_spt = ?", [$total_baru, $spt_id]);
    
    set_flash('success', 'Rincian objek SPT berhasil dihapus dan total pajak telah disinkronkan.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
}

header('Location: ../spt/detail.php?id=' . $spt_id);
exit;
