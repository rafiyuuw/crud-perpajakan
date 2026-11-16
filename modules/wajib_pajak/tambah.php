<?php
$judul_halaman = 'Pendaftaran Wajib Pajak Baru';
require_once __DIR__ . '/../../includes/header.php';

$daftar_jenis = ambil_semua("SELECT id_jenis_wajib_pajak, nama_jenis FROM jenis_wajib_pajak ORDER BY nama_jenis ASC");
$daftar_kantor = ambil_semua("SELECT id_kantor, nama_kantor, kode_kantor FROM kantor_pajak ORDER BY nama_kantor ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_jenis_wajib_pajak = (int)($_POST['id_jenis_wajib_pajak'] ?? 0);
    $id_kantor            = (int)($_POST['id_kantor'] ?? 0);
    $nomor_npwp           = bersihkan($_POST['nomor_npwp'] ?? '');
    $nama_wajib_pajak     = bersihkan($_POST['nama_wajib_pajak'] ?? '');
    $tanggal_pendaftaran  = bersihkan($_POST['tanggal_pendaftaran'] ?? date('Y-m-d'));
    $tanggal_lahir        = !empty($_POST['tanggal_lahir']) ? bersihkan($_POST['tanggal_lahir']) : null;
    $jenis_kelamin        = !empty($_POST['jenis_kelamin']) ? bersihkan($_POST['jenis_kelamin']) : null;
    $status_wajib_pajak   = bersihkan($_POST['status_wajib_pajak'] ?? 'Aktif');
    
    if (empty($id_jenis_wajib_pajak) || empty($id_kantor) || empty($nomor_npwp) || empty($nama_wajib_pajak) || empty($tanggal_pendaftaran)) {
        $error = 'Bidang dengan tanda bintang (*) wajib diisi!';
    } else {
        // Cek duplikasi nomor_npwp
        $cek = ambil_satu("SELECT id_wajib_pajak FROM wajib_pajak WHERE nomor_npwp = ?", [$nomor_npwp]);
        if ($cek) {
            $error = 'Nomor NPWP sudah terdaftar dalam sistem!';
        } else {
            try {
                $sql = "INSERT INTO wajib_pajak (id_jenis_wajib_pajak, id_kantor, nomor_npwp, nama_wajib_pajak, tanggal_pendaftaran, tanggal_lahir, jenis_kelamin, status_wajib_pajak) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                eksekusi($sql, [$id_jenis_wajib_pajak, $id_kantor, $nomor_npwp, $nama_wajib_pajak, $tanggal_pendaftaran, $tanggal_lahir, $jenis_kelamin, $status_wajib_pajak]);
                $new_id = $koneksi->lastInsertId();
                set_flash('success', 'Wajib Pajak <strong>' . escape($nama_wajib_pajak) . '</strong> berhasil didaftarkan!');
                header('Location: detail.php?id=' . $new_id);
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal mendaftarkan Wajib Pajak: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Pendaftaran Wajib Pajak Baru</h4>
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
                            <label class="form-label fw-semibold">Jenis Wajib Pajak <span class="text-danger">*</span></label>
                            <select name="id_jenis_wajib_pajak" class="form-select" required>
                                <option value="">-- Pilih Jenis Subjek Pajak --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_wajib_pajak'] ?>" <?= (($_POST['id_jenis_wajib_pajak'] ?? '') == $j['id_jenis_wajib_pajak']) ? 'selected' : '' ?>>
                                        <?= escape($j['nama_jenis']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Kantor Pelayanan Pajak (KPP) <span class="text-danger">*</span></label>
                            <select name="id_kantor" class="form-select" required>
                                <option value="">-- Pilih KPP Terdaftar --</option>
                                <?php foreach ($daftar_kantor as $k): ?>
                                    <option value="<?= $k['id_kantor'] ?>" <?= (($_POST['id_kantor'] ?? '') == $k['id_kantor']) ? 'selected' : '' ?>>
                                        [<?= escape($k['kode_kantor']) ?>] <?= escape($k['nama_kantor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nomor Pokok Wajib Pajak (NPWP) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_npwp" class="form-control font-monospace" placeholder="Contoh: 09.123.456.7-011.000" value="<?= escape($_POST['nomor_npwp'] ?? '') ?>" required>
                            <small class="text-muted">Nomor identitas perpajakan (disimpan sebagai VARCHAR).</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap / Nama Badan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_wajib_pajak" class="form-control" placeholder="Nama orang pribadi atau nama badan usaha..." value="<?= escape($_POST['nama_wajib_pajak'] ?? '') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Pendaftaran <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pendaftaran" class="form-control" value="<?= escape($_POST['tanggal_pendaftaran'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir / Pendirian</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?= escape($_POST['tanggal_lahir'] ?? '') ?>">
                            <small class="text-muted">Opsional untuk badan hukum.</small>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">-- Tidak Ditentukan / Badan --</option>
                                <option value="Laki-laki" <?= (($_POST['jenis_kelamin'] ?? '') === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= (($_POST['jenis_kelamin'] ?? '') === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Wajib Pajak</label>
                            <select name="status_wajib_pajak" class="form-select">
                                <option value="Aktif" <?= (($_POST['status_wajib_pajak'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= (($_POST['status_wajib_pajak'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                <option value="Suspen" <?= (($_POST['status_wajib_pajak'] ?? '') === 'Suspen') ? 'selected' : '' ?>>Suspen</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Daftarkan Wajib Pajak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
