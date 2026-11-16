<?php
$judul_halaman = 'Detail Pembayaran Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT p.*, m.nama_metode, m.deskripsi as deskripsi_metode, 
           t.nomor_tagihan, t.jumlah_tagihan, t.status_tagihan, t.tanggal_jatuh_tempo,
           s.nomor_spt, wp.id_wajib_pajak, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak
    FROM pembayaran_pajak p
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE p.id_pembayaran = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// Ambil bukti pembayaran terkait (One-to-Many atau 1-to-1)
$daftar_bukti = ambil_semua("SELECT * FROM bukti_pembayaran WHERE id_pembayaran = ? ORDER BY id_bukti DESC", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nomor_transaksi']) ?></h4>
            <div class="text-muted small">Pembayar: <?= escape($data['nama_wajib_pajak']) ?> (<?= escape($data['nomor_npwp']) ?>)</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="../bukti_pembayaran/tambah.php?id_pembayaran=<?= $data['id_pembayaran'] ?>" class="btn btn-success btn-sm">
            <i class="bi bi-cloud-arrow-up me-1"></i>Unggah Bukti Setor
        </a>
        <a href="edit.php?id=<?= $data['id_pembayaran'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Transaksi
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Rincian Transaksi -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack me-2 text-success"></i>Kwitansi &amp; Data Transaksi</h6>
                <div><?= badge_status($data['status_pembayaran']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Nomor Transaksi</td>
                        <td class="fw-bold font-monospace text-dark fs-5"><?= escape($data['nomor_transaksi']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Tagihan (STP)</td>
                        <td>
                            <a href="../tagihan_pajak/detail.php?id=<?= $data['id_tagihan'] ?>" class="fw-semibold text-danger text-decoration-none font-monospace">
                                <?= escape($data['nomor_tagihan']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pelaporan SPT</td>
                        <td>
                            <span class="badge bg-light text-dark border">[<?= escape($data['kode_pajak']) ?>]</span>
                            <?= escape($data['nomor_spt']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak</td>
                        <td>
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode Pembayaran</td>
                        <td>
                            <span class="badge bg-light text-secondary border me-1"><i class="bi bi-wallet2 me-1"></i><?= escape($data['nama_metode']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Penyetoran</td>
                        <td><?= format_tanggal($data['tanggal_pembayaran'], true) ?></td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-muted fw-bold">Nominal Disetor</td>
                        <td class="fw-bold text-success fs-4"><?= format_rupiah($data['jumlah_pembayaran']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Bukti Pembayaran (Bukti Setor Bank / Pos) -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-check me-2 text-primary"></i>Bukti Pembayaran / NTPN (bukti_pembayaran)</h6>
                <a href="../bukti_pembayaran/tambah.php?id_pembayaran=<?= $data['id_pembayaran'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Unggah Bukti
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($daftar_bukti)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                        Belum ada berkas bukti setoran yang diunggah untuk transaksi ini.
                        <div class="mt-2">
                            <a href="../bukti_pembayaran/tambah.php?id_pembayaran=<?= $data['id_pembayaran'] ?>" class="btn btn-sm btn-outline-primary">
                                Unggah Sekarang
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($daftar_bukti as $bk): ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark font-monospace"><i class="bi bi-patch-check-fill text-success me-1"></i><?= escape($bk['nomor_bukti']) ?></div>
                                    <small class="text-muted"><?= escape($bk['nama_file']) ?> &bull; <?= format_tanggal($bk['tanggal_upload'], true) ?></small>
                                </div>
                                <div>
                                    <a href="../bukti_pembayaran/detail.php?id=<?= $bk['id_bukti'] ?>" class="btn btn-sm btn-outline-info py-0 px-2">Lihat</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
