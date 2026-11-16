<?php
$judul_halaman = 'Detail Jenis Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_wajib_pajak WHERE id_jenis_wajib_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_wp = ambil_semua("
    SELECT wp.*, k.nama_kantor 
    FROM wajib_pajak wp 
    JOIN kantor_pajak k ON wp.id_kantor = k.id_kantor 
    WHERE wp.id_jenis_wajib_pajak = ? 
    ORDER BY wp.id_wajib_pajak ASC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Jenis Wajib Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_jenis_wajib_pajak'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Data
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-tags me-2 text-primary"></i>Informasi Jenis WP</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">ID Jenis</td>
                        <td class="fw-bold">#<?= $data['id_jenis_wajib_pajak'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Jenis</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_jenis']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td><?= escape($data['keterangan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Terdaftar</td>
                        <td><span class="badge bg-primary"><?= count($daftar_wp) ?> Wajib Pajak</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-2 text-success"></i>Daftar Wajib Pajak Kategori Ini</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_wp) ?> Data</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NPWP</th>
                            <th>Nama Wajib Pajak</th>
                            <th>KPP Terdaftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_wp)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada wajib pajak dalam kategori ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_wp as $wp): ?>
                                <tr>
                                    <td><small class="text-muted"><?= escape($wp['nomor_npwp']) ?></small></td>
                                    <td>
                                        <a href="../wajib_pajak/detail.php?id=<?= $wp['id_wajib_pajak'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($wp['nama_wajib_pajak']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= escape($wp['nama_kantor']) ?></small></td>
                                    <td><?= badge_status($wp['status_wajib_pajak']) ?></td>
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
