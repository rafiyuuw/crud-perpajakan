<?php
$judul_halaman = 'Kelola Alamat Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_wp = (int)($_GET['wajib_pajak'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (a.alamat_lengkap LIKE ? OR a.kota LIKE ? OR a.provinsi LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_wp > 0) {
    $where .= " AND a.id_wajib_pajak = ?";
    $params[] = $filter_wp;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM alamat_wajib_pajak a 
    JOIN wajib_pajak wp ON a.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT a.*, wp.nama_wajib_pajak, wp.nomor_npwp
    FROM alamat_wajib_pajak a
    JOIN wajib_pajak wp ON a.id_wajib_pajak = wp.id_wajib_pajak
    $where
    ORDER BY a.id_alamat ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Alamat Wajib Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Alamat Wajib Pajak</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_wp ? '?id_wajib_pajak=' . $filter_wp : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Alamat WP
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari alamat, kota, nama WP, NPWP..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="wajib_pajak" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Wajib Pajak --</option>
                    <?php foreach ($daftar_wp as $wp): ?>
                        <option value="<?= $wp['id_wajib_pajak'] ?>" <?= ($filter_wp == $wp['id_wajib_pajak']) ? 'selected' : '' ?>>
                            <?= escape($wp['nama_wajib_pajak']) ?> (<?= escape($wp['nomor_npwp']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> data
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Wajib Pajak (Foreign Key)</th>
                        <th>Jenis Alamat</th>
                        <th>Alamat Lengkap</th>
                        <th>Kecamatan / Kota</th>
                        <th>Provinsi</th>
                        <th>Kode Pos</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data alamat ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="../wajib_pajak/detail.php?id=<?= $row['id_wajib_pajak'] ?>" class="fw-semibold text-decoration-none">
                                        <?= escape($row['nama_wajib_pajak']) ?>
                                    </a>
                                    <div class="small text-muted"><?= escape($row['nomor_npwp']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['jenis_alamat']) ?></span></td>
                                <td><div class="text-truncate" style="max-width: 200px;"><?= escape($row['alamat_lengkap']) ?></div></td>
                                <td><?= escape($row['kecamatan']) ?>, <?= escape($row['kota']) ?></td>
                                <td><?= escape($row['provinsi']) ?></td>
                                <td><span class="font-monospace small"><?= escape($row['kode_pos']) ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_alamat'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_alamat'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_alamat'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus alamat ini?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
