<?php
$judul_halaman = 'Detail Objek Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT o.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, jp.nama_pajak
    FROM objek_pajak o
    JOIN wajib_pajak wp ON o.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak
    WHERE o.id_objek_pajak = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data objek pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Cek apakah objek pernah dilaporkan dalam Detail SPT
$riwayat_detail = ambil_semua("
    SELECT d.*, s.nomor_spt, s.tanggal_pelaporan, st.nama_status
    FROM detail_spt d
    JOIN spt s ON d.id_spt = s.id_spt
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    WHERE d.id_objek_pajak = ?
    ORDER BY s.id_spt DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Objek Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_objek_pajak'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Objek
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-house-door-fill me-2 text-warning"></i>Spesifikasi Objek Pajak</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nama Objek</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_objek']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pemilik (WP)</td>
                        <td>
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none fw-semibold">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace"><?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Pajak</td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= escape($data['kode_pajak']) ?></span>
                            <span class="small ms-1"><?= escape($data['nama_pajak']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nilai Objek</td>
                        <td class="fw-bold text-success fs-5"><?= format_rupiah($data['nilai_objek']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lokasi / Alamat</td>
                        <td><?= escape($data['alamat_objek']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td><?= escape($data['keterangan'] ?: '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Riwayat Pengenaan Pajak (Detail SPT)</h6>
                <span class="badge bg-light text-secondary border"><?= count($riwayat_detail) ?> Transaksi</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Tanggal Lapor</th>
                            <th>Dasar Pengenaan</th>
                            <th>Pajak Terutang</th>
                            <th>Status SPT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayat_detail)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada pengenaan SPT untuk objek ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($riwayat_detail as $rd): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $rd['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($rd['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= format_tanggal($rd['tanggal_pelaporan']) ?></small></td>
                                    <td><?= format_rupiah($rd['dasar_pengenaan']) ?></td>
                                    <td class="fw-bold"><?= format_rupiah($rd['jumlah_pajak']) ?></td>
                                    <td><?= badge_status($rd['nama_status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
