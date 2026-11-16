<?php
$judul_halaman = 'Detail Rincian SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT d.*, s.nomor_spt, s.tanggal_pelaporan, wp.nama_wajib_pajak, wp.nomor_npwp,
           o.nama_objek, o.alamat_objek, t.nama_tarif, jp.kode_pajak
    FROM detail_spt d
    JOIN spt s ON d.id_spt = s.id_spt
    JOIN objek_pajak o ON d.id_objek_pajak = o.id_objek_pajak
    JOIN tarif_pajak t ON d.id_tarif = t.id_tarif
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE d.id_detail_spt = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data rincian tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali ke SPT</a>
        <h4 class="fw-bold mb-0 text-dark">Rincian Perhitungan Objek SPT</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_detail_spt'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Rincian
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-2 text-primary"></i>Formula &amp; Komponen Penghitungan</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Nomor SPT (Induk)</td>
                        <td class="fw-bold">
                            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="text-decoration-none">
                                <?= escape($data['nomor_spt']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak</td>
                        <td><?= escape($data['nama_wajib_pajak']) ?> (<?= escape($data['nomor_npwp']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Objek Pajak</td>
                        <td class="fw-semibold text-dark">
                            <a href="../objek_pajak/detail.php?id=<?= $data['id_objek_pajak'] ?>" class="text-decoration-none">
                                <?= escape($data['nama_objek']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tarif Pajak (Master)</td>
                        <td><?= escape($data['nama_tarif']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dasar Pengenaan (DPP)</td>
                        <td class="fw-bold text-dark fs-5"><?= format_rupiah($data['dasar_pengenaan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tarif Terkunci (Digunakan)</td>
                        <td class="fw-bold text-primary fs-5"><?= (float)$data['tarif_digunakan'] ?>%</td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-muted fw-bold">Pajak Terutang (Hasil)</td>
                        <td class="fw-bold text-success fs-4"><?= format_rupiah($data['jumlah_pajak']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td><?= escape($data['keterangan'] ?: '-') ?></td>
                    </tr>
                </table>

                <div class="mt-4 p-3 bg-light rounded small text-muted border">
                    <strong>Rumus Perhitungan Simulasi:</strong><br>
                    <code>Pajak Terutang = Dasar Pengenaan &times; (Tarif Digunakan &divide; 100)</code><br>
                    <?= format_rupiah($data['dasar_pengenaan']) ?> &times; <?= (float)$data['tarif_digunakan'] ?>% = <strong><?= format_rupiah($data['jumlah_pajak']) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
