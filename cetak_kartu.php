<?php
require 'config.php';

// Validasi Input
if (!isset($_GET['nisn'])) {
    die("NISN tidak ditemukan.");
}

$nisn = clean($_GET['nisn']);

// Ambil Data Siswa
$query = "SELECT * FROM siswa_pribadi WHERE nisn = '$nisn' LIMIT 1";
$result = $conn->query($query);
$data = $result->fetch_assoc();

if (!$data) {
    die("Data siswa tidak ditemukan.");
}

// Format Data
$tempat_lahir = ucwords(strtolower($data['tempat_lahir']));
$tgl_lahir = date("d F Y", strtotime($data['tanggal_lahir']));
$gender = ($data['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';
$qrData = "NISN: " . $data['nisn'] . " - " . $data['nama_lengkap'];
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrData);
$logo_tutwuri = "https://upload.wikimedia.org/wikipedia/commons/thumb/9/9c/Logo_of_Ministry_of_Education_and_Culture_of_Republic_of_Indonesia.svg/800px-Logo_of_Ministry_of_Education_and_Culture_of_Republic_of_Indonesia.svg.png";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu NISN - <?= $data['nama_lengkap'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Arial:wght@400;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e0e0e0;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            gap: 20px;
            min-height: 100vh;
            -webkit-print-color-adjust: exact;
        }

        /* --- CONTAINER UTAMA KARTU (8.6cm x 5.4cm) --- */
        .card-box {
            width: 8.6cm;
            height: 5.4cm;
            background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 50%, #b2ebf2 100%);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid #90caf9;
            page-break-inside: avoid;
        }

        /* Elemen Dekorasi */
        .wave-bg {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 60%;
            background: linear-gradient(to bottom left, #a5dcf5 0%, rgba(255, 255, 255, 0) 60%);
            z-index: 0;
            border-radius: 0 8px 8px 0;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            /* Transparan agar tulisan di atasnya terbaca */
            width: 4cm;
            z-index: 0;
        }

        /* =========================================
           STYLE KARTU DEPAN
           ========================================= */
        .header {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            padding: 0.3cm 0.4cm 0.1cm;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .logo-kemdikbud {
            width: 0.7cm;
        }

        .header-text {
            font-size: 4pt;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .header-right {
            text-align: right;
        }

        .title-card {
            font-size: 9pt;
            font-weight: 800;
            color: #2c88d9;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .subtitle-card {
            font-size: 4pt;
            font-weight: bold;
            color: #2c88d9;
            letter-spacing: 0.5px;
        }

        .content {
            position: relative;
            z-index: 2;
            display: flex;
            padding: 0.2cm 0.4cm;
            gap: 0.3cm;
        }

        .photo-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 1.6cm;
        }

        .student-photo {
            width: 1.5cm;
            height: 1.9cm;
            background-color: #ddd;
            border: 1px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 5pt;
            color: #666;
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .data-area {
            flex: 1;
            padding-top: 2px;
        }

        .data-row {
            display: flex;
            margin-bottom: 1px;
            font-size: 6pt;
            font-weight: 600;
            color: #444;
            line-height: 1.4;
        }

        .label {
            width: 1.8cm;
        }

        .separator {
            width: 0.2cm;
        }

        .value {
            flex: 1;
            font-weight: bold;
            color: #000;
        }

        .footer {
            position: absolute;
            bottom: 0.2cm;
            left: 0.4cm;
            right: 0.4cm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            z-index: 2;
        }

        .dapodik-logo {
            height: 0.6cm;
            width: auto;
            display: block;
        }

        .student-logo {
            height: 0.3cm;
            width: auto;
            display: block;
        }

        .qr-code {
            width: 1.1cm;
            height: 1.1cm;
            border: 1px solid white;
        }

        /* =========================================
           STYLE KARTU BELAKANG (DIPERBAIKI)
           ========================================= */
        .back-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            /* Mulai dari atas */
            text-align: center;
            padding-top: 0.5cm;
            /* Jarak dari atas */
        }

        /* Header PUSDATIN */
        .pusdatin-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0.2cm;
        }

        .pusdatin-logo {
            width: 0.8cm;
            margin-bottom: 2px;
        }

        .pusdatin-text {
            font-size: 9pt;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .pusdatin-sub {
            font-size: 6pt;
            color: #555;
        }

        /* Strip Tengah */
        .strip-band {
            width: 100%;
            background: rgba(80, 90, 100, 0.7);
            /* Abu-abu gelap transparan */
            padding: 0.15cm 0;
            margin-bottom: 0.1cm;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .strip-title {
            font-size: 15pt;
            font-weight: 800;
            color: white;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            /* Shadow agar tulisan 'pop' */
        }

        /* Link Website */
        .web-link {
            font-size: 6pt;
            color: #333;
            margin-bottom: 0.1cm;
            font-weight: bold;
        }

        /* Tulisan Biru Bawah */
        .sub-text {
            font-size: 7pt;
            font-weight: 800;
            color: #2c88d9;
            /* Biru */
            text-transform: uppercase;
            margin-bottom: 0.2cm;
            letter-spacing: 0.5px;
        }

        /* Logo Dapodik Bawah */
        .dapodik-back-logo {
            height: 0.8cm;
            width: auto;
        }

        /* =========================================
           SETTING PRINT
           ========================================= */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }

            body {
                margin: 0;
                background: none;
                display: block;
            }

            .card-box {
                box-shadow: none;
                border: none;
                margin-bottom: 0.5cm;
                page-break-inside: avoid;
            }

            .no-print {
                display: none;
            }

            .print-container {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>

    <div class="print-container">

        <div class="card-box">
            <div class="wave-bg"></div>
            <img src="<?= $logo_tutwuri ?>" class="watermark" alt="Watermark">

            <div class="header">
                <div class="header-left">
                    <img src="<?= $logo_tutwuri ?>" class="logo-kemdikbud" alt="Logo">
                    <div class="header-text">Kementerian<br>Pendidikan, Kebudayaan<br>Riset, dan Teknologi</div>
                </div>
                <div class="header-right">
                    <div class="title-card">Kartu NISN</div>
                    <div class="subtitle-card">NOMOR INDUK SISWA NASIONAL</div>
                </div>
            </div>

            <div class="content">
                <div class="photo-area">
                    <div class="student-photo">
                        <img src="uploads/student.png" class="student-logo" alt="Logo Dapodik">
                    </div>
                </div>
                <div class="data-area">
                    <div class="data-row">
                        <div class="label">NISN</div>
                        <div class="separator">:</div>
                        <div class="value" style="font-size: 7pt;"><?= $data['nisn'] ?></div>
                    </div>
                    <div class="data-row">
                        <div class="label">Nama</div>
                        <div class="separator">:</div>
                        <div class="value text-uppercase"><?= $data['nama_lengkap'] ?></div>
                    </div>
                    <div class="data-row">
                        <div class="label">Tempat Lahir</div>
                        <div class="separator">:</div>
                        <div class="value"><?= $tempat_lahir ?></div>
                    </div>
                    <div class="data-row">
                        <div class="label">Tanggal Lahir</div>
                        <div class="separator">:</div>
                        <div class="value"><?= $tgl_lahir ?></div>
                    </div>
                    <div class="data-row">
                        <div class="label">Jenis Kelamin</div>
                        <div class="separator">:</div>
                        <div class="value"><?= $gender ?></div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div><img src="uploads/dapodik.png" class="dapodik-logo" alt="Logo Dapodik"></div>
                <img src="<?= $qrUrl ?>" class="qr-code" alt="QR Validasi">
            </div>
        </div>

        <div class="no-print" style="height: 20px;"></div>

        <div class="card-box">
            <div class="wave-bg"></div>
            <img src="<?= $logo_tutwuri ?>" class="watermark" style="width: 5cm; opacity: 0.1;" alt="Watermark">

            <div class="back-content">

                <div class="pusdatin-header">
                    <img src="<?= $logo_tutwuri ?>" class="pusdatin-logo" alt="Logo">
                    <div class="pusdatin-text">PUSDATIN</div>
                    <div class="pusdatin-sub">Pusat Data dan Teknologi Informasi</div>
                </div>

                <div class="strip-band">
                    <div class="strip-title">KARTU NISN</div>
                </div>

                <div class="web-link">https://dapo.kemdikbud.go.id</div>

                <div class="sub-text">NOMOR INDUK SISWA NASIONAL</div>

                <img src="uploads/dapodik.png" class="dapodik-back-logo" alt="Logo Dapodik">

            </div>
        </div>

    </div>

    <div class="no-print" style="position: fixed; bottom: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 12px 25px; background: #2c88d9; color: white; border: none; font-weight: bold; border-radius: 50px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            Cetak Kartu
        </button>
    </div>

</body>

</html>