<?php
$judul_halaman = 'Detail Riwayat Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT r.*, s.nomor_spt, s.tanggal_pelaporan, wp.nama_wajib_pajak, wp.nomor_npwp,
           st.nama_status, p.nama_petugas, p.jabatan, p.nomor_identitas
    FROM riwayat_status_spt r
    JOIN spt s ON r.id_spt = s.id_spt
    JOIN status_spt st ON r.id_status_spt = st.id_status_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN petugas_pajak p ON r.id_petugas = p.id_petugas
    WHERE r.id_riwayat_spt = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data riwayat tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali ke SPT</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Log Perubahan Status SPT</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_riwayat_spt'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Catatan
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Informasi Catatan Riwayat</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Nomor SPT</td>
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
                        <td class="text-muted">Status Tercatat</td>
                        <td><?= badge_status($data['nama_status']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Perubahan</td>
                        <td><?= format_tanggal($data['tanggal_perubahan'], true) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Pemeriksa</td>
                        <td><?= escape($data['nama_petugas'] ? $data['nama_petugas'] . ' (' . $data['jabatan'] . ')' : 'Sistem Otomatis') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan Verifikasi</td>
                        <td class="fw-semibold text-dark"><?= escape($data['catatan'] ?: '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
