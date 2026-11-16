<?php
$judul_halaman = 'Ubah Data Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM wajib_pajak WHERE id_wajib_pajak = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data wajib pajak tidak ditemukan!');
    header('Location: index.php');
    exit;
}

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
        // Cek duplikasi nomor_npwp selain id ini
        $cek = ambil_satu("SELECT id_wajib_pajak FROM wajib_pajak WHERE nomor_npwp = ? AND id_wajib_pajak != ?", [$nomor_npwp, $id]);
        if ($cek) {
            $error = 'Nomor NPWP sudah terdaftar untuk wajib pajak lain!';
        } else {
            try {
                $sql = "UPDATE wajib_pajak SET id_jenis_wajib_pajak = ?, id_kantor = ?, nomor_npwp = ?, nama_wajib_pajak = ?, tanggal_pendaftaran = ?, tanggal_lahir = ?, jenis_kelamin = ?, status_wajib_pajak = ? WHERE id_wajib_pajak = ?";
                eksekusi($sql, [$id_jenis_wajib_pajak, $id_kantor, $nomor_npwp, $nama_wajib_pajak, $tanggal_pendaftaran, $tanggal_lahir, $jenis_kelamin, $status_wajib_pajak, $id]);
                set_flash('success', 'Data Wajib Pajak berhasil diperbarui!');
                header('Location: detail.php?id=' . $id);
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui data: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Data Wajib Pajak</h4>
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
                                    <option value="<?= $j['id_jenis_wajib_pajak'] ?>" <?= (($data['id_jenis_wajib_pajak'] ?? 0) == $j['id_jenis_wajib_pajak']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $k['id_kantor'] ?>" <?= (($data['id_kantor'] ?? 0) == $k['id_kantor']) ? 'selected' : '' ?>>
                                        [<?= escape($k['kode_kantor']) ?>] <?= escape($k['nama_kantor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nomor Pokok Wajib Pajak (NPWP) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_npwp" class="form-control font-monospace" value="<?= escape($data['nomor_npwp']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Lengkap / Nama Badan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_wajib_pajak" class="form-control" value="<?= escape($data['nama_wajib_pajak']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Pendaftaran <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pendaftaran" class="form-control" value="<?= escape($data['tanggal_pendaftaran']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir / Pendirian</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?= escape($data['tanggal_lahir'] ?? '') ?>">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">-- Tidak Ditentukan / Badan --</option>
                                <option value="Laki-laki" <?= ($data['jenis_kelamin'] === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($data['jenis_kelamin'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Wajib Pajak</label>
                            <select name="status_wajib_pajak" class="form-select">
                                <option value="Aktif" <?= ($data['status_wajib_pajak'] === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= ($data['status_wajib_pajak'] === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                                <option value="Suspen" <?= ($data['status_wajib_pajak'] === 'Suspen') ? 'selected' : '' ?>>Suspen</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
