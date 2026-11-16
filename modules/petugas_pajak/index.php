<?php
$judul_halaman = 'Kelola Petugas Pajak';
require_once __DIR__ . '/../../includes/header.php';

// Parameter Pencarian dan Pagination
$cari = bersihkan($_GET['cari'] ?? '');
$filter_kantor = (int)($_GET['kantor'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (p.nama_petugas LIKE ? OR p.nomor_identitas LIKE ? OR p.jabatan LIKE ? OR p.email LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}
if ($filter_kantor > 0) {
    $where .= " AND p.id_kantor = ?";
    $params[] = $filter_kantor;
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM petugas_pajak p $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT p.*, k.nama_kantor, k.kode_kantor, a.id_akun, a.nama_pengguna, a.peran
    FROM petugas_pajak p
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    LEFT JOIN akun_petugas a ON p.id_petugas = a.id_petugas
    $where
    ORDER BY p.id_petugas ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

// List kantor untuk filter
$daftar_kantor = ambil_semua("SELECT id_kantor, nama_kantor, kode_kantor FROM kantor_pajak ORDER BY nama_kantor ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Petugas Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Petugas Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Petugas Pajak
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <!-- Form Pencarian & Filter -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama, NIP, jabatan, email..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="kantor" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Kantor Pajak --</option>
                    <?php foreach ($daftar_kantor as $k): ?>
                        <option value="<?= $k['id_kantor'] ?>" <?= ($filter_kantor == $k['id_kantor']) ? 'selected' : '' ?>>
                            [<?= escape($k['kode_kantor']) ?>] <?= escape($k['nama_kantor']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari total <?= $total_data ?> data
            </div>
        </form>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIP / Identitas</th>
                        <th>Nama Petugas</th>
                        <th>Kantor Pajak</th>
                        <th>Jabatan</th>
                        <th>Akun Sistem</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data petugas ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['nomor_identitas']) ?></span></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_petugas']) ?></div>
                                    <small class="text-muted"><i class="bi bi-envelope me-1"></i><?= escape($row['email']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-medium text-dark"><?= escape($row['nama_kantor']) ?></div>
                                    <small class="text-muted"><?= escape($row['kode_kantor']) ?></small>
                                </td>
                                <td><?= escape($row['jabatan']) ?></td>
                                <td>
                                    <?php if ($row['id_akun']): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                            <i class="bi bi-person-check me-1"></i><?= escape($row['nama_pengguna']) ?> (<?= escape($row['peran']) ?>)
                                        </span>
                                    <?php else: ?>
                                        <a href="../akun_petugas/tambah.php?id_petugas=<?= $row['id_petugas'] ?>" class="badge bg-secondary text-decoration-none" title="Buat Akun">
                                            <i class="bi bi-plus me-1"></i>Belum Ada Akun
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_petugas'] ?>" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_petugas'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_petugas'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus petugas <strong><?= escape($row['nama_petugas']) ?></strong>?');">
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
