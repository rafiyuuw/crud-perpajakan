<?php
$judul_halaman = 'Ubah Denda Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT d.*, t.nomor_tagihan, wp.nama_wajib_pajak 
    FROM denda_pajak d
    JOIN tagihan_pajak t ON d.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE d.id_denda = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data denda pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_tagihan = ambil_semua("
    SELECT t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan, t.status_tagihan,
           wp.nama_wajib_pajak
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY t.id_tagihan DESC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $id_tagihan        = (int)($_POST['id_tagihan'] ?? 0);
    $alasan_denda      = bersihkan($_POST['alasan_denda'] ?? '');
    $dasar_perhitungan = bersihkan($_POST['dasar_perhitungan'] ?? '');
    $jumlah_denda      = (float)($_POST['jumlah_denda'] ?? 0);
    $tanggal_pengenaan = bersihkan($_POST['tanggal_pengenaan'] ?? '');
    $status_denda      = bersihkan($_POST['status_denda'] ?? 'Aktif');

    if (empty($id_tagihan) || empty($alasan_denda) || empty($tanggal_pengenaan)) {
        $error = 'Tagihan pajak, alasan denda, dan tanggal pengenaan wajib diisi!';
    } elseif ($jumlah_denda <= 0) {
        $error = 'Jumlah denda harus lebih besar dari 0!';
    } else {
        $sql = "UPDATE denda_pajak 
                SET id_tagihan = ?, alasan_denda = ?, dasar_perhitungan = ?, jumlah_denda = ?, tanggal_pengenaan = ?, status_denda = ?
                WHERE id_denda = ?";
        eksekusi($sql, [$id_tagihan, $alasan_denda, $dasar_perhitungan, $jumlah_denda, $tanggal_pengenaan, $status_denda, $id]);

        set_flash('success', 'Data denda pajak berhasil diperbarui!');
        header('Location: detail.php?id=' . $id);
        exit;
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Ubah Sanksi / Denda Pajak</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Perubahan Data Denda Pajak</h5>
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
                        <label for="id_tagihan" class="form-label fw-semibold">Pilih Tagihan Pajak (SKP / STP) <span class="text-danger">*</span></label>
                        <select name="id_tagihan" id="id_tagihan" class="form-select select2-enable" required>
                            <?php foreach ($daftar_tagihan as $t): ?>
                                <option value="<?= $t['id_tagihan'] ?>" <?= ($data['id_tagihan'] == $t['id_tagihan']) ? 'selected' : '' ?>>
                                    <?= escape($t['nomor_tagihan']) ?> &bull; <?= escape($t['nama_wajib_pajak']) ?> &bull; <?= format_rupiah($t['jumlah_tagihan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="alasan_denda" class="form-label fw-semibold">Alasan Pengenaan Sanksi / Denda <span class="text-danger">*</span></label>
                        <input type="text" name="alasan_denda" id="alasan_denda" class="form-control" value="<?= escape($data['alasan_denda']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="dasar_perhitungan" class="form-label fw-semibold">Dasar Hukum &amp; Formula Perhitungan</label>
                        <input type="text" name="dasar_perhitungan" id="dasar_perhitungan" class="form-control" value="<?= escape($data['dasar_perhitungan']) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="jumlah_denda" class="form-label fw-semibold">Nominal Jumlah Denda (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="jumlah_denda" id="jumlah_denda" class="form-control font-monospace" value="<?= escape($data['jumlah_denda']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pengenaan" class="form-label fw-semibold">Tanggal Pengenaan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengenaan" id="tanggal_pengenaan" class="form-control" value="<?= escape($data['tanggal_pengenaan']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status_denda" class="form-label fw-semibold">Status Denda</label>
                        <select name="status_denda" id="status_denda" class="form-select">
                            <option value="Aktif" <?= ($data['status_denda'] === 'Aktif') ? 'selected' : '' ?>>Aktif (Belum Diselesaikan)</option>
                            <option value="Lunas" <?= ($data['status_denda'] === 'Lunas') ? 'selected' : '' ?>>Lunas (Sudah Dibayarkan)</option>
                            <option value="Dibatalkan" <?= ($data['status_denda'] === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan (Peninjauan / Keringanan)</option>
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
