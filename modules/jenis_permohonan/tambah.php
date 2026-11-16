<?php
$judul_halaman = 'Tambah Jenis Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $nama_permohonan = bersihkan($_POST['nama_permohonan'] ?? '');
    $deskripsi       = bersihkan($_POST['deskripsi'] ?? '');
    $status          = bersihkan($_POST['status'] ?? 'Aktif');

    if (empty($nama_permohonan)) {
        $error = 'Nama jenis permohonan wajib diisi!';
    } else {
        // Cek duplikasi nama
        $ada = ambil_satu("SELECT id_jenis_permohonan FROM jenis_permohonan WHERE nama_permohonan = ?", [$nama_permohonan]);
        if ($ada) {
            $error = 'Nama permohonan sudah ada dalam sistem!';
        } else {
            $sql = "INSERT INTO jenis_permohonan (nama_permohonan, deskripsi, status) VALUES (?, ?, ?)";
            $insert_id = eksekusi($sql, [$nama_permohonan, $deskripsi, $status]);

            if ($insert_id) {
                set_flash('success', 'Jenis permohonan berhasil ditambahkan!');
                header('Location: detail.php?id=' . $insert_id);
                exit;
            } else {
                $error = 'Gagal menyimpan jenis permohonan.';
            }
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Tambah Jenis Layanan Permohonan</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Jenis Layanan Perpajakan</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div><?= escape($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="nama_permohonan" class="form-label fw-semibold">Nama Jenis Permohonan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_permohonan" id="nama_permohonan" class="form-control" placeholder="Contoh: Permohonan Pengurangan PBB, Restitusi Lebih Bayar, dll." value="<?= escape($_POST['nama_permohonan'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi Layanan / Prosedur</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control" placeholder="Penjelasan mengenai dasar hukum, syarat pengajuan, dan durasi proses layanan..."><?= escape($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status Layanan</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Aktif" <?= (($_POST['status'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif (Tersedia untuk diajukan)</option>
                            <option value="Nonaktif" <?= (($_POST['status'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif (Ditutup sementara)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Jenis Permohonan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
