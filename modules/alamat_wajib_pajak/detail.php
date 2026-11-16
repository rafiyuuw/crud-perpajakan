<?php
$judul_halaman = 'Detail Alamat Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT a.*, wp.nama_wajib_pajak, wp.nomor_npwp, k.nama_kantor, k.kode_kantor
    FROM alamat_wajib_pajak a
    JOIN wajib_pajak wp ON a.id_wajib_pajak = wp.id_wajib_pajak
    JOIN kantor_pajak k ON wp.id_kantor = k.id_kantor
    WHERE a.id_alamat = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data alamat tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Alamat Wajib Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_alamat'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Alamat
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>Informasi Alamat</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 160px;">Pemilik Alamat (WP)</td>
                        <td class="fw-bold text-dark fs-5">
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace">NPWP: <?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Alamat</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['jenis_alamat']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat Lengkap</td>
                        <td class="fw-semibold"><?= escape($data['alamat_lengkap']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kecamatan</td>
                        <td><?= escape($data['kecamatan']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kota / Kabupaten</td>
                        <td><?= escape($data['kota']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Provinsi</td>
                        <td><?= escape($data['provinsi']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kode Pos</td>
                        <td><span class="font-monospace fw-bold"><?= escape($data['kode_pos']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">KPP Terdaftar</td>
                        <td>[<?= escape($data['kode_kantor']) ?>] <?= escape($data['nama_kantor']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
