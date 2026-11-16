<?php
$judul_halaman = 'Ubah Tagihan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM tagihan_pajak WHERE id_tagihan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data tagihan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, s.total_pajak, wp.nama_wajib_pajak
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY s.id_spt DESC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_spt              = (int)($_POST['id_spt'] ?? 0);
    $nomor_tagihan       = bersihkan($_POST['nomor_tagihan'] ?? '');
    $tanggal_tagihan     = bersihkan($_POST['tanggal_tagihan'] ?? date('Y-m-d'));
    $tanggal_jatuh_tempo = bersihkan($_POST['tanggal_jatuh_tempo'] ?? '');
    $jumlah_tagihan      = (float)str_replace(['.', ','], ['', '.'], $_POST['jumlah_tagihan'] ?? 0);
    $status_tagihan      = bersihkan($_POST['status_tagihan'] ?? 'Belum Bayar');
    
    if (empty($id_spt) || empty($nomor_tagihan) || empty($tanggal_tagihan) || empty($tanggal_jatuh_tempo) || $jumlah_tagihan <= 0) {
        $error = 'Semua bidang formulir wajib diisi dengan benar!';
    } else {
        $cek = ambil_satu("SELECT id_tagihan FROM tagihan_pajak WHERE nomor_tagihan = ? AND id_tagihan != ?", [$nomor_tagihan, $id]);
        if ($cek) {
            $error = 'Nomor tagihan sudah digunakan pada data lain!';
        } else {
            try {
                $sql = "UPDATE tagihan_pajak SET id_spt = ?, nomor_tagihan = ?, tanggal_tagihan = ?, tanggal_jatuh_tempo = ?, jumlah_tagihan = ?, status_tagihan = ? WHERE id_tagihan = ?";
                eksekusi($sql, [$id_spt, $nomor_tagihan, $tanggal_tagihan, $tanggal_jatuh_tempo, $jumlah_tagihan, $status_tagihan, $id]);
                set_flash('success', 'Data tagihan pajak berhasil diperbarui!');
                header('Location: detail.php?id=' . $id);
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Tagihan Pajak</h4>
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
                            <label class="form-label fw-semibold">Nomor Tagihan <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_tagihan" class="form-control font-monospace fw-bold" value="<?= escape($data['nomor_tagihan']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Laporan SPT Terkait <span class="text-danger">*</span></label>
                            <select name="id_spt" class="form-select" required>
                                <?php foreach ($daftar_spt as $s): ?>
                                    <option value="<?= $s['id_spt'] ?>" <?= (($data['id_spt'] ?? 0) == $s['id_spt']) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> &mdash; <?= escape($s['nama_wajib_pajak']) ?> (<?= format_rupiah($s['total_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Tagihan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_tagihan" class="form-control" value="<?= escape($data['tanggal_tagihan']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="<?= escape($data['tanggal_jatuh_tempo']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Tagihan</label>
                            <select name="status_tagihan" class="form-select">
                                <option value="Belum Bayar" <?= ($data['status_tagihan'] === 'Belum Bayar') ? 'selected' : '' ?>>Belum Bayar</option>
                                <option value="Dibayar Sebagian" <?= ($data['status_tagihan'] === 'Dibayar Sebagian') ? 'selected' : '' ?>>Dibayar Sebagian</option>
                                <option value="Lunas" <?= ($data['status_tagihan'] === 'Lunas') ? 'selected' : '' ?>>Lunas</option>
                                <option value="Kadaluarsa" <?= ($data['status_tagihan'] === 'Kadaluarsa') ? 'selected' : '' ?>>Kadaluarsa</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jumlah Nominal Tagihan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="jumlah_tagihan" class="form-control" value="<?= (float)$data['jumlah_tagihan'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
