<?php
$judul_halaman = 'Ubah Permohonan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM permohonan_pajak WHERE id_permohonan = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data permohonan tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_permohonan, nama_permohonan FROM jenis_permohonan ORDER BY nama_permohonan ASC");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak ORDER BY nama_petugas ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $nomor_permohonan    = bersihkan($_POST['nomor_permohonan'] ?? '');
    $id_wajib_pajak      = (int)($_POST['id_wajib_pajak'] ?? 0);
    $id_jenis_permohonan = (int)($_POST['id_jenis_permohonan'] ?? 0);
    $id_petugas          = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : null;
    $tanggal_pengajuan   = bersihkan($_POST['tanggal_pengajuan'] ?? '');
    $status_permohonan   = bersihkan($_POST['status_permohonan'] ?? 'Diajukan');
    $keterangan          = bersihkan($_POST['keterangan'] ?? '');
    $catatan_perubahan   = bersihkan($_POST['catatan_perubahan'] ?? '');

    if (empty($nomor_permohonan) || empty($id_wajib_pajak) || empty($id_jenis_permohonan) || empty($tanggal_pengajuan)) {
        $error = 'Nomor permohonan, wajib pajak, jenis layanan, dan tanggal pengajuan wajib diisi!';
    } else {
        // Cek duplikasi nomor kecuali id ini
        $ada = ambil_satu("SELECT id_permohonan FROM permohonan_pajak WHERE nomor_permohonan = ? AND id_permohonan != ?", [$nomor_permohonan, $id]);
        if ($ada) {
            $error = 'Nomor permohonan sudah terdaftar dalam sistem!';
        } else {
            $status_lama = $data['status_permohonan'];

            $sql = "UPDATE permohonan_pajak 
                    SET nomor_permohonan = ?, id_wajib_pajak = ?, id_jenis_permohonan = ?, id_petugas = ?,
                        tanggal_pengajuan = ?, status_permohonan = ?, keterangan = ?
                    WHERE id_permohonan = ?";
            eksekusi($sql, [$nomor_permohonan, $id_wajib_pajak, $id_jenis_permohonan, $id_petugas, $tanggal_pengajuan, $status_permohonan, $keterangan, $id]);

            // Jika status berubah, catat ke riwayat_permohonan
            if ($status_lama !== $status_permohonan) {
                $sql_riwayat = "INSERT INTO riwayat_permohonan (id_permohonan, id_petugas, status_sebelumnya, status_baru, tanggal_perubahan, catatan)
                                VALUES (?, ?, ?, ?, ?, ?)";
                $catatan_final = !empty($catatan_perubahan) 
                    ? $catatan_perubahan 
                    : "Status permohonan diubah dari $status_lama menjadi $status_permohonan.";
                eksekusi($sql_riwayat, [
                    $id,
                    $id_petugas,
                    $status_lama,
                    $status_permohonan,
                    date('Y-m-d H:i:s'),
                    $catatan_final
                ]);
            }

            set_flash('success', 'Permohonan pajak berhasil diperbarui!');
            header('Location: detail.php?id=' . $id);
            exit;
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Ubah Permohonan Pajak</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Perubahan Berkas Permohonan</h5>
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

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="nomor_permohonan" class="form-label fw-semibold">Nomor Registrasi Permohonan <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_permohonan" id="nomor_permohonan" class="form-control font-monospace" value="<?= escape($data['nomor_permohonan']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pengajuan" class="form-label fw-semibold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengajuan" id="tanggal_pengajuan" class="form-control" value="<?= escape($data['tanggal_pengajuan']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="id_wajib_pajak" class="form-label fw-semibold">Wajib Pajak Pemohon <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" id="id_wajib_pajak" class="form-select select2-enable" required>
                                <?php foreach ($daftar_wp as $w): ?>
                                    <option value="<?= $w['id_wajib_pajak'] ?>" <?= ($data['id_wajib_pajak'] == $w['id_wajib_pajak']) ? 'selected' : '' ?>>
                                        <?= escape($w['nama_wajib_pajak']) ?> (<?= escape($w['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="id_jenis_permohonan" class="form-label fw-semibold">Jenis Layanan Permohonan <span class="text-danger">*</span></label>
                            <select name="id_jenis_permohonan" id="id_jenis_permohonan" class="form-select select2-enable" required>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_permohonan'] ?>" <?= ($data['id_jenis_permohonan'] == $j['id_jenis_permohonan']) ? 'selected' : '' ?>>
                                        <?= escape($j['nama_permohonan']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="id_petugas" class="form-label fw-semibold">Petugas Penelaah / Pemeriksa</label>
                            <select name="id_petugas" id="id_petugas" class="form-select select2-enable">
                                <option value="">-- Belum Ditugaskan --</option>
                                <?php foreach ($daftar_petugas as $p): ?>
                                    <option value="<?= $p['id_petugas'] ?>" <?= ($data['id_petugas'] == $p['id_petugas']) ? 'selected' : '' ?>>
                                        <?= escape($p['nama_petugas']) ?> (<?= escape($p['jabatan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="status_permohonan" class="form-label fw-semibold">Status Permohonan</label>
                            <select name="status_permohonan" id="status_permohonan" class="form-select">
                                <option value="Diajukan" <?= ($data['status_permohonan'] === 'Diajukan') ? 'selected' : '' ?>>Diajukan</option>
                                <option value="Diverifikasi" <?= ($data['status_permohonan'] === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                                <option value="Disetujui" <?= ($data['status_permohonan'] === 'Disetujui') ? 'selected' : '' ?>>Disetujui</option>
                                <option value="Ditolak" <?= ($data['status_permohonan'] === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                                <option value="Selesai" <?= ($data['status_permohonan'] === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan_perubahan" class="form-label fw-semibold">Catatan Perubahan Status (Riwayat)</label>
                        <input type="text" name="catatan_perubahan" id="catatan_perubahan" class="form-control" placeholder="Tuliskan catatan alasan jika mengubah status permohonan...">
                        <div class="form-text">Catatan ini akan otomatis masuk ke tabel Riwayat Permohonan jika status berubah.</div>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold">Keterangan Tambahan Permohonan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" class="form-control"><?= escape($data['keterangan']) ?></textarea>
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
