<?php
$judul_halaman = 'Kelola Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = '';
$params = [];
if (!empty($cari)) {
    $where = "WHERE nama_status LIKE ? OR keterangan LIKE ?";
    $params = ["%$cari%", "%$cari%"];
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM status_spt $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT st.*, COUNT(s.id_spt) as total_spt
    FROM status_spt st
    LEFT JOIN spt s ON st.id_status_spt = s.id_status_spt
    $where
    GROUP BY st.id_status_spt
    ORDER BY st.id_status_spt ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Status SPT</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Status Pelaporan SPT</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Status SPT
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari status atau keterangan..." value="<?= escape($cari) ?>">
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
                        <th>Badge Tampilan</th>
                        <th>Nama Status SPT</th>
                        <th>Keterangan / Definisi Alur</th>
                        <th>Jumlah SPT Terkait</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data status SPT.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= badge_status($row['nama_status']) ?></td>
                                <td><div class="fw-bold text-dark"><?= escape($row['nama_status']) ?></div></td>
                                <td><?= escape($row['keterangan']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= (int)$row['total_spt'] ?> Laporan
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_status_spt'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_status_spt'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_status_spt'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus status <strong><?= escape($row['nama_status']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
