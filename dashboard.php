<?php
/**
 * Halaman Dashboard Utama
 * Sistem Informasi Perpajakan
 */

$judul_halaman = 'Dashboard Administrasi Perpajakan';
require_once __DIR__ . '/includes/header.php';

// Ambil Statistik Dinamis dari Basis Data
$total_wp          = hitung_total('wajib_pajak');
$total_petugas     = hitung_total('petugas_pajak');
$total_jenis_pajak = hitung_total('jenis_pajak');
$total_spt         = hitung_total('spt');
$total_tagihan     = hitung_total('tagihan_pajak');
$total_pembayaran  = hitung_total('pembayaran_pajak');
$total_permohonan  = hitung_total('permohonan_pajak');

// Agregat Nominal
$total_rupiah_bayar = ambil_satu("SELECT COALESCE(SUM(jumlah_pembayaran), 0) as total FROM pembayaran_pajak WHERE status_pembayaran = 'Berhasil'")['total'] ?? 0;
$total_rupiah_piutang = ambil_satu("SELECT COALESCE(SUM(jumlah_tagihan), 0) as total FROM tagihan_pajak WHERE status_tagihan != 'Lunas'")['total'] ?? 0;

// 1. Data Grafik Pelaporan SPT per Status
$data_status_spt = ambil_semua("
    SELECT st.nama_status, COUNT(s.id_spt) as jumlah 
    FROM status_spt st 
    LEFT JOIN spt s ON st.id_status_spt = s.id_status_spt 
    GROUP BY st.id_status_spt, st.nama_status
");
$labels_status = [];
$counts_status = [];
foreach ($data_status_spt as $row) {
    $labels_status[] = $row['nama_status'];
    $counts_status[] = (int)$row['jumlah'];
}

// 2. Data Grafik Pembayaran Bulanan
$data_grafik_bayar = ambil_semua("
    SELECT DATE_FORMAT(tanggal_pembayaran, '%M %Y') as label_bulan, SUM(jumlah_pembayaran) as total
    FROM pembayaran_pajak
    WHERE status_pembayaran = 'Berhasil'
    GROUP BY DATE_FORMAT(tanggal_pembayaran, '%Y-%m'), label_bulan
    ORDER BY DATE_FORMAT(tanggal_pembayaran, '%Y-%m') ASC
");
$labels_bayar = [];
$values_bayar = [];
foreach ($data_grafik_bayar as $row) {
    $labels_bayar[] = $row['label_bulan'];
    $values_bayar[] = (float)$row['total'];
}
if (empty($labels_bayar)) {
    $labels_bayar = ['Bulan Ini'];
    $values_bayar = [0];
}

// 3. Daftar SPT Terbaru
$spt_terbaru = ambil_semua("
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, st.nama_status
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    ORDER BY s.id_spt DESC
    LIMIT 5
");

// 4. Daftar Pembayaran Terbaru
$bayar_terbaru = ambil_semua("
    SELECT p.*, m.nama_metode, t.nomor_tagihan, wp.nama_wajib_pajak
    FROM pembayaran_pajak p
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY p.id_pembayaran DESC
    LIMIT 5
");

// 5. Tagihan Mendekati Jatuh Tempo
$tagihan_tempo = ambil_semua("
    SELECT t.*, s.nomor_spt, wp.nama_wajib_pajak, wp.nomor_npwp
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE t.status_tagihan != 'Lunas'
    ORDER BY t.tanggal_jatuh_tempo ASC
    LIMIT 5
");

// 6. Permohonan Terbaru
$mohon_terbaru = ambil_semua("
    SELECT p.*, jp.nama_permohonan, wp.nama_wajib_pajak, wp.nomor_npwp
    FROM permohonan_pajak p
    JOIN jenis_permohonan jp ON p.id_jenis_permohonan = jp.id_jenis_permohonan
    JOIN wajib_pajak wp ON p.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY p.id_permohonan DESC
    LIMIT 5
");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Dashboard Perpajakan</h4>
        <p class="text-muted small mb-0">Ikhtisar administrasi perpajakan, realisasi penerimaan kas negara, dan pelaporan SPT.</p>
    </div>
    <div>
        <a href="<?= base_url('modules/spt/tambah.php') ?>" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-file-earmark-plus me-1"></i>Input SPT Baru
        </a>
        <a href="<?= base_url('modules/pembayaran_pajak/tambah.php') ?>" class="btn btn-success btn-sm">
            <i class="bi bi-cash me-1"></i>Catat Pembayaran
        </a>
    </div>
</div>

<!-- 7 KARTU STATISTIK UTAMA -->
<div class="row g-3 mb-4">
    <!-- Total Wajib Pajak -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Wajib Pajak</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_wp) ?></h3>
                <small class="text-muted">Terdaftar resmi</small>
            </div>
        </div>
    </div>

    <!-- Total Petugas Pajak -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-info bg-opacity-10 text-info">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Petugas Pajak</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_petugas) ?></h3>
                <small class="text-muted">Aparatur pengawas</small>
            </div>
        </div>
    </div>

    <!-- Total Jenis Pajak -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                <i class="bi bi-bookmark-star-fill"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Jenis Pajak</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_jenis_pajak) ?></h3>
                <small class="text-muted">PPh, PPN, PBB aktif</small>
            </div>
        </div>
    </div>

    <!-- Total Pelaporan SPT -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-file-earmark-check-fill"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Pelaporan SPT</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_spt) ?></h3>
                <small class="text-muted">Berkas dilaporkan</small>
            </div>
        </div>
    </div>

    <!-- Total Tagihan Pajak -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Tagihan Pajak</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_tagihan) ?> Tagihan</h3>
                <small class="text-danger">Sisa: <?= format_rupiah($total_rupiah_piutang) ?></small>
            </div>
        </div>
    </div>

    <!-- Total Pembayaran Pajak -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Realisasi Pembayaran</small>
                <h3 class="fw-bold mb-0 text-success"><?= format_rupiah($total_rupiah_bayar) ?></h3>
                <small class="text-muted"><?= number_format($total_pembayaran) ?> transaksi sukses</small>
            </div>
        </div>
    </div>

    <!-- Total Permohonan -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card stat-card border-0 bg-white">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-envelope-paper-fill"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Permohonan Pajak</small>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_permohonan) ?></h3>
                <small class="text-muted">Angsuran &amp; Restitusi</small>
            </div>
        </div>
    </div>
</div>

<!-- 2 GRAFIK UTAMA: PELAPORAN SPT & PEMBAYARAN PAJAK -->
<div class="row g-3 mb-4">
    <!-- Grafik Pembayaran Pajak -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Grafik Realisasi Penerimaan Pajak</div>
                <span class="badge bg-light text-secondary border">Tahun 2026</span>
            </div>
            <div class="card-body">
                <canvas id="chartPembayaran" height="130"></canvas>
            </div>
        </div>
    </div>

    <!-- Grafik Status SPT -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark"><i class="bi bi-pie-chart-fill me-2 text-info"></i>Distribusi Status SPT</div>
                <span class="badge bg-light text-secondary border">Total: <?= $total_spt ?></span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="max-width: 280px; width: 100%;">
                    <canvas id="chartStatusSPT"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL 1: DAFTAR SPT TERBARU & DAFTAR PEMBAYARAN TERBARU -->
<div class="row g-3 mb-4">
    <!-- SPT Terbaru -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Pelaporan SPT Terbaru</div>
                <a href="<?= base_url('modules/spt/index.php') ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.75rem;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nomor SPT</th>
                            <th>Wajib Pajak</th>
                            <th>Pajak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($spt_terbaru)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada SPT dilaporkan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($spt_terbaru as $spt): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('modules/spt/detail.php?id=' . $spt['id_spt']) ?>" class="fw-semibold text-decoration-none text-primary">
                                            <?= escape($spt['nomor_spt']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= format_tanggal($spt['tanggal_pelaporan']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= escape($spt['nama_wajib_pajak']) ?></div>
                                        <small class="text-muted"><?= escape($spt['nomor_npwp']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= escape($spt['kode_pajak']) ?></span>
                                        <div class="fw-semibold text-dark mt-1" style="font-size: 0.78rem;"><?= format_rupiah($spt['total_pajak']) ?></div>
                                    </td>
                                    <td><?= badge_status($spt['nama_status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pembayaran Terbaru -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark"><i class="bi bi-cash-stack me-2 text-success"></i>Pembayaran Pajak Terbaru</div>
                <a href="<?= base_url('modules/pembayaran_pajak/index.php') ?>" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 0.75rem;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Wajib Pajak</th>
                            <th>Jumlah Bayar</th>
                            <th>Metode</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bayar_terbaru)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada transaksi pembayaran.</td></tr>
                        <?php else: ?>
                            <?php foreach ($bayar_terbaru as $byr): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('modules/pembayaran_pajak/detail.php?id=' . $byr['id_pembayaran']) ?>" class="fw-semibold text-decoration-none text-dark">
                                            <?= escape($byr['nomor_transaksi']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= format_tanggal($byr['tanggal_pembayaran'], true) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= escape($byr['nama_wajib_pajak']) ?></div>
                                        <small class="text-muted">Tagihan: <?= escape($byr['nomor_tagihan']) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success"><?= format_rupiah($byr['jumlah_pembayaran']) ?></span>
                                        <div><?= badge_status($byr['status_pembayaran']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border"><?= escape($byr['nama_metode']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TABEL 2: TAGIHAN MENDEKATI JATUH TEMPO & PERMOHONAN TERBARU -->
<div class="row g-3">
    <!-- Tagihan Jatuh Tempo -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-danger"><i class="bi bi-clock-history me-2"></i>Tagihan Mendekati Jatuh Tempo</div>
                <a href="<?= base_url('modules/tagihan_pajak/index.php') ?>" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.75rem;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Wajib Pajak</th>
                            <th>Jatuh Tempo</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tagihan_tempo)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada tagihan tertunggak.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tagihan_tempo as $tag): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('modules/tagihan_pajak/detail.php?id=' . $tag['id_tagihan']) ?>" class="fw-semibold text-danger text-decoration-none">
                                            <?= escape($tag['nomor_tagihan']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;">SPT: <?= escape($tag['nomor_spt']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= escape($tag['nama_wajib_pajak']) ?></div>
                                        <small class="text-muted"><?= escape($tag['nomor_npwp']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                            <i class="bi bi-calendar-event me-1"></i><?= format_tanggal($tag['tanggal_jatuh_tempo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= format_rupiah($tag['jumlah_tagihan']) ?></div>
                                        <div><?= badge_status($tag['status_tagihan']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Permohonan Terbaru -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="fw-bold text-dark"><i class="bi bi-envelope-paper me-2 text-info"></i>Permohonan Pajak Terbaru</div>
                <a href="<?= base_url('modules/permohonan_pajak/index.php') ?>" class="btn btn-outline-info btn-sm py-0 px-2" style="font-size: 0.75rem;">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Jenis Permohonan</th>
                            <th>Pemohon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mohon_terbaru)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada permohonan diajukan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($mohon_terbaru as $pmh): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('modules/permohonan_pajak/detail.php?id=' . $pmh['id_permohonan']) ?>" class="fw-semibold text-decoration-none text-dark">
                                            <?= escape($pmh['nomor_permohonan']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= format_tanggal($pmh['tanggal_pengajuan']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= escape($pmh['nama_permohonan']) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?= escape($pmh['nama_wajib_pajak']) ?></div>
                                        <small class="text-muted"><?= escape($pmh['nomor_npwp']) ?></small>
                                    </td>
                                    <td><?= badge_status($pmh['status_permohonan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Chart Realisasi Pembayaran Bulanan
    const ctxBayar = document.getElementById('chartPembayaran');
    if (ctxBayar) {
        new Chart(ctxBayar, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels_bayar) ?>,
                datasets: [{
                    label: 'Realisasi Pembayaran (Rp)',
                    data: <?= json_encode($values_bayar) ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderColor: '#1d4ed8',
                    borderWidth: 1.5,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) {
                                return 'Rp ' + (val / 1000000).toLocaleString('id-ID') + ' Jt';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Chart Distribusi Status SPT (Doughnut)
    const ctxStatus = document.getElementById('chartStatusSPT');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_status) ?>,
                datasets: [{
                    data: <?= json_encode($counts_status) ?>,
                    backgroundColor: [
                        '#94a3b8', // Draft
                        '#38bdf8', // Diajukan
                        '#fbbf24', // Dalam Pemeriksaan
                        '#22c55e', // Diterima
                        '#ef4444', // Ditolak
                        '#f97316'  // Perlu Perbaikan
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
