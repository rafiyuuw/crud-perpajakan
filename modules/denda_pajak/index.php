<?php
$judul_halaman = 'Kelola Sanksi & Denda Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$filter_tagihan = (int)($_GET['tagihan'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (d.alasan_denda LIKE ? OR d.dasar_perhitungan LIKE ? OR t.nomor_tagihan LIKE ? OR wp.nama_wajib_pajak LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if (!empty($filter_status)) {
    $where .= " AND d.status_denda = ?";
    $params[] = $filter_status;
}
if ($filter_tagihan > 0) {
    $where .= " AND d.id_tagihan = ?";
    $params[] = $filter_tagihan;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM denda_pajak d
    JOIN tagihan_pajak t ON d.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT d.*, t.nomor_tagihan, t.jumlah_tagihan, t.tanggal_jatuh_tempo,
           wp.id_wajib_pajak, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak
    FROM denda_pajak d
    JOIN tagihan_pajak t ON d.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    $where
    ORDER BY d.id_denda DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Denda Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Sanksi Administrasi &amp; Denda Pajak</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_tagihan ? '?id_tagihan=' . $filter_tagihan : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Denda Pajak
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <input type="text" name="cari" class="form-control" placeholder="Cari alasan, nomor tagihan, wajib pajak..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" <?= ($filter_status === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Lunas" <?= ($filter_status === 'Lunas') ? 'selected' : '' ?>>Lunas</option>
                    <option value="Dibatalkan" <?= ($filter_status === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
            <div class="col-12 col-md-3 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> data denda
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tagihan &amp; Wajib Pajak</th>
                        <th>Alasan Pengenaan</th>
                        <th>Dasar Perhitungan</th>
                        <th>Jumlah Denda</th>
                        <th>Tgl Pengenaan</th>
                        <th>Status</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data denda pajak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="../tagihan_pajak/detail.php?id=<?= $row['id_tagihan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                        <?= escape($row['nomor_tagihan']) ?>
                                    </a>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted"><?= escape($row['kode_pajak']) ?></small>
                                </td>
                                <td>
                                    <span class="text-dark fw-medium"><?= escape($row['alasan_denda']) ?></span>
                                </td>
                                <td><small class="text-muted"><?= escape($row['dasar_perhitungan']) ?></small></td>
                                <td><span class="fw-bold text-danger"><?= format_rupiah($row['jumlah_denda']) ?></span></td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_pengenaan']) ?></small></td>
                                <td><?= badge_status($row['status_denda']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_denda'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_denda'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_denda'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus denda tagihan <strong><?= escape($row['nomor_tagihan']) ?></strong> sebesar <?= format_rupiah($row['jumlah_denda']) ?>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&tagihan=<?= $filter_tagihan ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&tagihan=<?= $filter_tagihan ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&tagihan=<?= $filter_tagihan ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
