# Sistem Informasi Perpajakan (SIP)

Aplikasi web administrasi perpajakan komprehensif berbasis **PHP Native (PHP 8+)** dan **MySQL**, dirancang khusus sebagai media pembelajaran basis data relasional tingkat lanjut untuk siswa **SMK Rekayasa Perangkat Lunak (RPL)**.

Aplikasi ini mengimplementasikan operasi **CRUD (Create, Read, Update, Delete)** penuh pada **tepat 25 tabel database relasional** yang saling terhubung dengan relasi *One-to-One*, *One-to-Many*, dan *Many-to-Many*, lengkap dengan pencarian, pemfilteran, paginasi, validasi keamanan CSRF, dan grafik analitik dinamis.

---

## 1. Spesifikasi Teknologi

| Komponen | Teknologi yang Digunakan |
| :--- | :--- |
| **Backend** | PHP Native 8.2+ (Tanpa Framework / Pure Native) |
| **Database** | MySQL 8.0+ (Engine InnoDB, Foreign Key Constraints) |
| **Database Driver** | PHP Data Objects (PDO) dengan Prepared Statements |
| **Frontend Styling** | Bootstrap 5.3 (Offline Bundle) |
| **Icons** | Bootstrap Icons 1.11 (Offline Webfonts) |
| **Data Visualization** | Chart.js 4.4.1 (Offline Bundle) |
| **Server Lingkungan** | Laragon (Apache & MySQL) / XAMPP |
| **Database Manager** | phpMyAdmin / MySQL CLI |

---

## 2. Arsitektur Database (25 Tabel Utama)

Database dirancang dengan nama **`db_perpajakan`** dan seluruh entitas, atribut, dan relasinya menggunakan Bahasa Indonesia dengan format `snake_case`.

### A. Data Kantor dan Petugas Pajak
1. `kantor_pajak`: Menyimpan data unit kantor pelayanan pajak (KPP/KP2KP).
2. `petugas_pajak`: Data aparatur pegawai perpajakan (Account Representative / Fungsional).
3. `akun_petugas`: Kredensial login petugas dengan relasi **One-to-One** terhadap `petugas_pajak` (`id_petugas UNIQUE`).

### B. Data Wajib Pajak
4. `jenis_wajib_pajak`: Kategori wajib pajak (Orang Pribadi, Badan Usaha, Bendaharawan).
5. `wajib_pajak`: Master data entitas wajib pajak (NPWP, Nama, TTL, Jenis Kelamin, Status).
6. `alamat_wajib_pajak`: Alamat domisili, korespondensi, maupun usaha (**One-to-Many**).
7. `kontak_wajib_pajak`: Nomor telepon, WhatsApp, dan surel resmi wajib pajak (**One-to-Many**).

### C. Data Perpajakan
8. `jenis_pajak`: Master jenis pajak (PPh 21, PPh 23, PPh Final, PPN, PBB-P2).
9. `objek_pajak`: Objek kena pajak (Tanah & Bangunan, Penghasilan Jasa, Usaha Dagang).
10. `tarif_pajak`: Pengelolaan tarif persentase atau nominal pajak dinamis dari database.
11. `masa_pajak`: Periode masa/tahun pajak dan batas waktu pelaporan SPT.

### D. Dokumen Perpajakan
12. `jenis_dokumen`: Kategori berkas (KTP, NPWP, Bukti Potong 1721-A1, Faktur Pajak).
13. `dokumen_perpajakan`: Berkas fisik digital yang diunggah dan ditautkan ke WP dan SPT.

### E. Pelaporan SPT
14. `status_spt`: Status workflow pelaporan (Draft, Diajukan, Dalam Pemeriksaan, Diterima, Ditolak).
15. `spt`: Surat Pemberitahuan Pajak induk (Tahunan / Masa).
16. `detail_spt`: Rincian pos objek pajak, tarif terpasang, dan perhitungan nominal (**Many-to-Many** bridge).
17. `riwayat_status_spt`: Log audit jejak perjalanan status pemeriksaan SPT (**One-to-Many**).

### F. Tagihan dan Pembayaran
18. `tagihan_pajak`: Surat Ketetapan Pajak (SKP) atau tagihan otomatis dari pelaporan SPT.
19. `metode_pembayaran`: Kanal setor negara (Bank Persepsi, Teller Kantor Pos, Virtual Account, QRIS).
20. `pembayaran_pajak`: Catatan transaksi setoran pajak ke kas negara (Mendukung angsuran).
21. `bukti_pembayaran`: Bukti Penerimaan Negara (BPN) dan Nomor Transaksi Penerimaan Negara (NTPN).
22. `denda_pajak`: Sanksi bunga administrasi atas keterlambatan bayar/lapor.

### G. Permohonan Perpajakan
23. `jenis_permohonan`: Layanan perpajakan (Restitusi Lebih Bayar, Pengurangan PBB, Angsuran Pajak).
24. `permohonan_pajak`: Berkas permohonan yang diajukan oleh wajib pajak.
25. `riwayat_permohonan`: Log audit disposisi dan mutasi status permohonan wajib pajak.

---

## 3. Implementasi Relasi Database

- **One-to-One**: `petugas_pajak` <-> `akun_petugas` (1 pegawai hanya memiliki tepat 1 akun).
- **One-to-Many**:
  - `kantor_pajak` -> `petugas_pajak` & `wajib_pajak`
  - `wajib_pajak` -> `alamat_wajib_pajak`, `kontak_wajib_pajak`, `objek_pajak`, `spt`, `permohonan_pajak`
  - `jenis_pajak` -> `tarif_pajak`, `masa_pajak`, `spt`
  - `spt` -> `detail_spt`, `riwayat_status_spt`, `tagihan_pajak`
  - `tagihan_pajak` -> `pembayaran_pajak`, `denda_pajak`
  - `pembayaran_pajak` -> `bukti_pembayaran`
  - `permohonan_pajak` -> `riwayat_permohonan`
- **Integritas Relasi**:
  - Seluruh relasi dibentengi dengan **Foreign Key Constraints** (InnoDB).
  - Penghapusan data induk yang memiliki keterikatan transaksi dilindungi dengan penanganan `PDOException 23000` (*Integrity Constraint Violation*).

---

## 4. Panduan Menjalankan Aplikasi di Laragon

### Langkah 1: Penempatan Berkas
Pastikan seluruh file aplikasi berada di dalam direktori `www` Laragon:
```
C:\laragon\www\Perpajakan
```
*(Atau tautan simbolik `C:\laragon\www\sistem-perpajakan`)*

### Langkah 2: Jalankan Layanan Laragon
1. Buka aplikasi **Laragon**.
2. Klik tombol **Start All** untuk menyalakan Apache dan MySQL.

### Langkah 3: Impor Basis Data
1. Buka **phpMyAdmin** melalui browser di `http://localhost/phpmyadmin/` atau klik menu **Database** pada Laragon.
2. Buat database baru bernama:
   ```sql
   CREATE DATABASE db_perpajakan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Pilih database `db_perpajakan`, klik tab **Import**, lalu pilih berkas:
   ```
   C:\laragon\www\Perpajakan\database.sql
   ```
   *(Atau impor melalui CLI command prompt: `mysql -u root db_perpajakan < database.sql`)*

### Langkah 4: Akses Aplikasi
Buka peramban (Google Chrome, Microsoft Edge, Mozilla Firefox) dan kunjungi:
```
http://localhost/sistem-perpajakan/
```
*(Atau: `http://localhost/Perpajakan/`)*

---

## 5. Akun Login Pengujian (Demo Accounts)

Aplikasi dilengkapi otentikasi aman berbasis enkripsi hash **`password_hash(PASSWORD_BCRYPT)`**:

| Peran (Role) | Username | Kata Sandi (Password) | Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Akses Penuh ke Seluruh 25 Modul |
| **Petugas Pajak** | `petugas` | `admin123` | Akses Operasional Pajak & Pelaporan |

---

## 6. Fitur Unggulan Sistem

1. **Dashboard Eksekutif Real-Time**:
   - 7 Kartu Penghitung Statistik Dinamis (Total WP, Petugas, Jenis Pajak, SPT, Tagihan, Realisasi Penerimaan Negara, Permohonan).
   - Grafik Batang Realisasi Pembayaran Pajak 6 Bulan Terakhir menggunakan **Chart.js**.
   - Grafik Donat Komposisi Status Pelaporan SPT.
   - 4 Tabel Widget Ringkasan (SPT Terbaru, Penerimaan Terbaru, Tagihan Jatuh Tempo, Permohonan Terkini).
2. **Operasi CRUD Lengkap pada 25 Tabel**:
   - Setiap tabel memiliki halaman terisolasi: `index.php`, `tambah.php`, `edit.php`, `detail.php`, dan `hapus.php`.
   - Pencarian cerdas berbasis multi-kolom (*wildcard search*).
   - Pemfilteran data dinamis berdasarkan kategori/status.
   - Paginasi data terintegrasi.
   - Pilihan relasi Foreign Key menggunakan Dropdown yang informatif (menampilkan nama entitas, bukan nomor ID asing).
3. **Logika Bisnis Perpajakan Terintegrasi**:
   - **Perhitungan Pajak Otomatis**: Saat menginput `detail_spt`, nominal pajak dihitung otomatis berdasarkan tarif dan dikunci (*rate snapshot*) untuk audit masa depan.
   - **Sinkronisasi Tagihan & SPT**: Penambahan/perubahan detail SPT otomatis menyinkronkan akumulasi total pajak pada SPT dan memperbarui tagihan pajak.
   - **Manajemen Pelunasan**: Pencatatan pembayaran secara cerdas mengubah status tagihan menjadi *Lunas* atau *Dibayar Sebagian*.
   - **Audit Trail & Jejak Status**: Perubahan status pelaporan SPT atau permohonan layanan otomatis terekam ke tabel riwayat.
   - **Manajemen Berkas Fisik**: Unggah berkas dokumen perpajakan dan struk NTPN dengan penyimpanan lokal yang terorganisasi.
4. **Keamanan Berlapis**:
   - **PDO Prepared Statements**: Mencegah serangan SQL Injection pada 100% kueri database.
   - **Token CSRF (Cross-Site Request Forgery)**: Mencegah manipulasi formulir dari domain eksternal.
   - **XSS Protection**: Sanitasi keluaran teks dengan `htmlspecialchars()`.
   - **Bcrypt Hashing**: Kata sandi tersimpan aman tanpa plaintext.
   - **Foreign Key Protection**: Penanganan galat integritas relasi tabel dengan dialog konfirmasi aman.

---

## 7. Struktur Berkas Proyek

```
Perpajakan/
│
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── bootstrap-icons.min.css
│   │   ├── style.css
│   │   └── fonts/ (bootstrap-icons.woff & woff2)
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   └── chart.umd.min.js
│   └── uploads/
│       ├── dokumen/
│       └── bukti/
│
├── config/
│   └── koneksi.php           # Koneksi PDO Database terpusat
│
├── includes/
│   ├── fungsi.php            # Kumpulan helper global (CSRF, sanitasi, auth, format)
│   ├── header.php            # Layout navigasi atas & stylesheet
│   ├── sidebar.php           # Navigasi 7 kategori menu (25 modul)
│   └── footer.php            # Modal konfirmasi hapus & script JS
│
├── modules/                  # 25 Modul CRUD (125 Berkas PHP)
│   ├── kantor_pajak/
│   ├── petugas_pajak/
│   ├── akun_petugas/
│   ├── jenis_wajib_pajak/
│   ├── wajib_pajak/
│   ├── alamat_wajib_pajak/
│   ├── kontak_wajib_pajak/
│   ├── jenis_pajak/
│   ├── objek_pajak/
│   ├── tarif_pajak/
│   ├── masa_pajak/
│   ├── jenis_dokumen/
│   ├── dokumen_perpajakan/
│   ├── status_spt/
│   ├── spt/
│   ├── detail_spt/
│   ├── riwayat_status_spt/
│   ├── tagihan_pajak/
│   ├── metode_pembayaran/
│   ├── pembayaran_pajak/
│   ├── bukti_pembayaran/
│   ├── denda_pajak/
│   ├── jenis_permohonan/
│   ├── permohonan_pajak/
│   └── riwayat_permohonan/
│
├── database.sql              # Skema 25 tabel DDL + Data Awal (DML)
├── dashboard.php             # Dashboard analitik & statistik
├── login.php                 # Halaman login petugas
├── logout.php                # Halaman terminasi sesi
├── index.php                 # Entry redirector
└── README.md                 # Dokumentasi lengkap proyek
```

---

*Dikembangkan dengan standar rekayasa perangkat lunak yang bersih, terstruktur, dan mudah dipelajari untuk pembelajaran basis data relasional SMK RPL.*
#   c r u d - p e r p a j a k a n  
 