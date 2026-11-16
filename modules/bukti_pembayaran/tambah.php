<?php
$judul_halaman = 'Unggah Bukti Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$id_bayar_param = (int)($_GET['id_pembayaran'] ?? 0);

// Ambil daftar pembayaran
$daftar_pembayaran = ambil_semua("
    SELECT p.id_pembayaran, p.nomor_transaksi, p.jumlah_pembayaran, p.tanggal_pembayaran,
           wp.nama_wajib_pajak, t.nomor_tagihan
    FROM pembayaran_pajak p
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY p.id_pembayaran DESC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $id_pembayaran   = (int)($_POST['id_pembayaran'] ?? 0);
    $nomor_bukti     = bersihkan($_POST['nomor_bukti'] ?? '');
    $nama_file_input = bersihkan($_POST['nama_file'] ?? '');
    $tanggal_upload  = date('Y-m-d H:i:s');

    if (empty($id_pembayaran) || empty($nomor_bukti)) {
        $error = 'Data pembayaran dan nomor bukti (NTPN/BPN) wajib diisi!';
    } else {
        $nama_file_final = $nama_file_input;
        $lokasi_file_final = 'assets/uploads/bukti/';

        // Cek upload berkas
        if (!empty($_FILES['berkas']['name'])) {
            $file_tmp = $_FILES['berkas']['tmp_name'];
            $file_name_orig = $_FILES['berkas']['name'];
            $ext = strtolower(pathinfo($file_name_orig, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = 'Format file tidak didukung! Gunakan format JPG, PNG, atau PDF.';
            } else {
                $new_filename = 'bukti_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $target_dir = __DIR__ . '/../../assets/uploads/bukti/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_path = $target_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $target_path)) {
                    $nama_file_final = !empty($nama_file_input) ? $nama_file_input : $file_name_orig;
                    $lokasi_file_final = 'assets/uploads/bukti/' . $new_filename;
                } else {
                    $nama_file_final = $file_name_orig;
                    $lokasi_file_final = 'assets/uploads/bukti/' . $file_name_orig;
                }
            }
        } elseif (empty($nama_file_final)) {
            $nama_file_final = 'struk_' . time() . '.pdf';
            $lokasi_file_final = 'assets/uploads/bukti/' . $nama_file_final;
        } else {
            $lokasi_file_final = 'assets/uploads/bukti/' . $nama_file_final;
        }

        if (empty($error)) {
            $sql = "INSERT INTO bukti_pembayaran (id_pembayaran, nomor_bukti, nama_file, lokasi_file, tanggal_upload) 
                    VALUES (?, ?, ?, ?, ?)";
            $insert_id = eksekusi($sql, [$id_pembayaran, $nomor_bukti, $nama_file_final, $lokasi_file_final, $tanggal_upload]);

            if ($insert_id) {
                set_flash('success', 'Bukti pembayaran / NTPN berhasil disimpan!');
                header('Location: detail.php?id=' . $insert_id);
                exit;
            } else {
                $error = 'Gagal menyimpan bukti pembayaran ke database.';
            }
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Unggah / Rekam Bukti Pembayaran</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Bukti Pembayaran Pajak (NTPN / BPN)</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div><?= escape($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="id_pembayaran" class="form-label fw-semibold">Pilih Transaksi Pembayaran <span class="text-danger">*</span></label>
                        <select name="id_pembayaran" id="id_pembayaran" class="form-select select2-enable" required>
                            <option value="">-- Pilih Pembayaran Terkait --</option>
                            <?php foreach ($daftar_pembayaran as $p): ?>
                                <option value="<?= $p['id_pembayaran'] ?>" <?= ($id_bayar_param == $p['id_pembayaran'] || (isset($_POST['id_pembayaran']) && $_POST['id_pembayaran'] == $p['id_pembayaran'])) ? 'selected' : '' ?>>
                                    <?= escape($p['nomor_transaksi']) ?> &bull; <?= escape($p['nama_wajib_pajak']) ?> &bull; <?= format_rupiah($p['jumlah_pembayaran']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Pilih transaksi setoran pajak yang memiliki bukti pembayaran/struk ini.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nomor_bukti" class="form-label fw-semibold">Nomor Bukti / NTPN <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_bukti" id="nomor_bukti" class="form-control font-monospace" placeholder="Contoh: NTPN-20261116-8971" value="<?= escape($_POST['nomor_bukti'] ?? ('NTPN-' . date('Ymd') . '-' . rand(1000, 9999))) ?>" required>
                        <div class="form-text">Nomor Transaksi Penerimaan Negara (NTPN) atau Nomor Resi Bank/Kantor Pos.</div>
                    </div>

                    <div class="mb-3">
                        <label for="berkas" class="form-label fw-semibold">Unggah File Bukti / Struk Fisik</label>
                        <input type="file" name="berkas" id="berkas" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
                        <div class="form-text">Format yang diperbolehkan: JPG, PNG, PDF (Maks. 5MB).</div>
                    </div>

                    <div class="mb-4">
                        <label for="nama_file" class="form-label fw-semibold">Keterangan / Label Nama File</label>
                        <input type="text" name="nama_file" id="nama_file" class="form-control" placeholder="Contoh: Struk Transfer BSI Setoran Pajak" value="<?= escape($_POST['nama_file'] ?? '') ?>">
                        <div class="form-text">Opsional. Jika dikosongkan, nama file asli akan digunakan.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Bukti Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
