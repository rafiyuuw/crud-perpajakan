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

$pajak = ambil_satu("SELECT nama_pajak FROM jenis_pajak WHERE id_jenis_pajak = ?", [$id]);
if (!$pajak) {
    set_flash('danger', 'Data jenis pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM jenis_pajak WHERE id_jenis_pajak = ?", [$id]);
    set_flash('success', 'Jenis pajak <strong>' . escape($pajak['nama_pajak']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Jenis pajak tidak dapat dihapus karena masih digunakan pada data Tarif Pajak, Objek Pajak, atau Laporan SPT.');
    } else {
        set_flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
