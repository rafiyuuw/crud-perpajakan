<?php
$judul_halaman = 'Kelola Bukti Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_bayar = (int)($_GET['pembayaran'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (b.nomor_bukti LIKE ? OR b.nama_file LIKE ? OR p.nomor_transaksi LIKE ? OR wp.nama_wajib_pajak LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_bayar > 0) {
    $where .= " AND b.id_pembayaran = ?";
    $params[] = $filter_bayar;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM bukti_pembayaran b 
    JOIN pembayaran_pajak p ON b.id_pembayaran = p.id_pembayaran 
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan 
    JOIN spt s ON t.id_spt = s.id_spt 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT b.*, p.nomor_transaksi, p.jumlah_pembayaran, p.tanggal_pembayaran,
           t.nomor_tagihan, wp.nama_wajib_pajak, wp.nomor_npwp, m.nama_metode
    FROM bukti_pembayaran b
    JOIN pembayaran_pajak p ON b.id_pembayaran = p.id_pembayaran
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    $where
    ORDER BY b.id_bukti DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bukti Pembayaran</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Bukti Pembayaran Pajak (BPN / NTPN)</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_bayar ? '?id_pembayaran=' . $filter_bayar : '' ?>" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up me-1"></i>Unggah Bukti Bayar
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari No Bukti, Transaksi, nama WP..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (!empty($cari) || $filter_bayar > 0): ?>
                        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-7 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> data bukti
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Bukti (NTPN)</th>
                        <th>Nomor Transaksi (FK)</th>
                        <th>Wajib Pajak</th>
                        <th>Nominal Disetor</th>
                        <th>Nama File Struk</th>
                        <th>Waktu Unggah</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada bukti pembayaran ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold text-success font-monospace"><i class="bi bi-patch-check-fill me-1"></i><?= escape($row['nomor_bukti']) ?></span>
                                </td>
                                <td>
                                    <a href="../pembayaran_pajak/detail.php?id=<?= $row['id_pembayaran'] ?>" class="text-decoration-none">
                                        <?= escape($row['nomor_transaksi']) ?>
                                    </a>
                                    <div><small class="text-muted"><?= escape($row['nama_metode']) ?></small></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td><span class="fw-bold text-dark"><?= format_rupiah($row['jumlah_pembayaran']) ?></span></td>
                                <td>
                                    <small class="text-muted"><i class="bi bi-file-earmark-image me-1"></i><?= escape($row['nama_file']) ?></small>
                                </td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_upload'], true) ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_bukti'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_bukti'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_bukti'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus bukti <strong><?= escape($row['nomor_bukti']) ?></strong>?');"><i class="bi bi-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_halaman > 1): ?>
            <nav class="mt-4">
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&pembayaran=<?= $filter_bayar ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&pembayaran=<?= $filter_bayar ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&pembayaran=<?= $filter_bayar ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
