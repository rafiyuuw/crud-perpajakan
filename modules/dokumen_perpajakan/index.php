<?php
$judul_halaman = 'Kelola Dokumen Perpajakan';
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
    $where .= " AND (d.nama_file LIKE ? OR d.keterangan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ? OR jd.nama_dokumen LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_jenis > 0) {
    $where .= " AND d.id_jenis_dokumen = ?";
    $params[] = $filter_jenis;
}
if ($filter_wp > 0) {
    $where .= " AND d.id_wajib_pajak = ?";
    $params[] = $filter_wp;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM dokumen_perpajakan d 
    JOIN wajib_pajak wp ON d.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN jenis_dokumen jd ON d.id_jenis_dokumen = jd.id_jenis_dokumen 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT d.*, wp.nama_wajib_pajak, wp.nomor_npwp, jd.nama_dokumen, s.nomor_spt
    FROM dokumen_perpajakan d
    JOIN wajib_pajak wp ON d.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_dokumen jd ON d.id_jenis_dokumen = jd.id_jenis_dokumen
    LEFT JOIN spt s ON d.id_spt = s.id_spt
    $where
    ORDER BY d.id_dokumen DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_jenis = ambil_semua("SELECT id_jenis_dokumen, nama_dokumen FROM jenis_dokumen ORDER BY nama_dokumen ASC");
$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dokumen Perpajakan</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Berkas &amp; Dokumen Perpajakan</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up me-1"></i>Unggah Dokumen Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari nama berkas, jenis, WP..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="jenis" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Jenis Dokumen --</option>
                    <?php foreach ($daftar_jenis as $jd): ?>
                        <option value="<?= $jd['id_jenis_dokumen'] ?>" <?= ($filter_jenis == $jd['id_jenis_dokumen']) ? 'selected' : '' ?>>
                            <?= escape($jd['nama_dokumen']) ?>
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
                <?= count($data_list) ?> / <?= $total_data ?> berkas
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Berkas File</th>
                        <th>Jenis Dokumen</th>
                        <th>Wajib Pajak (Pemilik)</th>
                        <th>Kaitan SPT</th>
                        <th>Tanggal Unggah</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada dokumen tersimpan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><i class="bi bi-file-earmark-pdf text-danger me-1"></i><?= escape($row['nama_file']) ?></div>
                                    <small class="text-muted"><?= escape($row['lokasi_file']) ?></small>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= escape($row['nama_dokumen']) ?></span></td>
                                <td>
                                    <a href="../wajib_pajak/detail.php?id=<?= $row['id_wajib_pajak'] ?>" class="text-decoration-none fw-medium text-dark">
                                        <?= escape($row['nama_wajib_pajak']) ?>
                                    </a>
                                    <div class="small text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></div>
                                </td>
                                <td>
                                    <?php if ($row['id_spt']): ?>
                                        <a href="../spt/detail.php?id=<?= $row['id_spt'] ?>" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 text-decoration-none">
                                            <?= escape($row['nomor_spt']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Umum</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_upload'], true) ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_dokumen'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_dokumen'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_dokumen'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus berkas <strong><?= escape($row['nama_file']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
