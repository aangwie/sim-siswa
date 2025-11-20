<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 1. Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. Definisikan Header Kolom (UPDATE: Menambahkan Tempat Lahir di F)
$headers = [
    'A' => 'NISN (Wajib)',
    'B' => 'NIK (Wajib)',
    'C' => 'Nama Lengkap',
    'D' => 'Kelas',
    'E' => 'L/P',
    'F' => 'Tempat Lahir',          // <--- KOLOM BARU
    'G' => 'Tgl Lahir (YYYY-MM-DD)', // Bergeser ke G
    'H' => 'Alamat Jalan',           // Bergeser ke H
    'I' => 'Desa/Kelurahan',
    'J' => 'Kecamatan',
    'K' => 'Kota/Kabupaten',
    'L' => 'Nama Ayah',
    'M' => 'Pekerjaan Ayah',
    'N' => 'Nama Ibu'                // Berakhir di N
];

// 3. Tulis Header ke Baris 1
foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $sheet->getStyle($col . '1')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFFF00'); // Kuning
}

// 4. Tambahkan Contoh Data (Dummy)
$sheet->setCellValue('A2', '0012345678');
$sheet->setCellValue('B2', '3302010101010001');
$sheet->setCellValue('C2', 'Siswa Contoh');
$sheet->setCellValue('D2', '7A');
$sheet->setCellValue('E2', 'L');
$sheet->setCellValue('F2', 'Jakarta'); // Contoh Tempat Lahir
$sheet->setCellValue('G2', '2010-05-20');
$sheet->setCellValue('H2', 'Jl. Merdeka No. 1');
$sheet->setCellValue('I2', 'Sukamaju');
$sheet->setCellValue('J2', 'Banyumas');
$sheet->setCellValue('K2', 'Banyumas');
$sheet->setCellValue('L2', 'Budi Santoso');
$sheet->setCellValue('M2', 'Wiraswasta');
$sheet->setCellValue('N2', 'Siti Aminah');

// Format Kolom Tanggal (G) jadi Text
$sheet->getStyle('G')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

// Auto Size Kolom
foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. Output Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Siswa_Baru.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
