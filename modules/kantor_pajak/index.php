<?php
$judul_halaman = 'Kelola Kantor Pajak';
require_once __DIR__ . '/../../includes/header.php';

// Parameter Pencarian dan Pagination
$cari = bersihkan($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = '';
$params = [];
if (!empty($cari)) {
    $where = "WHERE kode_kantor LIKE ? OR nama_kantor LIKE ? OR kota LIKE ? OR email LIKE ?";
    $params = ["%$cari%", "%$cari%", "%$cari%", "%$cari%"];
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM kantor_pajak $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "SELECT * FROM kantor_pajak $where ORDER BY id_kantor ASC LIMIT $limit OFFSET $offset";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kantor Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Kantor Pelayanan Pajak (KPP)</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Kantor Pajak
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <!-- Form Pencarian -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari kode, nama kantor, kota, email..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (!empty($cari)): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-7 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari total <?= $total_data ?> data
            </div>
        </form>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Kode</th>
                        <th>Nama Kantor Pajak</th>
                        <th>Kota</th>
                        <th>Nomor Telepon</th>
                        <th>Email Resmi</th>
                        <th style="width: 160px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data kantor pajak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['kode_kantor']) ?></span></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_kantor']) ?></div>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;"><?= escape($row['alamat']) ?></small>
                                </td>
                                <td><?= escape($row['kota']) ?></td>
                                <td><i class="bi bi-telephone me-1 text-muted"></i><?= escape($row['nomor_telepon']) ?></td>
                                <td><i class="bi bi-envelope me-1 text-muted"></i><?= escape($row['email']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_kantor'] ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_kantor'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_kantor'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus kantor pajak <strong><?= escape($row['nama_kantor']) ?></strong>?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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
