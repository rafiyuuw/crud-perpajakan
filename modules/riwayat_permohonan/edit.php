<?php
$judul_halaman = 'Ubah Riwayat Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT rp.*, pp.nomor_permohonan, wp.nama_wajib_pajak
    FROM riwayat_permohonan rp
    JOIN permohonan_pajak pp ON rp.id_permohonan = pp.id_permohonan
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    WHERE rp.id_riwayat_permohonan = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data riwayat permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_permohonan = ambil_semua("
    SELECT pp.id_permohonan, pp.nomor_permohonan, wp.nama_wajib_pajak
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY pp.id_permohonan DESC
");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $id_permohonan     = (int)($_POST['id_permohonan'] ?? 0);
    $id_petugas        = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : null;
    $status_sebelumnya = bersihkan($_POST['status_sebelumnya'] ?? '');
    $status_baru       = bersihkan($_POST['status_baru'] ?? '');
    $tanggal_perubahan = bersihkan($_POST['tanggal_perubahan'] ?? '');
    $catatan           = bersihkan($_POST['catatan'] ?? '');

    if (empty($id_permohonan) || empty($status_baru) || empty($tanggal_perubahan)) {
        $error = 'Permohonan pajak, status baru, dan waktu perubahan wajib diisi!';
    } else {
        $sql = "UPDATE riwayat_permohonan 
                SET id_permohonan = ?, id_petugas = ?, status_sebelumnya = ?, status_baru = ?, 
                    tanggal_perubahan = ?, catatan = ?
                WHERE id_riwayat_permohonan = ?";
        eksekusi($sql, [$id_permohonan, $id_petugas, $status_sebelumnya, $status_baru, $tanggal_perubahan, $catatan, $id]);

        set_flash('success', 'Catatan riwayat permohonan berhasil diperbarui!');
        header('Location: detail.php?id=' . $id);
        exit;
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Ubah Riwayat Permohonan</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Perubahan Riwayat Permohonan</h5>
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
                        <label for="id_permohonan" class="form-label fw-semibold">Pilih Berkas Permohonan Pajak <span class="text-danger">*</span></label>
                        <select name="id_permohonan" id="id_permohonan" class="form-select select2-enable" required>
                            <?php foreach ($daftar_permohonan as $p): ?>
                                <option value="<?= $p['id_permohonan'] ?>" <?= ($data['id_permohonan'] == $p['id_permohonan']) ? 'selected' : '' ?>>
                                    <?= escape($p['nomor_permohonan']) ?> &bull; <?= escape($p['nama_wajib_pajak']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="status_sebelumnya" class="form-label fw-semibold">Status Sebelumnya</label>
                            <input type="text" name="status_sebelumnya" id="status_sebelumnya" class="form-control" value="<?= escape($data['status_sebelumnya']) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="status_baru" class="form-label fw-semibold">Status Baru <span class="text-danger">*</span></label>
                            <select name="status_baru" id="status_baru" class="form-select" required>
                                <option value="Diajukan" <?= ($data['status_baru'] === 'Diajukan') ? 'selected' : '' ?>>Diajukan</option>
                                <option value="Diverifikasi" <?= ($data['status_baru'] === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                                <option value="Disetujui" <?= ($data['status_baru'] === 'Disetujui') ? 'selected' : '' ?>>Disetujui</option>
                                <option value="Ditolak" <?= ($data['status_baru'] === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                                <option value="Selesai" <?= ($data['status_baru'] === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="id_petugas" class="form-label fw-semibold">Petugas yang Memproses</label>
                            <select name="id_petugas" id="id_petugas" class="form-select select2-enable">
                                <option value="">-- Sistem / Otomatis --</option>
                                <?php foreach ($daftar_petugas as $pt): ?>
                                    <option value="<?= $pt['id_petugas'] ?>" <?= ($data['id_petugas'] == $pt['id_petugas']) ? 'selected' : '' ?>>
                                        <?= escape($pt['nama_petugas']) ?> (<?= escape($pt['jabatan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_perubahan" class="form-label fw-semibold">Waktu Perubahan <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_perubahan" id="tanggal_perubahan" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($data['tanggal_perubahan'])) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="catatan" class="form-label fw-semibold">Catatan / Disposisi Pemeriksaan</label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control"><?= escape($data['catatan']) ?></textarea>
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
