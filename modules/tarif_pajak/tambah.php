<?php
$judul_halaman = 'Tambah Tarif Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_jenis_param = (int)($_GET['id_jenis_pajak'] ?? 0);
$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak WHERE status = 'Aktif' ORDER BY nama_pajak ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_jenis_pajak    = (int)($_POST['id_jenis_pajak'] ?? 0);
    $nama_tarif        = bersihkan($_POST['nama_tarif'] ?? '');
    $jenis_perhitungan = bersihkan($_POST['jenis_perhitungan'] ?? 'Persentase');
    $nilai_tarif       = (float)str_replace(['%', ' '], '', str_replace(',', '.', $_POST['nilai_tarif'] ?? 0));
    $tanggal_mulai     = bersihkan($_POST['tanggal_mulai'] ?? date('Y-m-d'));
    $tanggal_berakhir  = !empty($_POST['tanggal_berakhir']) ? bersihkan($_POST['tanggal_berakhir']) : null;
    $status            = bersihkan($_POST['status'] ?? 'Aktif');
    
    if (empty($id_jenis_pajak) || empty($nama_tarif) || empty($tanggal_mulai)) {
        $error = 'Bidang dengan tanda bintang (*) wajib diisi!';
    } else {
        try {
            $sql = "INSERT INTO tarif_pajak (id_jenis_pajak, nama_tarif, jenis_perhitungan, nilai_tarif, tanggal_mulai, tanggal_berakhir, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            eksekusi($sql, [$id_jenis_pajak, $nama_tarif, $jenis_perhitungan, $nilai_tarif, $tanggal_mulai, $tanggal_berakhir, $status]);
            set_flash('success', 'Tarif pajak baru berhasil disimpan ke dalam basis data!');
            header('Location: index.php?jenis=' . $id_jenis_pajak);
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
            <h4 class="fw-bold mb-0 text-dark">Tambah Tarif Pajak</h4>
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
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jenis Pajak (Foreign Key) <span class="text-danger">*</span></label>
                            <select name="id_jenis_pajak" class="form-select" required>
                                <option value="">-- Pilih Jenis Pajak --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_pajak'] ?>" <?= (($id_jenis_param == $j['id_jenis_pajak']) || (($_POST['id_jenis_pajak'] ?? '') == $j['id_jenis_pajak'])) ? 'selected' : '' ?>>
                                        [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama / Lapisan Tarif <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tarif" class="form-control" placeholder="Contoh: PPh 21 Tarif 5% Lapisan 1" value="<?= escape($_POST['nama_tarif'] ?? '') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Metode Perhitungan <span class="text-danger">*</span></label>
                            <select name="jenis_perhitungan" class="form-select" required>
                                <option value="Persentase">Persentase (%)</option>
                                <option value="Nominal Tetap">Nominal Tetap (Rp)</option>
                                <option value="Progresif">Progresif</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nilai Tarif (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.0001" name="nilai_tarif" class="form-control" placeholder="Contoh: 11.0000" value="<?= escape($_POST['nilai_tarif'] ?? '') ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Tarif</label>
                            <select name="status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai Berlaku <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= escape($_POST['tanggal_mulai'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tanggal Berakhir Berlaku</label>
                            <input type="date" name="tanggal_berakhir" class="form-control" value="<?= escape($_POST['tanggal_berakhir'] ?? '') ?>">
                            <small class="text-muted">Biarkan kosong jika berlaku seterusnya.</small>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mt-4">
                        <i class="bi bi-info-circle me-1"></i><strong>Konsep Basis Data:</strong> Tarif dikelola langsung melalui tabel database dan tidak di-hardcode dalam logika script. Perubahan tarif di kemudian hari tidak akan merusak riwayat transaksi SPT lama karena nilai tarif tercatat di detail transaksi.
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Tarif Pajak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
