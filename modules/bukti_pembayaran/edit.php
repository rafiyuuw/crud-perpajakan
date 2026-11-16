<?php
$judul_halaman = 'Ubah Bukti Pembayaran';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT b.*, p.nomor_transaksi, p.jumlah_pembayaran, wp.nama_wajib_pajak
    FROM bukti_pembayaran b
    JOIN pembayaran_pajak p ON b.id_pembayaran = p.id_pembayaran
    JOIN tagihan_pajak t ON p.id_tagihan = t.id_tagihan
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE b.id_bukti = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data bukti pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_pembayaran = ambil_semua("
    SELECT p.id_pembayaran, p.nomor_transaksi, p.jumlah_pembayaran, wp.nama_wajib_pajak
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

    if (empty($id_pembayaran) || empty($nomor_bukti)) {
        $error = 'Transaksi pembayaran dan nomor bukti (NTPN/BPN) wajib diisi!';
    } else {
        $nama_file_final = !empty($nama_file_input) ? $nama_file_input : $data['nama_file'];
        $lokasi_file_final = $data['lokasi_file'];

        // Cek jika ada unggahan file baru pengganti
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
                    // Hapus file lama jika ada dan berada di uploads
                    $old_file = __DIR__ . '/../../' . $data['lokasi_file'];
                    if (!empty($data['lokasi_file']) && file_exists($old_file) && is_file($old_file)) {
                        @unlink($old_file);
                    }
                    $lokasi_file_final = 'assets/uploads/bukti/' . $new_filename;
                    if (empty($nama_file_input)) {
                        $nama_file_final = $file_name_orig;
                    }
                }
            }
        }

        if (empty($error)) {
            $sql = "UPDATE bukti_pembayaran 
                    SET id_pembayaran = ?, nomor_bukti = ?, nama_file = ?, lokasi_file = ? 
                    WHERE id_bukti = ?";
            eksekusi($sql, [$id_pembayaran, $nomor_bukti, $nama_file_final, $lokasi_file_final, $id]);

            set_flash('success', 'Bukti pembayaran berhasil diperbarui!');
            header('Location: detail.php?id=' . $id);
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Ubah Bukti Pembayaran</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Perubahan Bukti Pembayaran</h5>
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
                            <?php foreach ($daftar_pembayaran as $p): ?>
                                <option value="<?= $p['id_pembayaran'] ?>" <?= ($data['id_pembayaran'] == $p['id_pembayaran']) ? 'selected' : '' ?>>
                                    <?= escape($p['nomor_transaksi']) ?> &bull; <?= escape($p['nama_wajib_pajak']) ?> &bull; <?= format_rupiah($p['jumlah_pembayaran']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nomor_bukti" class="form-label fw-semibold">Nomor Bukti / NTPN <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_bukti" id="nomor_bukti" class="form-control font-monospace" value="<?= escape($data['nomor_bukti']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama_file" class="form-label fw-semibold">Label / Nama File</label>
                        <input type="text" name="nama_file" id="nama_file" class="form-control" value="<?= escape($data['nama_file']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Saat Ini</label>
                        <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                            <span class="small font-monospace text-muted"><i class="bi bi-file-earmark-check me-1"></i><?= escape($data['lokasi_file']) ?></span>
                            <?php if (file_exists(__DIR__ . '/../../' . $data['lokasi_file']) && is_file(__DIR__ . '/../../' . $data['lokasi_file'])): ?>
                                <a href="<?= base_url($data['lokasi_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Buka File</a>
                            <?php else: ?>
                                <span class="badge bg-secondary">File Belum Tersedia Fisik</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="berkas" class="form-label fw-semibold">Ganti File Struk (Opsional)</label>
                        <input type="file" name="berkas" id="berkas" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
                        <div class="form-text">Biarkan kosong jika tidak ingin mengubah berkas struk pembayaran.</div>
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
