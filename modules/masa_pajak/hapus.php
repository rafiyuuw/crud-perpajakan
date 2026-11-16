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

$masa = ambil_satu("SELECT id_jenis_pajak, tahun_pajak FROM masa_pajak WHERE id_masa_pajak = ?", [$id]);
if (!$masa) {
    set_flash('danger', 'Data masa pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM masa_pajak WHERE id_masa_pajak = ?", [$id]);
    set_flash('success', 'Masa pajak tahun ' . $masa['tahun_pajak'] . ' berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Masa pajak tidak dapat dihapus karena sudah memiliki data laporan SPT yang terhubung.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php' . ($masa['id_jenis_pajak'] ? '?jenis=' . $masa['id_jenis_pajak'] : ''));
exit;
