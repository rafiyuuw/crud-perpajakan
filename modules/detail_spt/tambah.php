<?php
$judul_halaman = 'Tambah Rincian Detail SPT';
require_once __DIR__ . '/../../includes/header.php';

$id_spt_param = (int)($_GET['id_spt'] ?? 0);

$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, wp.nama_wajib_pajak, jp.kode_pajak 
    FROM spt s 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak 
    ORDER BY s.id_spt DESC
");
$daftar_objek = ambil_semua("
    SELECT o.id_objek_pajak, o.nama_objek, o.nilai_objek, wp.nama_wajib_pajak, jp.kode_pajak
    FROM objek_pajak o
    JOIN wajib_pajak wp ON o.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak
    ORDER BY o.nama_objek ASC
");
$daftar_tarif = ambil_semua("
    SELECT t.id_tarif, t.nama_tarif, t.nilai_tarif, jp.kode_pajak
    FROM tarif_pajak t
    JOIN jenis_pajak jp ON t.id_jenis_pajak = jp.id_jenis_pajak
    WHERE t.status = 'Aktif'
    ORDER BY t.nama_tarif ASC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_spt          = (int)($_POST['id_spt'] ?? 0);
    $id_objek_pajak  = (int)($_POST['id_objek_pajak'] ?? 0);
    $id_tarif        = (int)($_POST['id_tarif'] ?? 0);
    $dasar_pengenaan = (float)str_replace(['.', ','], ['', '.'], $_POST['dasar_pengenaan'] ?? 0);
    $keterangan      = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($id_spt) || empty($id_objek_pajak) || empty($id_tarif) || $dasar_pengenaan <= 0) {
        $error = 'SPT, Objek Pajak, Tarif, dan Dasar Pengenaan wajib diisi dengan benar!';
    } else {
        // Ambil nilai tarif dari master tabel tarif_pajak
        $tarif_row = ambil_satu("SELECT nilai_tarif FROM tarif_pajak WHERE id_tarif = ?", [$id_tarif]);
        $tarif_digunakan = $tarif_row ? (float)$tarif_row['nilai_tarif'] : 0.0;
        
        // Rumus sederhana perhitungan pajak demonstrasi: dasar_pengenaan * (tarif / 100)
        $jumlah_pajak = $dasar_pengenaan * ($tarif_digunakan / 100);
        
        try {
            $sql = "INSERT INTO detail_spt (id_spt, id_objek_pajak, id_tarif, dasar_pengenaan, tarif_digunakan, jumlah_pajak, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)";
            eksekusi($sql, [$id_spt, $id_objek_pajak, $id_tarif, $dasar_pengenaan, $tarif_digunakan, $jumlah_pajak, $keterangan]);
            
            // Perbarui Total Pajak pada Master SPT
            $total_baru = ambil_satu("SELECT SUM(jumlah_pajak) as total FROM detail_spt WHERE id_spt = ?", [$id_spt])['total'] ?? 0;
            eksekusi("UPDATE spt SET total_pajak = ? WHERE id_spt = ?", [$total_baru, $id_spt]);
            
            // Perbarui juga tagihan jika sudah ada
            eksekusi("UPDATE tagihan_pajak SET jumlah_tagihan = ? WHERE id_spt = ?", [$total_baru, $id_spt]);
            
            set_flash('success', 'Rincian objek SPT berhasil ditambahkan dan total pajak SPT telah disinkronkan!');
            header('Location: ../spt/detail.php?id=' . $id_spt);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="<?= $id_spt_param ? '../spt/detail.php?id=' . $id_spt_param : 'index.php' ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Tambah Rincian Objek Detail SPT</h4>
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
                        <div class="col-12">
                            <label class="form-label fw-semibold">Pilih Laporan SPT (Induk Relasi) <span class="text-danger">*</span></label>
                            <select name="id_spt" class="form-select" required>
                                <option value="">-- Pilih Laporan SPT --</option>
                                <?php foreach ($daftar_spt as $s): ?>
                                    <option value="<?= $s['id_spt'] ?>" <?= (($id_spt_param == $s['id_spt']) || (($_POST['id_spt'] ?? '') == $s['id_spt'])) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> &mdash; <?= escape($s['nama_wajib_pajak']) ?> (<?= escape($s['kode_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Objek Pajak <span class="text-danger">*</span></label>
                            <select name="id_objek_pajak" id="selectObjek" class="form-select" required>
                                <option value="">-- Pilih Objek Pajak --</option>
                                <?php foreach ($daftar_objek as $o): ?>
                                    <option value="<?= $o['id_objek_pajak'] ?>" data-nilai="<?= (float)$o['nilai_objek'] ?>">
                                        <?= escape($o['nama_objek']) ?> &mdash; [<?= escape($o['kode_pajak']) ?>] (<?= escape($o['nama_wajib_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Tarif Pajak <span class="text-danger">*</span></label>
                            <select name="id_tarif" id="selectTarif" class="form-select" required>
                                <option value="">-- Pilih Tarif Pajak --</option>
                                <?php foreach ($daftar_tarif as $t): ?>
                                    <option value="<?= $t['id_tarif'] ?>" data-tarif="<?= (float)$t['nilai_tarif'] ?>">
                                        <?= escape($t['nama_tarif']) ?> (<?= (float)$t['nilai_tarif'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Dasar Pengenaan Pajak (DPP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="dasar_pengenaan" id="inputDpp" class="form-control" placeholder="0.00" value="<?= escape($_POST['dasar_pengenaan'] ?? '') ?>" required>
                            </div>
                            <small class="text-muted">Nilai dasar yang akan dikalikan dengan tarif.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Keterangan / Uraian Rincian</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Penghasilan bruto bulan Januari" value="<?= escape($_POST['keterangan'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mt-4">
                        <i class="bi bi-calculator me-1 text-primary"></i><strong>Simulasi Otomatis:</strong> Nilai tarif yang berlaku akan dikunci ke dalam kolom <code>tarif_digunakan</code> saat data disimpan, sehingga perubahan tarif master di masa depan tidak mempengaruhi data riwayat ini.
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $id_spt_param ? '../spt/detail.php?id=' . $id_spt_param : 'index.php' ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Rincian SPT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const selObjek = document.getElementById('selectObjek');
    const inputDpp = document.getElementById('inputDpp');
    if (selObjek && inputDpp) {
        selObjek.addEventListener('change', function() {
            const opt = selObjek.options[selObjek.selectedIndex];
            const nilai = opt.getAttribute('data-nilai');
            if (nilai && parseFloat(nilai) > 0 && !inputDpp.value) {
                inputDpp.value = nilai;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
