<?php
$judul_halaman = 'Detail Masa Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT m.*, jp.kode_pajak, jp.nama_pajak
    FROM masa_pajak m
    JOIN jenis_pajak jp ON m.id_jenis_pajak = jp.id_jenis_pajak
    WHERE m.id_masa_pajak = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data masa pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_spt = ambil_semua("
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, st.nama_status
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    WHERE s.id_masa_pajak = ?
    ORDER BY s.id_spt DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?jenis=<?= $data['id_jenis_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Masa Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_masa_pajak'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Masa
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar3 me-2 text-info"></i>Periode Pelaporan Pajak</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Jenis Pajak</td>
                        <td class="fw-bold text-dark fs-5">
                            <span class="badge bg-light text-primary border">[<?= escape($data['kode_pajak']) ?>]</span>
                            <?= escape($data['nama_pajak']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tahun Pajak</td>
                        <td><span class="badge bg-light text-dark border fs-6"><?= escape($data['tahun_pajak']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Mulai</td>
                        <td><?= format_tanggal($data['tanggal_mulai']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Berakhir</td>
                        <td><?= format_tanggal($data['tanggal_berakhir']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Batas Pelaporan</td>
                        <td class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i><?= format_tanggal($data['batas_pelaporan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Masa</td>
                        <td><?= badge_status($data['status']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>SPT Dilaporkan Pada Masa Ini</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_spt) ?> SPT</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Wajib Pajak</th>
                            <th>Tgl Lapor</th>
                            <th>Total Pajak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_spt)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada SPT pada masa pajak ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_spt as $spt): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $spt['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($spt['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div><?= escape($spt['nama_wajib_pajak']) ?></div>
                                        <small class="text-muted font-monospace"><?= escape($spt['nomor_npwp']) ?></small>
                                    </td>
                                    <td><small><?= format_tanggal($spt['tanggal_pelaporan']) ?></small></td>
                                    <td class="fw-bold"><?= format_rupiah($spt['total_pajak']) ?></td>
                                    <td><?= badge_status($spt['nama_status']) ?></td>
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
