<?php
$judul_halaman = 'Kelola Riwayat Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_spt = (int)($_GET['spt'] ?? 0);
$filter_status = (int)($_GET['status'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (s.nomor_spt LIKE ? OR r.catatan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR p.nama_petugas LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_spt > 0) {
    $where .= " AND r.id_spt = ?";
    $params[] = $filter_spt;
}
if ($filter_status > 0) {
    $where .= " AND r.id_status_spt = ?";
    $params[] = $filter_status;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM riwayat_status_spt r 
    JOIN spt s ON r.id_spt = s.id_spt 
    JOIN status_spt st ON r.id_status_spt = st.id_status_spt 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    LEFT JOIN petugas_pajak p ON r.id_petugas = p.id_petugas 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT r.*, s.nomor_spt, wp.nama_wajib_pajak, wp.nomor_npwp, st.nama_status, p.nama_petugas, p.jabatan
    FROM riwayat_status_spt r
    JOIN spt s ON r.id_spt = s.id_spt
    JOIN status_spt st ON r.id_status_spt = st.id_status_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN petugas_pajak p ON r.id_petugas = p.id_petugas
    $where
    ORDER BY r.id_riwayat_spt DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_spt = ambil_semua("SELECT id_spt, nomor_spt FROM spt ORDER BY id_spt DESC");
$daftar_status = ambil_semua("SELECT id_status_spt, nama_status FROM status_spt ORDER BY id_status_spt ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Status SPT</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Log Riwayat Perubahan Status SPT</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_spt ? '?id_spt=' . $filter_spt : '' ?>" class="btn btn-primary">
            <i class="bi bi-clock-history me-1"></i>Catat Perubahan Status
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <input type="text" name="cari" class="form-control" placeholder="Cari No SPT, WP, catatan..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="spt" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua SPT --</option>
                    <?php foreach ($daftar_spt as $s): ?>
                        <option value="<?= $s['id_spt'] ?>" <?= ($filter_spt == $s['id_spt']) ? 'selected' : '' ?>>
                            <?= escape($s['nomor_spt']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Status --</option>
                    <?php foreach ($daftar_status as $st): ?>
                        <option value="<?= $st['id_status_spt'] ?>" <?= ($filter_status == $st['id_status_spt']) ? 'selected' : '' ?>>
                            <?= escape($st['nama_status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>Filter</button>
                <?php if (!empty($cari) || $filter_spt > 0 || $filter_status > 0): ?>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor SPT</th>
                        <th>Wajib Pajak</th>
                        <th>Status Tercatat</th>
                        <th>Waktu Perubahan</th>
                        <th>Petugas Pengubah</th>
                        <th>Catatan Perubahan</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada riwayat status SPT ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="../spt/detail.php?id=<?= $row['id_spt'] ?>" class="fw-semibold text-decoration-none">
                                        <?= escape($row['nomor_spt']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td><?= badge_status($row['nama_status']) ?></td>
                                <td><small class="text-muted"><i class="bi bi-clock me-1"></i><?= format_tanggal($row['tanggal_perubahan'], true) ?></small></td>
                                <td><small><?= escape($row['nama_petugas'] ?: 'Sistem') ?></small></td>
                                <td><small class="text-truncate d-inline-block" style="max-width: 250px;"><?= escape($row['catatan'] ?: '-') ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_riwayat_spt'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_riwayat_spt'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_riwayat_spt'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus log riwayat ini?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&status=<?= $filter_status ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&status=<?= $filter_status ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&status=<?= $filter_status ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
