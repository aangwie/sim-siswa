-- 1. Tabel Admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Akun Default (User: admin, Pass: admin123)
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 2. Tabel Identitas Sekolah
CREATE TABLE identitas_sekolah (
    id INT PRIMARY KEY,
    nama_sekolah VARCHAR(100),
    slogan VARCHAR(255),
    alamat TEXT,
    email_sekolah VARCHAR(100),
    telepon VARCHAR(20)
);

-- Data Default Sekolah
INSERT INTO identitas_sekolah (id, nama_sekolah, slogan, alamat, email_sekolah, telepon) 
VALUES (1, 'SMP Nusantara', 'Unggul dalam Prestasi, Santun dalam Pekerti', 'Jl. Pendidikan No. 1, Jakarta', 'info@smpnusantara.sch.id', '(021) 123456');

-- 3. Tabel Data Pribadi Siswa
CREATE TABLE siswa_pribadi (
    siswa_id INT AUTO_INCREMENT PRIMARY KEY,
    nisn VARCHAR(20) UNIQUE NOT NULL,
    nik VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    status ENUM('Aktif', 'Non-Aktif') DEFAULT 'Aktif',
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    tanggal_lahir DATE NOT NULL
);

-- 4. Tabel Alamat Siswa
CREATE TABLE siswa_alamat (
    alamat_id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    alamat_jalan TEXT,
    desa_kelurahan VARCHAR(50),
    kecamatan VARCHAR(50),
    kota_kabupaten VARCHAR(50),
    FOREIGN KEY (siswa_id) REFERENCES siswa_pribadi(siswa_id) ON DELETE CASCADE
);

-- 5. Tabel Orang Tua Siswa
CREATE TABLE siswa_ortu (
    ortu_id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    nama_ayah VARCHAR(100),
    pekerjaan_ayah VARCHAR(50),
    nama_ibu VARCHAR(100),
    nama_wali VARCHAR(100) NULL,
    FOREIGN KEY (siswa_id) REFERENCES siswa_pribadi(siswa_id) ON DELETE CASCADE
);