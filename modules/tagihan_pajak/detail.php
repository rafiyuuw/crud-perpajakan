<?php
$judul_halaman = 'Detail Tagihan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT t.*, s.nomor_spt, s.tanggal_pelaporan, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, jp.nama_pajak
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE t.id_tagihan = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data tagihan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

// 1. Daftar Pembayaran atas tagihan ini
$daftar_bayar = ambil_semua("
    SELECT p.*, m.nama_metode, b.nomor_bukti
    FROM pembayaran_pajak p
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    LEFT JOIN bukti_pembayaran b ON p.id_pembayaran = b.id_pembayaran
    WHERE p.id_tagihan = ?
    ORDER BY p.id_pembayaran DESC
", [$id]);

$total_dibayar = 0;
foreach ($daftar_bayar as $byr) {
    if ($byr['status_pembayaran'] === 'Berhasil') {
        $total_dibayar += $byr['jumlah_pembayaran'];
    }
}
$sisa_tagihan = max(0, $data['jumlah_tagihan'] - $total_dibayar);

// 2. Daftar Denda atas tagihan ini
$daftar_denda = ambil_semua("SELECT * FROM denda_pajak WHERE id_tagihan = ? ORDER BY id_denda DESC", [$id]);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nomor_tagihan']) ?></h4>
            <div class="text-muted small">Wajib Pajak: <?= escape($data['nama_wajib_pajak']) ?> (<?= escape($data['nomor_npwp']) ?>)</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($sisa_tagihan > 0): ?>
            <a href="../pembayaran_pajak/tambah.php?id_tagihan=<?= $data['id_tagihan'] ?>" class="btn btn-success btn-sm">
                <i class="bi bi-cash me-1"></i>Bayar Tagihan Ini
            </a>
        <?php endif; ?>
        <a href="edit.php?id=<?= $data['id_tagihan'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Tagihan
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Informasi Tagihan -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-2 text-danger"></i>Rincian Surat Tagihan</h6>
                <div><?= badge_status($data['status_tagihan']) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 150px;">Nomor Tagihan</td>
                        <td class="fw-bold font-monospace text-danger fs-5"><?= escape($data['nomor_tagihan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor SPT (Induk)</td>
                        <td>
                            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                <?= escape($data['nomor_spt']) ?>
                            </a>
                            <span class="badge bg-light text-dark border ms-1"><?= escape($data['kode_pajak']) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak</td>
                        <td><?= escape($data['nama_wajib_pajak']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Tagihan</td>
                        <td><?= format_tanggal($data['tanggal_tagihan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jatuh Tempo</td>
                        <td class="text-danger fw-bold"><i class="bi bi-calendar-event me-1"></i><?= format_tanggal($data['tanggal_jatuh_tempo']) ?></td>
                    </tr>
                    <tr class="table-light">
                        <td class="text-muted fw-bold">Total Tagihan Pokok</td>
                        <td class="fw-bold text-dark fs-5"><?= format_rupiah($data['jumlah_tagihan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Realisasi Pembayaran</td>
                        <td class="fw-bold text-success fs-5"><?= format_rupiah($total_dibayar) ?></td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted fw-bold">Sisa Kewajiban</td>
                        <td class="fw-bold <?= $sisa_tagihan > 0 ? 'text-danger' : 'text-success' ?> fs-5">
                            <?= format_rupiah($sisa_tagihan) ?>
                            <?php if ($sisa_tagihan == 0): ?>
                                <span class="badge bg-success ms-1"><i class="bi bi-check-all me-1"></i>Lunas</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Sanksi Administrasi / Denda Terkait -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Sanksi &amp; Denda Pajak</h6>
                <a href="../denda_pajak/tambah.php?id_tagihan=<?= $data['id_tagihan'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.78rem;">+ Denda</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Alasan Denda</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_denda)): ?>
                            <tr><td colspan="3" class="text-center py-3 text-muted">Tidak ada denda yang dikenakan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_denda as $dn): ?>
                                <tr>
                                    <td>
                                        <a href="../denda_pajak/detail.php?id=<?= $dn['id_denda'] ?>" class="text-decoration-none fw-medium text-dark">
                                            <?= escape($dn['alasan_denda']) ?>
                                        </a>
                                        <div class="small text-muted"><?= format_tanggal($dn['tanggal_pengenaan']) ?></div>
                                    </td>
                                    <td class="fw-bold text-danger"><?= format_rupiah($dn['jumlah_denda']) ?></td>
                                    <td><?= badge_status($dn['status_denda']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Pembayaran Masuk -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack me-2 text-success"></i>Riwayat Pembayaran (One-to-Many)</h6>
                <a href="../pembayaran_pajak/tambah.php?id_tagihan=<?= $data['id_tagihan'] ?>" class="btn btn-success btn-sm py-0 px-2" style="font-size: 0.78rem;">
                    <i class="bi bi-plus-circle me-1"></i>Input Setoran
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Metode Bayar</th>
                            <th>Waktu Bayar</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_bayar)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada pembayaran dicatat untuk tagihan ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_bayar as $pb): ?>
                                <tr>
                                    <td>
                                        <a href="../pembayaran_pajak/detail.php?id=<?= $pb['id_pembayaran'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($pb['nomor_transaksi']) ?>
                                        </a>
                                        <?php if ($pb['nomor_bukti']): ?>
                                            <div><span class="badge bg-light text-muted border font-monospace" style="font-size: 0.72rem;">Bukti: <?= escape($pb['nomor_bukti']) ?></span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= escape($pb['nama_metode']) ?></small></td>
                                    <td><small><?= format_tanggal($pb['tanggal_pembayaran'], true) ?></small></td>
                                    <td><span class="fw-bold text-success"><?= format_rupiah($pb['jumlah_pembayaran']) ?></span></td>
                                    <td><?= badge_status($pb['status_pembayaran']) ?></td>
                                    <td class="text-center">
                                        <a href="../pembayaran_pajak/detail.php?id=<?= $pb['id_pembayaran'] ?>" class="btn btn-sm btn-outline-info py-0 px-1"><i class="bi bi-eye"></i></a>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
