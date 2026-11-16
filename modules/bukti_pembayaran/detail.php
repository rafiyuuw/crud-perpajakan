<?php
$judul_halaman = 'Detail Bukti Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT b.*, p.nomor_transaksi, p.jumlah_pembayaran, p.tanggal_pembayaran, p.status_pembayaran,
           m.nama_metode, t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan,
           s.id_spt, s.nomor_spt, wp.id_wajib_pajak, wp.nama_wajib_pajak, wp.nomor_npwp,
           jp.kode_pajak, jp.nama_pajak
    FROM bukti_pembayaran b
    JOIN pembayaran_pajak p ON b.id_pembayaran = p.id_pembayaran
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE b.id_bukti = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data bukti pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$file_path = __DIR__ . '/../../' . $data['lokasi_file'];
$file_exists = !empty($data['lokasi_file']) && file_exists($file_path) && is_file($file_path);
$ext = strtolower(pathinfo($data['lokasi_file'], PATHINFO_EXTENSION));
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <div>
            <h4 class="fw-bold mb-0 text-dark"><?= escape($data['nomor_bukti']) ?></h4>
            <div class="text-muted small">Bukti Setor Pajak Negara (BPN / NTPN)</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($file_exists): ?>
            <a href="<?= base_url($data['lokasi_file']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-download me-1"></i>Unduh Berkas
            </a>
        <?php endif; ?>
        <a href="edit.php?id=<?= $data['id_bukti'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah
        </a>
        <a href="#" class="btn btn-danger btn-sm" onclick="return konfirmasiHapus('hapus.php?id=<?= $data['id_bukti'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus bukti <strong><?= escape($data['nomor_bukti']) ?></strong>?');">
            <i class="bi bi-trash me-1"></i>Hapus
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Rincian Bukti & Transaksi -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Informasi Bukti Setor</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 170px;">Nomor Bukti (NTPN)</td>
                        <td class="fw-bold text-success font-monospace fs-5">
                            <i class="bi bi-patch-check-fill me-1"></i><?= escape($data['nomor_bukti']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Unggah</td>
                        <td><?= format_tanggal($data['tanggal_upload'], true) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Label File</td>
                        <td class="fw-semibold text-dark"><?= escape($data['nama_file']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Lokasi Penyimpanan</td>
                        <td><code class="small text-muted"><?= escape($data['lokasi_file']) ?></code></td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-2 text-muted"></td></tr>
                    <tr>
                        <td class="text-muted">Nomor Transaksi (FK)</td>
                        <td>
                            <a href="../pembayaran_pajak/detail.php?id=<?= $data['id_pembayaran'] ?>" class="fw-bold font-monospace text-decoration-none">
                                <?= escape($data['nomor_transaksi']) ?> <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nominal Pembayaran</td>
                        <td class="fw-bold text-primary fs-5"><?= format_rupiah($data['jumlah_pembayaran']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode Penyetoran</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['nama_metode']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Transaksi</td>
                        <td><?= badge_status($data['status_pembayaran']) ?></td>
                    </tr>
                    <tr><td colspan="2"><hr class="my-2 text-muted"></td></tr>
                    <tr>
                        <td class="text-muted">Wajib Pajak</td>
                        <td>
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none fw-semibold">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace"><?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tagihan Pajak Terkait</td>
                        <td>
                            <a href="../tagihan_pajak/detail.php?id=<?= $data['id_tagihan'] ?>" class="text-decoration-none">
                                <?= escape($data['nomor_tagihan']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Laporan SPT</td>
                        <td>
                            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="text-decoration-none">
                                <?= escape($data['nomor_spt']) ?> (<?= escape($data['kode_pajak']) ?>)
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Tampilan Pratinjau File / Dokumen -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-image me-2 text-success"></i>Pratinjau Struk / Bukti Fisik</h6>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center align-items-center p-4">
                <?php if ($file_exists): ?>
                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                        <div class="border rounded p-2 mb-3 bg-light shadow-sm w-100" style="max-height: 400px; overflow: auto;">
                            <img src="<?= base_url($data['lokasi_file']) ?>" alt="Bukti Pembayaran" class="img-fluid rounded">
                        </div>
                    <?php elseif ($ext === 'pdf'): ?>
                        <div class="py-4">
                            <i class="bi bi-file-earmark-pdf-fill text-danger display-1 mb-3"></i>
                            <h6 class="fw-bold text-dark">Dokumen Bukti Berformat PDF</h6>
                            <p class="text-muted small">Klik tombol di bawah untuk membaca atau mencetak berkas PDF.</p>
                            <a href="<?= base_url($data['lokasi_file']) ?>" target="_blank" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Buka PDF di Tab Baru
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="py-4">
                            <i class="bi bi-file-earmark-text text-secondary display-1 mb-3"></i>
                            <p class="text-muted">Format file tidak mendukung pratinjau langsung.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="py-5 text-muted">
                        <i class="bi bi-file-earmark-x display-3 text-secondary mb-3"></i>
                        <h6>Berkas Fisik Tidak Ditemukan di Server</h6>
                        <p class="small mb-0">Lokasi terdaftar: <code><?= escape($data['lokasi_file']) ?></code></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
