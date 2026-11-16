<?php
$judul_halaman = 'Kelola Pembayaran Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_metode = (int)($_GET['metode'] ?? 0);
$filter_status = bersihkan($_GET['status'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (p.nomor_transaksi LIKE ? OR t.nomor_tagihan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_metode > 0) {
    $where .= " AND p.id_metode = ?";
    $params[] = $filter_metode;
}
if (!empty($filter_status)) {
    $where .= " AND p.status_pembayaran = ?";
    $params[] = $filter_status;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM pembayaran_pajak p 
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan 
    JOIN spt s ON t.id_spt = s.id_spt 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT p.*, m.nama_metode, t.nomor_tagihan, t.jumlah_tagihan, wp.nama_wajib_pajak, wp.nomor_npwp, b.id_bukti, b.nomor_bukti
    FROM pembayaran_pajak p
    JOIN metode_pembayaran m ON p.id_metode = m.id_metode
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN bukti_pembayaran b ON p.id_pembayaran = b.id_pembayaran
    $where
    ORDER BY p.id_pembayaran DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
$daftar_metode = ambil_semua("SELECT id_metode, nama_metode FROM metode_pembayaran ORDER BY nama_metode ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembayaran Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Transaksi Pembayaran Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-cash me-1"></i>Input Pembayaran Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <input type="text" name="cari" class="form-control" placeholder="Cari No Transaksi, Tagihan, nama WP..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="metode" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Metode Bayar --</option>
                    <?php foreach ($daftar_metode as $mt): ?>
                        <option value="<?= $mt['id_metode'] ?>" <?= ($filter_metode == $mt['id_metode']) ? 'selected' : '' ?>>
                            <?= escape($mt['nama_metode']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="Berhasil" <?= ($filter_status === 'Berhasil') ? 'selected' : '' ?>>Berhasil</option>
                    <option value="Menunggu Konfirmasi" <?= ($filter_status === 'Menunggu Konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                    <option value="Gagal" <?= ($filter_status === 'Gagal') ? 'selected' : '' ?>>Gagal</option>
                    <option value="Dibatalkan" <?= ($filter_status === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <?php if (!empty($cari) || $filter_metode > 0 || !empty($filter_status)): ?>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Transaksi</th>
                        <th>Nomor Tagihan (FK)</th>
                        <th>Wajib Pajak</th>
                        <th>Metode Bayar</th>
                        <th>Tanggal Bayar</th>
                        <th>Jumlah Bayar</th>
                        <th>Bukti Setor</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data transaksi pembayaran.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id_pembayaran'] ?>" class="fw-semibold text-decoration-none text-dark">
                                        <?= escape($row['nomor_transaksi']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="../tagihan_pajak/detail.php?id=<?= $row['id_tagihan'] ?>" class="text-decoration-none text-danger font-monospace small">
                                        <?= escape($row['nomor_tagihan']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?= escape($row['nama_metode']) ?></span></td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_pembayaran'], true) ?></small></td>
                                <td><span class="fw-bold text-success"><?= format_rupiah($row['jumlah_pembayaran']) ?></span></td>
                                <td>
                                    <?php if ($row['id_bukti']): ?>
                                        <a href="../bukti_pembayaran/detail.php?id=<?= $row['id_bukti'] ?>" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none">
                                            <i class="bi bi-check2-circle me-1"></i><?= escape($row['nomor_bukti']) ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="../bukti_pembayaran/tambah.php?id_pembayaran=<?= $row['id_pembayaran'] ?>" class="badge bg-light text-muted border text-decoration-none">
                                            <i class="bi bi-upload me-1"></i>Upload Bukti
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= badge_status($row['status_pembayaran']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_pembayaran'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_pembayaran'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_pembayaran'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus transaksi <strong><?= escape($row['nomor_transaksi']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&metode=<?= $filter_metode ?>&status=<?= $filter_status ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&metode=<?= $filter_metode ?>&status=<?= $filter_status ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&metode=<?= $filter_metode ?>&status=<?= $filter_status ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
