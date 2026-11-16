<?php
$judul_halaman = 'Buat Tagihan Pajak Baru';
require_once __DIR__ . '/../../includes/header.php';

$id_spt_param = (int)($_GET['id_spt'] ?? 0);

$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, s.total_pajak, wp.nama_wajib_pajak, jp.kode_pajak
    FROM spt s
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak
    ORDER BY s.id_spt DESC
");

// Auto-generate nomor tagihan
$tahun_sekarang = date('Y');
$count_bill = ambil_satu("SELECT COUNT(*) as jml FROM tagihan_pajak")['jml'] + 1;
$nomor_default = 'BILL-' . $tahun_sekarang . '-' . str_pad($count_bill, 4, '0', STR_PAD_LEFT);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_spt              = (int)($_POST['id_spt'] ?? 0);
    $nomor_tagihan       = bersihkan($_POST['nomor_tagihan'] ?? '');
    $tanggal_tagihan     = bersihkan($_POST['tanggal_tagihan'] ?? date('Y-m-d'));
    $tanggal_jatuh_tempo = bersihkan($_POST['tanggal_jatuh_tempo'] ?? '');
    $jumlah_tagihan      = (float)str_replace(['.', ','], ['', '.'], $_POST['jumlah_tagihan'] ?? 0);
    $status_tagihan      = bersihkan($_POST['status_tagihan'] ?? 'Belum Bayar');
    
    if (empty($id_spt) || empty($nomor_tagihan) || empty($tanggal_tagihan) || empty($tanggal_jatuh_tempo) || $jumlah_tagihan <= 0) {
        $error = 'Semua bidang formulir wajib diisi dengan benar!';
    } else {
        $cek = ambil_satu("SELECT id_tagihan FROM tagihan_pajak WHERE nomor_tagihan = ?", [$nomor_tagihan]);
        if ($cek) {
            $error = 'Nomor tagihan sudah pernah digunakan!';
        } else {
            try {
                $sql = "INSERT INTO tagihan_pajak (id_spt, nomor_tagihan, tanggal_tagihan, tanggal_jatuh_tempo, jumlah_tagihan, status_tagihan) VALUES (?, ?, ?, ?, ?, ?)";
                eksekusi($sql, [$id_spt, $nomor_tagihan, $tanggal_tagihan, $tanggal_jatuh_tempo, $jumlah_tagihan, $status_tagihan]);
                $new_id = $koneksi->lastInsertId();
                set_flash('success', 'Surat Tagihan Pajak (STP) berhasil diterbitkan!');
                header('Location: detail.php?id=' . $new_id);
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
            <h4 class="fw-bold mb-0 text-dark">Terbitkan Surat Tagihan Pajak</h4>
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
                            <label class="form-label fw-semibold">Nomor Tagihan (Unik) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_tagihan" class="form-control font-monospace fw-bold" value="<?= escape($_POST['nomor_tagihan'] ?? $nomor_default) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Laporan SPT Terkait (Foreign Key) <span class="text-danger">*</span></label>
                            <select name="id_spt" id="selectSpt" class="form-select" required>
                                <option value="">-- Pilih SPT --</option>
                                <?php foreach ($daftar_spt as $s): ?>
                                    <option value="<?= $s['id_spt'] ?>" data-total="<?= (float)$s['total_pajak'] ?>" <?= (($id_spt_param == $s['id_spt']) || (($_POST['id_spt'] ?? '') == $s['id_spt'])) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> &mdash; <?= escape($s['nama_wajib_pajak']) ?> (<?= format_rupiah($s['total_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Tagihan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_tagihan" class="form-control" value="<?= escape($_POST['tanggal_tagihan'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="<?= escape($_POST['tanggal_jatuh_tempo'] ?? date('Y-m-d', strtotime('+30 days'))) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Awal Tagihan</label>
                            <select name="status_tagihan" class="form-select">
                                <option value="Belum Bayar">Belum Bayar</option>
                                <option value="Dibayar Sebagian">Dibayar Sebagian</option>
                                <option value="Lunas">Lunas</option>
                                <option value="Kadaluarsa">Kadaluarsa</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jumlah Nominal Tagihan (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="jumlah_tagihan" id="inputJumlah" class="form-control" placeholder="0.00" value="<?= escape($_POST['jumlah_tagihan'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-receipt me-1"></i>Terbitkan Tagihan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selSpt = document.getElementById('selectSpt');
    const inputJumlah = document.getElementById('inputJumlah');
    if (selSpt && inputJumlah) {
        selSpt.addEventListener('change', function() {
            const opt = selSpt.options[selSpt.selectedIndex];
            const tot = opt.getAttribute('data-total');
            if (tot && parseFloat(tot) > 0 && !inputJumlah.value) {
                inputJumlah.value = tot;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
