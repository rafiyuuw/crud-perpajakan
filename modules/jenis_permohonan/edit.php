<?php
$judul_halaman = 'Ubah Jenis Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM jenis_permohonan WHERE id_jenis_permohonan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data jenis permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $nama_permohonan = bersihkan($_POST['nama_permohonan'] ?? '');
    $deskripsi       = bersihkan($_POST['deskripsi'] ?? '');
    $status          = bersihkan($_POST['status'] ?? 'Aktif');

    if (empty($nama_permohonan)) {
        $error = 'Nama jenis permohonan wajib diisi!';
    } else {
        // Cek duplikasi nama kecuali id saat ini
        $ada = ambil_satu("SELECT id_jenis_permohonan FROM jenis_permohonan WHERE nama_permohonan = ? AND id_jenis_permohonan != ?", [$nama_permohonan, $id]);
        if ($ada) {
            $error = 'Nama permohonan sudah ada dalam sistem!';
        } else {
            $sql = "UPDATE jenis_permohonan SET nama_permohonan = ?, deskripsi = ?, status = ? WHERE id_jenis_permohonan = ?";
            eksekusi($sql, [$nama_permohonan, $deskripsi, $status, $id]);

            set_flash('success', 'Jenis permohonan berhasil diperbarui!');
            header('Location: detail.php?id=' . $id);
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Ubah Jenis Layanan Permohonan</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Perubahan Jenis Layanan</h5>
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
                        <input type="text" name="nama_permohonan" id="nama_permohonan" class="form-control" value="<?= escape($data['nama_permohonan']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi Layanan / Prosedur</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4" class="form-control"><?= escape($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label fw-semibold">Status Layanan</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Aktif" <?= ($data['status'] === 'Aktif') ? 'selected' : '' ?>>Aktif (Tersedia)</option>
                            <option value="Nonaktif" <?= ($data['status'] === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif (Ditutup sementara)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
