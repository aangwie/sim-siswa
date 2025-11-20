<?php
session_start();
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

// --- LOGIKA PENYIMPANAN (PHP) TETAP SAMA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // 1. Insert Pribadi
        $stmt1 = $conn->prepare("INSERT INTO siswa_pribadi (nisn, nik, nama_lengkap, kelas, jenis_kelamin, tanggal_lahir) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt1->bind_param("ssssss", $_POST['nisn'], $_POST['nik'], $_POST['nama'], $_POST['kelas'], $_POST['jk'], $_POST['tgl_lahir']);
        $stmt1->execute();
        $siswa_id = $conn->insert_id;

        // 2. Insert Alamat
        $stmt2 = $conn->prepare("INSERT INTO siswa_alamat (siswa_id, alamat_jalan, desa_kelurahan, kecamatan, kota_kabupaten) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("issss", $siswa_id, $_POST['jalan'], $_POST['desa'], $_POST['kecamatan'], $_POST['kota']);
        $stmt2->execute();

        // 3. Insert Ortu
        $stmt3 = $conn->prepare("INSERT INTO siswa_ortu (siswa_id, nama_ayah, pekerjaan_ayah, nama_ibu) VALUES (?, ?, ?, ?)");
        $stmt3->bind_param("isss", $siswa_id, $_POST['ayah'], $_POST['pek_ayah'], $_POST['ibu']);
        $stmt3->execute();

        $conn->commit();
        echo "<script>alert('Data berhasil disimpan!'); window.location='dashboard.php';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Tambah Siswa Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --primary-color: #0f172a; --accent-color: #d97706; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        
        .navbar-custom { background-color: var(--primary-color); }
        .card-custom {
            border: none; border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .section-header {
            color: var(--primary-color);
            font-weight: 600;
            display: flex; align-items: center;
            margin-bottom: 1.5rem; padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .section-header i { color: var(--accent-color); margin-right: 10px; font-size: 1.2rem; }
        .form-label { font-size: 0.9rem; color: #64748b; font-weight: 500; }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-plus-circle me-2"></i>Tambah Siswa</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>
    </nav>

    <div class="container mb-5" style="max-width: 900px;">
        
        <?php if(isset($error)) echo "<div class='alert alert-danger shadow-sm'>$error</div>"; ?>

        <form method="POST" class="needs-validation">
            <div class="card card-custom p-4">
                
                <div class="section-header mt-2">
                    <i class="bi bi-person-vcard-fill"></i> Data Identitas
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">NISN <span class="text-danger">*</span></label>
                        <input type="number" name="nisn" class="form-control" placeholder="10 digit angka" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="number" name="nik" class="form-control" placeholder="16 digit angka" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control text-uppercase" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-control" placeholder="Cth: 7A" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">L/P</label>
                        <select name="jk" class="form-select" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Tgl Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control" required>
                    </div>
                </div>

                <div class="section-header">
                    <i class="bi bi-geo-alt-fill"></i> Alamat Domisili
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Jalan / Dusun / RT RW</label>
                        <input type="text" name="jalan" class="form-control" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Desa / Kelurahan</label>
                        <input type="text" name="desa" class="form-control" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="kecamatan" class="form-control" required>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Kota / Kabupaten</label>
                        <input type="text" name="kota" class="form-control" required>
                    </div>
                </div>

                <div class="section-header">
                    <i class="bi bi-people-fill"></i> Data Orang Tua
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="ayah" class="form-control">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Pekerjaan Ayah</label>
                        <input type="text" name="pek_ayah" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="ibu" class="form-control">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <a href="dashboard.php" class="btn btn-light border btn-lg px-4">Batal</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5" style="background-color: var(--primary-color);">
                        <i class="bi bi-save me-2"></i>Simpan Data
                    </button>
                </div>

            </div>
        </form>
    </div>
</body>
</html>