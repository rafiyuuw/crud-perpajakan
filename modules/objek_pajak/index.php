<?php
$judul_halaman = 'Kelola Objek Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_jenis = (int)($_GET['jenis'] ?? 0);
$filter_wp = (int)($_GET['wajib_pajak'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (o.nama_objek LIKE ? OR o.alamat_objek LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_jenis > 0) {
    $where .= " AND o.id_jenis_pajak = ?";
    $params[] = $filter_jenis;
}
if ($filter_wp > 0) {
    $where .= " AND o.id_wajib_pajak = ?";
    $params[] = $filter_wp;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM objek_pajak o 
    JOIN wajib_pajak wp ON o.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT o.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, jp.nama_pajak
    FROM objek_pajak o
    JOIN wajib_pajak wp ON o.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak
    $where
    ORDER BY o.id_objek_pajak ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak ORDER BY nama_pajak ASC");
$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Objek Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Objek Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Objek Pajak
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama objek, lokasi, nama WP..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Jenis Pajak --</option>
                    <?php foreach ($daftar_jenis as $j): ?>
                        <option value="<?= $j['id_jenis_pajak'] ?>" <?= ($filter_jenis == $j['id_jenis_pajak']) ? 'selected' : '' ?>>
                            [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="wajib_pajak" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Wajib Pajak --</option>
                    <?php foreach ($daftar_wp as $wp): ?>
                        <option value="<?= $wp['id_wajib_pajak'] ?>" <?= ($filter_wp == $wp['id_wajib_pajak']) ? 'selected' : '' ?>>
                            <?= escape($wp['nama_wajib_pajak']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                <?= count($data_list) ?> / <?= $total_data ?> data
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Objek Pajak</th>
                        <th>Wajib Pajak Pemilik</th>
                        <th>Jenis Pajak</th>
                        <th>Nilai Objek</th>
                        <th>Lokasi / Keterangan</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data objek pajak.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_objek']) ?></div>
                                </td>
                                <td>
                                    <a href="../wajib_pajak/detail.php?id=<?= $row['id_wajib_pajak'] ?>" class="text-decoration-none">
                                        <?= escape($row['nama_wajib_pajak']) ?>
                                    </a>
                                    <div class="small text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['kode_pajak']) ?></span></td>
                                <td><span class="fw-bold text-dark"><?= format_rupiah($row['nilai_objek']) ?></span></td>
                                <td><small class="text-muted text-truncate d-inline-block" style="max-width: 200px;"><?= escape($row['alamat_objek']) ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_objek_pajak'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_objek_pajak'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_objek_pajak'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus objek pajak <strong><?= escape($row['nama_objek']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&wajib_pajak=<?= $filter_wp ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
