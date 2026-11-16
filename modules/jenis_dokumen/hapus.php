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

$dok = ambil_satu("SELECT nama_dokumen FROM jenis_dokumen WHERE id_jenis_dokumen = ?", [$id]);
if (!$dok) {
    set_flash('danger', 'Data jenis dokumen tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM jenis_dokumen WHERE id_jenis_dokumen = ?", [$id]);
    set_flash('success', 'Jenis dokumen <strong>' . escape($dok['nama_dokumen']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Jenis dokumen tidak dapat dihapus karena masih digunakan pada data berkas Dokumen Perpajakan.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
