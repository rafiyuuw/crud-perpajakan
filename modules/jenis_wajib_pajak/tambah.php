<?php
$judul_halaman = 'Tambah Jenis Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_jenis = bersihkan($_POST['nama_jenis'] ?? '');
    $keterangan = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($nama_jenis)) {
        $error = 'Nama jenis wajib pajak wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_jenis_wajib_pajak FROM jenis_wajib_pajak WHERE nama_jenis = ?", [$nama_jenis]);
        if ($cek) {
            $error = 'Nama jenis wajib pajak sudah terdaftar!';
        } else {
            try {
                eksekusi("INSERT INTO jenis_wajib_pajak (nama_jenis, keterangan) VALUES (?, ?)", [$nama_jenis, $keterangan]);
                set_flash('success', 'Jenis wajib pajak berhasil ditambahkan!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Jenis Wajib Pajak</h4>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escape($error) ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Jenis Wajib Pajak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_jenis" class="form-control" placeholder="Contoh: Badan Usaha Asing" value="<?= escape($_POST['nama_jenis'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan / Deskripsi</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Penjelasan mengenai jenis subjek pajak ini..."><?= escape($_POST['keterangan'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
