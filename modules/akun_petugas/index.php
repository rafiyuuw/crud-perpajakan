<?php
$judul_halaman = 'Kelola Akun Petugas';
require_once __DIR__ . '/../../includes/header.php';

// Parameter Pencarian dan Pagination
$cari = bersihkan($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (a.nama_pengguna LIKE ? OR p.nama_petugas LIKE ? OR a.peran LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM akun_petugas a 
    JOIN petugas_pajak p ON a.id_petugas = p.id_petugas 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT a.*, p.nama_petugas, p.nomor_identitas, p.jabatan, k.nama_kantor
    FROM akun_petugas a
    JOIN petugas_pajak p ON a.id_petugas = p.id_petugas
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    $where
    ORDER BY a.id_akun ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Akun Petugas</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Akun Petugas Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Buat Akun Petugas
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <!-- Form Pencarian -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama pengguna, nama petugas, peran..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (!empty($cari)): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-circle"></i></a>
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
                        <th style="width: 50px;">No</th>
                        <th>Nama Pengguna</th>
                        <th>Petugas Pajak Terhubung (1-to-1)</th>
                        <th>Kantor</th>
                        <th>Peran (Role)</th>
                        <th>Terakhir Masuk</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada akun petugas ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="fw-bold text-primary">@<?= escape($row['nama_pengguna']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_petugas']) ?></div>
                                    <small class="text-muted">NIP: <?= escape($row['nomor_identitas']) ?></small>
                                </td>
                                <td><small><?= escape($row['nama_kantor']) ?></small></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= escape($row['peran']) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= format_tanggal($row['terakhir_masuk'], true) ?></small>
                                </td>
                                <td><?= badge_status($row['status_akun']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_akun'] ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_akun'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_akun'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus akun <strong>@<?= escape($row['nama_pengguna']) ?></strong>?');">
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
