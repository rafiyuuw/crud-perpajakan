<?php
$judul_halaman = 'Ubah Dokumen Perpajakan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM dokumen_perpajakan WHERE id_dokumen = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data dokumen tidak ditemukan!');
    header('Location: index.php');
    exit;
}

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
    
    if (empty($id_wajib_pajak) || empty($id_jenis_dokumen) || empty($nama_file_input)) {
        $error = 'Bidang bertanda bintang (*) wajib diisi!';
    } else {
        $lokasi_file_final = $data['lokasi_file'];
        
        // Cek jika ganti file fisik
        if (!empty($_FILES['berkas']['name'])) {
            $file_tmp = $_FILES['berkas']['tmp_name'];
            $file_name_orig = $_FILES['berkas']['name'];
            $ext = strtolower(pathinfo($file_name_orig, PATHINFO_EXTENSION));
            $new_filename = 'dok_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $target_dir = __DIR__ . '/../../assets/uploads/dokumen/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_path = $target_dir . $new_filename;
            if (move_uploaded_file($file_tmp, $target_path)) {
                $lokasi_file_final = 'assets/uploads/dokumen/' . $new_filename;
            }
        }

        try {
            $sql = "UPDATE dokumen_perpajakan SET id_wajib_pajak = ?, id_jenis_dokumen = ?, id_spt = ?, nama_file = ?, lokasi_file = ?, keterangan = ? WHERE id_dokumen = ?";
            eksekusi($sql, [$id_wajib_pajak, $id_jenis_dokumen, $id_spt, $nama_file_input, $lokasi_file_final, $keterangan, $id]);
            set_flash('success', 'Dokumen perpajakan berhasil diperbarui!');
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
            <h4 class="fw-bold mb-0 text-dark">Ubah Dokumen Perpajakan</h4>
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
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($data['id_wajib_pajak'] ?? 0) == $wp['id_wajib_pajak']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $jd['id_jenis_dokumen'] ?>" <?= (($data['id_jenis_dokumen'] ?? 0) == $jd['id_jenis_dokumen']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $s['id_spt'] ?>" <?= (($data['id_spt'] ?? 0) == $s['id_spt']) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> - <?= escape($s['nama_wajib_pajak']) ?> (<?= escape($s['kode_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Judul / Nama Tampilan Berkas <span class="text-danger">*</span></label>
                            <input type="text" name="nama_file" class="form-control" value="<?= escape($data['nama_file']) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ganti File Fisik (Opsional)</label>
                            <input type="file" name="berkas" class="form-control">
                            <div class="form-text text-muted">File saat ini: <code><?= escape($data['lokasi_file']) ?></code>. Biarkan kosong jika tidak ingin mengganti file.</div>
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
