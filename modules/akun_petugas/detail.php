<?php
$judul_halaman = 'Detail Akun Petugas';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT a.*, p.nama_petugas, p.nomor_identitas, p.jabatan, p.nomor_telepon, p.email, p.status as status_petugas, k.nama_kantor, k.kode_kantor
    FROM akun_petugas a
    JOIN petugas_pajak p ON a.id_petugas = p.id_petugas
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    WHERE a.id_akun = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data akun tidak ditemukan!');
    header('Location: index.php');
    exit;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h4 class="fw-bold mb-0 text-dark">Detail Akun Petugas Pajak</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="edit.php?id=<?= $data['id_akun'] ?>" class="btn btn-warning btn-sm text-dark">
            <i class="bi bi-pencil me-1"></i>Ubah Akun
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lock me-2 text-primary"></i>Informasi Akun &amp; Hak Akses</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 140px;">Nama Pengguna</td>
                                <td class="fw-bold text-primary fs-5">@<?= escape($data['nama_pengguna']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Peran (Role)</td>
                                <td><span class="badge bg-light text-dark border fs-6"><?= escape($data['peran']) ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Akun</td>
                                <td><?= badge_status($data['status_akun']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Terakhir Masuk</td>
                                <td><?= format_tanggal($data['terakhir_masuk'], true) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Waktu Dibuat</td>
                                <td><?= format_tanggal($data['created_at'], true) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6 border-start-md">
                        <h6 class="fw-bold text-muted small text-uppercase mb-3">Data Pemilik Akun (Petugas)</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width: 120px;">Nama Petugas</td>
                                <td class="fw-semibold text-dark">
                                    <a href="../petugas_pajak/detail.php?id=<?= $data['id_petugas'] ?>" class="text-decoration-none">
                                        <?= escape($data['nama_petugas']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">NIP</td>
                                <td><?= escape($data['nomor_identitas']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jabatan</td>
                                <td><?= escape($data['jabatan']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kantor Pajak</td>
                                <td>[<?= escape($data['kode_kantor']) ?>] <?= escape($data['nama_kantor']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td><?= escape($data['email']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
