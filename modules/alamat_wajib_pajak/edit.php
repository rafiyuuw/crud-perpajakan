<?php
$judul_halaman = 'Ubah Alamat Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT a.*, wp.nama_wajib_pajak, wp.nomor_npwp
    FROM alamat_wajib_pajak a
    JOIN wajib_pajak wp ON a.id_wajib_pajak = wp.id_wajib_pajak
    WHERE a.id_alamat = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data alamat tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_wajib_pajak = (int)($_POST['id_wajib_pajak'] ?? 0);
    $alamat_lengkap = bersihkan($_POST['alamat_lengkap'] ?? '');
    $provinsi       = bersihkan($_POST['provinsi'] ?? '');
    $kota           = bersihkan($_POST['kota'] ?? '');
    $kecamatan      = bersihkan($_POST['kecamatan'] ?? '');
    $kode_pos       = bersihkan($_POST['kode_pos'] ?? '');
    $jenis_alamat   = bersihkan($_POST['jenis_alamat'] ?? 'Domisili');
    
    if (empty($id_wajib_pajak) || empty($alamat_lengkap) || empty($provinsi) || empty($kota) || empty($kecamatan) || empty($kode_pos)) {
        $error = 'Semua bidang formulir wajib diisi!';
    } else {
        try {
            $sql = "UPDATE alamat_wajib_pajak SET id_wajib_pajak = ?, alamat_lengkap = ?, provinsi = ?, kota = ?, kecamatan = ?, kode_pos = ?, jenis_alamat = ? WHERE id_alamat = ?";
            eksekusi($sql, [$id_wajib_pajak, $alamat_lengkap, $provinsi, $kota, $kecamatan, $kode_pos, $jenis_alamat, $id]);
            set_flash('success', 'Alamat wajib pajak berhasil diperbarui!');
            header('Location: index.php?wajib_pajak=' . $id_wajib_pajak);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui data: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Alamat Wajib Pajak</h4>
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
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Pilih Wajib Pajak (Foreign Key) <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" class="form-select" required>
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <?php foreach ($daftar_wp as $wp): ?>
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($data['id_wajib_pajak'] ?? 0) == $wp['id_wajib_pajak']) ? 'selected' : '' ?>>
                                        <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Alamat <span class="text-danger">*</span></label>
                            <select name="jenis_alamat" class="form-select" required>
                                <option value="Domisili" <?= ($data['jenis_alamat'] === 'Domisili') ? 'selected' : '' ?>>Domisili</option>
                                <option value="KTP" <?= ($data['jenis_alamat'] === 'KTP') ? 'selected' : '' ?>>Sesuai KTP</option>
                                <option value="Kantor Pusat" <?= ($data['jenis_alamat'] === 'Kantor Pusat') ? 'selected' : '' ?>>Kantor Pusat</option>
                                <option value="Kantor Cabang" <?= ($data['jenis_alamat'] === 'Kantor Cabang') ? 'selected' : '' ?>>Kantor Cabang</option>
                                <option value="Pabrik / Gudang" <?= ($data['jenis_alamat'] === 'Pabrik / Gudang') ? 'selected' : '' ?>>Pabrik / Gudang</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_lengkap" class="form-control" rows="2" required><?= escape($data['alamat_lengkap']) ?></textarea>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="kecamatan" class="form-control" value="<?= escape($data['kecamatan']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kota / Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" name="kota" class="form-control" value="<?= escape($data['kota']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" class="form-control" value="<?= escape($data['provinsi']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" name="kode_pos" class="form-control font-monospace" value="<?= escape($data['kode_pos']) ?>" required>
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
