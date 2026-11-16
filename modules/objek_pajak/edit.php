<?php
$judul_halaman = 'Ubah Objek Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM objek_pajak WHERE id_objek_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data objek pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak WHERE status = 'Aktif' ORDER BY nama_pajak ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_wajib_pajak = (int)($_POST['id_wajib_pajak'] ?? 0);
    $id_jenis_pajak = (int)($_POST['id_jenis_pajak'] ?? 0);
    $nama_objek     = bersihkan($_POST['nama_objek'] ?? '');
    $alamat_objek   = bersihkan($_POST['alamat_objek'] ?? '');
    $nilai_objek    = (float)str_replace(['.', ','], ['', '.'], $_POST['nilai_objek'] ?? 0);
    $keterangan     = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($id_wajib_pajak) || empty($id_jenis_pajak) || empty($nama_objek) || empty($alamat_objek)) {
        $error = 'Semua bidang bertanda bintang (*) wajib diisi!';
    } else {
        try {
            $sql = "UPDATE objek_pajak SET id_wajib_pajak = ?, id_jenis_pajak = ?, nama_objek = ?, alamat_objek = ?, nilai_objek = ?, keterangan = ? WHERE id_objek_pajak = ?";
            eksekusi($sql, [$id_wajib_pajak, $id_jenis_pajak, $nama_objek, $alamat_objek, $nilai_objek, $keterangan, $id]);
            set_flash('success', 'Objek pajak berhasil diperbarui!');
            header('Location: index.php?wajib_pajak=' . $id_wajib_pajak);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Objek Pajak</h4>
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
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold">Wajib Pajak Pemilik (Foreign Key) <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" class="form-select" required>
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <?php foreach ($daftar_wp as $wp): ?>
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($data['id_wajib_pajak'] ?? 0) == $wp['id_wajib_pajak']) ? 'selected' : '' ?>>
                                        <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold">Jenis Pajak Terkait <span class="text-danger">*</span></label>
                            <select name="id_jenis_pajak" class="form-select" required>
                                <option value="">-- Pilih Jenis Pajak --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_pajak'] ?>" <?= (($data['id_jenis_pajak'] ?? 0) == $j['id_jenis_pajak']) ? 'selected' : '' ?>>
                                        [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold">Nama Objek Pajak <span class="text-danger">*</span></label>
                            <input type="text" name="nama_objek" class="form-control" value="<?= escape($data['nama_objek']) ?>" required>
                        </div>

                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold">Nilai Objek / Dasar Perhitungan (Rp)</label>
                            <input type="number" step="0.01" name="nilai_objek" class="form-control" value="<?= (float)$data['nilai_objek'] ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Lokasi / Alamat Objek <span class="text-danger">*</span></label>
                            <textarea name="alamat_objek" class="form-control" rows="2" required><?= escape($data['alamat_objek']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="2"><?= escape($data['keterangan']) ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
