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

$data = ambil_satu("SELECT * FROM denda_pajak WHERE id_denda = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data denda pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM denda_pajak WHERE id_denda = ?", [$id]);
    set_flash('success', 'Data denda pajak berhasil dihapus!');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Denda pajak tidak dapat dihapus karena masih terkait dengan data lain!');
    } else {
        set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
