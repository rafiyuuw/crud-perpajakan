<?php
$judul_halaman = 'Kelola Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

// Parameter Pencarian dan Pagination
$cari = bersihkan($_GET['cari'] ?? '');
$filter_jenis = (int)($_GET['jenis'] ?? 0);
$filter_kantor = (int)($_GET['kantor'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (w.nomor_npwp LIKE ? OR w.nama_wajib_pajak LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}
if ($filter_jenis > 0) {
    $where .= " AND w.id_jenis_wajib_pajak = ?";
    $params[] = $filter_jenis;
}
if ($filter_kantor > 0) {
    $where .= " AND w.id_kantor = ?";
    $params[] = $filter_kantor;
}

$total_data = ambil_satu("SELECT COUNT(*) as total FROM wajib_pajak w $where", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT w.*, j.nama_jenis, k.nama_kantor, k.kode_kantor
    FROM wajib_pajak w
    JOIN jenis_wajib_pajak j ON w.id_jenis_wajib_pajak = j.id_jenis_wajib_pajak
    JOIN kantor_pajak k ON w.id_kantor = k.id_kantor
    $where
    ORDER BY w.id_wajib_pajak ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_jenis = ambil_semua("SELECT id_jenis_wajib_pajak, nama_jenis FROM jenis_wajib_pajak ORDER BY nama_jenis ASC");
$daftar_kantor = ambil_semua("SELECT id_kantor, nama_kantor, kode_kantor FROM kantor_pajak ORDER BY nama_kantor ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Wajib Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Wajib Pajak (WP)</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>Pendaftaran WP Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <!-- Form Pencarian & Filter -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nomor NPWP atau nama..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Jenis WP --</option>
                    <?php foreach ($daftar_jenis as $j): ?>
                        <option value="<?= $j['id_jenis_wajib_pajak'] ?>" <?= ($filter_jenis == $j['id_jenis_wajib_pajak']) ? 'selected' : '' ?>>
                            <?= escape($j['nama_jenis']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="kantor" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua KPP --</option>
                    <?php foreach ($daftar_kantor as $k): ?>
                        <option value="<?= $k['id_kantor'] ?>" <?= ($filter_kantor == $k['id_kantor']) ? 'selected' : '' ?>>
                            [<?= escape($k['kode_kantor']) ?>] <?= escape($k['nama_kantor']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                <?= count($data_list) ?> / <?= $total_data ?> data
            </div>
        </form>

        <!-- Tabel Data -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor NPWP</th>
                        <th>Nama Wajib Pajak</th>
                        <th>Jenis WP</th>
                        <th>KPP Terdaftar</th>
                        <th>Tgl Terdaftar</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada data wajib pajak ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace"><?= escape($row['nomor_npwp']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <?php if ($row['jenis_kelamin']): ?>
                                        <small class="text-muted"><?= escape($row['jenis_kelamin']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['nama_jenis']) ?></span></td>
                                <td>
                                    <div class="small fw-medium text-dark"><?= escape($row['nama_kantor']) ?></div>
                                    <small class="text-muted"><?= escape($row['kode_kantor']) ?></small>
                                </td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_pendaftaran']) ?></small></td>
                                <td><?= badge_status($row['status_wajib_pajak']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_wajib_pajak'] ?>" class="btn btn-sm btn-outline-info" title="Lihat Profil & Riwayat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= $row['id_wajib_pajak'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah Data">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus Data" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_wajib_pajak'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus wajib pajak <strong><?= escape($row['nama_wajib_pajak']) ?></strong>?');">
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&kantor=<?= $filter_kantor ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
