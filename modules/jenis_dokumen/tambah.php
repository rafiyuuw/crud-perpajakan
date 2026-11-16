<?php
$judul_halaman = 'Tambah Jenis Dokumen';
require_once __DIR__ . '/../../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_dokumen = bersihkan($_POST['nama_dokumen'] ?? '');
    $deskripsi    = bersihkan($_POST['deskripsi'] ?? '');
    
    if (empty($nama_dokumen)) {
        $error = 'Nama jenis dokumen wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_jenis_dokumen FROM jenis_dokumen WHERE nama_dokumen = ?", [$nama_dokumen]);
        if ($cek) {
            $error = 'Nama jenis dokumen sudah terdaftar!';
        } else {
            try {
                eksekusi("INSERT INTO jenis_dokumen (nama_dokumen, deskripsi) VALUES (?, ?)", [$nama_dokumen, $deskripsi]);
                set_flash('success', 'Jenis dokumen berhasil ditambahkan!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Jenis Dokumen</h4>
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
                        <label class="form-label fw-semibold">Nama Dokumen Perpajakan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_dokumen" class="form-control" placeholder="Contoh: Bukti Potong 1721-VII atau Faktur Masukan" value="<?= escape($_POST['nama_dokumen'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Format Lampiran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Penjelasan isi dan kegunaan dokumen..."><?= escape($_POST['deskripsi'] ?? '') ?></textarea>
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
