<?php
/**
 * File Fungsi dan Helper Utama
 * Sistem Informasi Perpajakan
 */

require_once __DIR__ . '/../config/koneksi.php';

/**
 * Mendapatkan URL dasar aplikasi secara dinamis
 */
function base_url($path = '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    
    // Cari direktori root aplikasi
    $base = '';
    if (stripos($scriptName, '/sistem-perpajakan') !== false) {
        $base = '/sistem-perpajakan';
    } elseif (stripos($scriptName, '/Perpajakan') !== false) {
        $base = '/Perpajakan';
    } else {
        // Jika berjalan di root virtual host
        $base = '';
    }
    
    $path = ltrim($path, '/');
    return $base ? $base . '/' . $path : '/' . $path;
}

/**
 * Escape string HTML untuk mencegah XSS
 */
function escape($data) {
    if (is_null($data)) return '';
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

/**
 * Alias fungsi sanitasi
 */
function bersihkan($data) {
    return trim($data ?? '');
}

/**
 * Generate atau ambil CSRF Token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render field input hidden CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . escape(csrf_token()) . '">';
}

/**
 * Verifikasi CSRF token dari request POST
 */
function verifikasi_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            set_flash('danger', 'Validasi keamanan (CSRF Token) gagal atau sesi Anda telah kedaluwarsa. Silakan ulangi.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? base_url('dashboard.php')));
            exit;
        }
    }
}

/**
 * Set pesan notifikasi flash (Bootstrap alert)
 */
function set_flash($tipe, $pesan) {
    $_SESSION['flash'] = [
        'tipe'  => $tipe, // success, danger, warning, info
        'pesan' => $pesan
    ];
}

/**
 * Menampilkan pesan flash jika ada
 */
function tampilkan_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $ikon = 'info-circle';
        if ($flash['tipe'] === 'success') $ikon = 'check-circle-fill';
        if ($flash['tipe'] === 'danger')  $ikon = 'exclamation-triangle-fill';
        if ($flash['tipe'] === 'warning') $ikon = 'exclamation-circle-fill';
        
        echo '<div class="alert alert-' . escape($flash['tipe']) . ' alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">';
        echo '  <i class="bi bi-' . $ikon . ' me-2 fs-5"></i>';
        echo '  <div class="flex-grow-1">' . $flash['pesan'] . '</div>';
        echo '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>';
        echo '</div>';
    }
}

/**
 * Cek autentikasi sesi petugas
 */
function cek_login() {
    if (empty($_SESSION['akun_id']) || empty($_SESSION['nama_pengguna'])) {
        set_flash('warning', 'Silakan masuk terlebih dahulu untuk mengakses sistem.');
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/**
 * Mengambil data akun petugas yang sedang login
 */
function pengguna_login() {
    global $koneksi;
    if (empty($_SESSION['akun_id'])) return null;
    
    $stmt = $koneksi->prepare("
        SELECT a.*, p.nama_petugas, p.nomor_identitas, p.jabatan, p.email, k.nama_kantor, k.kode_kantor
        FROM akun_petugas a
        JOIN petugas_pajak p ON a.id_petugas = p.id_petugas
        JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
        WHERE a.id_akun = ?
    ");
    $stmt->execute([$_SESSION['akun_id']]);
    return $stmt->fetch();
}

/**
 * Format angka ke format mata uang Rupiah
 */
function format_rupiah($angka) {
    if (is_null($angka) || $angka === '') return 'Rp 0';
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 */
function format_tanggal($tanggal, $dengan_waktu = false) {
    if (empty($tanggal) || $tanggal === '0000-00-00' || $tanggal === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($tanggal);
    if (!$timestamp) return $tanggal;
    
    $tgl = date('d', $timestamp);
    $bln = (int)date('m', $timestamp);
    $thn = date('Y', $timestamp);
    
    $hasil = $tgl . ' ' . ($bulanIndo[$bln] ?? '') . ' ' . $thn;
    
    if ($dengan_waktu) {
        $hasil .= ' ' . date('H:i', $timestamp) . ' WIB';
    }
    
    return $hasil;
}

/**
 * Eksekusi query ambil satu baris
 */
function ambil_satu($sql, $params = []) {
    global $koneksi;
    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Eksekusi query ambil banyak baris
 */
function ambil_semua($sql, $params = []) {
    global $koneksi;
    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Eksekusi query INSERT, UPDATE, DELETE
 */
function eksekusi($sql, $params = []) {
    global $koneksi;
    $stmt = $koneksi->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Hitung jumlah baris tabel dengan kondisi opsional
 */
function hitung_total($tabel, $kondisi = '', $params = []) {
    global $koneksi;
    $sql = "SELECT COUNT(*) as total FROM `{$tabel}`";
    if (!empty($kondisi)) {
        $sql .= " WHERE " . $kondisi;
    }
    $stmt = $koneksi->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return (int)($row['total'] ?? 0);
}

/**
 * Render badge status warna-warni untuk UI
 */
function badge_status($status) {
    $st = trim((string)$status);
    $warna = 'secondary';
    $ikon = 'dot';
    
    switch (strtolower($st)) {
        case 'aktif':
        case 'diterima':
        case 'lunas':
        case 'berhasil':
        case 'disetujui':
        case 'selesai':
            $warna = 'success';
            $ikon = 'check-circle';
            break;
        case 'nonaktif':
        case 'ditolak':
        case 'gagal':
        case 'dibatalkan':
        case 'kadaluarsa':
        case 'suspen':
            $warna = 'danger';
            $ikon = 'x-circle';
            break;
        case 'dalam pemeriksaan':
        case 'diverifikasi':
        case 'dibayar sebagian':
        case 'perlu perbaikan':
        case 'menunggu konfirmasi':
            $warna = 'warning text-dark';
            $ikon = 'hourglass-split';
            break;
        case 'diajukan':
        case 'terbuka':
        case 'berjalan':
            $warna = 'info text-dark';
            $ikon = 'send';
            break;
        case 'draft':
        case 'belum bayar':
        case 'tutup buku':
            $warna = 'secondary';
            $ikon = 'clock';
            break;
    }
    
    return '<span class="badge bg-' . $warna . ' px-2 py-1"><i class="bi bi-' . $ikon . ' me-1"></i>' . escape($st) . '</span>';
}
