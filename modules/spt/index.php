<?php
$judul_halaman = 'Kelola Pelaporan SPT';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_status = (int)($_GET['status'] ?? 0);
$filter_jenis = (int)($_GET['jenis'] ?? 0);
$filter_wp = (int)($_GET['wajib_pajak'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (s.nomor_spt LIKE ? OR s.keterangan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_status > 0) {
    $where .= " AND s.id_status_spt = ?";
    $params[] = $filter_status;
}
if ($filter_jenis > 0) {
    $where .= " AND s.id_jenis_pajak = ?";
    $params[] = $filter_jenis;
}
if ($filter_wp > 0) {
    $where .= " AND s.id_wajib_pajak = ?";
    $params[] = $filter_wp;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM spt s 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT s.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak, jp.nama_pajak, 
           st.nama_status, mp.tahun_pajak, p.nama_petugas
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt
    JOIN masa_pajak mp ON s.id_masa_pajak = mp.id_masa_pajak
    LEFT JOIN petugas_pajak p ON s.id_petugas = p.id_petugas
    $where
    ORDER BY s.id_spt DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_status = ambil_semua("SELECT id_status_spt, nama_status FROM status_spt ORDER BY id_status_spt ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak ORDER BY nama_pajak ASC");
$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pelaporan SPT</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Pelaporan Surat Pemberitahuan (SPT)</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-1"></i>Input Pelaporan SPT Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <!-- Form Filter & Pencarian -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-3">
                <input type="text" name="cari" class="form-control" placeholder="Cari No SPT, nama WP, NPWP..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Status SPT --</option>
                    <?php foreach ($daftar_status as $st): ?>
                        <option value="<?= $st['id_status_spt'] ?>" <?= ($filter_status == $st['id_status_spt']) ? 'selected' : '' ?>>
                            <?= escape($st['nama_status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
            <div class="col-12 col-md-3 d-flex gap-1">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <?php if (!empty($cari) || $filter_status > 0 || $filter_jenis > 0 || $filter_wp > 0): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
            <div>Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> pelaporan SPT</div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor SPT</th>
                        <th>Wajib Pajak (Pemohon)</th>
                        <th>Jenis Pajak</th>
                        <th>Masa Pajak</th>
                        <th>Tanggal Pelaporan</th>
                        <th>Total Pajak Terutang</th>
                        <th>Petugas Pemeriksa</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data pelaporan SPT ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id_spt'] ?>" class="fw-bold text-decoration-none text-primary">
                                        <?= escape($row['nomor_spt']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="../wajib_pajak/detail.php?id=<?= $row['id_wajib_pajak'] ?>" class="text-decoration-none text-dark fw-medium">
                                        <?= escape($row['nama_wajib_pajak']) ?>
                                    </a>
                                    <div class="small text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['kode_pajak']) ?></span></td>
                                <td><span class="badge bg-light text-secondary border">Thn <?= $row['tahun_pajak'] ?></span></td>
                                <td><small><?= format_tanggal($row['tanggal_pelaporan']) ?></small></td>
                                <td><span class="fw-bold text-dark"><?= format_rupiah($row['total_pajak']) ?></span></td>
                                <td><small class="text-muted"><?= escape($row['nama_petugas'] ?: '-') ?></small></td>
                                <td><?= badge_status($row['nama_status']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_spt'] ?>" class="btn btn-sm btn-outline-info" title="Detail & Perhitungan"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_spt'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah SPT"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_spt'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus laporan SPT <strong><?= escape($row['nomor_spt']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
