<?php
$judul_halaman = 'Detail Tarif Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT t.*, jp.kode_pajak, jp.nama_pajak
    FROM tarif_pajak t
    JOIN jenis_pajak jp ON t.id_jenis_pajak = jp.id_jenis_pajak
    WHERE t.id_tarif = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data tarif tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$penggunaan_spt = ambil_semua("
    SELECT d.*, s.nomor_spt, s.tanggal_pelaporan, wp.nama_wajib_pajak
    FROM detail_spt d
    JOIN spt s ON d.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE d.id_tarif = ?
    ORDER BY s.id_spt DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?jenis=<?= $data['id_jenis_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Tarif Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_tarif'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Tarif
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-percent me-2 text-primary"></i>Ketentuan Tarif</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nama Tarif</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_tarif']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Pajak</td>
                        <td>
                            <a href="../jenis_pajak/detail.php?id=<?= $data['id_jenis_pajak'] ?>" class="text-decoration-none">
                                [<?= escape($data['kode_pajak']) ?>] <?= escape($data['nama_pajak']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nilai Tarif</td>
                        <td class="fw-bold text-primary fs-4"><?= (float)$data['nilai_tarif'] ?>%</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode Hitung</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['jenis_perhitungan']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Mulai Berlaku</td>
                        <td><?= format_tanggal($data['tanggal_mulai']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Berakhir Berlaku</td>
                        <td><?= $data['tanggal_berakhir'] ? format_tanggal($data['tanggal_berakhir']) : '<span class="text-success fw-semibold">Berlaku Seterusnya</span>' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td><?= badge_status($data['status']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-ruled me-2 text-success"></i>Penggunaan Dalam Laporan SPT</h6>
                <span class="badge bg-light text-secondary border"><?= count($penggunaan_spt) ?> Detail SPT</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Wajib Pajak</th>
                            <th>Tarif Terkunci</th>
                            <th>Dasar Pengenaan</th>
                            <th>Pajak Terutang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($penggunaan_spt)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada SPT yang menggunakan tarif ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($penggunaan_spt as $ps): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $ps['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($ps['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= escape($ps['nama_wajib_pajak']) ?></small></td>
                                    <td><span class="badge bg-light text-dark border"><?= (float)$ps['tarif_digunakan'] ?>%</span></td>
                                    <td><?= format_rupiah($ps['dasar_pengenaan']) ?></td>
                                    <td class="fw-bold"><?= format_rupiah($ps['jumlah_pajak']) ?></td>
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
