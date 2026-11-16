<?php
$judul_halaman = 'Input Pelaporan SPT Baru';
require_once __DIR__ . '/../../includes/header.php';

$id_wp_param = (int)($_GET['id_wajib_pajak'] ?? 0);

$daftar_wp = ambil_semua("SELECT id_wajib_pajak, nama_wajib_pajak, nomor_npwp FROM wajib_pajak WHERE status_wajib_pajak = 'Aktif' ORDER BY nama_wajib_pajak ASC");
$daftar_jenis = ambil_semua("SELECT id_jenis_pajak, nama_pajak, kode_pajak FROM jenis_pajak WHERE status = 'Aktif' ORDER BY nama_pajak ASC");
$daftar_masa = ambil_semua("
    SELECT m.id_masa_pajak, m.tahun_pajak, m.tanggal_mulai, m.tanggal_berakhir, jp.kode_pajak 
    FROM masa_pajak m 
    JOIN jenis_pajak jp ON m.id_jenis_pajak = jp.id_jenis_pajak 
    ORDER BY m.tahun_pajak DESC, m.id_masa_pajak DESC
");
$daftar_status = ambil_semua("SELECT id_status_spt, nama_status FROM status_spt ORDER BY id_status_spt ASC");
$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak WHERE status = 'Aktif' ORDER BY nama_petugas ASC");

// Nomor SPT Otomatis
$tahun_sekarang = date('Y');
$count_spt = ambil_satu("SELECT COUNT(*) as jml FROM spt")['jml'] + 1;
$nomor_spt_default = 'SPT-' . $tahun_sekarang . '-' . str_pad($count_spt, 4, '0', STR_PAD_LEFT);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();
    
    $nomor_spt         = bersihkan($_POST['nomor_spt'] ?? '');
    $id_wajib_pajak    = (int)($_POST['id_wajib_pajak'] ?? 0);
    $id_jenis_pajak    = (int)($_POST['id_jenis_pajak'] ?? 0);
    $id_masa_pajak     = (int)($_POST['id_masa_pajak'] ?? 0);
    $id_status_spt     = (int)($_POST['id_status_spt'] ?? 1);
    $id_petugas        = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : ($_SESSION['petugas_id'] ?? null);
    $tanggal_pelaporan = bersihkan($_POST['tanggal_pelaporan'] ?? date('Y-m-d'));
    $total_pajak       = (float)str_replace(['.', ','], ['', '.'], $_POST['total_pajak'] ?? 0);
    $keterangan        = bersihkan($_POST['keterangan'] ?? '');
    
    if (empty($nomor_spt) || empty($id_wajib_pajak) || empty($id_jenis_pajak) || empty($id_masa_pajak) || empty($id_status_spt)) {
        $error = 'Semua bidang formulir bertanda bintang (*) wajib diisi!';
    } else {
        $cek = ambil_satu("SELECT id_spt FROM spt WHERE nomor_spt = ?", [$nomor_spt]);
        if ($cek) {
            $error = 'Nomor SPT sudah pernah digunakan!';
        } else {
            try {
                $sql = "INSERT INTO spt (nomor_spt, id_wajib_pajak, id_jenis_pajak, id_masa_pajak, id_status_spt, id_petugas, tanggal_pelaporan, total_pajak, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                eksekusi($sql, [$nomor_spt, $id_wajib_pajak, $id_jenis_pajak, $id_masa_pajak, $id_status_spt, $id_petugas, $tanggal_pelaporan, $total_pajak, $keterangan]);
                $spt_id = $koneksi->lastInsertId();
                
                // Catat Riwayat Status Awal SPT
                eksekusi("INSERT INTO riwayat_status_spt (id_spt, id_status_spt, id_petugas, tanggal_perubahan, catatan) VALUES (?, ?, ?, NOW(), ?)", [
                    $spt_id, $id_status_spt, $id_petugas, 'Pelaporan SPT baru diinput ke dalam sistem.'
                ]);
                
                // Jika total_pajak > 0, otomatis buatkan Tagihan Pajak awal
                if ($total_pajak > 0) {
                    $nomor_tagihan = 'BILL-' . $tahun_sekarang . '-' . str_pad($spt_id, 4, '0', STR_PAD_LEFT);
                    $jatuh_tempo = date('Y-m-d', strtotime('+30 days', strtotime($tanggal_pelaporan)));
                    eksekusi("INSERT INTO tagihan_pajak (id_spt, nomor_tagihan, tanggal_tagihan, tanggal_jatuh_tempo, jumlah_tagihan, status_tagihan) VALUES (?, ?, ?, ?, ?, 'Belum Bayar')", [
                        $spt_id, $nomor_tagihan, $tanggal_pelaporan, $jatuh_tempo, $total_pajak
                    ]);
                }
                
                set_flash('success', 'Pelaporan SPT <strong>' . escape($nomor_spt) . '</strong> berhasil disimpan!');
                header('Location: detail.php?id=' . $spt_id);
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
            <h4 class="fw-bold mb-0 text-dark">Input Pelaporan SPT Baru</h4>
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
                            <label class="form-label fw-semibold">Nomor SPT (Unik) <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_spt" class="form-control font-monospace fw-bold" value="<?= escape($_POST['nomor_spt'] ?? $nomor_spt_default) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Wajib Pajak (Pemohon) <span class="text-danger">*</span></label>
                            <select name="id_wajib_pajak" class="form-select" required>
                                <option value="">-- Pilih Wajib Pajak --</option>
                                <?php foreach ($daftar_wp as $wp): ?>
                                    <option value="<?= $wp['id_wajib_pajak'] ?>" <?= (($id_wp_param == $wp['id_wajib_pajak']) || (($_POST['id_wajib_pajak'] ?? '') == $wp['id_wajib_pajak'])) ? 'selected' : '' ?>>
                                        <?= escape($wp['nama_wajib_pajak']) ?> (NPWP: <?= escape($wp['nomor_npwp']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Jenis Pajak <span class="text-danger">*</span></label>
                            <select name="id_jenis_pajak" class="form-select" required>
                                <option value="">-- Pilih Jenis Pajak --</option>
                                <?php foreach ($daftar_jenis as $j): ?>
                                    <option value="<?= $j['id_jenis_pajak'] ?>" <?= (($_POST['id_jenis_pajak'] ?? '') == $j['id_jenis_pajak']) ? 'selected' : '' ?>>
                                        [<?= escape($j['kode_pajak']) ?>] <?= escape($j['nama_pajak']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Masa / Periode Pajak <span class="text-danger">*</span></label>
                            <select name="id_masa_pajak" class="form-select" required>
                                <option value="">-- Pilih Masa Pajak --</option>
                                <?php foreach ($daftar_masa as $m): ?>
                                    <option value="<?= $m['id_masa_pajak'] ?>" <?= (($_POST['id_masa_pajak'] ?? '') == $m['id_masa_pajak']) ? 'selected' : '' ?>>
                                        Tahun <?= $m['tahun_pajak'] ?> (<?= escape($m['kode_pajak']) ?>: <?= format_tanggal($m['tanggal_mulai']) ?> s.d <?= format_tanggal($m['tanggal_berakhir']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Pelaporan <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_pelaporan" class="form-control" value="<?= escape($_POST['tanggal_pelaporan'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Awal SPT <span class="text-danger">*</span></label>
                            <select name="id_status_spt" class="form-select" required>
                                <?php foreach ($daftar_status as $st): ?>
                                    <option value="<?= $st['id_status_spt'] ?>" <?= (($_POST['id_status_spt'] ?? 2) == $st['id_status_spt']) ? 'selected' : '' ?>>
                                        <?= escape($st['nama_status']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Petugas Pemeriksa</label>
                            <select name="id_petugas" class="form-select">
                                <option value="">-- Otomatis Petugas Aktif --</option>
                                <?php foreach ($daftar_petugas as $p): ?>
                                    <option value="<?= $p['id_petugas'] ?>" <?= (($_SESSION['petugas_id'] ?? 0) == $p['id_petugas']) ? 'selected' : '' ?>>
                                        <?= escape($p['nama_petugas']) ?> (<?= escape($p['jabatan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Estimasi Total Pajak Terutang (Rp)</label>
                            <input type="number" step="0.01" name="total_pajak" class="form-control" placeholder="0.00" value="<?= escape($_POST['total_pajak'] ?? '0') ?>">
                            <small class="text-muted">Dapat dihitung otomatis dari rincian Detail SPT.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Keterangan / Judul Formulir</label>
                            <input type="text" name="keterangan" class="form-control" placeholder="Contoh: SPT Tahunan PPh Orang Pribadi Formulir 1770 S" value="<?= escape($_POST['keterangan'] ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Pelaporan SPT</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
