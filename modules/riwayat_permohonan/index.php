<?php
$judul_halaman = 'Kelola Riwayat Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_mohon = (int)($_GET['permohonan'] ?? 0);
$filter_status = bersihkan($_GET['status'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (pp.nomor_permohonan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR rp.catatan LIKE ? OR pt.nama_petugas LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_mohon > 0) {
    $where .= " AND rp.id_permohonan = ?";
    $params[] = $filter_mohon;
}
if (!empty($filter_status)) {
    $where .= " AND rp.status_baru = ?";
    $params[] = $filter_status;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM riwayat_permohonan rp
    JOIN permohonan_pajak pp ON rp.id_permohonan = pp.id_permohonan
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    LEFT JOIN petugas_pajak pt ON rp.id_petugas = pt.id_petugas
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT rp.*, pp.nomor_permohonan, pp.status_permohonan as status_saat_ini,
           wp.nama_wajib_pajak, wp.nomor_npwp, jp.nama_permohonan,
           pt.nama_petugas
    FROM riwayat_permohonan rp
    JOIN permohonan_pajak pp ON rp.id_permohonan = pp.id_permohonan
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    LEFT JOIN petugas_pajak pt ON rp.id_petugas = pt.id_petugas
    $where
    ORDER BY rp.id_riwayat_permohonan DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Permohonan</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Log &amp; Riwayat Perubahan Status Permohonan Pajak</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_mohon ? '?id_permohonan=' . $filter_mohon : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Riwayat Status
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <input type="text" name="cari" class="form-control" placeholder="Cari No Permohonan, WP, catatan..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status Baru --</option>
                    <option value="Diajukan" <?= ($filter_status === 'Diajukan') ? 'selected' : '' ?>>Diajukan</option>
                    <option value="Diverifikasi" <?= ($filter_status === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                    <option value="Disetujui" <?= ($filter_status === 'Disetujui') ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= ($filter_status === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    <option value="Selesai" <?= ($filter_status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
            <div class="col-12 col-md-2 text-md-end text-muted small d-flex align-items-center justify-content-md-end">
                Menampilkan <?= count($data_list) ?> dari <?= $total_data ?> log
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Permohonan &amp; Wajib Pajak</th>
                        <th>Perubahan Status</th>
                        <th>Petugas</th>
                        <th>Waktu Perubahan</th>
                        <th>Catatan / Keterangan</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada riwayat permohonan ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="../permohonan_pajak/detail.php?id=<?= $row['id_permohonan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                        <?= escape($row['nomor_permohonan']) ?>
                                    </a>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted"><?= escape($row['nama_permohonan']) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-light text-secondary border"><?= escape($row['status_sebelumnya']) ?></span>
                                        <i class="bi bi-arrow-right text-muted small"></i>
                                        <?= badge_status($row['status_baru']) ?>
                                    </div>
                                </td>
                                <td><small class="text-muted"><?= escape($row['nama_petugas'] ?? 'Sistem / Otomatis') ?></small></td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_perubahan'], true) ?></small></td>
                                <td><small class="text-dark"><?= escape($row['catatan'] ?? '-') ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_riwayat_permohonan'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_riwayat_permohonan'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_riwayat_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus catatan riwayat ini?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&permohonan=<?= $filter_mohon ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&permohonan=<?= $filter_mohon ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&permohonan=<?= $filter_mohon ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
