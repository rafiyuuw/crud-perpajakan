<?php
$judul_halaman = 'Detail Riwayat Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT rp.*, pp.id_permohonan, pp.nomor_permohonan, pp.status_permohonan as status_saat_ini, pp.tanggal_pengajuan,
           wp.id_wajib_pajak, wp.nama_wajib_pajak, wp.nomor_npwp,
           jp.id_jenis_permohonan, jp.nama_permohonan,
           pt.nama_petugas, pt.jabatan, pt.email as email_petugas
    FROM riwayat_permohonan rp
    JOIN permohonan_pajak pp ON rp.id_permohonan = pp.id_permohonan
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    LEFT JOIN petugas_pajak pt ON rp.id_petugas = pt.id_petugas
    WHERE rp.id_riwayat_permohonan = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data riwayat permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Log Riwayat Permohonan #<?= $data['id_riwayat_permohonan'] ?></h4>
            <div class="text-muted small">Atas Permohonan: <?= escape($data['nomor_permohonan']) ?> (<?= escape($data['nama_wajib_pajak']) ?>)</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_riwayat_permohonan'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah
        </a>
        <a href="#" class="btn btn-danger btn-sm" onclick="return konfirmasiHapus('hapus.php?id=<?= $data['id_riwayat_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus catatan riwayat permohonan ini?');">
            <i class="bi bi-trash me-1"></i>Hapus
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Log Perubahan -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Rincian Mutasi Status Permohonan</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Waktu Perubahan</td>
                        <td class="fw-bold text-dark"><?= format_tanggal($data['tanggal_perubahan'], true) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Sebelumnya</td>
                        <td><span class="badge bg-light text-secondary border"><?= escape($data['status_sebelumnya']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Baru / Dituju</td>
                        <td><?= badge_status($data['status_baru']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Pemeriksa</td>
                        <td>
                            <?php if (!empty($data['nama_petugas'])): ?>
                                <span class="fw-semibold text-dark"><?= escape($data['nama_petugas']) ?></span>
                                <div class="small text-muted"><?= escape($data['jabatan']) ?></div>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Sistem / Otomatis</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-2 text-muted"></td></tr>
                    <tr>
                        <td class="text-muted align-top">Catatan Disposisi</td>
                        <td class="text-dark bg-light p-3 rounded"><?= nl2br(escape($data['catatan'] ?? '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Informasi Berkas Terkait -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i>Berkas Permohonan Terkait</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">No Permohonan</td>
                        <td>
                            <a href="../permohonan_pajak/detail.php?id=<?= $data['id_permohonan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                <?= escape($data['nomor_permohonan']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Terkini</td>
                        <td><?= badge_status($data['status_saat_ini']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tgl Pengajuan</td>
                        <td><?= format_tanggal($data['tanggal_pengajuan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Layanan</td>
                        <td><?= escape($data['nama_permohonan']) ?></td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-2 text-muted"></td></tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak</td>
                        <td>
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="fw-semibold text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace"><?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
