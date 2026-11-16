<?php
$judul_halaman = 'Tambah Permohonan Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_wp_param = (int)($_GET['id_wajib_pajak'] ?? 0);
$id_jenis_param = (int)($_GET['id_jenis_permohonan'] ?? 0);

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_permohonan, nama_permohonan FROM jenis_permohonan WHERE status = 'Aktif' ORDER BY nama_permohonan ASC");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak WHERE status = 'Aktif' ORDER BY nama_petugas ASC");

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

    if (empty($nomor_permohonan) || empty($id_wajib_pajak) || empty($id_jenis_permohonan) || empty($tanggal_pengajuan)) {
        $error = 'Nomor permohonan, wajib pajak, jenis layanan, dan tanggal pengajuan wajib diisi!';
    } else {
        // Cek duplikasi nomor
        $ada = ambil_satu("SELECT id_permohonan FROM permohonan_pajak WHERE nomor_permohonan = ?", [$nomor_permohonan]);
        if ($ada) {
            $error = 'Nomor permohonan sudah terdaftar dalam sistem!';
        } else {
            $sql = "INSERT INTO permohonan_pajak (nomor_permohonan, id_wajib_pajak, id_jenis_permohonan, id_petugas, tanggal_pengajuan, status_permohonan, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_id = eksekusi($sql, [$nomor_permohonan, $id_wajib_pajak, $id_jenis_permohonan, $id_petugas, $tanggal_pengajuan, $status_permohonan, $keterangan]);

            if ($insert_id) {
                // Catat riwayat status awal
                $sql_riwayat = "INSERT INTO riwayat_permohonan (id_permohonan, id_petugas, status_sebelumnya, status_baru, tanggal_perubahan, catatan)
                                VALUES (?, ?, 'Draft / Awal', ?, ?, ?)";
                eksekusi($sql_riwayat, [
                    $insert_id,
                    $id_petugas,
                    $status_permohonan,
                    date('Y-m-d H:i:s'),
                    'Pengajuan berkas permohonan baru oleh wajib pajak.'
                ]);

                set_flash('success', 'Permohonan pajak berhasil diajukan dan dicatat dalam riwayat!');
                header('Location: detail.php?id=' . $insert_id);
                exit;
            } else {
                $error = 'Gagal menyimpan permohonan pajak ke database.';
            }
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Formulir Pengajuan Permohonan Pajak</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Data Pengajuan Berkas Permohonan</h5>
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
                            <input type="text" name="nomor_permohonan" id="nomor_permohonan" class="form-control font-monospace" placeholder="Contoh: REG-202611-001" value="<?= escape($_POST['nomor_permohonan'] ?? ('REG-' . date('Ym') . '-' . rand(1000, 9999))) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_pengajuan" class="form-label fw-semibold">Tanggal Pengajuan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pengajuan" id="tanggal_pengajuan" class="form-control" value="<?= escape($_POST['tanggal_pengajuan'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="id_wajib_pajak" class="form-label fw-semibold">Wajib Pajak Pemohon <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" id="id_wajib_pajak" class="form-select select2-enable" required>
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <?php foreach ($daftar_wp as $w): ?>
                                    <option value="<?= $w['id_wajib_pajak'] ?>" <?= ($id_wp_param == $w['id_wajib_pajak'] || (isset($_POST['id_wajib_pajak']) && $_POST['id_wajib_pajak'] == $w['id_wajib_pajak'])) ? 'selected' : '' ?>>
                                        <?= escape($w['nama_wajib_pajak']) ?> (<?= escape($w['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="id_jenis_permohonan" class="form-label fw-semibold">Jenis Layanan Permohonan <span class="text-danger">*</span></label>
                            <select name="id_jenis_permohonan" id="id_jenis_permohonan" class="form-select select2-enable" required>
                                <option value="">-- Pilih Jenis Layanan --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_permohonan'] ?>" <?= ($id_jenis_param == $j['id_jenis_permohonan'] || (isset($_POST['id_jenis_permohonan']) && $_POST['id_jenis_permohonan'] == $j['id_jenis_permohonan'])) ? 'selected' : '' ?>>
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
                                <option value="">-- Belum Ditugaskan / Otomatis --</option>
                                <?php foreach ($daftar_petugas as $p): ?>
                                    <option value="<?= $p['id_petugas'] ?>" <?= (isset($_POST['id_petugas']) && $_POST['id_petugas'] == $p['id_petugas']) ? 'selected' : '' ?>>
                                        <?= escape($p['nama_petugas']) ?> (<?= escape($p['jabatan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Bisa dikosongkan jika permohonan baru menunggu disposisi.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="status_permohonan" class="form-label fw-semibold">Status Awal Permohonan</label>
                            <select name="status_permohonan" id="status_permohonan" class="form-select">
                                <option value="Diajukan" <?= (($_POST['status_permohonan'] ?? 'Diajukan') === 'Diajukan') ? 'selected' : '' ?>>Diajukan (Baru Masuk)</option>
                                <option value="Diverifikasi" <?= (($_POST['status_permohonan'] ?? '') === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi (Dalam Penelitian Dokumen)</option>
                                <option value="Disetujui" <?= (($_POST['status_permohonan'] ?? '') === 'Disetujui') ? 'selected' : '' ?>>Disetujui (Diterima)</option>
                                <option value="Ditolak" <?= (($_POST['status_permohonan'] ?? '') === 'Ditolak') ? 'selected' : '' ?>>Ditolak (Syarat Tidak Terpenuhi)</option>
                                <option value="Selesai" <?= (($_POST['status_permohonan'] ?? '') === 'Selesai') ? 'selected' : '' ?>>Selesai (Surat Keputusan Diterbitkan)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label fw-semibold">Rincian / Keterangan Permohonan</label>
                        <textarea name="keterangan" id="keterangan" rows="4" class="form-control" placeholder="Tuliskan latar belakang permohonan, alasan permohonan, atau dokumen kelengkapan yang disertakan..."><?= escape($_POST['keterangan'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Permohonan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
