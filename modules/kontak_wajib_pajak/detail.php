<?php
$judul_halaman = 'Detail Kontak Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT k.*, wp.nama_wajib_pajak, wp.nomor_npwp, kp.nama_kantor, kp.kode_kantor
    FROM kontak_wajib_pajak k
    JOIN wajib_pajak wp ON k.id_wajib_pajak = wp.id_wajib_pajak
    JOIN kantor_pajak kp ON wp.id_kantor = kp.id_kantor
    WHERE k.id_kontak = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data kontak tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php?wajib_pajak=<?= $data['id_wajib_pajak'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Kontak Wajib Pajak</h4>
    </div>
    <a href="edit.php?id=<?= $data['id_kontak'] ?>" class="btn btn-warning btn-sm text-dark">
        <i class="bi bi-pencil me-1"></i>Ubah Kontak
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-telephone-fill me-2 text-success"></i>Informasi Kontak</h6>
            </div>
            <div class="card-body p-4">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width: 160px;">Pemilik Kontak (WP)</td>
                        <td class="fw-bold text-dark fs-5">
                            <a href="../wajib_pajak/detail.php?id=<?= $data['id_wajib_pajak'] ?>" class="text-decoration-none">
                                <?= escape($data['nama_wajib_pajak']) ?>
                            </a>
                            <div class="small text-muted font-monospace">NPWP: <?= escape($data['nomor_npwp']) ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jenis Kontak</td>
                        <td><span class="badge bg-light text-dark border"><?= escape($data['jenis_kontak']) ?></span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nomor Telepon</td>
                        <td class="fw-semibold"><i class="bi bi-telephone me-2 text-primary"></i><?= escape($data['nomor_telepon']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat Email</td>
                        <td class="fw-semibold"><i class="bi bi-envelope me-2 text-primary"></i><?= escape($data['email']) ?></td>
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
