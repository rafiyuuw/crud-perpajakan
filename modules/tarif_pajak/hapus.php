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

$tarif = ambil_satu("SELECT id_jenis_pajak, nama_tarif FROM tarif_pajak WHERE id_tarif = ?", [$id]);
if (!$tarif) {
    set_flash('danger', 'Data tarif tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM tarif_pajak WHERE id_tarif = ?", [$id]);
    set_flash('success', 'Tarif <strong>' . escape($tarif['nama_tarif']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Tarif tidak dapat dihapus karena sudah digunakan dalam riwayat perhitungan SPT. Gunakan opsi status "Nonaktif" agar tarif tidak lagi muncul pada transaksi baru tanpa merusak riwayat transaksi lama.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php' . ($tarif['id_jenis_pajak'] ? '?jenis=' . $tarif['id_jenis_pajak'] : ''));
exit;
