<?php
session_start();
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$id = clean($_GET['id']);

// --- PROSES UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // 1. Update Pribadi (UPDATE: Tambah tempat_lahir)
        $stmt1 = $conn->prepare("UPDATE siswa_pribadi SET nisn=?, nik=?, nama_lengkap=?, kelas=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, status=? WHERE siswa_id=?");

        // "ssssssssi" (8 string, 1 int)
        $stmt1->bind_param(
            "ssssssssi",
            $_POST['nisn'],
            $_POST['nik'],
            $_POST['nama'],
            $_POST['kelas'],
            $_POST['jk'],
            $_POST['tempat_lahir'], // Data Baru
            $_POST['tgl_lahir'],
            $_POST['status'],
            $id
        );
        $stmt1->execute();

        // 2. Update Alamat
        $stmt2 = $conn->prepare("UPDATE siswa_alamat SET alamat_jalan=?, desa_kelurahan=?, kecamatan=?, kota_kabupaten=? WHERE siswa_id=?");
        $stmt2->bind_param("ssssi", $_POST['jalan'], $_POST['desa'], $_POST['kecamatan'], $_POST['kota'], $id);
        $stmt2->execute();

        // 3. Update Ortu
        $stmt3 = $conn->prepare("UPDATE siswa_ortu SET nama_ayah=?, pekerjaan_ayah=?, nama_ibu=? WHERE siswa_id=?");
        $stmt3->bind_param("sssi", $_POST['ayah'], $_POST['pek_ayah'], $_POST['ibu'], $id);
        $stmt3->execute();

        $conn->commit();
        echo "<script>alert('Data berhasil diperbarui!'); window.location='dashboard.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal update: " . $e->getMessage();
    }
}

// --- FETCH DATA LAMA ---
$query = "SELECT * FROM siswa_pribadi 
          LEFT JOIN siswa_alamat ON siswa_pribadi.siswa_id = siswa_alamat.siswa_id 
          LEFT JOIN siswa_ortu ON siswa_pribadi.siswa_id = siswa_ortu.siswa_id 
          WHERE siswa_pribadi.siswa_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #d97706;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }

        .navbar-custom {
            background-color: var(--primary-color);
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            color: var(--primary-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .section-header i {
            color: var(--accent-color);
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .form-label {
            font-size: 0.9rem;
            color: #64748b;
            font-weight: 500;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark navbar-custom sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-pencil-square me-2"></i>Edit Siswa</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>
    </nav>

    <div class="container mb-5" style="max-width: 900px;">
        <?php if (isset($error)) echo "<div class='alert alert-danger shadow-sm'>$error</div>"; ?>

        <form method="POST">
            <div class="card card-custom p-4">

                <div class="section-header mt-2"><i class="bi bi-person-vcard-fill"></i> Data Identitas</div>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="<?= $data['nisn'] ?>" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control" value="<?= $data['nik'] ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control text-uppercase" value="<?= $data['nama_lengkap'] ?>" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="<?= $data['kelas'] ?>" required>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label">L/P</label>
                        <select name="jk" class="form-select">
                            <option value="L" <?= ($data['jenis_kelamin'] == 'L') ? 'selected' : '' ?>>L</option>
                            <option value="P" <?= ($data['jenis_kelamin'] == 'P') ? 'selected' : '' ?>>P</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?= $data['tempat_lahir'] ?? '' ?>" required>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label">Tgl Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control" value="<?= $data['tanggal_lahir'] ?>" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select fw-bold <?= ($data['status'] == 'Aktif') ? 'text-success' : 'text-danger' ?>">
                            <option value="Aktif" <?= ($data['status'] == 'Aktif') ? 'selected' : '' ?> class="text-success">Aktif</option>
                            <option value="Non-Aktif" <?= ($data['status'] == 'Non-Aktif') ? 'selected' : '' ?> class="text-danger">Non-Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="section-header"><i class="bi bi-geo-alt-fill"></i> Alamat Domisili</div>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label">Jalan / Dusun</label>
                        <input type="text" name="jalan" class="form-control" value="<?= $data['alamat_jalan'] ?>">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Desa / Kelurahan</label>
                        <input type="text" name="desa" class="form-control" value="<?= $data['desa_kelurahan'] ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="kecamatan" class="form-control" value="<?= $data['kecamatan'] ?>">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label">Kota / Kabupaten</label>
                        <input type="text" name="kota" class="form-control" value="<?= $data['kota_kabupaten'] ?>">
                    </div>
                </div>

                <div class="section-header"><i class="bi bi-people-fill"></i> Data Orang Tua</div>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="ayah" class="form-control" value="<?= $data['nama_ayah'] ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Pekerjaan Ayah</label>
                        <input type="text" name="pek_ayah" class="form-control" value="<?= $data['pekerjaan_ayah'] ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="ibu" class="form-control" value="<?= $data['nama_ibu'] ?>">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <a href="dashboard.php" class="btn btn-light border btn-lg px-4">Batal</a>
                    <button type="submit" class="btn btn-primary btn-lg px-5" style="background-color: var(--primary-color);">
                        <i class="bi bi-arrow-repeat me-2"></i>Update Data
                    </button>
                </div>

            </div>
        </form>
    </div>
</body>

</html>