<?php
$judul_halaman = 'Kelola Jenis Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (nama_permohonan LIKE ? OR deskripsi LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%"]);
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM jenis_permohonan $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT jp.*, 
           (SELECT COUNT(*) FROM permohonan_pajak pp WHERE pp.id_jenis_permohonan = jp.id_jenis_permohonan) as total_permohonan
    FROM jenis_permohonan jp
    $where
    ORDER BY jp.id_jenis_permohonan ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Jenis Permohonan</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Jenis Layanan &amp; Permohonan Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Jenis Permohonan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <input type="text" name="cari" class="form-control" placeholder="Cari nama permohonan, deskripsi..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Aktif" <?= ($filter_status === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                    <option value="Nonaktif" <?= ($filter_status === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
            <div class="col-12 col-md-2 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> data
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Layanan Permohonan</th>
                        <th>Deskripsi Layanan</th>
                        <th class="text-center">Jumlah Pengajuan</th>
                        <th>Status</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data jenis permohonan ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= escape($row['nama_permohonan']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= escape($row['deskripsi']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-primary border"><?= $row['total_permohonan'] ?> berkas</span>
                                </td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_jenis_permohonan'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_jenis_permohonan'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_jenis_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus jenis permohonan <strong><?= escape($row['nama_permohonan']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
