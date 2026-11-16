<?php
$judul_halaman = 'Detail Petugas Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT p.*, k.nama_kantor, k.kode_kantor, k.kota as kota_kantor, a.id_akun, a.nama_pengguna, a.peran, a.status_akun, a.terakhir_masuk
    FROM petugas_pajak p
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    LEFT JOIN akun_petugas a ON p.id_petugas = a.id_petugas
    WHERE p.id_petugas = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data petugas pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// SPT yang ditangani petugas ini
$daftar_spt = ambil_semua("
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, st.nama_status
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    WHERE s.id_petugas = ?
    ORDER BY s.id_spt DESC
", [$id]);

// Permohonan yang ditangani petugas ini
$daftar_mohon = ambil_semua("
    SELECT pm.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.nama_permohonan
    FROM permohonan_pajak pm
    JOIN wajib_pajak wp ON pm.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pm.id_jenis_permohonan = jp.id_jenis_permohonan
    WHERE pm.id_petugas = ?
    ORDER BY pm.id_permohonan DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Petugas Pajak</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_petugas'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Data
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Profil Petugas & Akun -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge me-2 text-primary"></i>Profil Petugas Pajak</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">NIP</td>
                        <td class="fw-bold text-dark"><?= escape($data['nomor_identitas']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Lengkap</td>
                        <td class="fw-semibold"><?= escape($data['nama_petugas']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jabatan</td>
                        <td><?= escape($data['jabatan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kantor Penugasan</td>
                        <td>
                            <a href="../kantor_pajak/detail.php?id=<?= $data['id_kantor'] ?>" class="text-decoration-none">
                                [<?= escape($data['kode_kantor']) ?>] <?= escape($data['nama_kantor']) ?>
                            </a>
                        </td>
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
                        <td class="text-muted">Status Pegawai</td>
                        <td><?= badge_status($data['status']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Akun Sistem Terkait (Relasi 1-to-1) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lock me-2 text-info"></i>Akun Akses Sistem (1-to-1)</h6>
                <?php if ($data['id_akun']): ?>
                    <a href="../akun_petugas/edit.php?id=<?= $data['id_akun'] ?>" class="btn btn-outline-warning btn-sm py-0 px-2">Edit Akun</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($data['id_akun']): ?>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nama Pengguna</td>
                            <td class="fw-bold text-primary">@<?= escape($data['nama_pengguna']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Peran / Hak Akses</td>
                            <td><span class="badge bg-light text-dark border"><?= escape($data['peran']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Akun</td>
                            <td><?= badge_status($data['status_akun']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terakhir Masuk</td>
                            <td><?= format_tanggal($data['terakhir_masuk'], true) ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted small mb-2">Petugas ini belum memiliki akun akses ke dalam aplikasi.</p>
                        <a href="../akun_petugas/tambah.php?id_petugas=<?= $data['id_petugas'] ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Buat Akun Akses Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data SPT & Permohonan Ditangani -->
    <div class="col-12 col-lg-7">
        <!-- Riwayat Penanganan SPT -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>SPT Yang Diverifikasi</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_spt) ?> SPT</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. SPT</th>
                            <th>Wajib Pajak</th>
                            <th>Total Pajak</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_spt)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada SPT yang diverifikasi petugas ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_spt as $s): ?>
                                <tr>
                                    <td>
                                        <a href="../spt/detail.php?id=<?= $s['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($s['nomor_spt']) ?>
                                        </a>
                                    </td>
                                    <td><?= escape($s['nama_wajib_pajak']) ?></td>
                                    <td><?= format_rupiah($s['total_pajak']) ?></td>
                                    <td><?= badge_status($s['nama_status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Penanganan Permohonan -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-envelope-paper me-2 text-success"></i>Permohonan Yang Diproses</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_mohon) ?> Permohonan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Permohonan</th>
                            <th>Jenis</th>
                            <th>Pemohon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_mohon)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada permohonan yang ditangani.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_mohon as $m): ?>
                                <tr>
                                    <td>
                                        <a href="../permohonan_pajak/detail.php?id=<?= $m['id_permohonan'] ?>" class="fw-semibold text-decoration-none text-dark">
                                            <?= escape($m['nomor_permohonan']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= escape($m['nama_permohonan']) ?></small></td>
                                    <td><?= escape($m['nama_wajib_pajak']) ?></td>
                                    <td><?= badge_status($m['status_permohonan']) ?></td>
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
