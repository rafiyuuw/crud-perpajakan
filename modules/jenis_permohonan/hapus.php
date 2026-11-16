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

$data = ambil_satu("SELECT * FROM jenis_permohonan WHERE id_jenis_permohonan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data jenis permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM jenis_permohonan WHERE id_jenis_permohonan = ?", [$id]);
    set_flash('success', 'Jenis permohonan ' . escape($data['nama_permohonan']) . ' berhasil dihapus!');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Jenis permohonan tidak dapat dihapus karena masih digunakan oleh berkas permohonan pajak!');
    } else {
        set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
