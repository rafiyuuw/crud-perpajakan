<?php
$judul_halaman = 'Detail Pelaporan SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, jp.nama_pajak,
           st.nama_status, mp.tahun_pajak, mp.tanggal_mulai as masa_mulai, mp.tanggal_berakhir as masa_akhir,
           p.nama_petugas, p.nomor_identitas, p.jabatan
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    JOIN masa_pajak mp ON s.id_masa_pajak = mp.id_masa_pajak
    LEFT JOIN petugas_pajak p ON s.id_petugas = p.id_petugas
    WHERE s.id_spt = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data SPT tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 1. Ambil Rincian Detail SPT (One-to-Many)
$rincian_detail = ambil_semua("
    SELECT d.*, o.nama_objek, o.alamat_objek, t.nama_tarif
    FROM detail_spt d
    JOIN objek_pajak o ON d.id_objek_pajak = o.id_objek_pajak
    JOIN tarif_pajak t ON d.id_tarif = t.id_tarif
    WHERE d.id_spt = ?
    ORDER BY d.id_detail_spt ASC
", [$id]);

// 2. Ambil Riwayat Status SPT (One-to-Many)
$riwayat_status = ambil_semua("
    SELECT r.*, st.nama_status, p.nama_petugas
    FROM riwayat_status_spt r
    JOIN status_spt st ON r.id_status_spt = st.id_status_spt
    LEFT JOIN petugas_pajak p ON r.id_petugas = p.id_petugas
    WHERE r.id_spt = ?
    ORDER BY r.id_riwayat_spt DESC
", [$id]);

// 3. Ambil Tagihan Terkait
$tagihan_list = ambil_semua("SELECT * FROM tagihan_pajak WHERE id_spt = ? ORDER BY id_tagihan DESC", [$id]);

// 4. Ambil Dokumen Lampiran
$lampiran_list = ambil_semua("
    SELECT d.*, jd.nama_dokumen 
    FROM dokumen_perpajakan d 
    JOIN jenis_dokumen jd ON d.id_jenis_dokumen = jd.id_jenis_dokumen 
    WHERE d.id_spt = ? 
    ORDER BY d.id_dokumen DESC
", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nomor_spt']) ?></h4>
            <div class="text-muted small">Pelaporan: <?= escape($data['keterangan'] ?: $data['nama_pajak']) ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_spt'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah SPT
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Header Informasi SPT -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Informasi Pelaporan SPT</h6>
                <div><?= badge_status($data['nama_status']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 150px;">Nomor SPT</td>
                        <td class="fw-bold font-monospace text-primary fs-5"><?= escape($data['nomor_spt']) ?></td>
                    </tr>
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
                        <td>
                            <span class="badge bg-light text-dark border">[<?= escape($data['kode_pajak']) ?>]</span>
                            <?= escape($data['nama_pajak']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Masa Pajak</td>
                        <td>
                            Tahun <?= $data['tahun_pajak'] ?>
                            <div class="small text-muted"><?= format_tanggal($data['masa_mulai']) ?> s.d <?= format_tanggal($data['masa_akhir']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Lapor</td>
                        <td><?= format_tanggal($data['tanggal_pelaporan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Verifikator</td>
                        <td><?= escape($data['nama_petugas'] ? $data['nama_petugas'] . ' (' . $data['jabatan'] . ')' : 'Belum Ditugaskan') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Pajak Terutang</td>
                        <td class="fw-bold text-dark fs-5"><?= format_rupiah($data['total_pajak']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Tagihan Terkait -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-danger"></i>Surat Tagihan Pajak (STP)</h6>
                <a href="../tagihan_pajak/tambah.php?id_spt=<?= $data['id_spt'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.78rem;">+ Tagihan</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tagihan_list)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada surat tagihan dibuat.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tagihan_list as $tg): ?>
                                <tr>
                                    <td>
                                        <a href="../tagihan_pajak/detail.php?id=<?= $tg['id_tagihan'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($tg['nomor_tagihan']) ?>
                                        </a>
                                    </td>
                                    <td><small><?= format_tanggal($tg['tanggal_jatuh_tempo']) ?></small></td>
                                    <td class="fw-bold"><?= format_rupiah($tg['jumlah_tagihan']) ?></td>
                                    <td><?= badge_status($tg['status_tagihan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dokumen Lampiran -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-folder-check me-2 text-success"></i>Lampiran Dokumen SPT</h6>
                <a href="../dokumen_perpajakan/tambah.php?id_wajib_pajak=<?= $data['id_wajib_pajak'] ?>&id_spt=<?= $data['id_spt'] ?>" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 0.78rem;">+ Lampiran</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <tbody>
                        <?php if (empty($lampiran_list)): ?>
                            <tr><td class="text-center py-3 text-muted">Belum ada lampiran diunggah.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lampiran_list as $lm): ?>
                                <tr>
                                    <td>
                                        <a href="../dokumen_perpajakan/detail.php?id=<?= $lm['id_dokumen'] ?>" class="text-decoration-none fw-semibold">
                                            <i class="bi bi-paperclip me-1 text-danger"></i><?= escape($lm['nama_file']) ?>
                                        </a>
                                        <div class="small text-muted"><?= escape($lm['nama_dokumen']) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rincian Detail SPT & Riwayat Status -->
    <div class="col-12 col-lg-7">
        <!-- 1. Detail Rincian SPT (Tabel detail_spt) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Rincian Penghitungan Pajak (detail_spt)</h6>
                <a href="../detail_spt/tambah.php?id_spt=<?= $data['id_spt'] ?>" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Rincian Objek
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Objek Pajak</th>
                            <th>Dasar Pengenaan (Rp)</th>
                            <th>Tarif Terkunci</th>
                            <th>Pajak Terutang (Rp)</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rincian_detail)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada rincian objek pajak ditambahkan ke formulir SPT ini.</td></tr>
                        <?php else: ?>
                            <?php $total_kalkulasi = 0; foreach ($rincian_detail as $rd): $total_kalkulasi += $rd['jumlah_pajak']; ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= escape($rd['nama_objek']) ?></div>
                                        <small class="text-muted"><?= escape($rd['nama_tarif']) ?></small>
                                    </td>
                                    <td><?= format_rupiah($rd['dasar_pengenaan']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= (float)$rd['tarif_digunakan'] ?>%</span></td>
                                    <td class="fw-bold text-dark"><?= format_rupiah($rd['jumlah_pajak']) ?></td>
                                    <td class="text-center">
                                        <a href="../detail_spt/edit.php?id=<?= $rd['id_detail_spt'] ?>" class="btn btn-sm btn-outline-warning py-0 px-1" title="Ubah"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger py-0 px-1" title="Hapus" onclick="return konfirmasiHapus('../detail_spt/hapus.php?id=<?= $rd['id_detail_spt'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus rincian objek ini dari SPT?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-light fw-bold">
                                <td colspan="3" class="text-end">Total Perhitungan Rincian:</td>
                                <td class="text-primary fs-6"><?= format_rupiah($total_kalkulasi) ?></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Riwayat Perubahan Status (riwayat_status_spt) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-info"></i>Riwayat Perubahan Status SPT</h6>
                <a href="../riwayat_status_spt/tambah.php?id_spt=<?= $data['id_spt'] ?>" class="btn btn-outline-info btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus me-1"></i>Catat Status Baru
                </a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($riwayat_status)): ?>
                    <p class="text-center text-muted py-3 mb-0">Belum ada riwayat perubahan status.</p>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($riwayat_status as $rs): ?>
                            <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                <span class="position-absolute top-0 start-0 translate-middle p-1 bg-primary rounded-circle"></span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div><?= badge_status($rs['nama_status']) ?></div>
                                    <small class="text-muted"><i class="bi bi-clock me-1"></i><?= format_tanggal($rs['tanggal_perubahan'], true) ?></small>
                                </div>
                                <div class="small mt-1 text-dark"><?= escape($rs['catatan']) ?></div>
                                <small class="text-muted d-block mt-1">Petugas: <strong><?= escape($rs['nama_petugas'] ?: 'Sistem Perpajakan') ?></strong></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
