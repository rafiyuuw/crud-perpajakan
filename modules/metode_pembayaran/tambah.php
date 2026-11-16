<?php
$judul_halaman = 'Tambah Metode Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_metode = bersihkan($_POST['nama_metode'] ?? '');
    $deskripsi   = bersihkan($_POST['deskripsi'] ?? '');
    $status      = bersihkan($_POST['status'] ?? 'Aktif');
    
    if (empty($nama_metode)) {
        $error = 'Nama metode pembayaran wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_metode FROM metode_pembayaran WHERE nama_metode = ?", [$nama_metode]);
        if ($cek) {
            $error = 'Nama metode pembayaran sudah terdaftar!';
        } else {
            try {
                eksekusi("INSERT INTO metode_pembayaran (nama_metode, deskripsi, status) VALUES (?, ?, ?)", [$nama_metode, $deskripsi, $status]);
                set_flash('success', 'Metode pembayaran baru berhasil ditambahkan!');
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
            <h4 class="fw-bold mb-0 text-dark">Tambah Metode Pembayaran</h4>
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
                        <label class="form-label fw-semibold">Nama Kanal / Metode Pembayaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_metode" class="form-control" placeholder="Contoh: Bank BNI Virtual Account atau Indomaret" value="<?= escape($_POST['nama_metode'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Petunjuk Penyetoran</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Instruksi pembayaran wajib pajak..."><?= escape($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Kanal</label>
                        <select name="status" class="form-select">
                            <option value="Aktif" <?= (($_POST['status'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="Nonaktif" <?= (($_POST['status'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Metode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
