<?php
/**
 * Header Template Sistem Informasi Perpajakan
 */

require_once __DIR__ . '/fungsi.php';

// Pastikan petugas sudah login
cek_login();

$user_aktif = pengguna_login();
$nama_user = $user_aktif['nama_petugas'] ?? $_SESSION['nama_pengguna'] ?? 'Petugas';
$peran_user = $user_aktif['peran'] ?? 'Petugas Pajak';
$kantor_user = $user_aktif['nama_kantor'] ?? 'Kantor Pajak';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judul_halaman) ? escape($judul_halaman) . ' - ' : '' ?>Sistem Informasi Perpajakan</title>
    
    <!-- CSS Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    
    <!-- Fallback CDN jika file lokal tidak sengaja terhapus -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" onerror="this.onerror=null;">
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar Navigasi -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Konten Utama -->
    <div class="app-content">
        <!-- Top Navigation Bar -->
        <header class="app-header">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary btn-sm me-3 d-lg-none" id="sidebarToggle" type="button" aria-label="Toggle Navigation">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-sm-block">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                        <i class="bi bi-geo-alt-fill me-1"></i><?= escape($kantor_user) ?>
                    </span>
                </div>
            </div>

            <!-- User Menu -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold text-dark lh-sm"><?= escape($nama_user) ?></div>
                    <small class="text-muted"><?= escape($peran_user) ?></small>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light rounded-circle p-2 border shadow-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 42px; height: 42px;">
                        <i class="bi bi-person-circle fs-5 text-primary"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                        <li class="px-3 py-2 border-bottom d-md-none">
                            <div class="fw-semibold text-dark"><?= escape($nama_user) ?></div>
                            <small class="text-muted"><?= escape($peran_user) ?></small>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('dashboard.php') ?>">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('modules/wajib_pajak/index.php') ?>">
                                <i class="bi bi-people me-2 text-primary"></i>Wajib Pajak
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="<?= base_url('modules/spt/index.php') ?>">
                                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Pelaporan SPT
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= base_url('logout.php') ?>" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                                <i class="bi bi-box-arrow-right me-2"></i>Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="p-3 p-md-4 flex-grow-1">
            <?php tampilkan_flash(); ?>
