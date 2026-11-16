<?php
$judul_halaman = 'Tambah Petugas Pajak';
require_once __DIR__ . '/../../includes/header.php';

$daftar_kantor = ambil_semua("SELECT id_kantor, nama_kantor, kode_kantor FROM kantor_pajak ORDER BY nama_kantor ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_kantor       = (int)($_POST['id_kantor'] ?? 0);
    $nama_petugas    = bersihkan($_POST['nama_petugas'] ?? '');
    $nomor_identitas = bersihkan($_POST['nomor_identitas'] ?? '');
    $jabatan         = bersihkan($_POST['jabatan'] ?? '');
    $nomor_telepon   = bersihkan($_POST['nomor_telepon'] ?? '');
    $email           = bersihkan($_POST['email'] ?? '');
    $status          = bersihkan($_POST['status'] ?? 'Aktif');
    
    if (empty($id_kantor) || empty($nama_petugas) || empty($nomor_identitas) || empty($jabatan) || empty($nomor_telepon) || empty($email)) {
        $error = 'Semua bidang bertanda bintang wajib diisi!';
    } else {
        // Cek duplikasi nomor_identitas (NIP)
        $cek = ambil_satu("SELECT id_petugas FROM petugas_pajak WHERE nomor_identitas = ?", [$nomor_identitas]);
        if ($cek) {
            $error = 'Nomor identitas (NIP) sudah terdaftar untuk petugas lain!';
        } else {
            try {
                $sql = "INSERT INTO petugas_pajak (id_kantor, nama_petugas, nomor_identitas, jabatan, nomor_telepon, email, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
                eksekusi($sql, [$id_kantor, $nama_petugas, $nomor_identitas, $jabatan, $nomor_telepon, $email, $status]);
                set_flash('success', 'Petugas pajak baru berhasil ditambahkan!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Petugas Pajak Baru</h4>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= escape($error) ?></div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="" method="POST">
                    <?= csrf_field() ?>
                    
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Kantor Pajak (Penugasan) <span class="text-danger">*</span></label>
                            <select name="id_kantor" class="form-select" required>
                                <option value="">-- Pilih Kantor Pelayanan Pajak --</option>
                                <?php foreach ($daftar_kantor as $k): ?>
                                    <option value="<?= $k['id_kantor'] ?>" <?= (($_POST['id_kantor'] ?? '') == $k['id_kantor']) ? 'selected' : '' ?>>
                                        [<?= escape($k['kode_kantor']) ?>] <?= escape($k['nama_kantor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nomor Identitas Pegawai (NIP) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_identitas" class="form-control" placeholder="Contoh: 199001012015011001" value="<?= escape($_POST['nomor_identitas'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Lengkap Petugas <span class="text-danger">*</span></label>
                            <input type="text" name="nama_petugas" class="form-control" placeholder="Contoh: Muhammad Ilham, S.E., M.Ak." value="<?= escape($_POST['nama_petugas'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Account Representative" value="<?= escape($_POST['jabatan'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_telepon" class="form-control" placeholder="Contoh: 081234567890" value="<?= escape($_POST['nomor_telepon'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold">Email Resmi <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Contoh: ilham@pajak.go.id" value="<?= escape($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Status Kepegawaian</label>
                            <select name="status" class="form-select">
                                <option value="Aktif" <?= (($_POST['status'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= (($_POST['status'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Petugas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
