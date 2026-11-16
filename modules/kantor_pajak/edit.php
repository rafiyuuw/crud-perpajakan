<?php
$judul_halaman = 'Ubah Kantor Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM kantor_pajak WHERE id_kantor = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data kantor pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $kode_kantor = bersihkan($_POST['kode_kantor'] ?? '');
    $nama_kantor = bersihkan($_POST['nama_kantor'] ?? '');
    $alamat      = bersihkan($_POST['alamat'] ?? '');
    $kota        = bersihkan($_POST['kota'] ?? '');
    $nomor_telepon = bersihkan($_POST['nomor_telepon'] ?? '');
    $email       = bersihkan($_POST['email'] ?? '');
    
    if (empty($kode_kantor) || empty($nama_kantor) || empty($alamat) || empty($kota) || empty($nomor_telepon) || empty($email)) {
        $error = 'Semua bidang formulir wajib diisi!';
    } else {
        // Cek duplikasi kode_kantor selain id ini
        $cek = ambil_satu("SELECT id_kantor FROM kantor_pajak WHERE kode_kantor = ? AND id_kantor != ?", [$kode_kantor, $id]);
        if ($cek) {
            $error = 'Kode kantor pajak sudah digunakan oleh kantor lain!';
        } else {
            try {
                $sql = "UPDATE kantor_pajak SET kode_kantor = ?, nama_kantor = ?, alamat = ?, kota = ?, nomor_telepon = ?, email = ? WHERE id_kantor = ?";
                eksekusi($sql, [$kode_kantor, $nama_kantor, $alamat, $kota, $nomor_telepon, $email, $id]);
                set_flash('success', 'Data kantor pajak berhasil diperbarui!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui data: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Data Kantor Pajak</h4>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= escape($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kode Kantor <span class="text-danger">*</span></label>
                            <input type="text" name="kode_kantor" class="form-control" value="<?= escape($_POST['kode_kantor'] ?? $data['kode_kantor']) ?>" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Kantor Pajak <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kantor" class="form-control" value="<?= escape($_POST['nama_kantor'] ?? $data['nama_kantor']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required><?= escape($_POST['alamat'] ?? $data['alamat']) ?></textarea>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kota / Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" name="kota" class="form-control" value="<?= escape($_POST['kota'] ?? $data['kota']) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_telepon" class="form-control" value="<?= escape($_POST['nomor_telepon'] ?? $data['nomor_telepon']) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Email Resmi <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= escape($_POST['email'] ?? $data['email']) ?>" required>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
