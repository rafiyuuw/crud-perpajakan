<?php
$judul_halaman = 'Detail Metode Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM metode_pembayaran WHERE id_metode = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_bayar = ambil_semua("
    SELECT p.*, t.nomor_tagihan, wp.nama_wajib_pajak
    FROM pembayaran_pajak p
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE p.id_metode = ?
    ORDER BY p.id_pembayaran DESC
", [$id]);
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Metode Pembayaran</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_metode'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Metode
    </a>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 me-2 text-success"></i>Informasi Kanal</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 140px;">Nama Metode</td>
                        <td class="fw-bold text-dark fs-5"><?= escape($data['nama_metode']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deskripsi</td>
                        <td><?= escape($data['deskripsi']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Kanal</td>
                        <td><?= badge_status($data['status']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Transaksi</td>
                        <td><span class="badge bg-primary"><?= count($daftar_bayar) ?> Transaksi</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-cash-stack me-2 text-primary"></i>Transaksi Pembayaran Melalui Kanal Ini</h6>
                <span class="badge bg-light text-secondary border"><?= count($daftar_bayar) ?> Data</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>No. Tagihan</th>
                            <th>Wajib Pajak</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($daftar_bayar)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada transaksi melalui kanal ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftar_bayar as $pb): ?>
                                <tr>
                                    <td>
                                        <a href="../pembayaran_pajak/detail.php?id=<?= $pb['id_pembayaran'] ?>" class="fw-semibold text-decoration-none">
                                            <?= escape($pb['nomor_transaksi']) ?>
                                        </a>
                                    </td>
                                    <td><?= escape($pb['nomor_tagihan']) ?></td>
                                    <td><?= escape($pb['nama_wajib_pajak']) ?></td>
                                    <td class="fw-bold text-success"><?= format_rupiah($pb['jumlah_pembayaran']) ?></td>
                                    <td><?= badge_status($pb['status_pembayaran']) ?></td>
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
