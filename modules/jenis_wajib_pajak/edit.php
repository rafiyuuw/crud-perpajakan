<?php
$judul_halaman = 'Ubah Jenis Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_wajib_pajak WHERE id_jenis_wajib_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data jenis wajib pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_jenis = bersihkan($_POST['nama_jenis'] ?? '');
    $keterangan = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($nama_jenis)) {
        $error = 'Nama jenis wajib pajak wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_jenis_wajib_pajak FROM jenis_wajib_pajak WHERE nama_jenis = ? AND id_jenis_wajib_pajak != ?", [$nama_jenis, $id]);
        if ($cek) {
            $error = 'Nama jenis wajib pajak sudah terdaftar!';
        } else {
            try {
                eksekusi("UPDATE jenis_wajib_pajak SET nama_jenis = ?, keterangan = ? WHERE id_jenis_wajib_pajak = ?", [$nama_jenis, $keterangan, $id]);
                set_flash('success', 'Jenis wajib pajak berhasil diperbarui!');
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
    <div class="col-12 col-lg-6">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Jenis Wajib Pajak</h4>
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
                        <input type="text" name="nama_jenis" class="form-control" value="<?= escape($data['nama_jenis']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Keterangan / Deskripsi</label>
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
