<?php
$judul_halaman = 'Detail Jenis Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_pajak WHERE id_jenis_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 1. Tarif Terkait
$daftar_tarif = ambil_semua("SELECT * FROM tarif_pajak WHERE id_jenis_pajak = ? ORDER BY id_tarif ASC", [$id]);

// 2. Masa Pajak Terkait
$daftar_masa = ambil_semua("SELECT * FROM masa_pajak WHERE id_jenis_pajak = ? ORDER BY id_masa_pajak DESC", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Jenis Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_jenis_pajak'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Data
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bookmark-check me-2 text-primary"></i>Informasi Pajak</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Kode Pajak</td>
                        <td><span class="badge bg-light text-primary border fs-6"><?= escape($data['kode_pajak']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Pajak</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_pajak']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deskripsi</td>
                        <td><?= escape($data['deskripsi']) ?></td>
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
        <!-- Tarif Terkait -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-percent me-2 text-success"></i>Tarif Pajak Berlaku</h6>
                <a href="../tarif_pajak/tambah.php?id_jenis_pajak=<?= $data['id_jenis_pajak'] ?>" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus me-1"></i>Tambah Tarif
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Tarif</th>
                            <th>Nilai Tarif</th>
                            <th>Mulai Berlaku</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_tarif)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada tarif dikonfigurasi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_tarif as $trf): ?>
                                <tr>
                                    <td>
                                        <a href="../tarif_pajak/detail.php?id=<?= $trf['id_tarif'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($trf['nama_tarif']) ?>
                                        </a>
                                    </td>
                                    <td class="fw-bold text-dark"><?= (float)$trf['nilai_tarif'] ?>%</td>
                                    <td><small><?= format_tanggal($trf['tanggal_mulai']) ?></small></td>
                                    <td><?= badge_status($trf['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Masa Pajak Terkait -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar3 me-2 text-info"></i>Periode / Masa Pajak</h6>
                <a href="../masa_pajak/tambah.php?id_jenis_pajak=<?= $data['id_jenis_pajak'] ?>" class="btn btn-outline-info btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus me-1"></i>Tambah Masa
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Periode Pelaporan</th>
                            <th>Batas Lapor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_masa)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada masa pajak terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_masa as $ms): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= $ms['tahun_pajak'] ?></span></td>
                                    <td><small><?= format_tanggal($ms['tanggal_mulai']) ?> s.d <?= format_tanggal($ms['tanggal_berakhir']) ?></small></td>
                                    <td><small class="text-danger fw-semibold"><?= format_tanggal($ms['batas_pelaporan']) ?></small></td>
                                    <td><?= badge_status($ms['status']) ?></td>
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
