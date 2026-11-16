<?php
$judul_halaman = 'Ubah Masa Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM masa_pajak WHERE id_masa_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data masa pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak ORDER BY nama_pajak ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_jenis_pajak   = (int)($_POST['id_jenis_pajak'] ?? 0);
    $tahun_pajak      = (int)($_POST['tahun_pajak'] ?? date('Y'));
    $tanggal_mulai    = bersihkan($_POST['tanggal_mulai'] ?? '');
    $tanggal_berakhir = bersihkan($_POST['tanggal_berakhir'] ?? '');
    $batas_pelaporan  = bersihkan($_POST['batas_pelaporan'] ?? '');
    $status           = bersihkan($_POST['status'] ?? 'Berjalan');
    
    if (empty($id_jenis_pajak) || empty($tahun_pajak) || empty($tanggal_mulai) || empty($tanggal_berakhir) || empty($batas_pelaporan)) {
        $error = 'Semua bidang formulir wajib diisi!';
    } else {
        try {
            $sql = "UPDATE masa_pajak SET id_jenis_pajak = ?, tahun_pajak = ?, tanggal_mulai = ?, tanggal_berakhir = ?, batas_pelaporan = ?, status = ? WHERE id_masa_pajak = ?";
            eksekusi($sql, [$id_jenis_pajak, $tahun_pajak, $tanggal_mulai, $tanggal_berakhir, $batas_pelaporan, $status, $id]);
            set_flash('success', 'Masa pajak berhasil diperbarui!');
            header('Location: index.php?jenis=' . $id_jenis_pajak);
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
            <h4 class="fw-bold mb-0 text-dark">Ubah Masa Pajak</h4>
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
                            <label class="form-label fw-semibold">Jenis Pajak (Foreign Key) <span class="text-danger">*</span></label>
                            <select name="id_jenis_pajak" class="form-select" required>
                                <option value="">-- Pilih Jenis Pajak --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_pajak'] ?>" <?= (($data['id_jenis_pajak'] ?? 0) == $j['id_jenis_pajak']) ? 'selected' : '' ?>>
                                        [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tahun Pajak <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_pajak" class="form-control" value="<?= escape($data['tahun_pajak']) ?>" min="2000" max="2099" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai Masa <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= escape($data['tanggal_mulai']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tanggal Berakhir Masa <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_berakhir" class="form-control" value="<?= escape($data['tanggal_berakhir']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Batas Akhir Pelaporan SPT <span class="text-danger">*</span></label>
                            <input type="date" name="batas_pelaporan" class="form-control" value="<?= escape($data['batas_pelaporan']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Masa Pajak</label>
                            <select name="status" class="form-select">
                                <option value="Berjalan" <?= ($data['status'] === 'Berjalan') ? 'selected' : '' ?>>Berjalan</option>
                                <option value="Terbuka" <?= ($data['status'] === 'Terbuka') ? 'selected' : '' ?>>Terbuka</option>
                                <option value="Tutup Buku" <?= ($data['status'] === 'Tutup Buku') ? 'selected' : '' ?>>Tutup Buku</option>
                            </select>
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
