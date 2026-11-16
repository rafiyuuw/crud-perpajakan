<?php
/**
 * Konfigurasi Database dan Koneksi PDO
 * Sistem Informasi Perpajakan
 */

// Mulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database Laragon Default
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'db_perpajakan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Variabel koneksi global
$koneksi = null;

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $koneksi = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Tampilkan pesan error ramah pengguna jika koneksi gagal
    die("<div style='font-family:sans-serif;padding:30px;background:#fef2f2;color:#991b1b;border:1px solid #f87171;border-radius:8px;max-width:600px;margin:50px auto;'>"
        . "<h3>Gagal Terhubung ke Database MySQL!</h3>"
        . "<p>Pastikan MySQL pada Laragon sudah berjalan dan database <strong>" . DB_NAME . "</strong> sudah dibuat.</p>"
        . "<small>Detail Teknis: " . htmlspecialchars($e->getMessage()) . "</small>"
        . "</div>");
}
