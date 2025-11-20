<?php
session_start();
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

// --- 1. LOGIKA UPDATE STATUS (SELESAI) ---
if (isset($_GET['selesai'])) {
    $id = (int)$_GET['selesai'];
    $conn->query("UPDATE bk_jadwal SET status='Selesai' WHERE jadwal_id=$id");
    // Redirect dengan pesan sukses (opsional untuk notifikasi)
    header("Location: bk_jadwal.php?msg=selesai");
    exit;
}

// --- 2. LOGIKA UPDATE STATUS (BATAL) ---
if (isset($_GET['batal'])) {
    $id = (int)$_GET['batal'];
    $conn->query("UPDATE bk_jadwal SET status='Batal' WHERE jadwal_id=$id");
    header("Location: bk_jadwal.php?msg=batal");
    exit;
}

// --- 3. LOGIKA HAPUS PERMANEN ---
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM bk_jadwal WHERE jadwal_id=$id");
    header("Location: bk_jadwal.php?msg=hapus");
    exit;
}

// --- 4. SIMPAN JADWAL BARU ---
if (isset($_POST['simpan_jadwal'])) {
    $siswa_id = clean($_POST['siswa_id']);
    $tanggal  = clean($_POST['tanggal']);
    $waktu    = clean($_POST['waktu']);
    $tempat   = clean($_POST['tempat']);
    $topik    = clean($_POST['topik']);

    $stmt = $conn->prepare("INSERT INTO bk_jadwal (siswa_id, tanggal_konseling, waktu, tempat, topik) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $siswa_id, $tanggal, $waktu, $tempat, $topik);
    $stmt->execute();
    header("Location: bk_jadwal.php?msg=sukses");
    exit;
}

// Ambil Data Jadwal
$query = "SELECT j.*, s.nama_lengkap, s.kelas 
          FROM bk_jadwal j 
          JOIN siswa_pribadi s ON j.siswa_id = s.siswa_id 
          ORDER BY 
            CASE WHEN j.status = 'Terjadwal' THEN 1 ELSE 2 END,
            j.tanggal_konseling DESC, 
            j.waktu ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Jadwal Konseling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-success sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand h1 mb-0"><i class="bi bi-calendar-check me-2"></i>Jadwal Konseling</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Kembali</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3">Buat Jadwal Baru</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Pilih Siswa</label>
                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Cari Siswa --</option>
                                    <?php 
                                    $siswa = $conn->query("SELECT siswa_id, nama_lengkap, kelas FROM siswa_pribadi ORDER BY nama_lengkap ASC");
                                    while($s = $siswa->fetch_assoc()) echo "<option value='{$s['siswa_id']}'>{$s['nama_lengkap']} ({$s['kelas']})</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Waktu</label>
                                <input type="time" name="waktu" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Tempat</label>
                                <input type="text" name="tempat" class="form-control" value="Ruang BK">
                            </div>
                            <div class="mb-3">
                                <label>Topik / Perihal</label>
                                <textarea name="topik" class="form-control" rows="2" required></textarea>
                            </div>
                            <button type="submit" name="simpan_jadwal" class="btn btn-success w-100">Jadwalkan</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between">
                        <span>Agenda Konseling</span>
                        <span class="badge bg-primary rounded-pill"><?= $result->num_rows ?> Agenda</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php 
                            if ($result->num_rows == 0) {
                                echo "<div class='p-4 text-center text-muted'>Belum ada jadwal konseling.</div>";
                            }
                            
                            while($row = $result->fetch_assoc()): 
                                $status = $row['status'];
                                
                                // Tentukan Warna & Icon
                                $bg_class = '';
                                $icon_status = '';
                                
                                if ($status == 'Selesai') {
                                    $bg_class = 'bg-light opacity-75';
                                    $icon_status = '<span class="badge bg-success"><i class="bi bi-check-lg"></i> Selesai</span>';
                                } elseif ($status == 'Batal') {
                                    $bg_class = 'bg-light opacity-75';
                                    $icon_status = '<span class="badge bg-danger"><i class="bi bi-x-lg"></i> Dibatalkan</span>';
                                } else {
                                    $bg_class = 'border-start border-5 border-warning'; 
                                    $icon_status = '<span class="badge bg-warning text-dark">Terjadwal</span>';
                                }
                            ?>
                            <li class="list-group-item <?= $bg_class ?> p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 fw-bold">
                                            <?= $row['nama_lengkap'] ?> 
                                            <span class="badge bg-secondary rounded-pill ms-1" style="font-size: 0.7em;"><?= $row['kelas'] ?></span>
                                        </h6>
                                        
                                        <div class="small text-secondary mb-2">
                                            <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($row['tanggal_konseling'])) ?> 
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-clock me-1"></i> <?= date('H:i', strtotime($row['waktu'])) ?> WIB
                                            <span class="mx-2">|</span>
                                            <i class="bi bi-geo-alt me-1"></i> <?= $row['tempat'] ?>
                                        </div>

                                        <div class="p-2 bg-white border rounded text-muted small fst-italic">
                                            "<?= $row['topik'] ?>"
                                        </div>
                                    </div>

                                    <div class="text-end ms-3" style="min-width: 120px;">
                                        <div class="mb-2"><?= $icon_status ?></div>

                                        <?php if($status == 'Terjadwal'): ?>
                                            <a href="?selesai=<?= $row['jadwal_id'] ?>" 
                                               class="btn btn-sm btn-outline-success d-block w-100 mb-1 btn-selesai">
                                               <i class="bi bi-check"></i> Selesai
                                            </a>
                                            
                                            <a href="?batal=<?= $row['jadwal_id'] ?>" 
                                               class="btn btn-sm btn-outline-danger d-block w-100 btn-batal">
                                               <i class="bi bi-x"></i> Batalkan
                                            </a>
                                        <?php else: ?>
                                            <a href="?hapus=<?= $row['jadwal_id'] ?>" 
                                               class="btn btn-sm btn-link text-muted text-decoration-none p-0 small btn-hapus">
                                               <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 1. Handler Tombol BATAL
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.btn-batal');
            if (target) {
                e.preventDefault();
                const href = target.getAttribute('href');

                Swal.fire({
                    title: 'Batalkan Jadwal?',
                    text: "Status jadwal akan diubah menjadi 'Dibatalkan'.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Batalkan!',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            }
        });

        // 2. Handler Tombol SELESAI (Opsional: Agar konsisten)
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.btn-selesai');
            if (target) {
                e.preventDefault();
                const href = target.getAttribute('href');

                Swal.fire({
                    title: 'Tandai Selesai?',
                    text: "Pastikan sesi konseling telah terlaksana.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754', // Hijau
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Selesai!',
                    cancelButtonText: 'Kembali'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            }
        });

        // 3. Handler Tombol HAPUS
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.btn-hapus');
            if (target) {
                e.preventDefault();
                const href = target.getAttribute('href');

                Swal.fire({
                    title: 'Hapus Permanen?',
                    text: "Data riwayat ini akan dihapus dari database!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            }
        });

        // 4. Notifikasi Sukses (Toast) setelah Redirect
        // Mengecek parameter URL ?msg=...
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            let title = '';
            let icon = 'success';

            if(msg === 'sukses') title = 'Jadwal berhasil dibuat!';
            if(msg === 'selesai') title = 'Jadwal ditandai selesai!';
            if(msg === 'batal') { title = 'Jadwal dibatalkan!'; icon = 'info'; }
            if(msg === 'hapus') { title = 'Data dihapus!'; icon = 'info'; }

            Swal.fire({
                icon: icon,
                title: title,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            // Bersihkan URL agar notifikasi tidak muncul saat refresh
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>