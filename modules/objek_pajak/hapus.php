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

$objek = ambil_satu("SELECT id_wajib_pajak, nama_objek FROM objek_pajak WHERE id_objek_pajak = ?", [$id]);
if (!$objek) {
    set_flash('danger', 'Data objek pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

try {
    eksekusi("DELETE FROM objek_pajak WHERE id_objek_pajak = ?", [$id]);
    set_flash('success', 'Objek pajak <strong>' . escape($objek['nama_objek']) . '</strong> berhasil dihapus.');
} catch (PDOException $e) {
    if ($e->getCode() == '23000') {
        set_flash('danger', 'Objek pajak tidak dapat dihapus karena sudah tercatat dalam riwayat formulir Detail SPT.');
    } else {
        set_flash('danger', 'Gagal menghapus: ' . $e->getMessage());
    }
}

header('Location: index.php' . ($objek['id_wajib_pajak'] ? '?wajib_pajak=' . $objek['id_wajib_pajak'] : ''));
exit;
