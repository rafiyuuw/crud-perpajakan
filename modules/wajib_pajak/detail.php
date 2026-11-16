<?php
$judul_halaman = 'Profil Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT wp.*, j.nama_jenis, k.nama_kantor, k.kode_kantor, k.kota as kota_kantor
    FROM wajib_pajak wp
    JOIN jenis_wajib_pajak j ON wp.id_jenis_wajib_pajak = j.id_jenis_wajib_pajak
    JOIN kantor_pajak k ON wp.id_kantor = k.id_kantor
    WHERE wp.id_wajib_pajak = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data wajib pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 1. Alamat Terdaftar
$daftar_alamat = ambil_semua("SELECT * FROM alamat_wajib_pajak WHERE id_wajib_pajak = ? ORDER BY id_alamat ASC", [$id]);

// 2. Kontak Terdaftar
$daftar_kontak = ambil_semua("SELECT * FROM kontak_wajib_pajak WHERE id_wajib_pajak = ? ORDER BY id_kontak ASC", [$id]);

// 3. Objek Pajak Dimiliki
$daftar_objek = ambil_semua("
    SELECT o.*, jp.kode_pajak, jp.nama_pajak
    FROM objek_pajak o
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak
    WHERE o.id_wajib_pajak = ?
    ORDER BY o.id_objek_pajak ASC
", [$id]);

// 4. Riwayat Pelaporan SPT
$daftar_spt = ambil_semua("
    SELECT s.*, jp.kode_pajak, st.nama_status
    FROM spt s
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    WHERE s.id_wajib_pajak = ?
    ORDER BY s.id_spt DESC
", [$id]);

// 5. Dokumen Perpajakan
$daftar_dokumen = ambil_semua("
    SELECT d.*, jd.nama_dokumen
    FROM dokumen_perpajakan d
    JOIN jenis_dokumen jd ON d.id_jenis_dokumen = jd.id_jenis_dokumen
    WHERE d.id_wajib_pajak = ?
    ORDER BY d.id_dokumen DESC
", [$id]);

// 6. Riwayat Permohonan Pajak
$daftar_mohon = ambil_semua("
    SELECT p.*, jp.nama_permohonan
    FROM permohonan_pajak p
    JOIN jenis_permohonan jp ON p.id_jenis_permohonan = jp.id_jenis_permohonan
    WHERE p.id_wajib_pajak = ?
    ORDER BY p.id_permohonan DESC
", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nama_wajib_pajak']) ?></h4>
            <div class="text-muted small">NPWP: <span class="font-monospace text-primary fw-semibold"><?= escape($data['nomor_npwp']) ?></span></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_wajib_pajak'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Profil WP
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Profil Pokok Wajib Pajak -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge-fill me-2 text-primary"></i>Informasi Pokok</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 130px;">NPWP</td>
                        <td class="fw-bold font-monospace text-primary"><?= escape($data['nomor_npwp']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama WP</td>
                        <td class="fw-semibold text-dark"><?= escape($data['nama_wajib_pajak']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['nama_jenis']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">KPP Terdaftar</td>
                        <td>[<?= escape($data['kode_kantor']) ?>] <?= escape($data['nama_kantor']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tgl Terdaftar</td>
                        <td><?= format_tanggal($data['tanggal_pendaftaran']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tgl Lahir/Berdiri</td>
                        <td><?= format_tanggal($data['tanggal_lahir']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Kelamin</td>
                        <td><?= escape($data['jenis_kelamin'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status WP</td>
                        <td><?= badge_status($data['status_wajib_pajak']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Ringkasan Statistik WP -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill me-2 text-info"></i>Riwayat Perpajakan</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-geo-alt me-2 text-secondary"></i>Alamat Terdaftar</span>
                        <span class="badge bg-secondary"><?= count($daftar_alamat) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-telephone me-2 text-secondary"></i>Kontak Terdaftar</span>
                        <span class="badge bg-secondary"><?= count($daftar_kontak) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-house me-2 text-secondary"></i>Objek Pajak Dimiliki</span>
                        <span class="badge bg-info text-dark"><?= count($daftar_objek) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-file-earmark-text me-2 text-secondary"></i>Laporan SPT</span>
                        <span class="badge bg-primary"><?= count($daftar_spt) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-folder me-2 text-secondary"></i>Dokumen Terupload</span>
                        <span class="badge bg-success"><?= count($daftar_dokumen) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-envelope me-2 text-secondary"></i>Permohonan Pajak</span>
                        <span class="badge bg-warning text-dark"><?= count($daftar_mohon) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Data Relasional Sub-Tabel -->
    <div class="col-12 col-lg-8">
        <!-- 1. Alamat Wajib Pajak -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Alamat Terdaftar (One-to-Many)</h6>
                <a href="../alamat_wajib_pajak/tambah.php?id_wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Alamat
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Jenis Alamat</th>
                            <th>Alamat Lengkap</th>
                            <th>Kota &amp; Provinsi</th>
                            <th>Kode Pos</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_alamat)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada alamat tersimpan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_alamat as $alm): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= escape($alm['jenis_alamat']) ?></span></td>
                                    <td><small><?= escape($alm['alamat_lengkap']) ?></small></td>
                                    <td><small><?= escape($alm['kota']) ?>, <?= escape($alm['provinsi']) ?></small></td>
                                    <td><small><?= escape($alm['kode_pos']) ?></small></td>
                                    <td class="text-center">
                                        <a href="../alamat_wajib_pajak/edit.php?id=<?= $alm['id_alamat'] ?>" class="btn btn-sm btn-outline-warning py-0 px-1" title="Ubah"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus" onclick="return konfirmasiHapus('../alamat_wajib_pajak/hapus.php?id=<?= $alm['id_alamat'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus alamat ini?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Kontak Wajib Pajak -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-telephone-fill me-2 text-success"></i>Kontak Terdaftar (One-to-Many)</h6>
                <a href="../kontak_wajib_pajak/tambah.php?id_wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Kontak
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Jenis Kontak</th>
                            <th>Nomor Telepon</th>
                            <th>Email</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_kontak)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada kontak tersimpan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_kontak as $ktk): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark border"><?= escape($ktk['jenis_kontak']) ?></span></td>
                                    <td><i class="bi bi-telephone me-1 text-muted"></i><?= escape($ktk['nomor_telepon']) ?></td>
                                    <td><i class="bi bi-envelope me-1 text-muted"></i><?= escape($ktk['email']) ?></td>
                                    <td class="text-center">
                                        <a href="../kontak_wajib_pajak/edit.php?id=<?= $ktk['id_kontak'] ?>" class="btn btn-sm btn-outline-warning py-0 px-1"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="return konfirmasiHapus('../kontak_wajib_pajak/hapus.php?id=<?= $ktk['id_kontak'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus kontak ini?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Objek Pajak Dimiliki -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-house-door-fill me-2 text-warning"></i>Objek Pajak Dimiliki</h6>
                <a href="../objek_pajak/tambah.php?id_wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-warning btn-sm py-0 px-2 text-dark" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Objek Pajak
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama Objek</th>
                            <th>Jenis Pajak</th>
                            <th>Nilai Objek</th>
                            <th>Alamat Objek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_objek)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada objek pajak terdaftar.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_objek as $obj): ?>
                                <tr>
                                    <td>
                                        <a href="../objek_pajak/detail.php?id=<?= $obj['id_objek_pajak'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($obj['nama_objek']) ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= escape($obj['kode_pajak']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= format_rupiah($obj['nilai_objek']) ?></td>
                                    <td><small class="text-muted"><?= escape($obj['alamat_objek']) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Laporan SPT -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text-fill me-2 text-primary"></i>Riwayat Pelaporan SPT</h6>
                <a href="../spt/tambah.php?id_wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Buat SPT Baru
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Jenis Pajak</th>
                            <th>Tgl Lapor</th>
                            <th>Total Pajak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_spt)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada laporan SPT.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_spt as $spt): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $spt['id_spt'] ?>" class="fw-semibold text-decoration-none text-primary">
                                            <?= escape($spt['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= escape($spt['kode_pajak']) ?></span></td>
                                    <td><small class="text-muted"><?= format_tanggal($spt['tanggal_pelaporan']) ?></small></td>
                                    <td class="fw-bold"><?= format_rupiah($spt['total_pajak']) ?></td>
                                    <td><?= badge_status($spt['nama_status']) ?></td>
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
