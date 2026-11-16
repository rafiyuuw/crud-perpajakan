<?php
$judul_halaman = 'Detail Denda Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT d.*, t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan, t.status_tagihan, t.tanggal_jatuh_tempo,
           s.id_spt, s.nomor_spt, wp.id_wajib_pajak, wp.nama_wajib_pajak, wp.nomor_npwp,
           jp.kode_pajak, jp.nama_pajak
    FROM denda_pajak d
    JOIN tagihan_pajak t ON d.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE d.id_denda = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data denda pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Denda Pajak #<?= $data['id_denda'] ?></h4>
            <div class="text-muted small">Atas Tagihan: <?= escape($data['nomor_tagihan']) ?> (<?= escape($data['nama_wajib_pajak']) ?>)</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_denda'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah
        </a>
        <a href="#" class="btn btn-danger btn-sm" onclick="return konfirmasiHapus('hapus.php?id=<?= $data['id_denda'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus denda sebesar <?= format_rupiah($data['jumlah_denda']) ?>?');">
            <i class="bi bi-trash me-1"></i>Hapus
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Informasi Denda -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-octagon me-2 text-danger"></i>Rincian Sanksi Administrasi</h6>
                <div><?= badge_status($data['status_denda']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 180px;">Nominal Sanksi / Denda</td>
                        <td class="fw-bold text-danger fs-4"><?= format_rupiah($data['jumlah_denda']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Pengenaan</td>
                        <td><?= format_tanggal($data['tanggal_pengenaan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alasan Pengenaan</td>
                        <td class="fw-semibold text-dark"><?= escape($data['alasan_denda']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dasar Perhitungan</td>
                        <td><?= !empty($data['dasar_perhitungan']) ? escape($data['dasar_perhitungan']) : '<span class="text-muted fst-italic">-</span>' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Penyelesaian</td>
                        <td><?= badge_status($data['status_denda']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Informasi Tagihan Terkait -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Informasi Tagihan Pajak (FK)</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nomor Tagihan</td>
                        <td>
                            <a href="../tagihan_pajak/detail.php?id=<?= $data['id_tagihan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                <?= escape($data['nomor_tagihan']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pokok Tagihan</td>
                        <td class="fw-bold text-dark"><?= format_rupiah($data['jumlah_tagihan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jatuh Tempo</td>
                        <td><?= format_tanggal($data['tanggal_jatuh_tempo']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Tagihan</td>
                        <td><?= badge_status($data['status_tagihan']) ?></td>
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
                    <tr>
                        <td class="text-muted">Jenis Pajak</td>
                        <td><?= escape($data['kode_pajak']) ?> - <?= escape($data['nama_pajak']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">SPT Terkait</td>
                        <td>
                            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="text-decoration-none font-monospace small">
                                <?= escape($data['nomor_spt']) ?>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
