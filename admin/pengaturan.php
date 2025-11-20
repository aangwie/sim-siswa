<?php
session_start();
require '../config.php';

// Cek Login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // Pastikan login.php sudah set session ini

// --- PROSES 1: UPDATE IDENTITAS SEKOLAH ---
if (isset($_POST['update_sekolah'])) {
    $nama   = clean($_POST['nama_sekolah']);
    $slogan = clean($_POST['slogan']);
    $alamat = clean($_POST['alamat']);
    $email  = clean($_POST['email']);
    $telp   = clean($_POST['telepon']);

    $stmt = $conn->prepare("UPDATE identitas_sekolah SET nama_sekolah=?, slogan=?, alamat=?, email_sekolah=?, telepon=? WHERE id=1");
    $stmt->bind_param("sssss", $nama, $slogan, $alamat, $email, $telp);
    
    if ($stmt->execute()) {
        $msg_sekolah = "Identitas sekolah berhasil diperbarui!";
        $msg_type_sekolah = "success";
    } else {
        $msg_sekolah = "Gagal update: " . $conn->error;
        $msg_type_sekolah = "danger";
    }
}

// --- PROSES 2: UPDATE PASSWORD ADMIN ---
if (isset($_POST['update_pass'])) {
    $pass_lama = $_POST['pass_lama'];
    $pass_baru = $_POST['pass_baru'];
    $pass_konf = $_POST['pass_konf'];

    // Ambil password lama dari DB
    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (password_verify($pass_lama, $res['password'])) {
        if ($pass_baru === $pass_konf) {
            // Hash Password Baru
            $new_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
            $stmt_upd = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt_upd->bind_param("si", $new_hash, $admin_id);
            
            if ($stmt_upd->execute()) {
                $msg_pass = "Password berhasil diubah! Silakan login ulang nanti.";
                $msg_type_pass = "success";
            }
        } else {
            $msg_pass = "Konfirmasi password baru tidak cocok.";
            $msg_type_pass = "danger";
        }
    } else {
        $msg_pass = "Password lama salah.";
        $msg_type_pass = "danger";
    }
}

// --- AMBIL DATA SEKOLAH (Untuk ditampilkan di form) ---
$sekolah = $conn->query("SELECT * FROM identitas_sekolah WHERE id=1")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --primary-color: #0f172a; --accent-color: #d97706; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .navbar-custom { background-color: var(--primary-color); }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .nav-tabs .nav-link { color: #64748b; font-weight: 600; }
        .nav-tabs .nav-link.active { color: var(--primary-color); border-bottom: 3px solid var(--accent-color); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="bi bi-gear-fill me-2"></i>Pengaturan</span>
            <a href="dashboard.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left me-1"></i> Dashboard</a>
        </div>
    </nav>

    <div class="container mb-5" style="max-width: 800px;">
        
        <div class="card card-custom overflow-hidden">
            <div class="card-header bg-white pt-3">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sekolah-tab" data-bs-toggle="tab" data-bs-target="#sekolah-pane" type="button" role="tab">Identitas Sekolah</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab">Ganti Password Admin</button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <div class="tab-content" id="myTabContent">
                    
                    <div class="tab-pane fade show active" id="sekolah-pane" role="tabpanel">
                        <?php if(isset($msg_sekolah)) echo "<div class='alert alert-$msg_type_sekolah'>$msg_sekolah</div>"; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Sekolah</label>
                                <input type="text" name="nama_sekolah" class="form-control" value="<?= $sekolah['nama_sekolah'] ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Slogan / Motto</label>
                                <input type="text" name="slogan" class="form-control" value="<?= $sekolah['slogan'] ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Alamat Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3"><?= $sekolah['alamat'] ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= $sekolah['email_sekolah'] ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Telepon</label>
                                    <input type="text" name="telepon" class="form-control" value="<?= $sekolah['telepon'] ?>">
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="update_sekolah" class="btn btn-primary" style="background-color: var(--primary-color);">
                                    <i class="bi bi-save me-2"></i>Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="password-pane" role="tabpanel">
                        <div class="alert alert-warning small">
                            <i class="bi bi-info-circle me-1"></i> Pastikan Anda mengingat password baru Anda.
                        </div>
                        
                        <?php if(isset($msg_pass)) echo "<div class='alert alert-$msg_type_pass'>$msg_pass</div>"; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="pass_lama" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="pass_baru" class="form-control" required minlength="6">
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="pass_konf" class="form-control" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" name="update_pass" class="btn btn-danger">
                                    <i class="bi bi-key me-2"></i>Ganti Password
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>