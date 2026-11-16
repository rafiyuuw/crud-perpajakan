<?php
/**
 * Index Router Utama
 * Sistem Informasi Perpajakan
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

if (!empty($_SESSION['akun_id'])) {
    header('Location: ' . base_url('dashboard.php'));
    exit;
} else {
    header('Location: ' . base_url('login.php'));
    exit;
}
