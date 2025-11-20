<?php
require '../vendor/autoload.php';
require '../config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

$step = $_POST['step'] ?? '';

// --- BAGIAN 1: UPLOAD (TIDAK BERUBAH) ---
if ($step === 'upload') {
    // ... (Kode upload sama persis dengan sebelumnya) ...
    // ... Salin kode upload lama Anda di sini ...

    // Jika Anda butuh kode lengkap bagian ini lagi, kabari saya.
    // Intinya bagian ini hanya upload file ke folder 'uploads/'

    // CONTOH RINGKAS BAGIAN UPLOAD AGAR TIDAK ERROR JIKA DICOPY-PASTE LANGSUNG:
    if (!isset($_FILES['file']['tmp_name'])) {
        echo json_encode(['status' => 'error', 'message' => 'File null']);
        exit;
    }
    $tempName = 'temp_import_' . time() . '.xlsx';
    if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
    move_uploaded_file($_FILES['file']['tmp_name'], '../uploads/' . $tempName);
    $reader = IOFactory::createReader('Xlsx');
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load('../uploads/' . $tempName);
    echo json_encode(['status' => 'success', 'filename' => $tempName, 'total_rows' => $spreadsheet->getActiveSheet()->getHighestDataRow() - 1]);
    exit;
}

// --- BAGIAN 2: PROSES BATCH (UPDATE PENTING DI SINI) ---
if ($step === 'process_batch') {
    $filename = $_POST['filename'];
    $start = (int)$_POST['start'];
    $limit = (int)$_POST['limit'];
    $filePath = '../uploads/' . $filename;

    if (!file_exists($filePath)) {
        echo json_encode(['status' => 'error', 'message' => 'File hilang']);
        exit;
    }

    try {
        // Chunk Filter Class (WAJIB ADA)
        class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
        {
            private $startRow, $endRow;
            public function __construct($start, $end)
            {
                $this->startRow = $start;
                $this->endRow = $end;
            }
            public function readCell(string $column, int $row, string $worksheetName = ''): bool
            {
                return ($row == 1 || ($row >= $this->startRow && $row <= $this->endRow));
            }
        }

        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $excelStartRow = $start + 2;
        $excelEndRow = $excelStartRow + $limit - 1;

        $filter = new ChunkReadFilter($excelStartRow, $excelEndRow);
        $reader->setReadFilter($filter);
        $spreadsheet = $reader->load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $success = 0;
        $failed = 0;

        foreach ($sheetData as $rowNum => $col) {
            if ($rowNum == 1) continue;
            if ($rowNum < $excelStartRow || $rowNum > $excelEndRow) continue;

            // MAPPING KOLOM BARU
            $nisn = $col['A'];
            $nik = $col['B'];
            $nama = $col['C'];
            $kelas = $col['D'];
            $jk = $col['E'];
            $tempat_lahir = $col['F']; // <--- BARU
            $tgl_lahir = $col['G'];    // <--- GESER

            $jalan = $col['H'];        // <--- GESER SEMUA KE BAWAH
            $desa = $col['I'];
            $kec = $col['J'];
            $kota = $col['K'];
            $ayah = $col['L'];
            $pek_ayah = $col['M'];
            $ibu = $col['N'];

            if (empty($nisn) || empty($nik)) {
                $failed++;
                continue;
            }

            $conn->begin_transaction();
            try {
                $cek = $conn->query("SELECT siswa_id FROM siswa_pribadi WHERE nisn = '$nisn'");
                if ($cek->num_rows > 0) throw new Exception("Exist");

                // 1. Insert Pribadi (UPDATE QUERY)
                $stmt1 = $conn->prepare("INSERT INTO siswa_pribadi (nisn, nik, nama_lengkap, kelas, jenis_kelamin, tempat_lahir, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt1->bind_param("sssssss", $nisn, $nik, $nama, $kelas, $jk, $tempat_lahir, $tgl_lahir);
                $stmt1->execute();
                $siswa_id = $conn->insert_id;

                // 2. Insert Alamat
                $stmt2 = $conn->prepare("INSERT INTO siswa_alamat (siswa_id, alamat_jalan, desa_kelurahan, kecamatan, kota_kabupaten) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param("issss", $siswa_id, $jalan, $desa, $kec, $kota);
                $stmt2->execute();

                // 3. Insert Ortu
                $stmt3 = $conn->prepare("INSERT INTO siswa_ortu (siswa_id, nama_ayah, pekerjaan_ayah, nama_ibu) VALUES (?, ?, ?, ?)");
                $stmt3->bind_param("isss", $siswa_id, $ayah, $pek_ayah, $ibu);
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
