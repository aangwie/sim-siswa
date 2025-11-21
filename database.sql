-- 1. Tabel Admin
--
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data admin (Pass: admin123)
INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- 2. Tabel Identitas Sekolah
--
CREATE TABLE `identitas_sekolah` (
  `id` int(11) NOT NULL,
  `nama_sekolah` varchar(100) DEFAULT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email_sekolah` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data sekolah
INSERT INTO `identitas_sekolah` (`id`, `nama_sekolah`, `slogan`, `alamat`, `email_sekolah`, `telepon`) VALUES
(1, 'SMP Nusantara', 'Unggul dalam Prestasi, Santun dalam Pekerti', 'Jl. Pendidikan No. 1, Jakarta', 'info@smpnusantara.sch.id', '(021) 123456');

-- --------------------------------------------------------

--
-- 3. Tabel Data Pribadi Siswa
--
CREATE TABLE `siswa_pribadi` (
  `siswa_id` int(11) NOT NULL AUTO_INCREMENT,
  `nisn` varchar(20) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `status` enum('Aktif','Non-Aktif') DEFAULT 'Aktif',
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  PRIMARY KEY (`siswa_id`),
  UNIQUE KEY `nisn` (`nisn`),
  UNIQUE KEY `nik` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 4. Tabel Alamat Siswa
--
CREATE TABLE `siswa_alamat` (
  `alamat_id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `alamat_jalan` text DEFAULT NULL,
  `desa_kelurahan` varchar(50) DEFAULT NULL,
  `kecamatan` varchar(50) DEFAULT NULL,
  `kota_kabupaten` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`alamat_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `fk_alamat_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_pribadi` (`siswa_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 5. Tabel Orang Tua Siswa
--
CREATE TABLE `siswa_ortu` (
  `ortu_id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(50) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `nama_wali` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ortu_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `fk_ortu_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_pribadi` (`siswa_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 6. Tabel Kasus & Prestasi (Modul BK)
--
CREATE TABLE `bk_kasus` (
  `kasus_id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kategori` enum('Pelanggaran','Prestasi','Masalah Pribadi','Lainnya') NOT NULL,
  `judul_kasus` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `penanganan` text DEFAULT NULL,
  `poin` int(11) DEFAULT 0,
  `tindak_lanjut` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`kasus_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `fk_kasus_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_pribadi` (`siswa_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 7. Tabel Jadwal Konseling (Modul BK)
--
CREATE TABLE `bk_jadwal` (
  `jadwal_id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `tanggal_konseling` date NOT NULL,
  `waktu` time NOT NULL,
  `tempat` varchar(100) DEFAULT 'Ruang BK',
  `topik` varchar(150) DEFAULT NULL,
  `status` enum('Terjadwal','Selesai','Batal') DEFAULT 'Terjadwal',
  `hasil_konseling` text DEFAULT NULL,
  PRIMARY KEY (`jadwal_id`),
  KEY `siswa_id` (`siswa_id`),
  CONSTRAINT `fk_jadwal_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa_pribadi` (`siswa_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;