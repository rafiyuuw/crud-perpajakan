<?php
$judul_halaman = 'Kelola Tagihan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$cari = bersihkan($_GET['cari'] ?? '');
$filter_status = bersihkan($_GET['status'] ?? '');
$halaman = max(1, (int)($_GET['halaman'] ?? 1));
$limit = 10;
$offset = ($halaman - 1) * $limit;

$where = "WHERE 1=1";
$params = [];
if (!empty($cari)) {
    $where .= " AND (t.nomor_tagihan LIKE ? OR s.nomor_spt LIKE ? OR wp.nama_wajib_pajak LIKE ? OR wp.nomor_npwp LIKE ?)";
    $params = array_merge($params, ["%$cari%", "%$cari%", "%$cari%", "%$cari%"]);
}
if (!empty($filter_status)) {
    $where .= " AND t.status_tagihan = ?";
    $params[] = $filter_status;
}

$total_data = ambil_satu("
    SELECT COUNT(*) as total 
    FROM tagihan_pajak t 
    JOIN spt s ON t.id_spt = s.id_spt 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    $where
", $params)['total'];
$total_halaman = ceil($total_data / $limit);

$query = "
    SELECT t.*, s.nomor_spt, wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak,
           (SELECT COALESCE(SUM(p.jumlah_pembayaran), 0) FROM pembayaran_pajak p WHERE p.id_tagihan = t.id_tagihan AND p.status_pembayaran = 'Berhasil') as total_terbayar
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    $where
    ORDER BY t.id_tagihan DESC
    LIMIT $limit OFFSET $offset
";
$data_list = ambil_semua($query, $params);
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tagihan Pajak</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0 text-dark">Data Surat Tagihan Pajak (STP)</h4>
    </div>
    <div>
        <a href="tambah.php" class="btn btn-primary">
            <i class="bi bi-receipt me-1"></i>Buat Tagihan Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3 p-md-4">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <input type="text" name="cari" class="form-control" placeholder="Cari No Tagihan, SPT, nama WP, NPWP..." value="<?= escape($cari) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status Tagihan --</option>
                    <option value="Belum Bayar" <?= ($filter_status === 'Belum Bayar') ? 'selected' : '' ?>>Belum Bayar</option>
                    <option value="Dibayar Sebagian" <?= ($filter_status === 'Dibayar Sebagian') ? 'selected' : '' ?>>Dibayar Sebagian</option>
                    <option value="Lunas" <?= ($filter_status === 'Lunas') ? 'selected' : '' ?>>Lunas</option>
                    <option value="Kadaluarsa" <?= ($filter_status === 'Kadaluarsa') ? 'selected' : '' ?>>Kadaluarsa</option>
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
                        <th>Nomor Tagihan</th>
                        <th>Nomor SPT (Relasi)</th>
                        <th>Wajib Pajak (Penanggung)</th>
                        <th>Jatuh Tempo</th>
                        <th>Jumlah Tagihan</th>
                        <th>Sudah Dibayar</th>
                        <th>Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data_list)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data tagihan ditemukan.</td></tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($data_list as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <a href="detail.php?id=<?= $row['id_tagihan'] ?>" class="fw-bold text-decoration-none text-danger font-monospace">
                                        <?= escape($row['nomor_tagihan']) ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="../spt/detail.php?id=<?= $row['id_spt'] ?>" class="text-decoration-none">
                                        <?= escape($row['nomor_spt']) ?>
                                    </a>
                                    <div><span class="badge bg-light text-dark border"><?= escape($row['kode_pajak']) ?></span></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= escape($row['nama_wajib_pajak']) ?></div>
                                    <small class="text-muted font-monospace"><?= escape($row['nomor_npwp']) ?></small>
                                </td>
                                <td>
                                    <small class="text-danger fw-semibold"><i class="bi bi-calendar-event me-1"></i><?= format_tanggal($row['tanggal_jatuh_tempo']) ?></small>
                                </td>
                                <td><span class="fw-bold text-dark"><?= format_rupiah($row['jumlah_tagihan']) ?></span></td>
                                <td><span class="text-success fw-semibold"><?= format_rupiah($row['total_terbayar']) ?></span></td>
                                <td><?= badge_status($row['status_tagihan']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-action-group" role="group">
                                        <a href="detail.php?id=<?= $row['id_tagihan'] ?>" class="btn btn-sm btn-outline-info" title="Detail & Pembayaran"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_tagihan'] ?>" class="btn btn-sm btn-outline-warning" title="Ubah Tagihan"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return konfirmasiHapus('hapus.php?id=<?= $row['id_tagihan'] ?>&csrf_token=<?= csrf_token() ?>', 'Hapus tagihan <strong><?= escape($row['nomor_tagihan']) ?></strong>?');"><i class="bi bi-trash"></i></a>
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
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&halaman=<?= $halaman - 1 ?>">Sebelumnya</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= ($i == $halaman) ? 'active' : '' ?>">
                            <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&halaman=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari) ?>&status=<?= $filter_status ?>&halaman=<?= $halaman + 1 ?>">Selanjutnya</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
