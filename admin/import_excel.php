<?php
require '../vendor/autoload.php'; // Load library PhpSpreadsheet
require '../config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $file_mimes = [
        'application/vnd.ms-excel',
        'application/excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    // Validasi File
    if (isset($_FILES['file_siswa']['name']) && in_array($_FILES['file_siswa']['type'], $file_mimes)) {
        
        $arr_file = explode('.', $_FILES['file_siswa']['name']);
        $extension = end($arr_file);

        if ('xlsx' == $extension) {
            $reader = IOFactory::createReader('Xlsx');
        } else {
            $reader = IOFactory::createReader('Xls');
        }

        $spreadsheet = $reader->load($_FILES['file_siswa']['tmp_name']);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();

        $sukses = 0;
        $gagal = 0;

        // Loop data (Mulai dari index 1 karena index 0 biasanya Header/Judul Kolom)
        for ($i = 1; $i < count($sheetData); $i++) {
            $row = $sheetData[$i];

            // Pastikan baris tidak kosong (cek NISN)
            if (empty($row[0])) continue;

            // Mapping Kolom Excel ke Variabel
            // Asumsi urutan kolom di Excel:
            // 0:NISN, 1:NIK, 2:Nama, 3:Kelas, 4:L/P, 5:Tgl Lahir(YYYY-MM-DD)
            // 6:Jalan, 7:Desa, 8:Kecamatan, 9:Kota
            // 10:Ayah, 11:Pek Ayah, 12:Ibu

            $nisn = $row[0];
            $nik = $row[1];
            $nama = $row[2];
            $kelas = $row[3];
            $jk = $row[4];
            $tgl_lahir = $row[5]; // Format Excel harus Text/Date YYYY-MM-DD
            
            $jalan = $row[6];
            $desa = $row[7];
            $kecamatan = $row[8];
            $kota = $row[9];

            $ayah = $row[10];
            $pek_ayah = $row[11];
            $ibu = $row[12];

            // Mulai Transaksi Database
            $conn->begin_transaction();

            try {
                // Cek duplikasi NISN dulu
                $cek = $conn->query("SELECT siswa_id FROM siswa_pribadi WHERE nisn = '$nisn'");
                if ($cek->num_rows > 0) {
                    throw new Exception("NISN Sudah ada");
                }

                // 1. Insert Pribadi
                $stmt1 = $conn->prepare("INSERT INTO siswa_pribadi (nisn, nik, nama_lengkap, kelas, jenis_kelamin, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt1->bind_param("ssssss", $nisn, $nik, $nama, $kelas, $jk, $tgl_lahir);
                $stmt1->execute();
                $siswa_id = $conn->insert_id;

                // 2. Insert Alamat
                $stmt2 = $conn->prepare("INSERT INTO siswa_alamat (siswa_id, alamat_jalan, desa_kelurahan, kecamatan, kota_kabupaten) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("issss", $siswa_id, $jalan, $desa, $kecamatan, $kota);
                $stmt2->execute();

                // 3. Insert Ortu
                $stmt3 = $conn->prepare("INSERT INTO siswa_ortu (siswa_id, nama_ayah, pekerjaan_ayah, nama_ibu) VALUES (?, ?, ?, ?)");
                $stmt3->bind_param("isss", $siswa_id, $ayah, $pek_ayah, $ibu);
                $stmt3->execute();

                $conn->commit();
                $sukses++;

            } catch (Exception $e) {
                $conn->rollback();
                $gagal++;
                // Opsi: Catat error log jika perlu
            }
        }

        echo "<script>
                alert('Import Selesai! Sukses: $sukses, Gagal/Duplikat: $gagal');
                window.location = 'dashboard.php';
              </script>";
    } else {
        echo "<script>alert('Format file salah! Harap upload file .xlsx'); window.location='dashboard.php';</script>";
    }
}
?>