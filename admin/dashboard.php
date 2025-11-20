<?php
session_start();
require '../config.php'; // Pastikan path config benar

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['pesan']) && $_GET['pesan'] == 'hapus_sukses') {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data siswa telah dihapus.',
                timer: 2000,
                showConfirmButton: false
            });
        });
    </script>";
}

// --- LOGIKA PAGINASI DINAMIS ---
// 1. Tentukan opsi limit yang diperbolehkan
$limit_options = [10, 25, 50, 100];

// 2. Ambil limit dari URL, jika tidak ada atau tidak valid, gunakan default 10
$jumlah_data_per_halaman = (isset($_GET['limit']) && in_array($_GET['limit'], $limit_options))
    ? (int)$_GET['limit']
    : 10;

// 3. Hitung Offset
$halaman_aktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
$awal_data = ($jumlah_data_per_halaman * $halaman_aktif) - $jumlah_data_per_halaman;

// 4. Hitung Total Data & Halaman
$result_total = $conn->query("SELECT COUNT(*) as total FROM siswa_pribadi");
$row_total = $result_total->fetch_assoc();
$total_data = $row_total['total'];
$jumlah_halaman = ceil($total_data / $jumlah_data_per_halaman);

// 5. Query Data Utama
$query = "SELECT siswa_id, nisn, nik, nama_lengkap, kelas, status FROM siswa_pribadi 
          ORDER BY siswa_id DESC LIMIT $awal_data, $jumlah_data_per_halaman";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMP Nusantara</title>

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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            background: white;
            overflow: hidden;
        }

        .table-custom thead {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-custom th,
        .table-custom td {
            padding: 1rem;
            vertical-align: middle;
        }

        .avatar-placeholder {
            width: 35px;
            height: 35px;
            background-color: #e2e8f0;
            color: #64748b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .search-input {
            border-radius: 50px;
            padding-left: 40px;
            border: 1px solid #e2e8f0;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2394a3b8' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 15px center;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
            border-color: var(--primary-color);
            background-color: white;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-mortarboard-fill text-warning me-2"></i>Sistem Sekolah
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <a href="pengaturan.php" class="btn btn-outline-light btn-sm border-0" title="Pengaturan Sekolah & Akun">
                            <i class="bi bi-gear-fill"></i> <span class="d-lg-none">Pengaturan</span>
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <span class="text-white-50 small">| Admin Panel</span>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalLogout">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">

        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-4">
                <h2 class="fw-bold text-dark mb-0">Data Siswa</h2>
                <p class="text-muted small mb-0">Total <?= $total_data ?> siswa terdaftar</p>
            </div>

            <div class="col-6 col-md-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">Show</span>
                    <select class="form-select border-start-0 ps-0" onchange="changeLimit(this.value)">
                        <?php foreach ($limit_options as $option): ?>
                            <option value="<?= $option ?>" <?= ($jumlah_data_per_halaman == $option) ? 'selected' : '' ?>>
                                <?= $option ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="position-relative">
                    <input type="text" id="keyword" class="form-control search-input form-control-sm" placeholder="Cari data..." autocomplete="off">
                </div>
            </div>

            <div class="col-md-3 text-md-end d-flex gap-2 justify-content-md-end">
                <button type="button" class="btn btn-success rounded-pill btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="bi bi-file-earmark-excel"></i> Import
                </button>
                <a href="tambah.php" class="btn btn-primary rounded-pill btn-sm shadow-sm" style="background-color: var(--primary-color); border:none;">
                    <i class="bi bi-plus-lg"></i> Tambah
                </a>
            </div>
        </div>

        <div class="card card-custom">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Siswa</th>
                                <th>NISN</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-container">
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder me-3">
                                                <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= $row['nama_lengkap'] ?></div>
                                                <div class="small text-muted">NIK: <?= $row['nik'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= $row['nisn'] ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['kelas'] ?></span></td>
                                    <td>
                                        <select
                                            class="form-select form-select-sm fw-bold <?= ($row['status'] == 'Aktif') ? 'text-success border-success' : 'text-danger border-danger' ?>"
                                            style="width: 120px;"
                                            onchange="updateStatus(<?= $row['siswa_id'] ?>, this)">

                                            <option value="Aktif" <?= ($row['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                                            <option value="Non-Aktif" <?= ($row['status'] == 'Non-Aktif') ? 'selected' : '' ?>>Non-Aktif</option>
                                        </select>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light text-info border me-1 btn-detail"
                                            data-id="<?= $row['siswa_id'] ?>" title="Lihat Detail">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <a href="edit.php?id=<?= $row['siswa_id'] ?>" class="btn btn-sm btn-light text-primary border" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['siswa_id'] ?>" class="btn btn-sm btn-light text-danger border ms-1 tombol-hapus" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3" id="pagination-container">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mb-0">
                        
                        <?php if ($halaman_aktif > 1): ?>
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="?halaman=<?= $halaman_aktif - 1 ?>&limit=<?= $jumlah_data_per_halaman ?>">&laquo;</a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $adjacents = 2; // Jumlah halaman tetangga
                        $pages_to_show = [];

                        // 1. Selalu masukkan Halaman 1
                        $pages_to_show[] = 1;

                        // 2. Selalu masukkan Halaman Terakhir
                        if ($jumlah_halaman > 1) {
                            $pages_to_show[] = $jumlah_halaman;
                        }

                        // 3. Masukkan halaman di sekitar halaman aktif
                        for ($j = ($halaman_aktif - $adjacents); $j <= ($halaman_aktif + $adjacents); $j++) {
                            if ($j > 1 && $j < $jumlah_halaman) {
                                $pages_to_show[] = $j;
                            }
                        }

                        // 4. Urutkan dan hapus duplikat
                        $pages_to_show = array_unique($pages_to_show);
                        sort($pages_to_show);

                        // 5. Loop untuk menampilkan (DEFINISI $i ADA DI SINI)
                        $prev_page = 0;
                        foreach ($pages_to_show as $i): // <--- Variabel $i didefinisikan di sini
                            
                            // Jika ada celah, tampilkan "..."
                            if ($prev_page > 0 && $i > ($prev_page + 1)): 
                        ?>
                                <li class="page-item disabled">
                                    <span class="page-link border-0 text-muted bg-transparent">...</span>
                                </li>
                        <?php 
                            endif;
                            
                            // Tentukan Class Aktif
                            $active_class = ($i == $halaman_aktif) ? 'bg-primary text-white' : 'text-dark';
                            $is_active = ($i == $halaman_aktif) ? 'active' : '';
                        ?>
                            <li class="page-item <?= $is_active ?>">
                                <a class="page-link border-0 rounded-circle <?= $active_class ?> mx-1" href="?halaman=<?= $i; ?>&limit=<?= $jumlah_data_per_halaman ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php
                            $prev_page = $i;
                        endforeach;
                        ?>

                        <?php if ($halaman_aktif < $jumlah_halaman): ?>
                            <li class="page-item">
                                <a class="page-link border-0 text-dark" href="?halaman=<?= $halaman_aktif + 1 ?>&limit=<?= $jumlah_data_per_halaman ?>">&raquo;</a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <?php
    include 'modal_dashboard.php';
    include 'modal_logout.php';
    include 'modal_detailsiswa.php';
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Script AJAX Live Search
        const keyword = document.getElementById('keyword');
        const container = document.getElementById('tabel-container');
        const pagination = document.getElementById('pagination-container');

        keyword.addEventListener('keyup', function() {
            // Buat objek AJAX
            const xhr = new XMLHttpRequest();

            // Cek kesiapan ajax
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    container.innerHTML = xhr.responseText;
                }
            }

            // Eksekusi ajax
            // Jika input kosong, load halaman dashboard (refresh) atau biarkan kosong
            // Di sini kita panggil file cari_siswa.php
            xhr.open('GET', 'cari_siswa.php?keyword=' + keyword.value, true);
            xhr.send();

            // Sembunyikan paginasi jika sedang mencari (karena hasil pencarian mungkin beda jumlah halamannya)
            if (keyword.value.length > 0) {
                pagination.style.display = 'none';
            } else {
                // Jika kosong, sebaiknya reload page untuk mengembalikan paginasi normal
                // Atau biarkan hidden. Opsi terbaik reload:
                location.reload();
            }
        });
        // Fungsi Ubah Limit
        function changeLimit(limit) {
            // Reset ke halaman 1 setiap kali limit berubah agar tidak error offset
            window.location.href = '?halaman=1&limit=' + limit;
        }
    </script>
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle-fill me-2"></i> Status berhasil diperbarui!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Fungsi Update Status via AJAX
        function updateStatus(id, selectElement) {
            const newStatus = selectElement.value;

            // Ubah warna select box secara visual langsung (UX Feedback)
            if (newStatus === 'Aktif') {
                selectElement.classList.remove('text-danger', 'border-danger');
                selectElement.classList.add('text-success', 'border-success');
            } else {
                selectElement.classList.remove('text-success', 'border-success');
                selectElement.classList.add('text-danger', 'border-danger');
            }

            // Kirim data ke backend
            const formData = new FormData();
            formData.append('id', id);
            formData.append('status', newStatus);

            fetch('update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Tampilkan notifikasi Toast
                        const toastLiveExample = document.getElementById('liveToast');
                        const toast = new bootstrap.Toast(toastLiveExample);
                        toast.show();
                    } else {
                        alert('Gagal mengubah status: ' + data.message);
                        location.reload(); // Reset jika gagal
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan.');
                });
        }

        // ... kode updateStatus dan changeLimit sebelumnya ...

        // --- LOGIKA SWEETALERT HAPUS (EVENT DELEGATION) ---
        document.addEventListener('click', function(e) {
            // Cek apakah elemen yang diklik adalah tombol hapus (atau ikon di dalamnya)
            const target = e.target.closest('.tombol-hapus');

            if (target) {
                // 1. Cegah link agar tidak langsung pindah halaman
                e.preventDefault();

                // 2. Ambil link dari atribut href
                const href = target.getAttribute('href');

                // 3. Tampilkan SweetAlert
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data siswa akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33', // Merah
                    cancelButtonColor: '#3085d6', // Biru
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true // Posisi tombol dibalik agar tombol Batal di kiri
                }).then((result) => {
                    if (result.isConfirmed) {
                        // 4. Jika user klik Ya, arahkan ke link hapus.php
                        window.location.href = href;
                    }
                });
            }
        });

        // Event Listener untuk Tombol Detail (Event Delegation)
        // Kita pakai delegation agar tombol tetap jalan meskipun setelah pencarian AJAX
        document.addEventListener('click', function(e) {
            // Cek apakah yang diklik adalah tombol detail atau icon di dalamnya
            const target = e.target.closest('.btn-detail');

            if (target) {
                const idSiswa = target.getAttribute('data-id');
                const modalElement = document.getElementById('modalDetailSiswa');
                const modalContent = document.getElementById('detailContent');

                // 1. Tampilkan Modal
                const myModal = new bootstrap.Modal(modalElement);
                myModal.show();

                // 2. Reset isi modal ke loading state
                modalContent.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Mengambil data...</p>
            </div>`;

                // 3. Fetch Data via AJAX
                const formData = new FormData();
                formData.append('id', idSiswa);

                fetch('get_detail_siswa.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        // 4. Isi modal dengan data yang didapat
                        modalContent.innerHTML = html;
                    })
                    .catch(error => {
                        modalContent.innerHTML = '<div class="alert alert-danger">Gagal mengambil data.</div>';
                    });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>