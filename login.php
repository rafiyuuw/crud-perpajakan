<?php
/**
 * Halaman Masuk Petugas (Login)
 * Sistem Informasi Perpajakan
 */

require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/includes/fungsi.php';

// Jika sudah login, langsung ke dashboard
if (!empty($_SESSION['akun_id'])) {
    header('Location: ' . base_url('dashboard.php'));
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_pengguna = bersihkan($_POST['nama_pengguna'] ?? '');
    $kata_sandi = $_POST['kata_sandi'] ?? '';
    
    if (empty($nama_pengguna) || empty($kata_sandi)) {
        $pesan_error = 'Nama pengguna dan kata sandi wajib diisi!';
    } else {
        // Cari akun petugas
        $stmt = $koneksi->prepare("
            SELECT a.*, p.nama_petugas, p.jabatan, p.status as status_petugas
            FROM akun_petugas a
            JOIN petugas_pajak p ON a.id_petugas = p.id_petugas
            WHERE a.nama_pengguna = ?
            LIMIT 1
        ");
        $stmt->execute([$nama_pengguna]);
        $akun = $stmt->fetch();
        
        if ($akun && password_verify($kata_sandi, $akun['kata_sandi'])) {
            if ($akun['status_akun'] !== 'Aktif' || $akun['status_petugas'] !== 'Aktif') {
                $pesan_error = 'Akun atau status petugas Anda tidak aktif. Silakan hubungi Administrator.';
            } else {
                // Login Berhasil
                $_SESSION['akun_id'] = $akun['id_akun'];
                $_SESSION['petugas_id'] = $akun['id_petugas'];
                $_SESSION['nama_pengguna'] = $akun['nama_pengguna'];
                $_SESSION['nama_petugas'] = $akun['nama_petugas'];
                $_SESSION['peran'] = $akun['peran'];
                
                // Perbarui waktu terakhir masuk
                $update = $koneksi->prepare("UPDATE akun_petugas SET terakhir_masuk = NOW() WHERE id_akun = ?");
                $update->execute([$akun['id_akun']]);
                
                set_flash('success', 'Selamat datang kembali, <strong>' . escape($akun['nama_petugas']) . '</strong> (' . escape($akun['peran']) . ')!');
                header('Location: ' . base_url('dashboard.php'));
                exit;
            }
        } else {
            $pesan_error = 'Nama pengguna atau kata sandi tidak valid!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - Sistem Informasi Perpajakan</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 2rem 1.5rem;
            text-align: center;
        }
        .brand-badge {
            width: 58px;
            height: 58px;
            background: #1e3a8a;
            color: #ffffff;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.35);
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="brand-badge">
            <i class="bi bi-shield-check"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">SIM Perpajakan</h4>
        <p class="text-muted small mb-0">Sistem Administrasi &amp; Pelaporan Pajak SMK RPL</p>
    </div>
    
    <div class="p-4 p-md-5">
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4 py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div class="small"><?= escape($pesan_error) ?></div>
            </div>
        <?php endif; ?>

        <?php tampilkan_flash(); ?>

        <form action="" method="POST">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="nama_pengguna" class="form-label fw-semibold text-secondary small">NAMA PENGGUNA</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" id="nama_pengguna" name="nama_pengguna" placeholder="Masukkan nama pengguna..." value="<?= escape($_POST['nama_pengguna'] ?? 'admin') ?>" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="kata_sandi" class="form-label fw-semibold text-secondary small">KATA SANDI</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="kata_sandi" name="kata_sandi" placeholder="Masukkan kata sandi..." value="admin123" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-3" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Sistem
            </button>
        </form>

        <div class="alert alert-light border small text-muted mb-0">
            <div class="fw-bold mb-1 text-dark"><i class="bi bi-info-circle me-1"></i>Akun Demo Pengujian:</div>
            <div>&bull; Admin: <code>admin</code> &bull; Sandi: <code>admin123</code></div>
            <div>&bull; Petugas: <code>petugas</code> &bull; Sandi: <code>admin123</code></div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
