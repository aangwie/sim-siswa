# Sistem Manajemen Data Siswa SMP (PHP Native)

Aplikasi berbasis web untuk pengelolaan data siswa Sekolah Menengah Pertama (SMP). Aplikasi ini dirancang dengan **PHP Native** (tanpa framework), **MySQLi**, dan antarmuka modern menggunakan **Bootstrap 5**.

Aplikasi ini memiliki dua sisi antarmuka:
1.  **Publik:** Untuk mengecek status siswa berdasarkan NISN/NIK.
2.  **Admin:** Dashboard pengelolaan data lengkap (CRUD), Import Excel, dan Pengaturan Sekolah.

---

## 🚀 Fitur Unggulan

### 🔹 Frontend (Publik)
* **Pencarian Data Siswa:** Cek data berdasarkan NIK atau NISN.
* **Digital Student Card:** Tampilan hasil pencarian berbentuk kartu digital yang elegan.
* **Dynamic School Identity:** Nama sekolah, logo, dan alamat diambil dari database.

### 🔹 Backend (Admin Dashboard)
* **Secure Login:** Sistem login dengan password hashing (`password_verify`).
* **Dashboard Interaktif:** Ringkasan data dengan navigasi responsif.
* **Manajemen Siswa (CRUD):**
    * Data terpisah (Pribadi, Alamat, Orang Tua) namun terintegrasi.
    * **Live Search:** Pencarian data instan (AJAX) tanpa reload halaman.
    * **Inline Edit Status:** Ubah status Aktif/Non-Aktif langsung dari tabel.
    * **SweetAlert2:** Konfirmasi hapus data yang cantik dan aman.
* **Import Data Excel (.xlsx):**
    * Upload massal data siswa.
    * **Progress Bar:** Indikator visual saat proses import berjalan.
    * Download Template Excel otomatis.
* **Pengaturan Sistem:**
    * Update identitas sekolah (Nama, Slogan, Alamat, dll).
    * Ganti password admin.
* **Mobile Friendly:** Tampilan responsif di HP, Tablet, dan Desktop.

---

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP 7.4 / 8.x (Native)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Bootstrap 5.3
* **Icons:** Bootstrap Icons
* **Library:**
    * `phpoffice/phpspreadsheet` (Untuk Import/Export Excel)
    * `SweetAlert2` (Untuk Notifikasi Popup)

---

## 📦 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal (Localhost):

### 1. Persiapan Folder
Pastikan Anda sudah menginstall **XAMPP** atau web server sejenis.
* Buat folder `smp_sys` di dalam `htdocs`.
* Salin semua file proyek ke folder tersebut.

### 2. Instalasi Library (Composer)
Karena fitur Import Excel menggunakan library pihak ketiga, Anda wajib menginstall dependensi via Composer.
Buka terminal/CMD di folder proyek, lalu jalankan:

```bash
composer require phpoffice/phpspreadsheet