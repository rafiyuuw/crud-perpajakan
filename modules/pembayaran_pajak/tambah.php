<?php
$judul_halaman = 'Input Pembayaran Pajak';
require_once __DIR__ . '/../../includes/header.php';

$id_tagihan_param = (int)($_GET['id_tagihan'] ?? 0);

$daftar_tagihan = ambil_semua("
    SELECT t.id_tagihan, t.nomor_tagihan, t.jumlah_tagihan, t.status_tagihan, wp.nama_wajib_pajak,
           (SELECT COALESCE(SUM(p.jumlah_pembayaran), 0) FROM pembayaran_pajak p WHERE p.id_tagihan = t.id_tagihan AND p.status_pembayaran = 'Berhasil') as sudah_bayar
    FROM tagihan_pajak t
    JOIN spt s ON t.id_spt = s.id_spt
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    WHERE t.status_tagihan != 'Lunas' OR t.id_tagihan = ?
    ORDER BY t.id_tagihan DESC
", [$id_tagihan_param]);

$daftar_metode = ambil_semua("SELECT id_metode, nama_metode FROM metode_pembayaran WHERE status = 'Aktif' ORDER BY nama_metode ASC");

// Auto-generate nomor transaksi
$nomor_trx_default = 'TRX-' . date('Ymd') . '-' . rand(1000, 9999);

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
        $error = 'Tagihan, Metode, Nomor Transaksi, dan Jumlah Pembayaran wajib diisi dengan benar!';
    } else {
        $cek = ambil_satu("SELECT id_pembayaran FROM pembayaran_pajak WHERE nomor_transaksi = ?", [$nomor_transaksi]);
        if ($cek) {
            $error = 'Nomor transaksi sudah pernah tercatat!';
        } else {
            try {
                $sql = "INSERT INTO pembayaran_pajak (id_tagihan, id_metode, nomor_transaksi, tanggal_pembayaran, jumlah_pembayaran, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?)";
                eksekusi($sql, [$id_tagihan, $id_metode, $nomor_transaksi, $tanggal_pembayaran, $jumlah_pembayaran, $status_pembayaran]);
                $bayar_id = $koneksi->lastInsertId();
                
                // Evaluasi Otomatis Status Tagihan Pajak (Business Logic)
                $tagihan_row = ambil_satu("SELECT jumlah_tagihan FROM tagihan_pajak WHERE id_tagihan = ?", [$id_tagihan]);
                $total_masuk = ambil_satu("SELECT COALESCE(SUM(jumlah_pembayaran), 0) as total FROM pembayaran_pajak WHERE id_tagihan = ? AND status_pembayaran = 'Berhasil'", [$id_tagihan])['total'] ?? 0;
                
                if ($total_masuk >= (float)$tagihan_row['jumlah_tagihan']) {
                    eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Lunas' WHERE id_tagihan = ?", [$id_tagihan]);
                } elseif ($total_masuk > 0) {
                    eksekusi("UPDATE tagihan_pajak SET status_tagihan = 'Dibayar Sebagian' WHERE id_tagihan = ?", [$id_tagihan]);
                }
                
                set_flash('success', 'Pembayaran pajak berhasil dicatat dan status tagihan telah diperbarui!');
                header('Location: detail.php?id=' . $bayar_id);
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Input Pembayaran Pajak Baru</h4>
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
                            <label class="form-label fw-semibold">Nomor Transaksi (Unik) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_transaksi" class="form-control font-monospace fw-bold" value="<?= escape($_POST['nomor_transaksi'] ?? $nomor_trx_default) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Waktu / Tanggal Pembayaran <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_pembayaran" class="form-control" value="<?= escape($_POST['tanggal_pembayaran'] ?? date('Y-m-d\TH:i')) ?>" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Surat Tagihan Pajak (STP) <span class="text-danger">*</span></label>
                            <select name="id_tagihan" id="selectTagihan" class="form-select" required>
                                <option value="">-- Pilih Tagihan yang Akan Dibayar --</option>
                                <?php foreach ($daftar_tagihan as $tg): 
                                    $sisa = max(0, $tg['jumlah_tagihan'] - $tg['sudah_bayar']);
                                ?>
                                    <option value="<?= $tg['id_tagihan'] ?>" data-sisa="<?= (float)$sisa ?>" <?= (($id_tagihan_param == $tg['id_tagihan']) || (($_POST['id_tagihan'] ?? '') == $tg['id_tagihan'])) ? 'selected' : '' ?>>
                                        <?= escape($tg['nomor_tagihan']) ?> &mdash; <?= escape($tg['nama_wajib_pajak']) ?> (Sisa: <?= format_rupiah($sisa) ?> &bull; Status: <?= escape($tg['status_tagihan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select name="id_metode" class="form-select" required>
                                <option value="">-- Pilih Metode / Kanal --</option>
                                <?php foreach ($daftar_metode as $mt): ?>
                                    <option value="<?= $mt['id_metode'] ?>" <?= (($_POST['id_metode'] ?? '') == $mt['id_metode']) ? 'selected' : '' ?>>
                                        <?= escape($mt['nama_metode']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jumlah Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="jumlah_pembayaran" id="inputBayar" class="form-control fw-bold" placeholder="0.00" value="<?= escape($_POST['jumlah_pembayaran'] ?? '') ?>" required>
                            </div>
                            <small class="text-muted">Mendukung pembayaran bertahap (cicil) atau lunas sekaligus.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Konfirmasi Pembayaran</label>
                            <select name="status_pembayaran" class="form-select">
                                <option value="Berhasil" <?= (($_POST['status_pembayaran'] ?? 'Berhasil') === 'Berhasil') ? 'selected' : '' ?>>Berhasil (Tervalidasi Bank / Pos)</option>
                                <option value="Menunggu Konfirmasi" <?= (($_POST['status_pembayaran'] ?? '') === 'Menunggu Konfirmasi') ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                                <option value="Gagal" <?= (($_POST['status_pembayaran'] ?? '') === 'Gagal') ? 'selected' : '' ?>>Gagal</option>
                                <option value="Dibatalkan" <?= (($_POST['status_pembayaran'] ?? '') === 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mt-4">
                        <i class="bi bi-info-circle me-1 text-primary"></i><strong>Logika Pembayaran Otomatis:</strong> Apabila akumulasi pembayaran sukses telah mencukupi total nominal tagihan, sistem akan otomatis mengubah status tagihan menjadi <strong>Lunas</strong>.
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Catat Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selTg = document.getElementById('selectTagihan');
    const inpt = document.getElementById('inputBayar');
    if (selTg && inpt) {
        selTg.addEventListener('change', function() {
            const opt = selTg.options[selTg.selectedIndex];
            const sisa = opt.getAttribute('data-sisa');
            if (sisa && parseFloat(sisa) > 0 && !inpt.value) {
                inpt.value = sisa;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
