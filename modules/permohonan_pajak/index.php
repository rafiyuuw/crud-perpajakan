<?php
$judul_halaman = 'Kelola Permohonan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$filter_jenis = (int)($_GET['jenis'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (pp.nomor_permohonan LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ? OR jp.nama_permohonan LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if (!empty($filter_status)) {
    $where .= " AND pp.status_permohonan = ?";
    $params[] = $filter_status;
}
if ($filter_jenis > 0) {
    $where .= " AND pp.id_jenis_permohonan = ?";
    $params[] = $filter_jenis;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT pp.*, wp.nama_wajib_pajak, wp.nomor_npwp, jp.nama_permohonan, pt.nama_petugas
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    LEFT JOIN petugas_pajak pt ON pp.id_petugas = pt.id_petugas
    $where
    ORDER BY pp.id_permohonan DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);

$daftar_jenis_filter = ambil_semua("SELECT id_jenis_permohonan, nama_permohonan FROM jenis_permohonan ORDER BY nama_permohonan ASC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Permohonan Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Pengajuan Permohonan Layanan Pajak</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Buat Permohonan Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <input type="text" name="cari" class="form-control" placeholder="Cari No Permohonan, WP, NPWP..." value="<?= escape($cari) ?>">
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="Diajukan" <?= ($filter_status === 'Diajukan') ? 'selected' : '' ?>>Diajukan</option>
                    <option value="Diverifikasi" <?= ($filter_status === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                    <option value="Disetujui" <?= ($filter_status === 'Disetujui') ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= ($filter_status === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                    <option value="Selesai" <?= ($filter_status === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="jenis" class="form-select">
                    <option value="0">-- Semua Jenis Layanan --</option>
                    <?php foreach ($daftar_jenis_filter as $j): ?>
                        <option value="<?= $j['id_jenis_permohonan'] ?>" <?= ($filter_jenis == $j['id_jenis_permohonan']) ? 'selected' : '' ?>>
                            <?= escape($j['nama_permohonan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nomor Permohonan</th>
                        <th>Wajib Pajak</th>
                        <th>Jenis Permohonan</th>
                        <th>Tgl Pengajuan</th>
                        <th>Petugas Pemeriksa</th>
                        <th>Status</th>
                        <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">Tidak ada permohonan pajak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id_permohonan'] ?>" class="fw-bold font-monospace text-decoration-none">
                                        <?= escape($row['nomor_permohonan']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= escape($row['nama_permohonan']) ?></span>
                                </td>
                                <td><small class="text-muted"><?= format_tanggal($row['tanggal_pengajuan']) ?></small></td>
                                <td><small class="text-muted"><?= escape($row['nama_petugas'] ?? 'Belum Ditugaskan') ?></small></td>
                                <td><?= badge_status($row['status_permohonan']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_permohonan'] ?>" class="btn btn-sm btn-outline-info" title="Detail"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_permohonan'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_permohonan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus permohonan <strong><?= escape($row['nomor_permohonan']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= urlencode($filter_status) ?>&jenis=<?= $filter_jenis ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
