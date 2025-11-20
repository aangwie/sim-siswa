<?php
require '../vendor/autoload.php'; // Pastikan path vendor benar

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// 1. Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Definisikan Header Kolom (Sesuai urutan import_excel.php)
$headers = [
    'A' => 'NISN (Wajib)',
    'B' => 'NIK (Wajib)',
    'C' => 'Nama Lengkap',
    'D' => 'Kelas',
    'E' => 'L/P',
    'F' => 'Tgl Lahir (YYYY-MM-DD)',
    'G' => 'Alamat Jalan',
    'H' => 'Desa/Kelurahan',
    'I' => 'Kecamatan',
    'J' => 'Kota/Kabupaten',
    'K' => 'Nama Ayah',
    'L' => 'Pekerjaan Ayah',
    'M' => 'Nama Ibu'
];

// 3. Tulis Header ke Baris 1
foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    // Styling Header: Bold, Background Kuning
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getStyle($col . '1')->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setARGB('FFFFFF00'); // Kuning
}

// 4. Tambahkan Contoh Data (Dummy) di Baris 2 (Opsional, agar user paham)
$sheet->setCellValue('A2', '0012345678');
$sheet->setCellValue('B2', '3302010101010001');
$sheet->setCellValue('C2', 'Siswa Contoh');
$sheet->setCellValue('D2', '7A');
$sheet->setCellValue('E2', 'L');
$sheet->setCellValue('F2', '2010-05-20'); // Format Text YYYY-MM-DD
$sheet->setCellValue('G2', 'Jl. Merdeka No. 1');
$sheet->setCellValue('H2', 'Sukamaju');
$sheet->setCellValue('I2', 'Banyumas');
$sheet->setCellValue('J2', 'Banyumas');
$sheet->setCellValue('K2', 'Budi Santoso');
$sheet->setCellValue('L2', 'Wiraswasta');
$sheet->setCellValue('M2', 'Siti Aminah');

// Atur format kolom F (Tanggal) menjadi Text agar tidak berubah jadi angka Excel
$sheet->getStyle('F')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// Auto Size Kolom (Agar lebar kolom menyesuaikan isi)
foreach (range('A', 'M') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. Set Header HTTP untuk Download File
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Siswa_SMP.xlsx"');
header('Cache-Control: max-age=0');

// 6. Simpan ke Output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>