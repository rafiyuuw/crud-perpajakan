<?php
$judul_halaman = 'Tambah Alamat Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_wp_param = (int)($_GET['id_wajib_pajak'] ?? 0);
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
            $sql = "INSERT INTO alamat_wajib_pajak (id_wajib_pajak, alamat_lengkap, provinsi, kota, kecamatan, kode_pos, jenis_alamat) VALUES (?, ?, ?, ?, ?, ?, ?)";
            eksekusi($sql, [$id_wajib_pajak, $alamat_lengkap, $provinsi, $kota, $kecamatan, $kode_pos, $jenis_alamat]);
            set_flash('success', 'Alamat wajib pajak berhasil ditambahkan!');
            header('Location: index.php?wajib_pajak=' . $id_wajib_pajak);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Alamat Wajib Pajak</h4>
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
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($id_wp_param == $wp['id_wajib_pajak']) || (($_POST['id_wajib_pajak'] ?? '') == $wp['id_wajib_pajak'])) ? 'selected' : '' ?>>
                                        <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Alamat <span class="text-danger">*</span></label>
                            <select name="jenis_alamat" class="form-select" required>
                                <option value="Domisili">Domisili</option>
                                <option value="KTP">Sesuai KTP</option>
                                <option value="Kantor Pusat">Kantor Pusat</option>
                                <option value="Kantor Cabang">Kantor Cabang</option>
                                <option value="Pabrik / Gudang">Pabrik / Gudang</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_lengkap" class="form-control" rows="2" placeholder="Nama jalan, gedung, nomor, RT/RW, kelurahan..." required><?= escape($_POST['alamat_lengkap'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kecamatan <span class="text-danger">*</span></label>
                            <input type="text" name="kecamatan" class="form-control" placeholder="Contoh: Menteng" value="<?= escape($_POST['kecamatan'] ?? '') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kota / Kabupaten <span class="text-danger">*</span></label>
                            <input type="text" name="kota" class="form-control" placeholder="Contoh: Jakarta Pusat" value="<?= escape($_POST['kota'] ?? '') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" name="provinsi" class="form-control" placeholder="Contoh: DKI Jakarta" value="<?= escape($_POST['provinsi'] ?? '') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kode Pos <span class="text-danger">*</span></label>
                            <input type="text" name="kode_pos" class="form-control font-monospace" placeholder="Contoh: 10340" value="<?= escape($_POST['kode_pos'] ?? '') ?>" required>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Alamat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
