<?php
$judul_halaman = 'Kelola Metode Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = '';
$params = [];
if (!empty($cari)) {
    $where = "WHERE nama_metode LIKE ? OR deskripsi LIKE ?";
    $params = ["%$cari%", "%$cari%"];
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM metode_pembayaran $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT m.*, COUNT(p.id_pembayaran) as total_transaksi,
           COALESCE(SUM(p.jumlah_pembayaran), 0) as total_penerimaan
    FROM metode_pembayaran m
    LEFT JOIN pembayaran_pajak p ON m.id_metode = p.id_metode AND p.status_pembayaran = 'Berhasil'
    $where
    GROUP BY m.id_metode
    ORDER BY m.id_metode ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Metode Pembayaran</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Metode Pembayaran Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Metode Pembayaran
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama metode pembayaran..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (!empty($cari)): ?>
                        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-7 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> data
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Kanal / Metode Pembayaran</th>
                        <th>Deskripsi &amp; Petunjuk</th>
                        <th>Transaksi Masuk</th>
                        <th>Total Penerimaan</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada metode pembayaran ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i><?= escape($row['nama_metode']) ?></div>
                                </td>
                                <td><small class="text-muted text-truncate d-inline-block" style="max-width: 250px;"><?= escape($row['deskripsi']) ?></small></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= (int)$row['total_transaksi'] ?> Transaksi</span>
                                </td>
                                <td><span class="fw-bold text-success"><?= format_rupiah($row['total_penerimaan']) ?></span></td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_metode'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_metode'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_metode'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus metode <strong><?= escape($row['nama_metode']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
