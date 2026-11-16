<?php
$judul_halaman = 'Detail Kantor Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM kantor_pajak WHERE id_kantor = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data kantor pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Data Petugas di Kantor Ini (Relasi One-to-Many)
$daftar_petugas = ambil_semua("SELECT * FROM petugas_pajak WHERE id_kantor = ? ORDER BY id_petugas ASC", [$id]);

// Data Wajib Pajak Terdaftar di Kantor Ini (Relasi One-to-Many)
$daftar_wp = ambil_semua("
    SELECT wp.*, j.nama_jenis 
    FROM wajib_pajak wp 
    JOIN jenis_wajib_pajak j ON wp.id_jenis_wajib_pajak = j.id_jenis_wajib_pajak 
    WHERE wp.id_kantor = ? 
    ORDER BY wp.id_wajib_pajak ASC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Kantor Pelayanan Pajak</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_kantor'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Profil Kantor -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Informasi Kantor Pajak</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">ID Kantor</td>
                        <td class="fw-semibold">#<?= $data['id_kantor'] ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kode Kantor</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['kode_kantor']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Kantor</td>
                        <td class="fw-bold text-dark"><?= escape($data['nama_kantor']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kota</td>
                        <td><?= escape($data['kota']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat</td>
                        <td><?= escape($data['alamat']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Telepon</td>
                        <td><i class="bi bi-telephone me-1 text-muted"></i><?= escape($data['nomor_telepon']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email Resmi</td>
                        <td><i class="bi bi-envelope me-1 text-muted"></i><?= escape($data['email']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Pada</td>
                        <td><?= format_tanggal($data['created_at'], true) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Relasi Terkait -->
    <div class="col-12 col-lg-7">
        <!-- Petugas di Kantor Ini -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-badge me-2 text-info"></i>Daftar Petugas Pajak
                </h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_petugas) ?> Petugas</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>NIP / Identitas</th>
                            <th>Nama Petugas</th>
                            <th>Jabatan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_petugas)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada petugas di kantor ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_petugas as $p): ?>
                                <tr>
                                    <td><small class="text-muted"><?= escape($p['nomor_identitas']) ?></small></td>
                                    <td>
                                        <a href="../petugas_pajak/detail.php?id=<?= $p['id_petugas'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($p['nama_petugas']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= escape($p['jabatan']) ?></small></td>
                                    <td><?= badge_status($p['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Wajib Pajak Terdaftar -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-people me-2 text-success"></i>Wajib Pajak Terdaftar
                </h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_wp) ?> Wajib Pajak</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nomor NPWP</th>
                            <th>Nama Wajib Pajak</th>
                            <th>Jenis</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_wp)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada wajib pajak terdaftar di kantor ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_wp as $wp): ?>
                                <tr>
                                    <td><small class="text-muted"><?= escape($wp['nomor_npwp']) ?></small></td>
                                    <td>
                                        <a href="../wajib_pajak/detail.php?id=<?= $wp['id_wajib_pajak'] ?>" class="fw-semibold text-decoration-none text-dark">
                                            <?= escape($wp['nama_wajib_pajak']) ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= escape($wp['nama_jenis']) ?></span></td>
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
