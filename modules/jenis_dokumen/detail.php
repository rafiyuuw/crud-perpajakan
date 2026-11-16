<?php
$judul_halaman = 'Detail Jenis Dokumen';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_dokumen WHERE id_jenis_dokumen = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_file = ambil_semua("
    SELECT d.*, wp.nama_wajib_pajak, wp.nomor_npwp, s.nomor_spt
    FROM dokumen_perpajakan d
    JOIN wajib_pajak wp ON d.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN spt s ON d.id_spt = s.id_spt
    WHERE d.id_jenis_dokumen = ?
    ORDER BY d.id_dokumen DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Jenis Dokumen</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_jenis_dokumen'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Data
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-folder2 me-2 text-primary"></i>Informasi Jenis Dokumen</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nama Dokumen</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_dokumen']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deskripsi</td>
                        <td><?= escape($data['deskripsi']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total File</td>
                        <td><span class="badge bg-primary"><?= count($daftar_file) ?> File Terupload</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-files me-2 text-info"></i>Daftar Berkas Terunggah</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_file) ?> Berkas</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama File</th>
                            <th>Wajib Pajak</th>
                            <th>Lampiran SPT</th>
                            <th>Tgl Upload</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_file)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada file terupload untuk jenis dokumen ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_file as $df): ?>
                                <tr>
                                    <td>
                                        <a href="../dokumen_perpajakan/detail.php?id=<?= $df['id_dokumen'] ?>" class="fw-semibold text-decoration-none">
                                            <i class="bi bi-paperclip me-1"></i><?= escape($df['nama_file']) ?>
                                        </a>
                                    </td>
                                    <td><?= escape($df['nama_wajib_pajak']) ?></td>
                                    <td><?= $df['nomor_spt'] ? escape($df['nomor_spt']) : '<span class="text-muted small">Umum (Bukan SPT)</span>' ?></td>
                                    <td><small class="text-muted"><?= format_tanggal($df['tanggal_upload'], true) ?></small></td>
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
