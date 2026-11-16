<?php
$judul_halaman = 'Tambah Kontak Wajib Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_wp_param = (int)($_GET['id_wajib_pajak'] ?? 0);
$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak ORDER BY nama_wajib_pajak ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_wajib_pajak = (int)($_POST['id_wajib_pajak'] ?? 0);
    $nomor_telepon  = bersihkan($_POST['nomor_telepon'] ?? '');
    $email          = bersihkan($_POST['email'] ?? '');
    $jenis_kontak   = bersihkan($_POST['jenis_kontak'] ?? 'Ponsel Pribadi');
    
    if (empty($id_wajib_pajak) || empty($nomor_telepon) || empty($email) || empty($jenis_kontak)) {
        $error = 'Semua bidang formulir wajib diisi!';
    } else {
        try {
            $sql = "INSERT INTO kontak_wajib_pajak (id_wajib_pajak, nomor_telepon, email, jenis_kontak) VALUES (?, ?, ?, ?)";
            eksekusi($sql, [$id_wajib_pajak, $nomor_telepon, $email, $jenis_kontak]);
            set_flash('success', 'Kontak wajib pajak berhasil ditambahkan!');
            header('Location: index.php?wajib_pajak=' . $id_wajib_pajak);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan data: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Kontak Wajib Pajak</h4>
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
                        <label class="form-label fw-semibold">Pilih Wajib Pajak (Foreign Key) <span class="text-danger">*</span></label>
                        <select name="id_wajib_pajak" class="form-select" required>
                            <option value="">-- Pilih Wajib Pajak --</option>
                            <?php foreach ($daftar_wp as $wp): ?>
                                <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($id_wp_param == $wp['id_wajib_pajak']) || (($_POST['id_wajib_pajak'] ?? '') == $wp['id_wajib_pajak'])) ? 'selected' : '' ?>>
                                    <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jenis Kontak <span class="text-danger">*</span></label>
                        <select name="jenis_kontak" class="form-select" required>
                            <option value="Ponsel Pribadi">Ponsel Pribadi</option>
                            <option value="Telepon Kantor">Telepon Kantor</option>
                            <option value="Telepon Rumah">Telepon Rumah</option>
                            <option value="Email Resmi Perusahaan">Email Resmi Perusahaan</option>
                            <option value="PIC Perpajakan (Tax Officer)">PIC Perpajakan (Tax Officer)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" name="nomor_telepon" class="form-control" placeholder="Contoh: 081234567890 atau 021-5551234" value="<?= escape($_POST['nomor_telepon'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: kontak@perusahaan.co.id" value="<?= escape($_POST['email'] ?? '') ?>" required>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Kontak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
