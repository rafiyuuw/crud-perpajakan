<?php
$judul_halaman = 'Detail Dokumen Perpajakan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT d.*, wp.nama_wajib_pajak, wp.nomor_npwp, jd.nama_dokumen, s.nomor_spt, s.tanggal_pelaporan
    FROM dokumen_perpajakan d
    JOIN wajib_pajak wp ON d.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_dokumen jd ON d.id_jenis_dokumen = jd.id_jenis_dokumen
    LEFT JOIN spt s ON d.id_spt = s.id_spt
    WHERE d.id_dokumen = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data dokumen tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Berkas Dokumen</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_dokumen'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Dokumen
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Informasi Berkas Perpajakan</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 160px;">Nama File</td>
                        <td class="fw-bold text-dark fs-5">
                            <i class="bi bi-file-earmark-pdf text-danger me-2"></i><?= escape($data['nama_file']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Dokumen</td>
                        <td><span class="badge bg-light text-dark border fs-6"><?= escape($data['nama_dokumen']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak (Pemilik)</td>
                        <td>
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="fw-semibold text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace">NPWP: <?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kaitan Laporan SPT</td>
                        <td>
                            <?php if ($data['id_spt']): ?>
                                <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="text-decoration-none fw-semibold">
                                    <i class="bi bi-link-45deg me-1"></i><?= escape($data['nomor_spt']) ?>
                                </a>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border">Berkas Dokumen Umum</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lokasi Penyimpanan</td>
                        <td><code><?= escape($data['lokasi_file']) ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Pengunggahan</td>
                        <td><?= format_tanggal($data['tanggal_upload'], true) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td><?= escape($data['keterangan'] ?: '-') ?></td>
                    </tr>
                </table>

                <div class="mt-4 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark"><i class="bi bi-shield-lock me-1 text-primary"></i>Integritas Dokumen Terjaga</div>
                        <small class="text-muted">Berkas tersimpan di storage server lokal sistem perpajakan.</small>
                    </div>
                    <?php if (file_exists(__DIR__ . '/../../' . $data['lokasi_file'])): ?>
                        <a href="<?= base_url($data['lokasi_file']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-download me-1"></i>Unduh Berkas
                        </a>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">Arsip Terverifikasi</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
