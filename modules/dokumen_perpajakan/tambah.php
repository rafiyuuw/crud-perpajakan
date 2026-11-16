<?php
$judul_halaman = 'Unggah Dokumen Perpajakan';
require_once __DIR__ . '/../../includes/header.php';

$id_wp_param = (int)($_GET['id_wajib_pajak'] ?? 0);
$id_spt_param = (int)($_GET['id_spt'] ?? 0);

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_dokumen, nama_dokumen FROM jenis_dokumen ORDER BY nama_dokumen ASC");
$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, wp.nama_wajib_pajak, jp.kode_pajak 
    FROM spt s 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak 
    ORDER BY s.id_spt DESC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_wajib_pajak   = (int)($_POST['id_wajib_pajak'] ?? 0);
    $id_jenis_dokumen = (int)($_POST['id_jenis_dokumen'] ?? 0);
    $id_spt           = !empty($_POST['id_spt']) ? (int)$_POST['id_spt'] : null;
    $nama_file_input  = bersihkan($_POST['nama_file'] ?? '');
    $keterangan       = bersihkan($_POST['keterangan'] ?? '');
    $tanggal_upload   = date('Y-m-d H:i:s');
    
    if (empty($id_wajib_pajak) || empty($id_jenis_dokumen)) {
        $error = 'Wajib pajak dan jenis dokumen wajib dipilih!';
    } else {
        $nama_file_final = $nama_file_input;
        $lokasi_file_final = 'assets/uploads/dokumen/';
        
        // Cek apakah ada file fisik yang diunggah
        if (!empty($_FILES['berkas']['name'])) {
            $file_tmp = $_FILES['berkas']['tmp_name'];
            $file_name_orig = $_FILES['berkas']['name'];
            $ext = strtolower(pathinfo($file_name_orig, PATHINFO_EXTENSION));
            
            // Format nama file unik
            $new_filename = 'dok_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $target_dir = __DIR__ . '/../../assets/uploads/dokumen/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_path = $target_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $target_path)) {
                $nama_file_final = !empty($nama_file_input) ? $nama_file_input : $file_name_orig;
                $lokasi_file_final = 'assets/uploads/dokumen/' . $new_filename;
            } else {
                $nama_file_final = $file_name_orig;
                $lokasi_file_final = 'assets/uploads/dokumen/' . $file_name_orig;
            }
        } elseif (empty($nama_file_final)) {
            $nama_file_final = 'lampiran_' . time() . '.pdf';
            $lokasi_file_final = 'assets/uploads/dokumen/' . $nama_file_final;
        } else {
            $lokasi_file_final = 'assets/uploads/dokumen/' . $nama_file_final;
        }

        try {
            $sql = "INSERT INTO dokumen_perpajakan (id_wajib_pajak, id_jenis_dokumen, id_spt, nama_file, lokasi_file, tanggal_upload, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)";
            eksekusi($sql, [$id_wajib_pajak, $id_jenis_dokumen, $id_spt, $nama_file_final, $lokasi_file_final, $tanggal_upload, $keterangan]);
            set_flash('success', 'Dokumen perpajakan berhasil diunggah!');
            header('Location: index.php?wajib_pajak=' . $id_wajib_pajak);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Unggah Dokumen Perpajakan</h4>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= escape($error) ?>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Wajib Pajak (Pemilik Berkas) <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" class="form-select" required>
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <?php foreach ($daftar_wp as $wp): ?>
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($id_wp_param == $wp['id_wajib_pajak']) || (($_POST['id_wajib_pajak'] ?? '') == $wp['id_wajib_pajak'])) ? 'selected' : '' ?>>
                                        <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jenis Dokumen <span class="text-danger">*</span></label>
                            <select name="id_jenis_dokumen" class="form-select" required>
                                <option value="">-- Pilih Jenis Dokumen --</option>
                                <?php foreach ($daftar_jenis as $jd): ?>
                                    <option value="<?= $jd['id_jenis_dokumen'] ?>" <?= (($_POST['id_jenis_dokumen'] ?? '') == $jd['id_jenis_dokumen']) ? 'selected' : '' ?>>
                                        <?= escape($jd['nama_dokumen']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Terkait Laporan SPT (Opsional)</label>
                            <select name="id_spt" class="form-select">
                                <option value="">-- Berkas Umum (Tidak Terikat SPT) --</option>
                                <?php foreach ($daftar_spt as $s): ?>
                                    <option value="<?= $s['id_spt'] ?>" <?= (($id_spt_param == $s['id_spt']) || (($_POST['id_spt'] ?? '') == $s['id_spt'])) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> - <?= escape($s['nama_wajib_pajak']) ?> (<?= escape($s['kode_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Judul / Nama Tampilan Berkas</label>
                            <input type="text" name="nama_file" class="form-control" placeholder="Contoh: Bukti Potong Gaji 2026.pdf" value="<?= escape($_POST['nama_file'] ?? '') ?>">
                            <small class="text-muted">Jika kosong, akan menggunakan nama file asli.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Berkas File (PDF / Gambar / Dokumen)</label>
                            <input type="file" name="berkas" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                            <div class="form-text text-muted">Format didukung: PDF, PNG, JPG, Word, Excel. Maksimal 10 MB.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan Tambahan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan kelengkapan lampiran berkas..."><?= escape($_POST['keterangan'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Unggah Dokumen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
