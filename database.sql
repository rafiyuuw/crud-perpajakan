-- =======================================================
-- SISTEM INFORMASI PERPAJAKAN
-- Database: db_perpajakan
-- Untuk Pembelajaran Basis Data SMK RPL
-- Mengimplementasikan 25 Tabel Relasional dengan PK & FK
-- =======================================================

CREATE DATABASE IF NOT EXISTS `db_perpajakan` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_perpajakan`;

-- Nonaktifkan Foreign Key Check sementara saat setup
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------
-- 1. Tabel: kantor_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `kantor_pajak`;
CREATE TABLE `kantor_pajak` (
  `id_kantor` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_kantor` VARCHAR(20) NOT NULL UNIQUE,
  `nama_kantor` VARCHAR(150) NOT NULL,
  `alamat` TEXT NOT NULL,
  `kota` VARCHAR(100) NOT NULL,
  `nomor_telepon` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 2. Tabel: petugas_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `petugas_pajak`;
CREATE TABLE `petugas_pajak` (
  `id_petugas` INT AUTO_INCREMENT PRIMARY KEY,
  `id_kantor` INT NOT NULL,
  `nama_petugas` VARCHAR(150) NOT NULL,
  `nomor_identitas` VARCHAR(50) NOT NULL UNIQUE,
  `jabatan` VARCHAR(100) NOT NULL,
  `nomor_telepon` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_petugas_kantor` FOREIGN KEY (`id_kantor`) 
    REFERENCES `kantor_pajak` (`id_kantor`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Tabel: akun_petugas
-- -------------------------------------------------------
DROP TABLE IF EXISTS `akun_petugas`;
CREATE TABLE `akun_petugas` (
  `id_akun` INT AUTO_INCREMENT PRIMARY KEY,
  `id_petugas` INT NOT NULL UNIQUE,
  `nama_pengguna` VARCHAR(50) NOT NULL UNIQUE,
  `kata_sandi` VARCHAR(255) NOT NULL,
  `peran` VARCHAR(50) NOT NULL,
  `status_akun` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  `terakhir_masuk` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_akun_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `petugas_pajak` (`id_petugas`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Tabel: jenis_wajib_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `jenis_wajib_pajak`;
CREATE TABLE `jenis_wajib_pajak` (
  `id_jenis_wajib_pajak` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_jenis` VARCHAR(100) NOT NULL UNIQUE,
  `keterangan` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 5. Tabel: wajib_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `wajib_pajak`;
CREATE TABLE `wajib_pajak` (
  `id_wajib_pajak` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jenis_wajib_pajak` INT NOT NULL,
  `id_kantor` INT NOT NULL,
  `nomor_npwp` VARCHAR(30) NOT NULL UNIQUE,
  `nama_wajib_pajak` VARCHAR(150) NOT NULL,
  `tanggal_pendaftaran` DATE NOT NULL,
  `tanggal_lahir` DATE NULL,
  `jenis_kelamin` ENUM('Laki-laki', 'Perempuan', 'Lainnya') NULL,
  `status_wajib_pajak` ENUM('Aktif', 'Nonaktif', 'Suspen') DEFAULT 'Aktif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_wp_jenis` FOREIGN KEY (`id_jenis_wajib_pajak`) 
    REFERENCES `jenis_wajib_pajak` (`id_jenis_wajib_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_wp_kantor` FOREIGN KEY (`id_kantor`) 
    REFERENCES `kantor_pajak` (`id_kantor`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 6. Tabel: alamat_wajib_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `alamat_wajib_pajak`;
CREATE TABLE `alamat_wajib_pajak` (
  `id_alamat` INT AUTO_INCREMENT PRIMARY KEY,
  `id_wajib_pajak` INT NOT NULL,
  `alamat_lengkap` TEXT NOT NULL,
  `provinsi` VARCHAR(100) NOT NULL,
  `kota` VARCHAR(100) NOT NULL,
  `kecamatan` VARCHAR(100) NOT NULL,
  `kode_pos` VARCHAR(10) NOT NULL,
  `jenis_alamat` VARCHAR(50) NOT NULL,
  CONSTRAINT `fk_alamat_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 7. Tabel: kontak_wajib_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `kontak_wajib_pajak`;
CREATE TABLE `kontak_wajib_pajak` (
  `id_kontak` INT AUTO_INCREMENT PRIMARY KEY,
  `id_wajib_pajak` INT NOT NULL,
  `nomor_telepon` VARCHAR(30) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `jenis_kontak` VARCHAR(50) NOT NULL,
  CONSTRAINT `fk_kontak_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 8. Tabel: jenis_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `jenis_pajak`;
CREATE TABLE `jenis_pajak` (
  `id_jenis_pajak` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_pajak` VARCHAR(20) NOT NULL UNIQUE,
  `nama_pajak` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 9. Tabel: objek_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `objek_pajak`;
CREATE TABLE `objek_pajak` (
  `id_objek_pajak` INT AUTO_INCREMENT PRIMARY KEY,
  `id_wajib_pajak` INT NOT NULL,
  `id_jenis_pajak` INT NOT NULL,
  `nama_objek` VARCHAR(150) NOT NULL,
  `alamat_objek` TEXT NOT NULL,
  `nilai_objek` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT NULL,
  CONSTRAINT `fk_objek_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_objek_jenis` FOREIGN KEY (`id_jenis_pajak`) 
    REFERENCES `jenis_pajak` (`id_jenis_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 10. Tabel: tarif_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `tarif_pajak`;
CREATE TABLE `tarif_pajak` (
  `id_tarif` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jenis_pajak` INT NOT NULL,
  `nama_tarif` VARCHAR(150) NOT NULL,
  `jenis_perhitungan` VARCHAR(50) NOT NULL DEFAULT 'Persentase',
  `nilai_tarif` DECIMAL(10,4) NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_berakhir` DATE NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif',
  CONSTRAINT `fk_tarif_jenis` FOREIGN KEY (`id_jenis_pajak`) 
    REFERENCES `jenis_pajak` (`id_jenis_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 11. Tabel: masa_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `masa_pajak`;
CREATE TABLE `masa_pajak` (
  `id_masa_pajak` INT AUTO_INCREMENT PRIMARY KEY,
  `id_jenis_pajak` INT NOT NULL,
  `tahun_pajak` INT NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_berakhir` DATE NOT NULL,
  `batas_pelaporan` DATE NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'Berjalan',
  CONSTRAINT `fk_masa_jenis` FOREIGN KEY (`id_jenis_pajak`) 
    REFERENCES `jenis_pajak` (`id_jenis_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 12. Tabel: jenis_dokumen
-- -------------------------------------------------------
DROP TABLE IF EXISTS `jenis_dokumen`;
CREATE TABLE `jenis_dokumen` (
  `id_jenis_dokumen` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_dokumen` VARCHAR(150) NOT NULL UNIQUE,
  `deskripsi` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 14. Tabel: status_spt (dibuat sebelum spt)
-- -------------------------------------------------------
DROP TABLE IF EXISTS `status_spt`;
CREATE TABLE `status_spt` (
  `id_status_spt` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_status` VARCHAR(50) NOT NULL UNIQUE,
  `keterangan` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 15. Tabel: spt
-- -------------------------------------------------------
DROP TABLE IF EXISTS `spt`;
CREATE TABLE `spt` (
  `id_spt` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_spt` VARCHAR(50) NOT NULL UNIQUE,
  `id_wajib_pajak` INT NOT NULL,
  `id_jenis_pajak` INT NOT NULL,
  `id_masa_pajak` INT NOT NULL,
  `id_status_spt` INT NOT NULL,
  `id_petugas` INT NULL,
  `tanggal_pelaporan` DATE NOT NULL,
  `total_pajak` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT NULL,
  CONSTRAINT `fk_spt_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_jenis` FOREIGN KEY (`id_jenis_pajak`) 
    REFERENCES `jenis_pajak` (`id_jenis_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_masa` FOREIGN KEY (`id_masa_pajak`) 
    REFERENCES `masa_pajak` (`id_masa_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_status` FOREIGN KEY (`id_status_spt`) 
    REFERENCES `status_spt` (`id_status_spt`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_spt_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `petugas_pajak` (`id_petugas`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 13. Tabel: dokumen_perpajakan
-- -------------------------------------------------------
DROP TABLE IF EXISTS `dokumen_perpajakan`;
CREATE TABLE `dokumen_perpajakan` (
  `id_dokumen` INT AUTO_INCREMENT PRIMARY KEY,
  `id_wajib_pajak` INT NOT NULL,
  `id_jenis_dokumen` INT NOT NULL,
  `id_spt` INT NULL,
  `nama_file` VARCHAR(255) NOT NULL,
  `lokasi_file` VARCHAR(255) NOT NULL,
  `tanggal_upload` DATETIME NOT NULL,
  `keterangan` TEXT NULL,
  CONSTRAINT `fk_dok_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_dok_jenis` FOREIGN KEY (`id_jenis_dokumen`) 
    REFERENCES `jenis_dokumen` (`id_jenis_dokumen`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_dok_spt` FOREIGN KEY (`id_spt`) 
    REFERENCES `spt` (`id_spt`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 16. Tabel: detail_spt
-- -------------------------------------------------------
DROP TABLE IF EXISTS `detail_spt`;
CREATE TABLE `detail_spt` (
  `id_detail_spt` INT AUTO_INCREMENT PRIMARY KEY,
  `id_spt` INT NOT NULL,
  `id_objek_pajak` INT NOT NULL,
  `id_tarif` INT NOT NULL,
  `dasar_pengenaan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tarif_digunakan` DECIMAL(10,4) NOT NULL,
  `jumlah_pajak` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` TEXT NULL,
  CONSTRAINT `fk_detail_spt` FOREIGN KEY (`id_spt`) 
    REFERENCES `spt` (`id_spt`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detail_objek` FOREIGN KEY (`id_objek_pajak`) 
    REFERENCES `objek_pajak` (`id_objek_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_detail_tarif` FOREIGN KEY (`id_tarif`) 
    REFERENCES `tarif_pajak` (`id_tarif`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 17. Tabel: riwayat_status_spt
-- -------------------------------------------------------
DROP TABLE IF EXISTS `riwayat_status_spt`;
CREATE TABLE `riwayat_status_spt` (
  `id_riwayat_spt` INT AUTO_INCREMENT PRIMARY KEY,
  `id_spt` INT NOT NULL,
  `id_status_spt` INT NOT NULL,
  `id_petugas` INT NULL,
  `tanggal_perubahan` DATETIME NOT NULL,
  `catatan` TEXT NULL,
  CONSTRAINT `fk_riwayat_spt` FOREIGN KEY (`id_spt`) 
    REFERENCES `spt` (`id_spt`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_riwayat_status` FOREIGN KEY (`id_status_spt`) 
    REFERENCES `status_spt` (`id_status_spt`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_riwayat_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `petugas_pajak` (`id_petugas`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 18. Tabel: tagihan_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `tagihan_pajak`;
CREATE TABLE `tagihan_pajak` (
  `id_tagihan` INT AUTO_INCREMENT PRIMARY KEY,
  `id_spt` INT NOT NULL,
  `nomor_tagihan` VARCHAR(50) NOT NULL UNIQUE,
  `tanggal_tagihan` DATE NOT NULL,
  `tanggal_jatuh_tempo` DATE NOT NULL,
  `jumlah_tagihan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status_tagihan` ENUM('Belum Bayar', 'Dibayar Sebagian', 'Lunas', 'Kadaluarsa') DEFAULT 'Belum Bayar',
  CONSTRAINT `fk_tagihan_spt` FOREIGN KEY (`id_spt`) 
    REFERENCES `spt` (`id_spt`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 19. Tabel: metode_pembayaran
-- -------------------------------------------------------
DROP TABLE IF EXISTS `metode_pembayaran`;
CREATE TABLE `metode_pembayaran` (
  `id_metode` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_metode` VARCHAR(100) NOT NULL UNIQUE,
  `deskripsi` TEXT NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 20. Tabel: pembayaran_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `pembayaran_pajak`;
CREATE TABLE `pembayaran_pajak` (
  `id_pembayaran` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tagihan` INT NOT NULL,
  `id_metode` INT NOT NULL,
  `nomor_transaksi` VARCHAR(100) NOT NULL UNIQUE,
  `tanggal_pembayaran` DATETIME NOT NULL,
  `jumlah_pembayaran` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status_pembayaran` ENUM('Menunggu Konfirmasi', 'Berhasil', 'Gagal', 'Dibatalkan') DEFAULT 'Berhasil',
  CONSTRAINT `fk_bayar_tagihan` FOREIGN KEY (`id_tagihan`) 
    REFERENCES `tagihan_pajak` (`id_tagihan`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_bayar_metode` FOREIGN KEY (`id_metode`) 
    REFERENCES `metode_pembayaran` (`id_metode`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 21. Tabel: bukti_pembayaran
-- -------------------------------------------------------
DROP TABLE IF EXISTS `bukti_pembayaran`;
CREATE TABLE `bukti_pembayaran` (
  `id_bukti` INT AUTO_INCREMENT PRIMARY KEY,
  `id_pembayaran` INT NOT NULL,
  `nomor_bukti` VARCHAR(100) NOT NULL UNIQUE,
  `nama_file` VARCHAR(255) NOT NULL,
  `lokasi_file` VARCHAR(255) NOT NULL,
  `tanggal_upload` DATETIME NOT NULL,
  CONSTRAINT `fk_bukti_bayar` FOREIGN KEY (`id_pembayaran`) 
    REFERENCES `pembayaran_pajak` (`id_pembayaran`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 22. Tabel: denda_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `denda_pajak`;
CREATE TABLE `denda_pajak` (
  `id_denda` INT AUTO_INCREMENT PRIMARY KEY,
  `id_tagihan` INT NOT NULL,
  `alasan_denda` VARCHAR(255) NOT NULL,
  `dasar_perhitungan` VARCHAR(255) NOT NULL,
  `jumlah_denda` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tanggal_pengenaan` DATE NOT NULL,
  `status_denda` ENUM('Aktif', 'Lunas', 'Dibatalkan') DEFAULT 'Aktif',
  CONSTRAINT `fk_denda_tagihan` FOREIGN KEY (`id_tagihan`) 
    REFERENCES `tagihan_pajak` (`id_tagihan`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 23. Tabel: jenis_permohonan
-- -------------------------------------------------------
DROP TABLE IF EXISTS `jenis_permohonan`;
CREATE TABLE `jenis_permohonan` (
  `id_jenis_permohonan` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_permohonan` VARCHAR(150) NOT NULL UNIQUE,
  `deskripsi` TEXT NULL,
  `status` ENUM('Aktif', 'Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 24. Tabel: permohonan_pajak
-- -------------------------------------------------------
DROP TABLE IF EXISTS `permohonan_pajak`;
CREATE TABLE `permohonan_pajak` (
  `id_permohonan` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_permohonan` VARCHAR(50) NOT NULL UNIQUE,
  `id_wajib_pajak` INT NOT NULL,
  `id_jenis_permohonan` INT NOT NULL,
  `id_petugas` INT NULL,
  `tanggal_pengajuan` DATE NOT NULL,
  `status_permohonan` ENUM('Diajukan', 'Diverifikasi', 'Disetujui', 'Ditolak', 'Selesai') DEFAULT 'Diajukan',
  `keterangan` TEXT NULL,
  CONSTRAINT `fk_mohon_wp` FOREIGN KEY (`id_wajib_pajak`) 
    REFERENCES `wajib_pajak` (`id_wajib_pajak`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_mohon_jenis` FOREIGN KEY (`id_jenis_permohonan`) 
    REFERENCES `jenis_permohonan` (`id_jenis_permohonan`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_mohon_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `petugas_pajak` (`id_petugas`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 25. Tabel: riwayat_permohonan
-- -------------------------------------------------------
DROP TABLE IF EXISTS `riwayat_permohonan`;
CREATE TABLE `riwayat_permohonan` (
  `id_riwayat_permohonan` INT AUTO_INCREMENT PRIMARY KEY,
  `id_permohonan` INT NOT NULL,
  `id_petugas` INT NULL,
  `status_sebelumnya` VARCHAR(50) NOT NULL,
  `status_baru` VARCHAR(50) NOT NULL,
  `tanggal_perubahan` DATETIME NOT NULL,
  `catatan` TEXT NULL,
  CONSTRAINT `fk_riwayat_mohon` FOREIGN KEY (`id_permohonan`) 
    REFERENCES `permohonan_pajak` (`id_permohonan`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_riwayat_mohon_petugas` FOREIGN KEY (`id_petugas`) 
    REFERENCES `petugas_pajak` (`id_petugas`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Aktifkan kembali Foreign Key Check
SET FOREIGN_KEY_CHECKS = 1;


-- =======================================================
-- SEED DATA UNTUK PENGUJIAN DAN PEMBELAJARAN
-- Password default semua akun adalah: admin123
-- Hash: $2y$10$JtaPc0/Kd3Vh5r1UxRXcG.G0OTwSUVV67FMghpvZVeqDl32af.KPu
-- =======================================================

-- 1. kantor_pajak
INSERT INTO `kantor_pajak` (`id_kantor`, `kode_kantor`, `nama_kantor`, `alamat`, `kota`, `nomor_telepon`, `email`) VALUES
(1, 'KPP-011', 'KPP Pratama Jakarta Gambir Satu', 'Jl. Gambir Raya No. 10', 'Jakarta Pusat', '021-3849101', 'kpp.011@pajak.go.id'),
(2, 'KPP-012', 'KPP Pratama Jakarta Tebet', 'Jl. Prof. Dr. Soepomo No. 25', 'Jakarta Selatan', '021-8314567', 'kpp.012@pajak.go.id'),
(3, 'KPP-041', 'KPP Pratama Bandung Cibeunying', 'Jl. Supratman No. 88', 'Bandung', '022-7201234', 'kpp.041@pajak.go.id');

-- 2. petugas_pajak
INSERT INTO `petugas_pajak` (`id_petugas`, `id_kantor`, `nama_petugas`, `nomor_identitas`, `jabatan`, `nomor_telepon`, `email`, `status`) VALUES
(1, 1, 'Budi Wicaksono, S.E.', '198501152010121001', 'Administrator Sistem Perpajakan', '081234567890', 'budi.wicaksono@pajak.go.id', 'Aktif'),
(2, 1, 'Rina Anggraini, S.Ak.', '199004202014022003', 'Account Representative', '081298765432', 'rina.anggraini@pajak.go.id', 'Aktif'),
(3, 2, 'Dedi Kurniawan, S.Sos.', '198811122012011002', 'Fungsional Pemeriksa Pajak', '081377889900', 'dedi.kurniawan@pajak.go.id', 'Aktif'),
(4, 3, 'Sri Mulyani Hidayat, M.M.', '198207082006042001', 'Kepala Seksi Pengawasan', '081122334455', 'sri.mulyani@pajak.go.id', 'Aktif');

-- 3. akun_petugas
INSERT INTO `akun_petugas` (`id_akun`, `id_petugas`, `nama_pengguna`, `kata_sandi`, `peran`, `status_akun`) VALUES
(1, 1, 'admin', '$2y$10$JtaPc0/Kd3Vh5r1UxRXcG.G0OTwSUVV67FMghpvZVeqDl32af.KPu', 'Administrator', 'Aktif'),
(2, 2, 'petugas', '$2y$10$JtaPc0/Kd3Vh5r1UxRXcG.G0OTwSUVV67FMghpvZVeqDl32af.KPu', 'Petugas Pajak', 'Aktif'),
(3, 3, 'dedi_pemeriksa', '$2y$10$JtaPc0/Kd3Vh5r1UxRXcG.G0OTwSUVV67FMghpvZVeqDl32af.KPu', 'Pemeriksa Pajak', 'Aktif'),
(4, 4, 'sri_kasi', '$2y$10$JtaPc0/Kd3Vh5r1UxRXcG.G0OTwSUVV67FMghpvZVeqDl32af.KPu', 'Kepala Seksi', 'Aktif');

-- 4. jenis_wajib_pajak
INSERT INTO `jenis_wajib_pajak` (`id_jenis_wajib_pajak`, `nama_jenis`, `keterangan`) VALUES
(1, 'Orang Pribadi', 'Wajib Pajak perorangan yang memiliki penghasilan sendiri'),
(2, 'Badan Usaha', 'Wajib Pajak badan hukum berupa PT, CV, Yayasan, Koperasi, dll'),
(3, 'Bendahara Pemerintah', 'Instansi pemerintah yang ditunjuk sebagai pemotong/pemungut pajak');

-- 5. wajib_pajak
INSERT INTO `wajib_pajak` (`id_wajib_pajak`, `id_jenis_wajib_pajak`, `id_kantor`, `nomor_npwp`, `nama_wajib_pajak`, `tanggal_pendaftaran`, `tanggal_lahir`, `jenis_kelamin`, `status_wajib_pajak`) VALUES
(1, 1, 1, '09.123.456.7-011.000', 'Ahmad Pratama', '2021-03-15', '1992-06-18', 'Laki-laki', 'Aktif'),
(2, 1, 1, '08.234.567.8-011.000', 'Dewi Lestari', '2022-01-10', '1995-10-24', 'Perempuan', 'Aktif'),
(3, 2, 1, '01.345.678.9-011.000', 'PT Sinar Makmur Mandiri', '2020-05-20', NULL, NULL, 'Aktif'),
(4, 2, 2, '02.456.789.0-012.000', 'CV Nusantara Kreatif', '2022-08-14', NULL, NULL, 'Aktif'),
(5, 1, 2, '07.567.890.1-012.000', 'Bambang Sudarsono', '2019-11-05', '1987-03-02', 'Laki-laki', 'Aktif'),
(6, 3, 3, '00.678.901.2-041.000', 'Bendahara Dinas Pendidikan Kota Bandung', '2018-02-01', NULL, NULL, 'Aktif');

-- 6. alamat_wajib_pajak
INSERT INTO `alamat_wajib_pajak` (`id_alamat`, `id_wajib_pajak`, `alamat_lengkap`, `provinsi`, `kota`, `kecamatan`, `kode_pos`, `jenis_alamat`) VALUES
(1, 1, 'Jl. Kebon Sirih No. 45 RT 02/03', 'DKI Jakarta', 'Jakarta Pusat', 'Menteng', '10340', 'Domisili'),
(2, 2, 'Jl. Cempaka Putih Tengah No. 12', 'DKI Jakarta', 'Jakarta Pusat', 'Cempaka Putih', '10510', 'KTP'),
(3, 3, 'Gedung Menara Batavia Lt. 15, Jl. KH Mas Mansyur Kav. 126', 'DKI Jakarta', 'Jakarta Pusat', 'Tanah Abang', '10220', 'Kantor Pusat'),
(4, 4, 'Jl. Casablanca Raya Blok B2 No. 8', 'DKI Jakarta', 'Jakarta Selatan', 'Tebet', '12870', 'Kantor Pusat'),
(5, 5, 'Jl. Bukit Duri Utara No. 19', 'DKI Jakarta', 'Jakarta Selatan', 'Tebet', '12840', 'Domisili'),
(6, 6, 'Jl. Wastukencana No. 2', 'Jawa Barat', 'Bandung', 'Sumur Bandung', '40117', 'Kantor Instansi');

-- 7. kontak_wajib_pajak
INSERT INTO `kontak_wajib_pajak` (`id_kontak`, `id_wajib_pajak`, `nomor_telepon`, `email`, `jenis_kontak`) VALUES
(1, 1, '081211223344', 'ahmad.pratama@gmail.com', 'Ponsel Pribadi'),
(2, 2, '081322334455', 'dewi.lestari@yahoo.com', 'Ponsel Pribadi'),
(3, 3, '021-57901234', 'tax@sinarmakmur.co.id', 'Email Resmi Perusahaan'),
(4, 4, '021-83705678', 'keuangan@nusantarakreatif.com', 'Telepon Kantor'),
(5, 5, '081833445566', 'bambang.sudarsono@gmail.com', 'Ponsel Pribadi'),
(6, 6, '022-4231234', 'disdik@bandung.go.id', 'Email Resmi');

-- 8. jenis_pajak
INSERT INTO `jenis_pajak` (`id_jenis_pajak`, `kode_pajak`, `nama_pajak`, `deskripsi`, `status`) VALUES
(1, 'PPH21', 'Pajak Penghasilan Pasal 21 (PPh 21)', 'Pajak atas penghasilan berupa gaji, upah, honorarium, tunjangan pegawai dan non-pegawai', 'Aktif'),
(2, 'PPH23', 'Pajak Penghasilan Pasal 23 (PPh 23)', 'Pajak atas penghasilan dari modal, penyerahan jasa, atau hadiah dan penghargaan', 'Aktif'),
(3, 'PPN', 'Pajak Pertambahan Nilai (PPN)', 'Pajak atas konsumsi Barang Kena Pajak dan Jasa Kena Pajak di dalam Daerah Pabean', 'Aktif'),
(4, 'PBB', 'Pajak Bumi dan Bangunan (PBB)', 'Pajak kebendaan atas kepemilikan dan pemanfaatan tanah dan/atau bangunan', 'Aktif');

-- 9. objek_pajak
INSERT INTO `objek_pajak` (`id_objek_pajak`, `id_wajib_pajak`, `id_jenis_pajak`, `nama_objek`, `alamat_objek`, `nilai_objek`, `keterangan`) VALUES
(1, 1, 1, 'Gaji Pegawai Swasta PT Digital Media', 'Jl. Sudirman Kav. 50 Jakarta Pusat', 120000000.00, 'Penghasilan bruto tahunan'),
(2, 2, 1, 'Honorarium Tenaga Ahli Desain', 'Jl. Cempaka Putih Tengah No. 12', 45000000.00, 'Jasa profesional bebas'),
(3, 3, 3, 'Penjualan Barang Komputer & Elektronik', 'Jl. KH Mas Mansyur Kav. 126', 850000000.00, 'Penyerahan BKP Masa Pajak'),
(4, 3, 2, 'Pembayaran Jasa Konsultan Manajemen', 'Jl. KH Mas Mansyur Kav. 126', 50000000.00, 'Objek pemotongan PPh Pasal 23'),
(5, 4, 3, 'Jasa Produksi Periklanan & Digital Marketing', 'Jl. Casablanca Raya Blok B2 No. 8', 250000000.00, 'Penyerahan JKP'),
(6, 5, 4, 'Tanah dan Bangunan Rumah Tinggal Tebet', 'Jl. Bukit Duri Utara No. 19 Tebet', 1250000000.00, 'NJOP Bumi dan Bangunan');

-- 10. tarif_pajak
INSERT INTO `tarif_pajak` (`id_tarif`, `id_jenis_pajak`, `nama_tarif`, `jenis_perhitungan`, `nilai_tarif`, `tanggal_mulai`, `tanggal_berakhir`, `status`) VALUES
(1, 1, 'PPh 21 Lapisan 1 (0 s.d 60 Juta)', 'Persentase', 5.0000, '2022-01-01', NULL, 'Aktif'),
(2, 1, 'PPh 21 Lapisan 2 (> 60 Juta s.d 250 Juta)', 'Persentase', 15.0000, '2022-01-01', NULL, 'Aktif'),
(3, 2, 'PPh 23 Tarif Jasa Teknik & Manajemen', 'Persentase', 2.0000, '2020-01-01', NULL, 'Aktif'),
(4, 3, 'PPN Tarif Standar 11%', 'Persentase', 11.0000, '2022-04-01', NULL, 'Aktif'),
(5, 4, 'PBB Tarif Umum Perkotaan', 'Persentase', 0.2000, '2021-01-01', NULL, 'Aktif');

-- 11. masa_pajak
INSERT INTO `masa_pajak` (`id_masa_pajak`, `id_jenis_pajak`, `tahun_pajak`, `tanggal_mulai`, `tanggal_berakhir`, `batas_pelaporan`, `status`) VALUES
(1, 1, 2026, '2026-01-01', '2026-12-31', '2027-03-31', 'Berjalan'),
(2, 2, 2026, '2026-01-01', '2026-01-31', '2026-02-20', 'Tutup Buku'),
(3, 2, 2026, '2026-02-01', '2026-02-28', '2026-03-20', 'Berjalan'),
(4, 3, 2026, '2026-01-01', '2026-01-31', '2026-02-28', 'Tutup Buku'),
(5, 3, 2026, '2026-02-01', '2026-02-28', '2026-03-31', 'Berjalan'),
(6, 4, 2026, '2026-01-01', '2026-12-31', '2026-08-31', 'Berjalan');

-- 12. jenis_dokumen
INSERT INTO `jenis_dokumen` (`id_jenis_dokumen`, `nama_dokumen`, `deskripsi`) VALUES
(1, 'Formulir 1721-A1', 'Bukti pemotongan PPh Pasal 21 bagi pegawai tetap'),
(2, 'Faktur Pajak Keluaran', 'Bukti pungutan PPN yang dibuat oleh Pengusaha Kena Pajak'),
(3, 'Laporan Keuangan Neraca & Laba Rugi', 'Laporan keuangan pendukung pelaporan SPT Tahunan Badan'),
(4, 'Surat Keterangan Domisili Usaha', 'Dokumen legalitas lokasi operasional wajib pajak'),
(5, 'Surat Kuasa Khusus Perpajakan', 'Surat pelimpahan kewenangan pengurusan administrasi pajak');

-- 14. status_spt
INSERT INTO `status_spt` (`id_status_spt`, `nama_status`, `keterangan`) VALUES
(1, 'Draft', 'SPT baru diinput oleh wajib pajak dan belum dikirimkan'),
(2, 'Diajukan', 'SPT telah dikirimkan secara resmi ke kantor pajak'),
(3, 'Dalam Pemeriksaan', 'SPT sedang dalam proses verifikasi dan validasi oleh petugas pajak'),
(4, 'Diterima', 'SPT telah diverifikasi, data valid dan diterima lengkap'),
(5, 'Ditolak', 'SPT tidak memenuhi syarat legal atau kelengkapan ditolak'),
(6, 'Perlu Perbaikan', 'SPT dikembalikan ke wajib pajak untuk diperbaiki dalam batas waktu');

-- 15. spt
INSERT INTO `spt` (`id_spt`, `nomor_spt`, `id_wajib_pajak`, `id_jenis_pajak`, `id_masa_pajak`, `id_status_spt`, `id_petugas`, `tanggal_pelaporan`, `total_pajak`, `keterangan`) VALUES
(1, 'SPT-2026-0001', 1, 1, 1, 4, 2, '2026-02-15', 6000000.00, 'SPT Tahunan PPh Orang Pribadi 1770 S'),
(2, 'SPT-2026-0002', 2, 1, 1, 2, 2, '2026-02-20', 2250000.00, 'SPT Tahunan PPh Orang Pribadi 1770'),
(3, 'SPT-2026-0003', 3, 3, 4, 4, 3, '2026-02-10', 93500000.00, 'SPT Masa PPN Masa Januari 2026'),
(4, 'SPT-2026-0004', 3, 2, 2, 4, 3, '2026-02-12', 1000000.00, 'SPT Masa PPh Pasal 23 Januari 2026'),
(5, 'SPT-2026-0005', 4, 3, 4, 3, 3, '2026-02-25', 27500000.00, 'SPT Masa PPN Masa Januari 2026'),
(6, 'SPT-2026-0006', 5, 4, 6, 2, 2, '2026-02-28', 2500000.00, 'SPT PBB Tahun Pajak 2026');

-- 13. dokumen_perpajakan
INSERT INTO `dokumen_perpajakan` (`id_dokumen`, `id_wajib_pajak`, `id_jenis_dokumen`, `id_spt`, `nama_file`, `lokasi_file`, `tanggal_upload`, `keterangan`) VALUES
(1, 1, 1, 1, 'bukti_potong_1721a1_ahmad.pdf', 'assets/uploads/dokumen/dok_1.pdf', '2026-02-15 09:30:00', 'Lampiran 1721-A1 asli dari perusahaan'),
(2, 3, 2, 3, 'rekap_faktur_pajak_januari.pdf', 'assets/uploads/dokumen/dok_2.pdf', '2026-02-10 14:15:00', 'Rekapitulasi faktur pajak keluaran Januari'),
(3, 3, 3, 3, 'laporan_keuangan_januari2026.pdf', 'assets/uploads/dokumen/dok_3.pdf', '2026-02-10 14:20:00', 'Laporan laba rugi komparatif'),
(4, 4, 2, 5, 'faktur_pajak_cv_nusantara.pdf', 'assets/uploads/dokumen/dok_4.pdf', '2026-02-25 11:00:00', 'Faktur pajak penyerahan jasa');

-- 16. detail_spt
INSERT INTO `detail_spt` (`id_detail_spt`, `id_spt`, `id_objek_pajak`, `id_tarif`, `dasar_pengenaan`, `tarif_digunakan`, `jumlah_pajak`, `keterangan`) VALUES
(1, 1, 1, 1, 60000000.00, 5.0000, 3000000.00, 'Lapisan 1 tarif 5%'),
(2, 1, 1, 2, 20000000.00, 15.0000, 3000000.00, 'Lapisan 2 tarif 15%'),
(3, 2, 2, 1, 45000000.00, 5.0000, 2250000.00, 'Penghasilan honor desain grafis'),
(4, 3, 3, 4, 850000000.00, 11.0000, 93500000.00, 'Pajak keluaran penjualan elektronik'),
(5, 4, 4, 3, 50000000.00, 2.0000, 1000000.00, 'Pemotongan PPh 23 jasa konsultan'),
(6, 5, 5, 4, 250000000.00, 11.0000, 27500000.00, 'PPN jasa periklanan'),
(7, 6, 6, 5, 1250000000.00, 0.2000, 2500000.00, 'PBB objek rumah tinggal Tebet');

-- 17. riwayat_status_spt
INSERT INTO `riwayat_status_spt` (`id_riwayat_spt`, `id_spt`, `id_status_spt`, `id_petugas`, `tanggal_perubahan`, `catatan`) VALUES
(1, 1, 1, 2, '2026-02-14 10:00:00', 'Draft awal dibuat oleh wajib pajak'),
(2, 1, 2, 2, '2026-02-15 08:30:00', 'Wajib pajak mengajukan SPT secara online'),
(3, 1, 3, 2, '2026-02-15 09:00:00', 'SPT diperiksa oleh Account Representative'),
(4, 1, 4, 2, '2026-02-15 11:30:00', 'SPT dinyatakan lengkap dan diterima sah'),
(5, 3, 2, 3, '2026-02-10 13:00:00', 'SPT Masa PPN diajukan'),
(6, 3, 4, 3, '2026-02-10 16:00:00', 'Faktur validasi berhasil dicocokkan, SPT Diterima'),
(7, 5, 2, 3, '2026-02-25 10:00:00', 'SPT Masa PPN CV Nusantara diajukan'),
(8, 5, 3, 3, '2026-02-25 14:00:00', 'Sedang dalam verifikasi bukti potong');

-- 18. tagihan_pajak
INSERT INTO `tagihan_pajak` (`id_tagihan`, `id_spt`, `nomor_tagihan`, `tanggal_tagihan`, `tanggal_jatuh_tempo`, `jumlah_tagihan`, `status_tagihan`) VALUES
(1, 1, 'BILL-2026-0001', '2026-02-15', '2026-03-31', 6000000.00, 'Lunas'),
(2, 2, 'BILL-2026-0002', '2026-02-20', '2026-03-31', 2250000.00, 'Belum Bayar'),
(3, 3, 'BILL-2026-0003', '2026-02-10', '2026-02-28', 93500000.00, 'Lunas'),
(4, 4, 'BILL-2026-0004', '2026-02-12', '2026-02-20', 1000000.00, 'Lunas'),
(5, 5, 'BILL-2026-0005', '2026-02-25', '2026-03-15', 27500000.00, 'Dibayar Sebagian'),
(6, 6, 'BILL-2026-0006', '2026-02-28', '2026-08-31', 2500000.00, 'Belum Bayar');

-- 19. metode_pembayaran
INSERT INTO `metode_pembayaran` (`id_metode`, `nama_metode`, `deskripsi`, `status`) VALUES
(1, 'Bank Mandiri Virtual Account', 'Pembayaran via Mandiri Bill Payment / Livin by Mandiri', 'Aktif'),
(2, 'Bank BCA Virtual Account', 'Pembayaran via BCA KlikBCA / myBCA / ATM BCA', 'Aktif'),
(3, 'Bank BRI Virtual Account (BRIVA)', 'Pembayaran via BRImo dan Teller Kantor BRI', 'Aktif'),
(4, 'QRIS Pajak Nasional', 'Pembayaran instan melalui pemindaian kode QRIS serbaguna', 'Aktif'),
(5, 'Teller Kantor Pos Indonesia', 'Pembayaran tunai langsung di loket Pos Indonesia', 'Aktif');

-- 20. pembayaran_pajak
INSERT INTO `pembayaran_pajak` (`id_pembayaran`, `id_tagihan`, `id_metode`, `nomor_transaksi`, `tanggal_pembayaran`, `jumlah_pembayaran`, `status_pembayaran`) VALUES
(1, 1, 2, 'TRX-2026-0215-0001', '2026-02-16 10:15:30', 6000000.00, 'Berhasil'),
(2, 3, 1, 'TRX-2026-0210-0002', '2026-02-11 14:22:10', 93500000.00, 'Berhasil'),
(3, 4, 1, 'TRX-2026-0212-0003', '2026-02-13 09:40:00', 1000000.00, 'Berhasil'),
(4, 5, 4, 'TRX-2026-0226-0004', '2026-02-26 15:05:45', 15000000.00, 'Berhasil');

-- 21. bukti_pembayaran
INSERT INTO `bukti_pembayaran` (`id_bukti`, `id_pembayaran`, `nomor_bukti`, `nama_file`, `lokasi_file`, `tanggal_upload`) VALUES
(1, 1, 'BPN-00123984', 'struk_bca_ahmad.pdf', 'assets/uploads/bukti/bukti_1.pdf', '2026-02-16 10:16:00'),
(2, 2, 'BPN-00124501', 'ntpn_mandiri_pt_sinar.pdf', 'assets/uploads/bukti/bukti_2.pdf', '2026-02-11 14:25:00'),
(3, 3, 'BPN-00124899', 'struk_mandiri_pph23.pdf', 'assets/uploads/bukti/bukti_3.pdf', '2026-02-13 09:42:00'),
(4, 4, 'BPN-00125672', 'qris_cv_nusantara.png', 'assets/uploads/bukti/bukti_4.png', '2026-02-26 15:06:00');

-- 22. denda_pajak
INSERT INTO `denda_pajak` (`id_denda`, `id_tagihan`, `alasan_denda`, `dasar_perhitungan`, `jumlah_denda`, `tanggal_pengenaan`, `status_denda`) VALUES
(1, 4, 'Keterlambatan penyetoran PPh 23 Masa Januari', 'Pasal 9 ayat 2a UU KUP (bunga 1.8% sanksi telat bayar)', 18000.00, '2026-02-21', 'Lunas'),
(2, 5, 'Keterlambatan pelaporan SPT Masa PPN periode sebelumnya', 'Sanksi Administrasi Denda Pasal 7 UU KUP', 500000.00, '2026-02-27', 'Aktif');

-- 23. jenis_permohonan
INSERT INTO `jenis_permohonan` (`id_jenis_permohonan`, `nama_permohonan`, `deskripsi`, `status`) VALUES
(1, 'Permohonan Angsuran Pembayaran Pajak', 'Permohonan mencicil utang pajak dalam periode tertentu', 'Aktif'),
(2, 'Permohonan Pengurangan Ketetapan PBB', 'Permohonan keringanan pajak bumi dan bangunan karena kondisi khusus/wajar', 'Aktif'),
(3, 'Permohonan Restitusi Kelebihan Pembayaran', 'Permohonan pengembalian dana kelebihan setor pajak (Restitusi)', 'Aktif'),
(4, 'Permohonan Pemindahbukuan (Pbk)', 'Permohonan pemindahan penerimaan pajak karena salah setor kode atau identitas', 'Aktif');

-- 24. permohonan_pajak
INSERT INTO `permohonan_pajak` (`id_permohonan`, `nomor_permohonan`, `id_wajib_pajak`, `id_jenis_permohonan`, `id_petugas`, `tanggal_pengajuan`, `status_permohonan`, `keterangan`) VALUES
(1, 'REQ-2026-0001', 4, 1, 3, '2026-02-26', 'Diverifikasi', 'Permohonan angsuran sisa tagihan PPN menjadi 2 kali pembayaran'),
(2, 'REQ-2026-0002', 5, 2, 2, '2026-03-01', 'Diajukan', 'Permohonan pengurangan PBB lansia pensiunan'),
(3, 'REQ-2026-0003', 3, 3, 3, '2026-02-18', 'Disetujui', 'Restitusi lebih bayar PPh Badan tahun lalu');

-- 25. riwayat_permohonan
INSERT INTO `riwayat_permohonan` (`id_riwayat_permohonan`, `id_permohonan`, `id_petugas`, `status_sebelumnya`, `status_baru`, `tanggal_perubahan`, `catatan`) VALUES
(1, 1, 3, 'Diajukan', 'Diverifikasi', '2026-02-26 14:00:00', 'Berkas formulir angsuran dan rekening koran dinyatakan lengkap'),
(2, 2, 2, 'Draft', 'Diajukan', '2026-03-01 09:30:00', 'Pengajuan berkas secara online melalui portal permohonan'),
(3, 3, 3, 'Diajukan', 'Diverifikasi', '2026-02-20 11:00:00', 'Pemeriksaan laporan audit keuangan eksternal'),
(4, 3, 3, 'Diverifikasi', 'Disetujui', '2026-02-24 15:30:00', 'SKPLB telah diterbitkan dan permohonan restitusi disetujui');
