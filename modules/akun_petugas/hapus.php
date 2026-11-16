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

// Cegah petugas menghapus akunnya sendiri saat sedang login
if ($id == ($_SESSION['akun_id'] ?? 0)) {
    set_flash('danger', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang aktif digunakan!');
    header('Location: index.php');
    exit;
}

$akun = ambil_satu("SELECT nama_pengguna FROM akun_petugas WHERE id_akun = ?", [$id]);
if (!$akun) {
    set_flash('danger', 'Data akun tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM akun_petugas WHERE id_akun = ?", [$id]);
    set_flash('success', 'Akun petugas <strong>@' . escape($akun['nama_pengguna']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('danger', 'Gagal menghapus akun: ' . $e->getMessage());
}

header('Location: index.php');
exit;
