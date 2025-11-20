<?php
require '../vendor/autoload.php';
require '../config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

$step = $_POST['step'] ?? '';

// ==========================================
// LANGKAH 1: UPLOAD FILE & HITUNG TOTAL DATA
// ==========================================
if ($step === 'upload') {
    if (!isset($_FILES['file']['tmp_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'File tidak ditemukan']);
        exit;
    }

    $tempName = 'temp_import_' . time() . '.xlsx';
    $targetPath = '../uploads/' . $tempName; // Pastikan folder uploads ada!

    // Buat folder jika belum ada
    if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        try {
            // Load Excel hanya untuk menghitung baris
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($targetPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();

            // Dikurangi 1 karena baris 1 adalah Header
            $totalRows = $highestRow - 1;

            echo json_encode([
                'status' => 'success',
                'filename' => $tempName,
                'total_rows' => $totalRows
            ]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal upload file temp']);
    }
    exit;
}

// ==========================================
// LANGKAH 2: PROSES BATCH (POTONGAN DATA)
// ==========================================
if ($step === 'process_batch') {
    $filename = $_POST['filename'];
    $start = (int)$_POST['start']; // Index mulai (0 based relative to data)
    $limit = (int)$_POST['limit'];

    $filePath = '../uploads/' . $filename;

    if (!file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'File temp hilang']);
        exit;
    }

    try {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);

        // Filter agar PHP tidak load seluruh file (Hemat Memori)
        // Baris Excel data dimulai dari baris 2 (karena 1 Header)
        // Jadi jika start=0 (data pertama), di excel itu row 2.
        $excelStartRow = $start + 2;
        $excelEndRow = $excelStartRow + $limit - 1;

        // Kita load chunk spesifik
        // Di dalam file admin/import_handler.php

        class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
        {
            private $startRow, $endRow;

            public function __construct($start, $end)
            {
                $this->startRow = $start;
                $this->endRow = $end;
            }

            // PERBAIKAN ADA DI BARIS INI:
            public function readCell(string $column, int $row, string $worksheetName = ''): bool
            {
                // Selalu baca baris 1 (header) atau range yg diminta
                return ($row == 1 || ($row >= $this->startRow && $row <= $this->endRow));
            }
        }

        $filter = new ChunkReadFilter($excelStartRow, $excelEndRow);
        $reader->setReadFilter($filter);
        $spreadsheet = $reader->load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $success = 0;
        $failed = 0;

        // Loop data yang diload saja
        foreach ($sheetData as $rowNum => $col) {
            if ($rowNum == 1) continue; // Skip Header
            if ($rowNum < $excelStartRow || $rowNum > $excelEndRow) continue; // Safety check

            // Mapping Kolom (A, B, C...) sesuai template
            $nisn = $col['A'];
            $nik = $col['B'];
            $nama = $col['C'];

            if (empty($nisn) || empty($nik)) {
                $failed++;
                continue;
            }

            // LOGIKA INSERT KE DATABASE (Sama seperti sebelumnya)
            $conn->begin_transaction();
            try {
                // Cek duplikat
                $cek = $conn->query("SELECT siswa_id FROM siswa_pribadi WHERE nisn = '$nisn'");
                if ($cek->num_rows > 0) throw new Exception("Exist");

                // Insert Pribadi
                $stmt1 = $conn->prepare("INSERT INTO siswa_pribadi (nisn, nik, nama_lengkap, kelas, jenis_kelamin, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt1->bind_param("ssssss", $nisn, $nik, $nama, $col['D'], $col['E'], $col['F']);
                $stmt1->execute();
                $siswa_id = $conn->insert_id;

                // Insert Alamat
                $stmt2 = $conn->prepare("INSERT INTO siswa_alamat (siswa_id, alamat_jalan, desa_kelurahan, kecamatan, kota_kabupaten) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("issss", $siswa_id, $col['G'], $col['H'], $col['I'], $col['J']);
                $stmt2->execute();

                // Insert Ortu
                $stmt3 = $conn->prepare("INSERT INTO siswa_ortu (siswa_id, nama_ayah, pekerjaan_ayah, nama_ibu) VALUES (?, ?, ?, ?)");
                $stmt3->bind_param("isss", $siswa_id, $col['K'], $col['L'], $col['M']);
                $stmt3->execute();

                $conn->commit();
                $success++;
            } catch (Exception $e) {
                $conn->rollback();
                $failed++;
            }
        }

        echo json_encode(['status' => 'success', 'success' => $success, 'failed' => $failed]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
