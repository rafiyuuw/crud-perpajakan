<?php
$judul_halaman = 'Ubah Catatan Riwayat Status SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT r.*, s.nomor_spt, wp.nama_wajib_pajak
    FROM riwayat_status_spt r
    JOIN spt s ON r.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE r.id_riwayat_spt = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data riwayat tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_status = ambil_semua("SELECT id_status_spt, nama_status FROM status_spt ORDER BY id_status_spt ASC");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_status_spt = (int)($_POST['id_status_spt'] ?? 0);
    $id_petugas    = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : null;
    $catatan       = bersihkan($_POST['catatan'] ?? '');
    
    if (empty($id_status_spt)) {
        $error = 'Status SPT wajib dipilih!';
    } else {
        try {
            $sql = "UPDATE riwayat_status_spt SET id_status_spt = ?, id_petugas = ?, catatan = ? WHERE id_riwayat_spt = ?";
            eksekusi($sql, [$id_status_spt, $id_petugas, $catatan, $id]);
            set_flash('success', 'Catatan riwayat status SPT berhasil diperbarui!');
            header('Location: ../spt/detail.php?id=' . $data['id_spt']);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-7">
        <div class="d-flex align-items-center mb-3">
            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Catatan Riwayat Status</h4>
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
                        <label class="form-label fw-semibold">Laporan SPT Terkait</label>
                        <input type="text" class="form-control bg-light" value="<?= escape($data['nomor_spt']) ?> &mdash; <?= escape($data['nama_wajib_pajak']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status SPT <span class="text-danger">*</span></label>
                        <select name="id_status_spt" class="form-select" required>
                            <?php foreach ($daftar_status as $st): ?>
                                <option value="<?= $st['id_status_spt'] ?>" <?= (($data['id_status_spt'] ?? 0) == $st['id_status_spt']) ? 'selected' : '' ?>>
                                    <?= escape($st['nama_status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Petugas Verifikator</label>
                        <select name="id_petugas" class="form-select">
                            <option value="">-- Tanpa Petugas Khusus --</option>
                            <?php foreach ($daftar_petugas as $p): ?>
                                <option value="<?= $p['id_petugas'] ?>" <?= (($data['id_petugas'] ?? 0) == $p['id_petugas']) ? 'selected' : '' ?>>
                                    <?= escape($p['nama_petugas']) ?> (<?= escape($p['jabatan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Perubahan</label>
                        <textarea name="catatan" class="form-control" rows="3"><?= escape($data['catatan']) ?></textarea>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
