<?php
session_start();
require '../config.php';

$info = $conn->query("SELECT * FROM identitas_sekolah WHERE id=1")->fetch_assoc();

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// Logika Login PHP
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Set Session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];
            
            header("Location: dashboard.php");
            exit;
        }
    }
    $error = "Username atau Password tidak valid.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - <?= htmlspecialchars($info['nama_sekolah']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-dark: #0f172a; /* Navy */
            --accent-gold: #d97706; /* Gold */
        }
        
        body, html {
            height: 100%;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Bagian Kiri (Branding) */
        .login-sidebar {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #1e293b 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Dekorasi background abstrak */
        .login-sidebar::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--accent-gold) 0%, transparent 70%);
            opacity: 0.1;
            top: -100px;
            left: -100px;
            border-radius: 50%;
        }

        /* Bagian Kanan (Form) */
        .login-section {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
        }

        .login-card-body {
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        /* Styling Input Form */
        .form-floating > .form-control {
            border: none;
            border-bottom: 2px solid #e2e8f0;
            border-radius: 0;
            padding-left: 0;
        }
        
        .form-floating > .form-control:focus {
            box-shadow: none;
            border-bottom-color: var(--primary-dark);
        }

        .form-floating > label {
            padding-left: 0;
            color: #64748b;
        }

        .btn-login {
            background-color: var(--primary-dark);
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: var(--accent-gold);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(217, 119, 6, 0.3);
        }

        .brand-logo i {
            font-size: 4rem;
            color: var(--accent-gold);
        }
    </style>
</head>
<body>

    <div class="container-fluid h-100">
        <div class="row h-100 g-0">
            
            <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center login-sidebar text-center px-5">
                <div class="brand-logo mb-4">
                    <i class="bi bi-building-fill-check"></i>
                </div>
                <h2 class="display-6 fw-bold mb-3">Sistem Manajemen Data Siswa</h2>
                <p class="lead opacity-75"><?= htmlspecialchars($info['nama_sekolah']) ?></p>
                <hr class="w-25 border-2 opacity-50 my-4" style="color: var(--accent-gold);">
                <p class="small opacity-50">
                    Platform terintegrasi untuk pengelolaan data akademik dan kesiswaan yang efisien, aman, dan transparan.
                </p>
            </div>

            <div class="col-lg-6 login-section">
                <div class="login-card-body">
                    
                    <div class="mb-5">
                        <h3 class="fw-bold text-dark">Selamat Datang Kembali</h3>
                        <p class="text-muted">Silakan masuk untuk mengakses dashboard admin.</p>
                    </div>

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= $error ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autocomplete="off">
                            <label for="username">Username</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Kata Sandi</label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label small text-muted" for="remember">Ingat saya</label>
                            </div>
                            <a href="#" class="small text-decoration-none fw-bold" style="color: var(--primary-dark);">Lupa password?</a>
                        </div>

                        <button type="submit" class="btn btn-login w-100">
                            MASUK DASHBOARD <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="mt-5 text-center">
                        <a href="../index.php" class="text-muted small text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Depan
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>