<?php
$judul_halaman = 'Ubah Akun Petugas';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("
    SELECT a.*, p.nama_petugas, p.nomor_identitas, p.jabatan, k.nama_kantor
    FROM akun_petugas a
    JOIN petugas_pajak p ON a.id_petugas = p.id_petugas
    JOIN kantor_pajak k ON p.id_kantor = k.id_kantor
    WHERE a.id_akun = ?
", [$id]);

if (!$data) {
    set_flash('danger', 'Data akun petugas tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nama_pengguna = bersihkan($_POST['nama_pengguna'] ?? '');
    $kata_sandi    = $_POST['kata_sandi'] ?? '';
    $peran         = bersihkan($_POST['peran'] ?? '');
    $status_akun   = bersihkan($_POST['status_akun'] ?? 'Aktif');
    
    if (empty($nama_pengguna) || empty($peran)) {
        $error = 'Nama pengguna dan peran wajib diisi!';
    } else {
        // Cek keunikan username selain akun ini
        $cek_user = ambil_satu("SELECT id_akun FROM akun_petugas WHERE nama_pengguna = ? AND id_akun != ?", [$nama_pengguna, $id]);
        if ($cek_user) {
            $error = 'Nama pengguna sudah digunakan oleh akun lain!';
        } else {
            try {
                if (!empty($kata_sandi)) {
                    if (strlen($kata_sandi) < 6) {
                        $error = 'Kata sandi baru minimal 6 karakter!';
                    } else {
                        $hash = password_hash($kata_sandi, PASSWORD_BCRYPT);
                        $sql = "UPDATE akun_petugas SET nama_pengguna = ?, kata_sandi = ?, peran = ?, status_akun = ? WHERE id_akun = ?";
                        eksekusi($sql, [$nama_pengguna, $hash, $peran, $status_akun, $id]);
                    }
                } else {
                    $sql = "UPDATE akun_petugas SET nama_pengguna = ?, peran = ?, status_akun = ? WHERE id_akun = ?";
                    eksekusi($sql, [$nama_pengguna, $peran, $status_akun, $id]);
                }
                
                if (empty($error)) {
                    set_flash('success', 'Data akun petugas berhasil diperbarui!');
                    header('Location: index.php');
                    exit;
                }
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
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Akun Petugas</h4>
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
                            <label class="form-label fw-semibold">Petugas Pajak Terkait</label>
                            <input type="text" class="form-control bg-light" value="<?= escape($data['nama_petugas']) ?> (NIP: <?= escape($data['nomor_identitas']) ?>) &mdash; <?= escape($data['nama_kantor']) ?>" disabled>
                            <div class="form-text text-muted">Petugas tidak dapat diubah karena relasi 1-to-1 bersifat permanen.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nama Pengguna (Username) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="nama_pengguna" class="form-control" value="<?= escape($data['nama_pengguna']) ?>" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Ganti Kata Sandi (Opsional)</label>
                            <input type="password" name="kata_sandi" class="form-control" placeholder="Kosongkan jika tidak diubah...">
                            <div class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah kata sandi.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Peran / Hak Akses (Role) <span class="text-danger">*</span></label>
                            <select name="peran" class="form-select" required>
                                <option value="Administrator" <?= ($data['peran'] === 'Administrator') ? 'selected' : '' ?>>Administrator</option>
                                <option value="Petugas Pajak" <?= ($data['peran'] === 'Petugas Pajak') ? 'selected' : '' ?>>Petugas Pajak</option>
                                <option value="Pemeriksa Pajak" <?= ($data['peran'] === 'Pemeriksa Pajak') ? 'selected' : '' ?>>Pemeriksa Pajak</option>
                                <option value="Kepala Seksi" <?= ($data['peran'] === 'Kepala Seksi') ? 'selected' : '' ?>>Kepala Seksi</option>
                                <option value="Kasir Pajak" <?= ($data['peran'] === 'Kasir Pajak') ? 'selected' : '' ?>>Kasir Pajak</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Akun</label>
                            <select name="status_akun" class="form-select">
                                <option value="Aktif" <?= ($data['status_akun'] === 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="Nonaktif" <?= ($data['status_akun'] === 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
