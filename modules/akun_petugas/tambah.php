<?php
$judul_halaman = 'Buat Akun Petugas';
require_once __DIR__ . '/../../includes/header.php';

$id_petugas_param = (int)($_GET['id_petugas'] ?? 0);

// Ambil petugas yang belum memiliki akun (karena relasi One-to-One)
$petugas_tersedia = ambil_semua("
    SELECT p.id_petugas, p.nama_petugas, p.nomor_identitas, p.jabatan, k.nama_kantor
    FROM petugas_pajak p
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    LEFT JOIN akun_petugas a ON p.id_petugas = a.id_petugas
    WHERE a.id_akun IS NULL OR p.id_petugas = ?
    ORDER BY p.nama_petugas ASC
", [$id_petugas_param]);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_petugas     = (int)($_POST['id_petugas'] ?? 0);
    $nama_pengguna  = bersihkan($_POST['nama_pengguna'] ?? '');
    $kata_sandi     = $_POST['kata_sandi'] ?? '';
    $peran          = bersihkan($_POST['peran'] ?? '');
    $status_akun    = bersihkan($_POST['status_akun'] ?? 'Aktif');
    
    if (empty($id_petugas) || empty($nama_pengguna) || empty($kata_sandi) || empty($peran)) {
        $error = 'Semua bidang wajib diisi!';
    } elseif (strlen($kata_sandi) < 6) {
        $error = 'Kata sandi minimal terdiri dari 6 karakter!';
    } else {
        // Cek apakah petugas sudah punya akun
        $cek_petugas = ambil_satu("SELECT id_akun FROM akun_petugas WHERE id_petugas = ?", [$id_petugas]);
        if ($cek_petugas) {
            $error = 'Petugas ini sudah memiliki akun sistem (Relasi 1-to-1)!';
        } else {
            // Cek keunikan username
            $cek_user = ambil_satu("SELECT id_akun FROM akun_petugas WHERE nama_pengguna = ?", [$nama_pengguna]);
            if ($cek_user) {
                $error = 'Nama pengguna sudah digunakan oleh akun lain!';
            } else {
                try {
                    $hash = password_hash($kata_sandi, PASSWORD_BCRYPT);
                    $sql = "INSERT INTO akun_petugas (id_petugas, nama_pengguna, kata_sandi, peran, status_akun) VALUES (?, ?, ?, ?, ?)";
                    eksekusi($sql, [$id_petugas, $nama_pengguna, $hash, $peran, $status_akun]);
                    set_flash('success', 'Akun petugas baru berhasil dibuat!');
                    header('Location: index.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Gagal membuat akun: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Buat Akun Petugas Pajak</h4>
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
                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Petugas Pajak (1-to-1) <span class="text-danger">*</span></label>
                            <select name="id_petugas" class="form-select" required>
                                <option value="">-- Pilih Petugas yang Belum Memiliki Akun --</option>
                                <?php foreach ($petugas_tersedia as $p): ?>
                                    <option value="<?= $p['id_petugas'] ?>" <?= (($id_petugas_param == $p['id_petugas']) || (($_POST['id_petugas'] ?? '') == $p['id_petugas'])) ? 'selected' : '' ?>>
                                        <?= escape($p['nama_petugas']) ?> (NIP: <?= escape($p['nomor_identitas']) ?>) &mdash; <?= escape($p['nama_kantor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Satu petugas hanya dapat memiliki tepat satu akun akses sistem.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Pengguna (Username) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="nama_pengguna" class="form-control" placeholder="Contoh: rina_tax" value="<?= escape($_POST['nama_pengguna'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Kata Sandi Baru <span class="text-danger">*</span></label>
                            <input type="password" name="kata_sandi" class="form-control" placeholder="Minimal 6 karakter..." required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                            <select name="peran" class="form-select" required>
                                <option value="Administrator" <?= (($_POST['peran'] ?? '') === 'Administrator') ? 'selected' : '' ?>>Administrator</option>
                                <option value="Petugas Pajak" <?= (($_POST['peran'] ?? 'Petugas Pajak') === 'Petugas Pajak') ? 'selected' : '' ?>>Petugas Pajak</option>
                                <option value="Pemeriksa Pajak" <?= (($_POST['peran'] ?? '') === 'Pemeriksa Pajak') ? 'selected' : '' ?>>Pemeriksa Pajak</option>
                                <option value="Kepala Seksi" <?= (($_POST['peran'] ?? '') === 'Kepala Seksi') ? 'selected' : '' ?>>Kepala Seksi</option>
                                <option value="Kasir Pajak" <?= (($_POST['peran'] ?? '') === 'Kasir Pajak') ? 'selected' : '' ?>>Kasir Pajak</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Akun</label>
                            <select name="status_akun" class="form-select">
                                <option value="Aktif" <?= (($_POST['status_akun'] ?? 'Aktif') === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= (($_POST['status_akun'] ?? '') === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
