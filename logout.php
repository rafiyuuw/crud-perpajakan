<?php
/**
 * Logout Petugas
 * Sistem Informasi Perpajakan
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

// Hapus seluruh data sesi
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Mulai sesi baru untuk pesan flash
session_start();
set_flash('info', 'Anda telah berhasil keluar dari sistem perpajakan.');
header('Location: ' . base_url('login.php'));
exit;
