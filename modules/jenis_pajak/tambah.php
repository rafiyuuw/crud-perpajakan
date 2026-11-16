<?php
$judul_halaman = 'Tambah Jenis Pajak';
require_once __DIR__ . '/../../includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $kode_pajak = bersihkan($_POST['kode_pajak'] ?? '');
    $nama_pajak = bersihkan($_POST['nama_pajak'] ?? '');
    $deskripsi  = bersihkan($_POST['deskripsi'] ?? '');
    $status     = bersihkan($_POST['status'] ?? 'Aktif');
    
    if (empty($kode_pajak) || empty($nama_pajak)) {
        $error = 'Kode dan nama jenis pajak wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_jenis_pajak FROM jenis_pajak WHERE kode_pajak = ?", [$kode_pajak]);
        if ($cek) {
            $error = 'Kode pajak sudah terdaftar!';
        } else {
            try {
                $sql = "INSERT INTO jenis_pajak (kode_pajak, nama_pajak, deskripsi, status) VALUES (?, ?, ?, ?)";
                eksekusi($sql, [$kode_pajak, $nama_pajak, $deskripsi, $status]);
                set_flash('success', 'Jenis pajak baru berhasil ditambahkan!');
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
            <h4 class="fw-bold mb-0 text-dark">Tambah Jenis Pajak Baru</h4>
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
                        <label class="form-label fw-semibold">Kode Pajak <span class="text-danger">*</span></label>
                        <input type="text" name="kode_pajak" class="form-control" placeholder="Contoh: PPH22 atau BPHTB" value="<?= escape($_POST['kode_pajak'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Jenis Pajak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pajak" class="form-control" placeholder="Contoh: Pajak Penghasilan Pasal 22 (PPh 22)" value="<?= escape($_POST['nama_pajak'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Ruang Lingkup</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Uraian objek dan ketentuan dasar hukum pajak ini..."><?= escape($_POST['deskripsi'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Operasional</label>
                        <select name="status" class="form-select">
                            <option value="Aktif" <?= (($_POST['status'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                            <option value="Nonaktif" <?= (($_POST['status'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Jenis Pajak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
