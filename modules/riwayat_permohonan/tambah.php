<?php
$judul_halaman = 'Tambah Riwayat Permohonan';
require_once __DIR__ . '/../../includes/header.php';

$id_mohon_param = (int)($_GET['id_permohonan'] ?? 0);

// Ambil daftar permohonan pajak
$daftar_permohonan = ambil_semua("
    SELECT pp.id_permohonan, pp.nomor_permohonan, pp.status_permohonan, pp.id_petugas,
           wp.nama_wajib_pajak, jp.nama_permohonan
    FROM permohonan_pajak pp
    JOIN wajib_pajak wp ON pp.id_wajib_pajak = wp.id_wajib_pajak
    JOIN jenis_permohonan jp ON pp.id_jenis_permohonan = jp.id_jenis_permohonan
    ORDER BY pp.id_permohonan DESC
");

// Cari status saat ini jika ada parameter
$status_saat_ini = '';
$petugas_saat_ini = 0;
if ($id_mohon_param > 0) {
    foreach ($daftar_permohonan as $p) {
        if ($p['id_permohonan'] == $id_mohon_param) {
            $status_saat_ini = $p['status_permohonan'];
            $petugas_saat_ini = $p['id_petugas'];
            break;
        }
    }
}

$daftar_petugas = ambil_semua("SELECT id_petugas, nama_petugas, jabatan FROM petugas_pajak WHERE status = 'Aktif' ORDER BY nama_petugas ASC");

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifikasi_csrf();

    $id_permohonan     = (int)($_POST['id_permohonan'] ?? 0);
    $id_petugas        = !empty($_POST['id_petugas']) ? (int)$_POST['id_petugas'] : null;
    $status_sebelumnya = bersihkan($_POST['status_sebelumnya'] ?? '');
    $status_baru       = bersihkan($_POST['status_baru'] ?? '');
    $tanggal_perubahan = bersihkan($_POST['tanggal_perubahan'] ?? date('Y-m-d H:i:s'));
    $catatan           = bersihkan($_POST['catatan'] ?? '');
    $sinkron_induk     = isset($_POST['sinkron_induk']);

    if (empty($id_permohonan) || empty($status_baru)) {
        $error = 'Permohonan pajak dan status baru wajib dipilih!';
    } else {
        // Ambil data induk jika status sebelumnya kosong
        if (empty($status_sebelumnya)) {
            $induk = ambil_satu("SELECT status_permohonan FROM permohonan_pajak WHERE id_permohonan = ?", [$id_permohonan]);
            $status_sebelumnya = $induk ? $induk['status_permohonan'] : 'Awal';
        }

        $sql = "INSERT INTO riwayat_permohonan (id_permohonan, id_petugas, status_sebelumnya, status_baru, tanggal_perubahan, catatan)
                VALUES (?, ?, ?, ?, ?, ?)";
        $insert_id = eksekusi($sql, [$id_permohonan, $id_petugas, $status_sebelumnya, $status_baru, $tanggal_perubahan, $catatan]);

        if ($insert_id) {
            if ($sinkron_induk) {
                // Sinkronkan status pada tabel permohonan_pajak
                if (!empty($id_petugas)) {
                    eksekusi("UPDATE permohonan_pajak SET status_permohonan = ?, id_petugas = ? WHERE id_permohonan = ?", [$status_baru, $id_petugas, $id_permohonan]);
                } else {
                    eksekusi("UPDATE permohonan_pajak SET status_permohonan = ? WHERE id_permohonan = ?", [$status_baru, $id_permohonan]);
                }
            }

            set_flash('success', 'Riwayat permohonan berhasil dicatat!');
            header('Location: detail.php?id=' . $insert_id);
            exit;
        } else {
            $error = 'Gagal menyimpan riwayat permohonan ke database.';
        }
    }
}
?>

<div class="d-flex align-items-center mb-4">
    <a href="index.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h4 class="fw-bold mb-0 text-dark">Catat Riwayat &amp; Disposisi Permohonan</h4>
</div>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="card-title fw-bold mb-0 text-dark">Formulir Log Status Permohonan</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div><?= escape($error) ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="id_permohonan" class="form-label fw-semibold">Pilih Berkas Permohonan Pajak <span class="text-danger">*</span></label>
                        <select name="id_permohonan" id="id_permohonan" class="form-select select2-enable" required>
                            <option value="">-- Pilih Permohonan --</option>
                            <?php foreach ($daftar_permohonan as $p): ?>
                                <option value="<?= $p['id_permohonan'] ?>" data-status="<?= escape($p['status_permohonan']) ?>" <?= ($id_mohon_param == $p['id_permohonan'] || (isset($_POST['id_permohonan']) && $_POST['id_permohonan'] == $p['id_permohonan'])) ? 'selected' : '' ?>>
                                    <?= escape($p['nomor_permohonan']) ?> &bull; <?= escape($p['nama_wajib_pajak']) ?> &bull; <?= escape($p['nama_permohonan']) ?> (Status: <?= escape($p['status_permohonan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="status_sebelumnya" class="form-label fw-semibold">Status Sebelumnya</label>
                            <input type="text" name="status_sebelumnya" id="status_sebelumnya" class="form-control" placeholder="Contoh: Diajukan" value="<?= escape($_POST['status_sebelumnya'] ?? $status_saat_ini) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="status_baru" class="form-label fw-semibold">Status Baru (Tujuan) <span class="text-danger">*</span></label>
                            <select name="status_baru" id="status_baru" class="form-select" required>
                                <option value="">-- Pilih Status Baru --</option>
                                <option value="Diajukan" <?= (($_POST['status_baru'] ?? '') === 'Diajukan') ? 'selected' : '' ?>>Diajukan</option>
                                <option value="Diverifikasi" <?= (($_POST['status_baru'] ?? '') === 'Diverifikasi') ? 'selected' : '' ?>>Diverifikasi</option>
                                <option value="Disetujui" <?= (($_POST['status_baru'] ?? '') === 'Disetujui') ? 'selected' : '' ?>>Disetujui</option>
                                <option value="Ditolak" <?= (($_POST['status_baru'] ?? '') === 'Ditolak') ? 'selected' : '' ?>>Ditolak</option>
                                <option value="Selesai" <?= (($_POST['status_baru'] ?? '') === 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="id_petugas" class="form-label fw-semibold">Petugas yang Memproses</label>
                            <select name="id_petugas" id="id_petugas" class="form-select select2-enable">
                                <option value="">-- Sistem / Otomatis --</option>
                                <?php foreach ($daftar_petugas as $pt): ?>
                                    <option value="<?= $pt['id_petugas'] ?>" <?= (($petugas_saat_ini == $pt['id_petugas']) || (isset($_POST['id_petugas']) && $_POST['id_petugas'] == $pt['id_petugas'])) ? 'selected' : '' ?>>
                                        <?= escape($pt['nama_petugas']) ?> (<?= escape($pt['jabatan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tanggal_perubahan" class="form-label fw-semibold">Waktu Perubahan Status <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_perubahan" id="tanggal_perubahan" class="form-control" value="<?= escape($_POST['tanggal_perubahan'] ?? date('Y-m-d\TH:i')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="catatan" class="form-label fw-semibold">Catatan / Disposisi Pemeriksaan</label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control" placeholder="Contoh: Dokumen lampiran telah diverifikasi lengkap dan memenuhi syarat formal..."><?= escape($_POST['catatan'] ?? '') ?></textarea>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="sinkron_induk" id="sinkron_induk" value="1" checked>
                        <label class="form-check-label fw-semibold text-primary" for="sinkron_induk">
                            Perbarui status pada data induk permohonan secara otomatis
                        </label>
                        <div class="form-text">Jika dicentang, status permohonan utama akan otomatis diselaraskan dengan status baru di atas.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="index.php" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Riwayat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
