<?php
$judul_halaman = 'Ubah Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM status_spt WHERE id_status_spt = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_status = bersihkan($_POST['nama_status'] ?? '');
    $keterangan  = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($nama_status)) {
        $error = 'Nama status SPT wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_status_spt FROM status_spt WHERE nama_status = ? AND id_status_spt != ?", [$nama_status, $id]);
        if ($cek) {
            $error = 'Nama status SPT sudah terdaftar!';
        } else {
            try {
                eksekusi("UPDATE status_spt SET nama_status = ?, keterangan = ? WHERE id_status_spt = ?", [$nama_status, $keterangan, $id]);
                set_flash('success', 'Status SPT berhasil diperbarui!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Status SPT</h4>
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
                        <label class="form-label fw-semibold">Nama Status SPT <span class="text-danger">*</span></label>
                        <input type="text" name="nama_status" class="form-control" value="<?= escape($data['nama_status']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan / Deskripsi Tahapan</label>
                        <textarea name="keterangan" class="form-control" rows="3"><?= escape($data['keterangan']) ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
