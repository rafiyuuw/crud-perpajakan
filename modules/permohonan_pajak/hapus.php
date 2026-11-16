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

$data = ambil_satu("SELECT * FROM permohonan_pajak WHERE id_permohonan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM permohonan_pajak WHERE id_permohonan = ?", [$id]);
    set_flash('success', 'Permohonan pajak ' . escape($data['nomor_permohonan']) . ' berhasil dihapus!');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Permohonan pajak tidak dapat dihapus karena masih terkait dengan data transaksi lain!');
    } else {
        set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
