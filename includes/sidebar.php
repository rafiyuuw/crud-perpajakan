<?php
/**
 * Sidebar Navigasi Sistem Informasi Perpajakan
 */

// Tentukan modul aktif berdasarkan URL
$current_uri = $_SERVER['REQUEST_URI'] ?? '';
function is_active($modul_name) {
    global $current_uri;
    return (strpos($current_uri, '/modules/' . $modul_name) !== false) ? 'active' : '';
}
$is_dashboard = (strpos($current_uri, 'dashboard.php') !== false || substr($current_uri, -1) === '/' || strpos($current_uri, 'index.php') !== false) && strpos($current_uri, '/modules/') === false;
?>

<aside class="app-sidebar" id="appSidebar">
    <!-- Brand Header -->
    <div class="sidebar-brand">
        <i class="bi bi-shield-check"></i>
        <div>
            <div class="lh-1">SIM Perpajakan</div>
            <small class="text-muted fw-normal" style="font-size: 0.72rem; color: #94a3b8 !important;">SMK Rekayasa Perangkat Lunak</small>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="sidebar-menu">
        <!-- Dashboard -->
        <a href="<?= base_url('dashboard.php') ?>" class="sidebar-link <?= $is_dashboard ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>

        <!-- 1. Data Kantor dan Petugas -->
        <div class="sidebar-heading">Data Kantor dan Petugas</div>
        <a href="<?= base_url('modules/kantor_pajak/index.php') ?>" class="sidebar-link <?= is_active('kantor_pajak') ?>">
            <i class="bi bi-building"></i>
            <span>Kantor Pajak</span>
        </a>
        <a href="<?= base_url('modules/petugas_pajak/index.php') ?>" class="sidebar-link <?= is_active('petugas_pajak') ?>">
            <i class="bi bi-person-badge"></i>
            <span>Petugas Pajak</span>
        </a>
        <a href="<?= base_url('modules/akun_petugas/index.php') ?>" class="sidebar-link <?= is_active('akun_petugas') ?>">
            <i class="bi bi-person-lock"></i>
            <span>Akun Petugas</span>
        </a>

        <!-- 2. Data Wajib Pajak -->
        <div class="sidebar-heading">Data Wajib Pajak</div>
        <a href="<?= base_url('modules/jenis_wajib_pajak/index.php') ?>" class="sidebar-link <?= is_active('jenis_wajib_pajak') ?>">
            <i class="bi bi-tags"></i>
            <span>Jenis Wajib Pajak</span>
        </a>
        <a href="<?= base_url('modules/wajib_pajak/index.php') ?>" class="sidebar-link <?= is_active('wajib_pajak') ?>">
            <i class="bi bi-people-fill"></i>
            <span>Wajib Pajak</span>
        </a>
        <a href="<?= base_url('modules/alamat_wajib_pajak/index.php') ?>" class="sidebar-link <?= is_active('alamat_wajib_pajak') ?>">
            <i class="bi bi-geo-alt"></i>
            <span>Alamat Wajib Pajak</span>
        </a>
        <a href="<?= base_url('modules/kontak_wajib_pajak/index.php') ?>" class="sidebar-link <?= is_active('kontak_wajib_pajak') ?>">
            <i class="bi bi-telephone"></i>
            <span>Kontak Wajib Pajak</span>
        </a>

        <!-- 3. Data Perpajakan -->
        <div class="sidebar-heading">Data Perpajakan</div>
        <a href="<?= base_url('modules/jenis_pajak/index.php') ?>" class="sidebar-link <?= is_active('jenis_pajak') ?>">
            <i class="bi bi-bookmark-check"></i>
            <span>Jenis Pajak</span>
        </a>
        <a href="<?= base_url('modules/objek_pajak/index.php') ?>" class="sidebar-link <?= is_active('objek_pajak') ?>">
            <i class="bi bi-house-door"></i>
            <span>Objek Pajak</span>
        </a>
        <a href="<?= base_url('modules/tarif_pajak/index.php') ?>" class="sidebar-link <?= is_active('tarif_pajak') ?>">
            <i class="bi bi-percent"></i>
            <span>Tarif Pajak</span>
        </a>
        <a href="<?= base_url('modules/masa_pajak/index.php') ?>" class="sidebar-link <?= is_active('masa_pajak') ?>">
            <i class="bi bi-calendar3"></i>
            <span>Masa Pajak</span>
        </a>

        <!-- 4. Dokumen Perpajakan -->
        <div class="sidebar-heading">Dokumen Perpajakan</div>
        <a href="<?= base_url('modules/jenis_dokumen/index.php') ?>" class="sidebar-link <?= is_active('jenis_dokumen') ?>">
            <i class="bi bi-folder2"></i>
            <span>Jenis Dokumen</span>
        </a>
        <a href="<?= base_url('modules/dokumen_perpajakan/index.php') ?>" class="sidebar-link <?= is_active('dokumen_perpajakan') ?>">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span>Dokumen Perpajakan</span>
        </a>

        <!-- 5. Pelaporan SPT -->
        <div class="sidebar-heading">Pelaporan SPT</div>
        <a href="<?= base_url('modules/status_spt/index.php') ?>" class="sidebar-link <?= is_active('status_spt') ?>">
            <i class="bi bi-flag"></i>
            <span>Status SPT</span>
        </a>
        <a href="<?= base_url('modules/spt/index.php') ?>" class="sidebar-link <?= is_active('spt') ?>">
            <i class="bi bi-file-earmark-text"></i>
            <span>Data SPT</span>
        </a>
        <a href="<?= base_url('modules/detail_spt/index.php') ?>" class="sidebar-link <?= is_active('detail_spt') ?>">
            <i class="bi bi-list-check"></i>
            <span>Detail SPT</span>
        </a>
        <a href="<?= base_url('modules/riwayat_status_spt/index.php') ?>" class="sidebar-link <?= is_active('riwayat_status_spt') ?>">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat Status SPT</span>
        </a>

        <!-- 6. Tagihan dan Pembayaran -->
        <div class="sidebar-heading">Tagihan dan Pembayaran</div>
        <a href="<?= base_url('modules/tagihan_pajak/index.php') ?>" class="sidebar-link <?= is_active('tagihan_pajak') ?>">
            <i class="bi bi-receipt"></i>
            <span>Tagihan Pajak</span>
        </a>
        <a href="<?= base_url('modules/metode_pembayaran/index.php') ?>" class="sidebar-link <?= is_active('metode_pembayaran') ?>">
            <i class="bi bi-wallet2"></i>
            <span>Metode Pembayaran</span>
        </a>
        <a href="<?= base_url('modules/pembayaran_pajak/index.php') ?>" class="sidebar-link <?= is_active('pembayaran_pajak') ?>">
            <i class="bi bi-cash-stack"></i>
            <span>Pembayaran Pajak</span>
        </a>
        <a href="<?= base_url('modules/bukti_pembayaran/index.php') ?>" class="sidebar-link <?= is_active('bukti_pembayaran') ?>">
            <i class="bi bi-card-checklist"></i>
            <span>Bukti Pembayaran</span>
        </a>
        <a href="<?= base_url('modules/denda_pajak/index.php') ?>" class="sidebar-link <?= is_active('denda_pajak') ?>">
            <i class="bi bi-exclamation-triangle"></i>
            <span>Denda Pajak</span>
        </a>

        <!-- 7. Permohonan Perpajakan -->
        <div class="sidebar-heading">Permohonan Perpajakan</div>
        <a href="<?= base_url('modules/jenis_permohonan/index.php') ?>" class="sidebar-link <?= is_active('jenis_permohonan') ?>">
            <i class="bi bi-card-heading"></i>
            <span>Jenis Permohonan</span>
        </a>
        <a href="<?= base_url('modules/permohonan_pajak/index.php') ?>" class="sidebar-link <?= is_active('permohonan_pajak') ?>">
            <i class="bi bi-envelope-paper"></i>
            <span>Permohonan Pajak</span>
        </a>
        <a href="<?= base_url('modules/riwayat_permohonan/index.php') ?>" class="sidebar-link <?= is_active('riwayat_permohonan') ?>">
            <i class="bi bi-arrow-repeat"></i>
            <span>Riwayat Permohonan</span>
        </a>
        
        <div class="pb-4"></div>
    </div>
</aside>
