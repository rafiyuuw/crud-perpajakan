<?php
$judul_halaman = 'Detail Jenis Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_permohonan WHERE id_jenis_permohonan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data jenis permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Ambil permohonan terkait
$daftar_permohonan = ambil_semua("
    SELECT pp.*, wp.nama_wajib_pajak, wp.nomor_npwp, pt.nama_petugas
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN petugas_pajak pt ON pp.id_petugas = pt.id_petugas
    WHERE pp.id_jenis_permohonan = ?
    ORDER BY pp.id_permohonan DESC
", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nama_permohonan']) ?></h4>
            <div class="text-muted small">ID Jenis: #<?= $data['id_jenis_permohonan'] ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="../permohonan_pajak/tambah.php?id_jenis_permohonan=<?= $data['id_jenis_permohonan'] ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Buat Permohonan
        </a>
        <a href="edit.php?id=<?= $data['id_jenis_permohonan'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah
        </a>
        <a href="#" class="btn btn-danger btn-sm" onclick="return konfirmasiHapus('hapus.php?id=<?= $data['id_jenis_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus jenis permohonan <strong><?= escape($data['nama_permohonan']) ?></strong>?');">
            <i class="bi bi-trash me-1"></i>Hapus
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Informasi Utama -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi Layanan</h6>
                <div><?= badge_status($data['status']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nama Layanan</td>
                        <td class="fw-bold text-dark"><?= escape($data['nama_permohonan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Pengajuan</td>
                        <td><span class="badge bg-primary"><?= count($daftar_permohonan) ?> berkas</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted align-top">Deskripsi</td>
                        <td class="text-secondary"><?= nl2br(escape($data['deskripsi'] ?? '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Daftar Berkas Permohonan Terkait -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i>Berkas Permohonan Masuk</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No Permohonan</th>
                                <th>Wajib Pajak</th>
                                <th>Tgl Pengajuan</th>
                                <th>Petugas Pemeriksa</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftar_permohonan)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada permohonan diajukan untuk layanan ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($daftar_permohonan as $p): ?>
                                    <tr>
                                        <td>
                                            <a href="../permohonan_pajak/detail.php?id=<?= $p['id_permohonan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                                <?= escape($p['nomor_permohonan']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= escape($p['nama_wajib_pajak']) ?></div>
                                            <small class="text-muted font-monospace"><?= escape($p['nomor_npwp']) ?></small>
                                        </td>
                                        <td><small class="text-muted"><?= format_tanggal($p['tanggal_pengajuan']) ?></small></td>
                                        <td><small class="text-muted"><?= escape($p['nama_petugas'] ?? '-') ?></small></td>
                                        <td><?= badge_status($p['status_permohonan']) ?></td>
                                        <td class="text-center">
                                            <a href="../permohonan_pajak/detail.php?id=<?= $p['id_permohonan'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
