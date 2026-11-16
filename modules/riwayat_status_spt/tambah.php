<?php
$judul_halaman = 'Catat Perubahan Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$id_spt_param = (int)($_GET['id_spt'] ?? 0);

$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, wp.nama_wajib_pajak, st.nama_status 
    FROM spt s 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN status_spt st ON s.id_status_spt = st.id_status_spt 
    ORDER BY s.id_spt DESC
");
$daftar_status = ambil_semua("SELECT id_status_spt, nama_status FROM status_spt ORDER BY id_status_spt ASC");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_spt        = (int)($_POST['id_spt'] ?? 0);
    $id_status_spt = (int)($_POST['id_status_spt'] ?? 0);
    $id_petugas    = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : ($_SESSION['petugas_id'] ?? null);
    $catatan       = bersihkan($_POST['catatan'] ?? '');
    
    if (empty($id_spt) || empty($id_status_spt)) {
        $error = 'SPT dan Status Baru wajib dipilih!';
    } else {
        try {
            // Simpan riwayat
            $sql = "INSERT INTO riwayat_status_spt (id_spt, id_status_spt, id_petugas, tanggal_perubahan, catatan) VALUES (?, ?, ?, NOW(), ?)";
            eksekusi($sql, [$id_spt, $id_status_spt, $id_petugas, $catatan]);
            
            // Perbarui status terkini pada tabel spt
            eksekusi("UPDATE spt SET id_status_spt = ?, id_petugas = ? WHERE id_spt = ?", [$id_status_spt, $id_petugas, $id_spt]);
            
            set_flash('success', 'Status SPT berhasil diperbarui dan dicatat ke riwayat log!');
            header('Location: ../spt/detail.php?id=' . $id_spt);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan riwayat: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="d-flex align-items-center mb-3">
            <a href="<?= $id_spt_param ? '../spt/detail.php?id=' . $id_spt_param : 'index.php' ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Catat Perubahan Status SPT</h4>
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
                        <label class="form-label fw-semibold">Pilih Laporan SPT <span class="text-danger">*</span></label>
                        <select name="id_spt" class="form-select" required>
                            <option value="">-- Pilih SPT --</option>
                            <?php foreach ($daftar_spt as $s): ?>
                                <option value="<?= $s['id_spt'] ?>" <?= (($id_spt_param == $s['id_spt']) || (($_POST['id_spt'] ?? '') == $s['id_spt'])) ? 'selected' : '' ?>>
                                    <?= escape($s['nomor_spt']) ?> &mdash; <?= escape($s['nama_wajib_pajak']) ?> (Status Saat Ini: <?= escape($s['nama_status']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Baru SPT <span class="text-danger">*</span></label>
                        <select name="id_status_spt" class="form-select" required>
                            <option value="">-- Pilih Status Baru --</option>
                            <?php foreach ($daftar_status as $st): ?>
                                <option value="<?= $st['id_status_spt'] ?>" <?= (($_POST['id_status_spt'] ?? '') == $st['id_status_spt']) ? 'selected' : '' ?>>
                                    <?= escape($st['nama_status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Petugas Verifikator</label>
                        <select name="id_petugas" class="form-select">
                            <?php foreach ($daftar_petugas as $p): ?>
                                <option value="<?= $p['id_petugas'] ?>" <?= (($_SESSION['petugas_id'] ?? 0) == $p['id_petugas']) ? 'selected' : '' ?>>
                                    <?= escape($p['nama_petugas']) ?> (<?= escape($p['jabatan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan / Keterangan Perubahan</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: Berkas SPT dinyatakan lengkap dan diterima setelah pemeriksaan lampiran bukti potong."><?= escape($_POST['catatan'] ?? '') ?></textarea>
                    </div>

                    <div class="alert alert-light border small text-muted">
                        <i class="bi bi-arrow-repeat me-1 text-primary"></i>Status induk pada tabel <code>spt</code> akan otomatis sinkron dan diperbarui sesuai pilihan status baru ini.
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $id_spt_param ? '../spt/detail.php?id=' . $id_spt_param : 'index.php' ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Status Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
