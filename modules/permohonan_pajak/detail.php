<?php
$judul_halaman = 'Detail Permohonan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT pp.*, wp.nama_wajib_pajak, wp.nomor_npwp, wp.status_wajib_pajak,
           jp.nama_permohonan, jp.deskripsi as deskripsi_layanan,
           pt.nama_petugas, pt.jabatan, pt.email as email_petugas
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    LEFT JOIN petugas_pajak pt ON pp.id_petugas = pt.id_petugas
    WHERE pp.id_permohonan = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Ambil riwayat perubahan status permohonan (One-to-Many)
$daftar_riwayat = ambil_semua("
    SELECT rp.*, pt.nama_petugas
    FROM riwayat_permohonan rp
    LEFT JOIN petugas_pajak pt ON rp.id_petugas = pt.id_petugas
    WHERE rp.id_permohonan = ?
    ORDER BY rp.id_riwayat_permohonan DESC
", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nomor_permohonan']) ?></h4>
            <div class="text-muted small">Pemohon: <?= escape($data['nama_wajib_pajak']) ?> &bull; <?= escape($data['nama_permohonan']) ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="../riwayat_permohonan/tambah.php?id_permohonan=<?= $data['id_permohonan'] ?>" class="btn btn-success btn-sm">
            <i class="bi bi-arrow-repeat me-1"></i>Catat Update Status
        </a>
        <a href="edit.php?id=<?= $data['id_permohonan'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah
        </a>
        <a href="#" class="btn btn-danger btn-sm" onclick="return konfirmasiHapus('hapus.php?id=<?= $data['id_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus berkas permohonan <strong><?= escape($data['nomor_permohonan']) ?></strong>?');">
            <i class="bi bi-trash me-1"></i>Hapus
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Informasi Permohonan & Wajib Pajak -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Informasi Permohonan</h6>
                <div><?= badge_status($data['status_permohonan']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Nomor Permohonan</td>
                        <td class="fw-bold font-monospace fs-5 text-dark"><?= escape($data['nomor_permohonan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Layanan Permohonan</td>
                        <td>
                            <a href="../jenis_permohonan/detail.php?id=<?= $data['id_jenis_permohonan'] ?>" class="fw-bold text-decoration-none">
                                <?= escape($data['nama_permohonan']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Pengajuan</td>
                        <td><?= format_tanggal($data['tanggal_pengajuan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Pemeriksa</td>
                        <td>
                            <?php if (!empty($data['nama_petugas'])): ?>
                                <span class="fw-semibold text-dark"><?= escape($data['nama_petugas']) ?></span>
                                <div class="small text-muted"><?= escape($data['jabatan']) ?></div>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Belum ditugaskan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Saat Ini</td>
                        <td><?= badge_status($data['status_permohonan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted align-top">Keterangan / Alasan</td>
                        <td class="text-secondary"><?= nl2br(escape($data['keterangan'] ?? '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Data Wajib Pajak Pemohon -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-vcard me-2 text-primary"></i>Profil Wajib Pajak Pemohon</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 150px;">Nama Wajib Pajak</td>
                        <td class="fw-bold text-dark fs-5">
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor NPWP</td>
                        <td class="font-monospace fw-semibold text-primary"><?= escape($data['nomor_npwp']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Wajib Pajak</td>
                        <td><?= badge_status($data['status_wajib_pajak']) ?></td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-2 text-muted"></td></tr>
                    <tr>
                        <td class="text-muted align-top">Keterangan Layanan</td>
                        <td class="small text-muted"><?= nl2br(escape($data['deskripsi_layanan'] ?? '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Riwayat Perubahan Status Permohonan (One-to-Many) -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Perjalanan &amp; Disposisi Permohonan</h6>
        <a href="../riwayat_permohonan/tambah.php?id_permohonan=<?= $data['id_permohonan'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Riwayat Manual
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Waktu Perubahan</th>
                        <th>Status Sebelumnya</th>
                        <th>Status Baru</th>
                        <th>Petugas Pelaksana</th>
                        <th>Catatan / Disposisi</th>
                        <th style="width: 100px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_riwayat)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada catatan riwayat permohonan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($daftar_riwayat as $r): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><small class="text-muted fw-semibold"><?= format_tanggal($r['tanggal_perubahan'], true) ?></small></td>
                                <td><span class="badge bg-light text-secondary border"><?= escape($r['status_sebelumnya']) ?></span></td>
                                <td><?= badge_status($r['status_baru']) ?></td>
                                <td><small class="text-muted"><?= escape($r['nama_petugas'] ?? 'Sistem / Otomatis') ?></small></td>
                                <td><span class="small text-dark"><?= escape($r['catatan'] ?? '-') ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="../riwayat_permohonan/detail.php?id=<?= $r['id_riwayat_permohonan'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="../riwayat_permohonan/edit.php?id=<?= $r['id_riwayat_permohonan'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
