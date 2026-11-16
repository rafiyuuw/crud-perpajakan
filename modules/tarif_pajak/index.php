<?php
$judul_halaman = 'Kelola Tarif Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_jenis = (int)($_GET['jenis'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (t.nama_tarif LIKE ? OR t.jenis_perhitungan LIKE ? OR jp.kode_pajak LIKE ? OR jp.nama_pajak LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_jenis > 0) {
    $where .= " AND t.id_jenis_pajak = ?";
    $params[] = $filter_jenis;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM tarif_pajak t 
    JOIN jenis_pajak jp ON t.id_jenis_pajak = jp.id_jenis_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT t.*, jp.kode_pajak, jp.nama_pajak
    FROM tarif_pajak t
    JOIN jenis_pajak jp ON t.id_jenis_pajak = jp.id_jenis_pajak
    $where
    ORDER BY t.id_tarif ASC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak ORDER BY nama_pajak ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tarif Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Tarif Pajak (Dinamis)</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Tarif Pajak
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama tarif atau jenis pajak..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Jenis Pajak --</option>
                    <?php foreach ($daftar_jenis as $j): ?>
                        <option value="<?= $j['id_jenis_pajak'] ?>" <?= ($filter_jenis == $j['id_jenis_pajak']) ? 'selected' : '' ?>>
                            [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
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
                        <th>Jenis Pajak</th>
                        <th>Nama Tarif</th>
                        <th>Metode Perhitungan</th>
                        <th>Nilai Tarif</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada data tarif pajak.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['kode_pajak']) ?></span></td>
                                <td><div class="fw-semibold text-dark"><?= escape($row['nama_tarif']) ?></div></td>
                                <td><small class="text-muted"><?= escape($row['jenis_perhitungan']) ?></small></td>
                                <td>
                                    <span class="fw-bold text-primary fs-6"><?= (float)$row['nilai_tarif'] ?>%</span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= format_tanggal($row['tanggal_mulai']) ?> s.d 
                                        <?= $row['tanggal_berakhir'] ? format_tanggal($row['tanggal_berakhir']) : '<span class="text-success fw-semibold">Seterusnya</span>' ?>
                                    </small>
                                </td>
                                <td><?= badge_status($row['status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_tarif'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_tarif'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_tarif'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus tarif <strong><?= escape($row['nama_tarif']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
