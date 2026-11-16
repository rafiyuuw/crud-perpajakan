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

$metode = ambil_satu("SELECT nama_metode FROM metode_pembayaran WHERE id_metode = ?", [$id]);
if (!$metode) {
    set_flash('danger', 'Data metode tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM metode_pembayaran WHERE id_metode = ?", [$id]);
    set_flash('success', 'Metode pembayaran <strong>' . escape($metode['nama_metode']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Metode pembayaran tidak dapat dihapus karena masih digunakan pada data transaksi Pembayaran Pajak.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
