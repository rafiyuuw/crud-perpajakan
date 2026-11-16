<?php
$judul_halaman = 'Tambah Denda Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_tagihan_param = (int)($_GET['id_tagihan'] ?? 0);

// Ambil daftar tagihan pajak
$daftar_tagihan = ambil_semua("
    SELECT t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan, t.status_tagihan, t.tanggal_jatuh_tempo,
           wp.nama_wajib_pajak, wp.nomor_npwp, jp.kode_pajak
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
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
        $sql = "INSERT INTO denda_pajak (id_tagihan, alasan_denda, dasar_perhitungan, jumlah_denda, tanggal_pengenaan, status_denda)
                VALUES (?, ?, ?, ?, ?, ?)";
        $insert_id = eksekusi($sql, [$id_tagihan, $alasan_denda, $dasar_perhitungan, $jumlah_denda, $tanggal_pengenaan, $status_denda]);

        if ($insert_id) {
            set_flash('success', 'Sanksi denda pajak berhasil ditambahkan!');
            header('Location: detail.php?id=' . $insert_id);
            exit;
        } else {
            $error = 'Gagal menyimpan data denda pajak ke database.';
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Tambah Sanksi / Denda Pajak</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Pengenaan Denda Administrasi / Bunga</h5>
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
                            <option value="">-- Pilih Tagihan Pajak --</option>
                            <?php foreach ($daftar_tagihan as $t): ?>
                                <option value="<?= $t['id_tagihan'] ?>" <?= ($id_tagihan_param == $t['id_tagihan'] || (isset($_POST['id_tagihan']) && $_POST['id_tagihan'] == $t['id_tagihan'])) ? 'selected' : '' ?>>
                                    <?= escape($t['nomor_tagihan']) ?> &bull; <?= escape($t['nama_wajib_pajak']) ?> &bull; <?= format_rupiah($t['jumlah_tagihan']) ?> (<?= escape($t['status_tagihan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih tagihan pajak yang dikenakan sanksi bunga atau denda keterlambatan.</div>
                    </div>

                    <div class="mb-3">
                        <label for="alasan_denda" class="form-label fw-semibold">Alasan Pengenaan Sanksi / Denda <span class="text-danger">*</span></label>
                        <input type="text" name="alasan_denda" id="alasan_denda" class="form-control" placeholder="Contoh: Keterlambatan pembayaran tagihan melewati batas jatuh tempo" value="<?= escape($_POST['alasan_denda'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="dasar_perhitungan" class="form-label fw-semibold">Dasar Hukum &amp; Formula Perhitungan</label>
                        <input type="text" name="dasar_perhitungan" id="dasar_perhitungan" class="form-control" placeholder="Contoh: Sanksi Bunga 1% x 2 bulan dari pokok tagihan (Pasal 19 UU KUP/HPP)" value="<?= escape($_POST['dasar_perhitungan'] ?? '') ?>">
                        <div class="form-text">Rujukan aturan hukum dan penjelasan perhitungan sanksi.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="jumlah_denda" class="form-label fw-semibold">Nominal Jumlah Denda (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="jumlah_denda" id="jumlah_denda" class="form-control font-monospace" placeholder="0" value="<?= escape($_POST['jumlah_denda'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pengenaan" class="form-label fw-semibold">Tanggal Pengenaan / Terbit <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengenaan" id="tanggal_pengenaan" class="form-control" value="<?= escape($_POST['tanggal_pengenaan'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status_denda" class="form-label fw-semibold">Status Denda</label>
                        <select name="status_denda" id="status_denda" class="form-select">
                            <option value="Aktif" <?= (($_POST['status_denda'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif (Belum Diselesaikan)</option>
                            <option value="Lunas" <?= (($_POST['status_denda'] ?? '') === 'Lunas') ? 'selected' : '' ?>>Lunas (Sudah Dibayarkan)</option>
                            <option value="Dibatalkan" <?= (($_POST['status_denda'] ?? '') === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan (Peninjauan / Keringanan)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Denda Pajak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
