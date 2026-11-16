<?php
$judul_halaman = 'Detail Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM status_spt WHERE id_status_spt = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_spt = ambil_semua("
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    WHERE s.id_status_spt = ?
    ORDER BY s.id_spt DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Status SPT</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_status_spt'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Data
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-flag me-2 text-primary"></i>Informasi Status SPT</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Tampilan Badge</td>
                        <td><?= badge_status($data['nama_status']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Status</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_status']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan Alur</td>
                        <td><?= escape($data['keterangan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Laporan Saat Ini</td>
                        <td><span class="badge bg-primary"><?= count($daftar_spt) ?> Berkas SPT</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-info"></i>Daftar SPT Berstatus Ini</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_spt) ?> SPT</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Wajib Pajak</th>
                            <th>Jenis Pajak</th>
                            <th>Total Pajak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_spt)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada SPT dengan status ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_spt as $s): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $s['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($s['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td><?= escape($s['nama_wajib_pajak']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= escape($s['kode_pajak']) ?></span></td>
                                    <td class="fw-bold"><?= format_rupiah($s['total_pajak']) ?></td>
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
