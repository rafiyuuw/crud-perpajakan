<?php
$judul_halaman = 'Kelola Detail SPT';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_spt = (int)($_GET['spt'] ?? 0);
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (s.nomor_spt LIKE ? OR o.nama_objek LIKE ? OR t.nama_tarif LIKE ? OR wp.nama_wajib_pajak LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if ($filter_spt > 0) {
    $where .= " AND d.id_spt = ?";
    $params[] = $filter_spt;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM detail_spt d 
    JOIN spt s ON d.id_spt = s.id_spt 
    JOIN objek_pajak o ON d.id_objek_pajak = o.id_objek_pajak 
    JOIN tarif_pajak t ON d.id_tarif = t.id_tarif 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT d.*, s.nomor_spt, s.tanggal_pelaporan, wp.nama_wajib_pajak, wp.nomor_npwp, 
           o.nama_objek, t.nama_tarif
    FROM detail_spt d
    JOIN spt s ON d.id_spt = s.id_spt
    JOIN objek_pajak o ON d.id_objek_pajak = o.id_objek_pajak
    JOIN tarif_pajak t ON d.id_tarif = t.id_tarif
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    $where
    ORDER BY d.id_detail_spt DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
$daftar_spt = ambil_semua("SELECT id_spt, nomor_spt FROM spt ORDER BY id_spt DESC");
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail SPT</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Detail Rincian Penghitungan SPT</h4>
    </div>
    <div>
        <a href="tambah.php<?= $filter_spt ? '?id_spt=' . $filter_spt : '' ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Rincian SPT
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari No SPT, nama objek, wajib pajak..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="spt" class="form-select" onchange="this.form.submit()">
                    <option value="0">-- Semua Laporan SPT --</option>
                    <?php foreach ($daftar_spt as $s): ?>
                        <option value="<?= $s['id_spt'] ?>" <?= ($filter_spt == $s['id_spt']) ? 'selected' : '' ?>>
                            <?= escape($s['nomor_spt']) ?>
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
                        <th>Nomor SPT (Parent)</th>
                        <th>Wajib Pajak</th>
                        <th>Objek Pajak (FK)</th>
                        <th>Tarif Pajak (FK)</th>
                        <th>Dasar Pengenaan</th>
                        <th>Tarif Terkunci</th>
                        <th>Pajak Terutang</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada rincian detail SPT ditemukan.</td></tr>
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
                                    <div class="fw-medium text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td><?= escape($row['nama_objek']) ?></td>
                                <td><small class="text-muted"><?= escape($row['nama_tarif']) ?></small></td>
                                <td><?= format_rupiah($row['dasar_pengenaan']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= (float)$row['tarif_digunakan'] ?>%</span></td>
                                <td><span class="fw-bold text-dark"><?= format_rupiah($row['jumlah_pajak']) ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_detail_spt'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_detail_spt'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_detail_spt'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus rincian objek ini?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&spt=<?= $filter_spt ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
