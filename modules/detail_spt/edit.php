<?php
$judul_halaman = 'Ubah Rincian Detail SPT';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$data = ambil_satu("SELECT * FROM detail_spt WHERE id_detail_spt = ?", [$id]);

if (!$data) {
    set_flash('danger', 'Data rincian SPT tidak ditemukan!');
    header('Location: index.php');
    exit;
}

$daftar_spt = ambil_semua("
    SELECT s.id_spt, s.nomor_spt, wp.nama_wajib_pajak, jp.kode_pajak 
    FROM spt s 
    JOIN wajib_pajak wp ON s.id_wajib_pajak = wp.id_wajib_pajak 
    JOIN jenis_pajak jp ON s.id_jenis_pajak = jp.id_jenis_pajak 
    ORDER BY s.id_spt DESC
");
$daftar_objek = ambil_semua("
    SELECT o.id_objek_pajak, o.nama_objek, wp.nama_wajib_pajak, jp.kode_pajak
    FROM objek_pajak o
    JOIN wajib_pajak wp ON o.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_pajak jp ON o.id_jenis_pajak = jp.id_jenis_pajak
    ORDER BY o.nama_objek ASC
");
$daftar_tarif = ambil_semua("
    SELECT t.id_tarif, t.nama_tarif, t.nilai_tarif, jp.kode_pajak
    FROM tarif_pajak t
    JOIN jenis_pajak jp ON t.id_jenis_pajak = jp.id_jenis_pajak
    ORDER BY t.nama_tarif ASC
");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $id_spt          = (int)($_POST['id_spt'] ?? 0);
    $id_objek_pajak  = (int)($_POST['id_objek_pajak'] ?? 0);
    $id_tarif        = (int)($_POST['id_tarif'] ?? 0);
    $dasar_pengenaan = (float)str_replace(['.', ','], ['', '.'], $_POST['dasar_pengenaan'] ?? 0);
    $tarif_digunakan = (float)$_POST['tarif_digunakan'];
    $keterangan      = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($id_spt) || empty($id_objek_pajak) || empty($id_tarif) || $dasar_pengenaan <= 0) {
        $error = 'SPT, Objek Pajak, Tarif, dan Dasar Pengenaan wajib diisi dengan benar!';
    } else {
        $jumlah_pajak = $dasar_pengenaan * ($tarif_digunakan / 100);
        
        try {
            $sql = "UPDATE detail_spt SET id_spt = ?, id_objek_pajak = ?, id_tarif = ?, dasar_pengenaan = ?, tarif_digunakan = ?, jumlah_pajak = ?, keterangan = ? WHERE id_detail_spt = ?";
            eksekusi($sql, [$id_spt, $id_objek_pajak, $id_tarif, $dasar_pengenaan, $tarif_digunakan, $jumlah_pajak, $keterangan, $id]);
            
            // Perbarui Total Pajak pada Master SPT
            $total_baru = ambil_satu("SELECT SUM(jumlah_pajak) as total FROM detail_spt WHERE id_spt = ?", [$id_spt])['total'] ?? 0;
            eksekusi("UPDATE spt SET total_pajak = ? WHERE id_spt = ?", [$total_baru, $id_spt]);
            eksekusi("UPDATE tagihan_pajak SET jumlah_tagihan = ? WHERE id_spt = ?", [$total_baru, $id_spt]);
            
            set_flash('success', 'Rincian detail SPT berhasil diperbarui!');
            header('Location: ../spt/detail.php?id=' . $id_spt);
            exit;
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex align-items-center mb-3">
            <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <h4 class="fw-bold mb-0 text-dark">Ubah Rincian Detail SPT</h4>
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
                                <?php foreach ($daftar_spt as $s): ?>
                                    <option value="<?= $s['id_spt'] ?>" <?= (($data['id_spt'] ?? 0) == $s['id_spt']) ? 'selected' : '' ?>>
                                        <?= escape($s['nomor_spt']) ?> &mdash; <?= escape($s['nama_wajib_pajak']) ?> (<?= escape($s['kode_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Objek Pajak <span class="text-danger">*</span></label>
                            <select name="id_objek_pajak" class="form-select" required>
                                <?php foreach ($daftar_objek as $o): ?>
                                    <option value="<?= $o['id_objek_pajak'] ?>" <?= (($data['id_objek_pajak'] ?? 0) == $o['id_objek_pajak']) ? 'selected' : '' ?>>
                                        <?= escape($o['nama_objek']) ?> &mdash; [<?= escape($o['kode_pajak']) ?>] (<?= escape($o['nama_wajib_pajak']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Tarif Pajak <span class="text-danger">*</span></label>
                            <select name="id_tarif" class="form-select" required>
                                <?php foreach ($daftar_tarif as $t): ?>
                                    <option value="<?= $t['id_tarif'] ?>" <?= (($data['id_tarif'] ?? 0) == $t['id_tarif']) ? 'selected' : '' ?>>
                                        <?= escape($t['nama_tarif']) ?> (<?= (float)$t['nilai_tarif'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Dasar Pengenaan Pajak (DPP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="0.01" name="dasar_pengenaan" class="form-control" value="<?= (float)$data['dasar_pengenaan'] ?>" required>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Tarif Terkunci (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.0001" name="tarif_digunakan" class="form-control" value="<?= (float)$data['tarif_digunakan'] ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Keterangan / Uraian Rincian</label>
                            <input type="text" name="keterangan" class="form-control" value="<?= escape($data['keterangan']) ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="../spt/detail.php?id=<?= $data['id_spt'] ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning text-dark"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
