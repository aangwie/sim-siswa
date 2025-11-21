# Sistem Informasi Sekolah & Bimbingan Konseling (SIS-BK)

Aplikasi manajemen data sekolah berbasis web yang komprehensif, mencakup pengelolaan data siswa (Kesiswaan) dan modul Bimbingan Konseling (BK). Dibangun menggunakan **PHP Native** dan **MySQLi** dengan antarmuka modern (**Bootstrap 5** & **DataTables**).

## 🚀 Fitur Unggulan

### 🎓 Modul Kesiswaan (Admin)
* **Manajemen Siswa Lengkap (CRUD):** Data Pribadi, Tempat/Tgl Lahir, Alamat, dan Orang Tua.
* **Import Data Excel:** Upload massal data siswa dari file `.xlsx` dengan progress bar real-time.
* **Export Template:** Download format Excel otomatis untuk input data.
* **Cetak Kartu NISN:**
    * Desain mirip kartu resmi Kemendikbud (Depan & Belakang).
    * Support QR Code otomatis.
    * Layout cetak otomatis (Portrait).

### ❤️ Modul Bimbingan Konseling (Guru BK)
* **Buku Kasus & Prestasi:**
    * Pencatatan pelanggaran (dengan poin), prestasi, dan masalah pribadi.
    * Jejak audit (mencatat waktu terakhir edit).
    * Tabel interaktif dengan pencarian dan filter.
* **Manajemen Jadwal:**
    * Pembuatan janji temu konseling.
    * Status jadwal (Terjadwal, Selesai, Batal).
* **Riwayat Terintegrasi:** Melihat rekam jejak kasus siswa langsung dari profil siswa.

### 🌐 Frontend (Publik / Wali Murid)
* **Portal Cek Data:** Pencarian berdasarkan NISN atau NIK.
* **Profil Transparan:** Menampilkan data diri siswa.
* **Riwayat Kedisiplinan:** Wali murid dapat melihat catatan pelanggaran/prestasi siswa secara online.
* **Download Kartu:** Tombol akses cepat untuk mencetak kartu NISN.

### ⚙️ Fitur Sistem
* **Identitas Sekolah Dinamis:** Nama sekolah, logo, alamat, dll bisa diubah dari dashboard.
* **Keamanan:** Login Admin dengan Password Hashing (`password_verify`).
* **UI/UX Modern:**
    * DataTables (Pencarian, Sorting, Pagination otomatis).
    * SweetAlert2 (Notifikasi dan Konfirmasi yang cantik).
    * Responsive Design (Mobile Friendly).

---

## 🛠️ Teknologi

* **Backend:** PHP 7.4 / 8.x (Native)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Bootstrap 5.3
* **Plugins:**
    * [DataTables](https://datatables.net/) (Tabel Interaktif)
    * [SweetAlert2](https://sweetalert2.github.io/) (Alerts)
    * [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) (Excel Engine)
    * [Bootstrap Icons](https://icons.getbootstrap.com/)

---

## 📦 Struktur Folder

```text
smp_sys/
├── admin/
│   ├── dashboard.php       # Halaman Utama
│   ├── bk_kasus.php        # Modul Catatan Kasus (DataTables)
│   ├── bk_jadwal.php       # Modul Jadwal Konseling
│   ├── tambah.php          # Form Tambah Siswa
│   ├── edit.php            # Form Edit Siswa
│   ├── import_excel.php    # Handler Upload
│   ├── import_handler.php  # Proses Batch Excel
│   ├── get_detail_siswa.php# AJAX Detail Siswa
│   ├── cari_siswa.php      # AJAX Live Search
│   ├── pengaturan.php      # Setting Sekolah
│   └── ... (file pendukung lainnya)
├── uploads/                # Folder penyimpanan sementara Excel
├── vendor/                 # Library Composer
├── config.php              # Koneksi Database
├── index.php               # Halaman Depan (Publik)
├── cetak_kartu.php         # Fitur Cetak Kartu NISN
└── README.md               # Dokumentasi