<?php
require 'config.php';

// Ambil Data Identitas Sekolah
$info = $conn->query("SELECT * FROM identitas_sekolah WHERE id=1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Data Siswa - <?= htmlspecialchars($info['nama_sekolah']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-color: #0f172a;
            /* Navy Blue */
            --accent-color: #d97706;
            /* Gold */
            --bg-soft: #f1f5f9;
        }

        body {
            background-color: var(--bg-soft);
            font-family: 'Segoe UI', sans-serif;
        }

        .search-box {
            background: white;
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }

        .search-input {
            border: none;
            padding-left: 20px;
            font-size: 1.1rem;
        }

        .search-input:focus {
            box-shadow: none;
        }

        .btn-search {
            border-radius: 50px;
            padding: 10px 30px;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }

        .btn-search:hover {
            background-color: #1e293b;
            color: var(--accent-color);
        }

        /* Result Card Styling */
        .result-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            background: white;
            animation: fadeIn 0.5s ease-in-out;
        }

        .result-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            position: relative;
        }

        .result-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--accent-color);
        }

        .section-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 1px;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .data-label {
            font-size: 0.85rem;
            color: #64748b;
        }

        .data-value {
            font-size: 1rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 10px;
        }

        /* Tab Styling */
        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 600;
            border: none;
            border-bottom: 3px solid transparent;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--accent-color);
            background: transparent;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-light bg-white shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="bi bi-mortarboard-fill text-warning me-2"></i>
                <?= htmlspecialchars($info['nama_sekolah']) ?>
            </a>
            <a href="admin/login.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Login Admin</a>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">

        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark mb-3">Portal Data Siswa</h2>
            <p class="text-muted mb-2"><?= htmlspecialchars($info['slogan']) ?></p>
            <p class="small text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($info['alamat']) ?></p>

            <form action="" method="GET" class="mt-4 d-flex justify-content-center">
                <div class="input-group search-box w-75">
                    <input type="text" name="keyword" class="form-control search-input" placeholder="Ketik NISN atau NIK..." required autocomplete="off">
                    <button class="btn btn-search" type="submit">CARI DATA</button>
                </div>
            </form>
        </div>

        <?php
        if (isset($_GET['keyword'])) {
            $keyword = clean($_GET['keyword']);

            // Query Data Siswa
            $query = "SELECT * FROM siswa_pribadi 
                  LEFT JOIN siswa_alamat ON siswa_pribadi.siswa_id = siswa_alamat.siswa_id
                  LEFT JOIN siswa_ortu ON siswa_pribadi.siswa_id = siswa_ortu.siswa_id
                  WHERE siswa_pribadi.nisn = ? OR siswa_pribadi.nik = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $keyword, $keyword);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $formatter = new IntlDateFormatter(
                    'id_ID',
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                    'Asia/Jakarta',
                    IntlDateFormatter::GREGORIAN,
                    'd MMMM y' // Pola format: d=hari, MMMM=bulan lengkap, y=tahun
                );
                // Format Tanggal Indonesia
                $tgl_lahir = date("d F Y", strtotime($data['tanggal_lahir']));
                $tgl_konversi = new DateTime($tgl_lahir);
                $tgl_lahir_id = $formatter->format($tgl_konversi);
                $gender = ($data['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';
                $badge_class = ($data['status'] == 'Aktif') ? 'bg-success' : 'bg-danger';

                // Query Data BK (Kasus/Prestasi)
                $siswa_id = $data['siswa_id'];
                $query_bk = "SELECT * FROM bk_kasus WHERE siswa_id = '$siswa_id' ORDER BY tanggal DESC";
                $result_bk = $conn->query($query_bk);

                // Hitung Poin
                $total_poin = 0;
        ?>

                <div class="card result-card shadow-lg mb-5">
                    <div class="result-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold"><?= $data['nama_lengkap'] ?></h3>
                            <small class="opacity-75">NISN: <?= $data['nisn'] ?> | Kelas: <?= $data['kelas'] ?></small>
                        </div>
                        <div>
                            <a href="cetak_kartu.php?nisn=<?= $data['nisn'] ?>" target="_blank" class="btn btn-light btn-sm fw-bold text-primary me-2">
                                <i class="bi bi-printer-fill"></i> Cetak Kartu
                            </a>
                            <span class="badge <?= $badge_class ?> px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> <?= $data['status'] ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-3" id="profil-tab" data-bs-toggle="tab" data-bs-target="#profil-pane" type="button" role="tab"><i class="bi bi-person-vcard me-2"></i>Profil Siswa</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-3" id="bk-tab" data-bs-toggle="tab" data-bs-target="#bk-pane" type="button" role="tab"><i class="bi bi-journal-bookmark me-2"></i>Catatan & Prestasi</button>
                            </li>
                        </ul>

                        <div class="tab-content p-4" id="myTabContent">

                            <div class="tab-pane fade show active" id="profil-pane" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-4 border-end">
                                        <div class="section-title"><i class="bi bi-person-lines-fill me-2 text-warning"></i> Data Pribadi</div>
                                        <div class="mb-3">
                                            <div class="data-label">Tempat, Tgl Lahir</div>
                                            <div class="data-value"><?= $data['tempat_lahir'] ?>, <?= $tgl_lahir_id ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">NIK</div>
                                            <div class="data-value"><?= $data['nik'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">Jenis Kelamin</div>
                                            <div class="data-value"><?= $gender ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-4 border-end">
                                        <div class="section-title"><i class="bi bi-geo-alt-fill me-2 text-warning"></i> Alamat Domisili</div>
                                        <div class="mb-3">
                                            <div class="data-label">Jalan / Dusun</div>
                                            <div class="data-value"><?= $data['alamat_jalan'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">Desa - Kec</div>
                                            <div class="data-value"><?= $data['desa_kelurahan'] ?> - <?= $data['kecamatan'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">Kota / Kab</div>
                                            <div class="data-value"><?= $data['kota_kabupaten'] ?></div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="section-title"><i class="bi bi-people-fill me-2 text-warning"></i> Data Orang Tua</div>
                                        <div class="mb-3">
                                            <div class="data-label">Nama Ayah</div>
                                            <div class="data-value"><?= $data['nama_ayah'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">Pekerjaan Ayah</div>
                                            <div class="data-value"><?= $data['pekerjaan_ayah'] ?></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="data-label">Nama Ibu</div>
                                            <div class="data-value"><?= $data['nama_ibu'] ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="bk-pane" role="tabpanel">
                                <div class="alert alert-info small mb-3">
                                    <i class="bi bi-info-circle-fill me-1"></i> Data ini mencakup catatan prestasi, kedisiplinan, dan bimbingan selama menjadi siswa.
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Kategori</th>
                                                <th>Keterangan</th>
                                                <th class="text-center">Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result_bk->num_rows > 0) {
                                                while ($bk = $result_bk->fetch_assoc()) {
                                                    $total_poin += $bk['poin'];

                                                    // Warna Badge Kategori
                                                    $badge_bk = 'bg-secondary';
                                                    if ($bk['kategori'] == 'Pelanggaran') $badge_bk = 'bg-danger';
                                                    if ($bk['kategori'] == 'Prestasi') $badge_bk = 'bg-success';
                                                    if ($bk['kategori'] == 'Masalah Pribadi') $badge_bk = 'bg-warning text-dark';

                                                    echo "<tr>
                                                    <td class='small'>" . date('d/m/Y', strtotime($bk['tanggal'])) . "</td>
                                                    <td><span class='badge $badge_bk'>{$bk['kategori']}</span></td>
                                                    <td>
                                                        <strong>{$bk['judul_kasus']}</strong>
                                                        <div class='small text-muted mt-1'>{$bk['deskripsi']}</div>
                                                        " . (!empty($bk['tindak_lanjut']) ? "<div class='small text-primary mt-1'><i>TL: {$bk['tindak_lanjut']}</i></div>" : "") . "
                                                    </td>
                                                    <td class='text-center fw-bold " . ($bk['poin'] > 0 ? 'text-danger' : 'text-dark') . "'>
                                                        " . ($bk['poin'] > 0 ? '-' . $bk['poin'] : '-') . "
                                                    </td>
                                                </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4' class='text-center py-4 text-muted'>Belum ada catatan kasus atau prestasi.</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                        <?php if ($total_poin > 0): ?>
                                            <tfoot class="table-light">
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold">Total Poin Pelanggaran</td>
                                                    <td class="text-center fw-bold text-danger">-<?= $total_poin ?></td>
                                                </tr>
                                            </tfoot>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">Data ini dihasilkan oleh sistem pada <?= date('d-m-Y H:i') ?> WIB</small>
                    </div>
                </div>

        <?php
            } else {
                // JIKA DATA TIDAK DITEMUKAN
                echo '
            <div class="alert alert-light shadow-sm border-0 text-center py-5" role="alert" style="border-radius: 15px;">
                <div class="mb-3 text-danger display-1"><i class="bi bi-emoji-frown"></i></div>
                <h4 class="alert-heading fw-bold text-dark">Data Tidak Ditemukan</h4>
                <p class="text-muted">Maaf, kami tidak dapat menemukan data siswa dengan NISN/NIK: <strong>' . htmlspecialchars($keyword) . '</strong></p>
                <hr>
                <p class="mb-0 small">Silakan periksa kembali nomor yang Anda masukkan atau hubungi Tata Usaha Sekolah.</p>
            </div>';
            }
        }
        ?>

    </div>

    <div class="text-center text-muted mt-5 mb-3 small">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($info['nama_sekolah']) ?> | IT Division
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>