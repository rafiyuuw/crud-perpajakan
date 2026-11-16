<?php
$judul_halaman = 'Ubah Pembayaran Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM pembayaran_pajak WHERE id_pembayaran = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data pembayaran tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_tagihan = ambil_semua("
    SELECT t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan, wp.nama_wajib_pajak
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    ORDER BY t.id_tagihan DESC
");

$daftar_metode = ambil_semua("SELECT id_metode, nama_metode FROM metode_pembayaran ORDER BY nama_metode ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_tagihan         = (int)($_POST['id_tagihan'] ?? 0);
    $id_metode          = (int)($_POST['id_metode'] ?? 0);
    $nomor_transaksi    = bersihkan($_POST['nomor_transaksi'] ?? '');
    $tanggal_pembayaran = bersihkan($_POST['tanggal_pembayaran'] ?? date('Y-m-d H:i:s'));
    $jumlah_pembayaran  = (float)str_replace(['.', ','], ['', '.'], $_POST['jumlah_pembayaran'] ?? 0);
    $status_pembayaran  = bersihkan($_POST['status_pembayaran'] ?? 'Berhasil');
    
    if (empty($id_tagihan) || empty($id_metode) || empty($nomor_transaksi) || $jumlah_pembayaran <= 0) {
        $error = 'Tagihan, Metode, Nomor Transaksi, dan Jumlah Pembayaran wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_pembayaran FROM pembayaran_pajak WHERE nomor_transaksi = ? AND id_pembayaran != ?", [$nomor_transaksi, $id]);
        if ($cek) {
            $error = 'Nomor transaksi sudah digunakan pada data lain!';
        } else {
            try {
                $sql = "UPDATE pembayaran_pajak SET id_tagihan = ?, id_metode = ?, nomor_transaksi = ?, tanggal_pembayaran = ?, jumlah_pembayaran = ?, status_pembayaran = ? WHERE id_pembayaran = ?";
                eksekusi($sql, [$id_tagihan, $id_metode, $nomor_transaksi, $tanggal_pembayaran, $jumlah_pembayaran, $status_pembayaran, $id]);
                
                // Evaluasi Otomatis Status Tagihan
                $tagihan_row = ambil_satu("SELECT jumlah_tagihan FROM tagihan_pajak WHERE id_tagihan = ?", [$id_tagihan]);
                $total_masuk = ambil_satu("SELECT COALESCE(SUM(jumlah_pembayaran), 0) as total FROM pembayaran_pajak WHERE id_tagihan = ? AND status_pembayaran = 'Berhasil'", [$id_tagihan])['total'] ?? 0;
                
                if ($total_masuk >= (float)$tagihan_row['jumlah_tagihan']) {
                    eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Lunas' WHERE id_tagihan = ?", [$id_tagihan]);
                } elseif ($total_masuk > 0) {
                    eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Dibayar Sebagian' WHERE id_tagihan = ?", [$id_tagihan]);
                } else {
                    eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Belum Bayar' WHERE id_tagihan = ?", [$id_tagihan]);
                }
                
                set_flash('success', 'Data transaksi pembayaran berhasil diperbarui!');
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
            <h4 class="fw-bold mb-0 text-dark">Ubah Data Pembayaran Pajak</h4>
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
                            <label class="form-label fw-semibold">Nomor Transaksi <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_transaksi" class="form-control font-monospace fw-bold" value="<?= escape($data['nomor_transaksi']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Waktu / Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_pembayaran" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($data['tanggal_pembayaran'])) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Surat Tagihan Pajak (STP) <span class="text-danger">*</span></label>
                            <select name="id_tagihan" class="form-select" required>
                                <?php foreach ($daftar_tagihan as $tg): ?>
                                    <option value="<?= $tg['id_tagihan'] ?>" <?= (($data['id_tagihan'] ?? 0) == $tg['id_tagihan']) ? 'selected' : '' ?>>
                                        <?= escape($tg['nomor_tagihan']) ?> &mdash; <?= escape($tg['nama_wajib_pajak']) ?> (Tagihan: <?= format_rupiah($tg['jumlah_tagihan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="id_metode" class="form-select" required>
                                <?php foreach ($daftar_metode as $mt): ?>
                                    <option value="<?= $mt['id_metode'] ?>" <?= (($data['id_metode'] ?? 0) == $mt['id_metode']) ? 'selected' : '' ?>>
                                        <?= escape($mt['nama_metode']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jumlah Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="jumlah_pembayaran" class="form-control fw-bold" value="<?= (float)$data['jumlah_pembayaran'] ?>" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Konfirmasi Pembayaran</label>
                            <select name="status_pembayaran" class="form-select">
                                <option value="Berhasil" <?= ($data['status_pembayaran'] === 'Berhasil') ? 'selected' : '' ?>>Berhasil</option>
                                <option value="Menunggu Konfirmasi" <?= ($data['status_pembayaran'] === 'Menunggu Konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                <option value="Gagal" <?= ($data['status_pembayaran'] === 'Gagal') ? 'selected' : '' ?>>Gagal</option>
                                <option value="Dibatalkan" <?= ($data['status_pembayaran'] === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
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
