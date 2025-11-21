<?php
session_start();
require '../config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

// --- 1. PROSES SIMPAN KASUS BARU ---
if (isset($_POST['simpan_kasus'])) {
    $siswa_id = clean($_POST['siswa_id']);
    $tanggal  = clean($_POST['tanggal']);
    $kategori = clean($_POST['kategori']);
    $judul    = clean($_POST['judul']);
    $deskripsi= clean($_POST['deskripsi']);
    $poin     = (int)$_POST['poin'];
    $penanganan = clean($_POST['penanganan']);

    $stmt = $conn->prepare("INSERT INTO bk_kasus (siswa_id, tanggal, kategori, judul_kasus, deskripsi, poin, penanganan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $siswa_id, $tanggal, $kategori, $judul, $deskripsi, $poin, $penanganan);
    
    if ($stmt->execute()) {
        header("Location: bk_kasus.php?msg=added"); exit;
    } else {
        echo "<script>alert('Gagal menyimpan!');</script>";
    }
}

// --- 2. PROSES UPDATE KASUS ---
if (isset($_POST['update_kasus'])) {
    $kasus_id = (int)$_POST['kasus_id'];
    $siswa_id = clean($_POST['siswa_id']);
    $tanggal  = clean($_POST['tanggal']);
    $kategori = clean($_POST['kategori']);
    $judul    = clean($_POST['judul']);
    $deskripsi= clean($_POST['deskripsi']);
    $poin     = (int)$_POST['poin'];
    $penanganan = clean($_POST['penanganan']);

    $stmt = $conn->prepare("UPDATE bk_kasus SET siswa_id=?, tanggal=?, kategori=?, judul_kasus=?, deskripsi=?, poin=?, penanganan=?, updated_at=NOW() WHERE kasus_id=?");
    $stmt->bind_param("issssisi", $siswa_id, $tanggal, $kategori, $judul, $deskripsi, $poin, $penanganan, $kasus_id);

    if ($stmt->execute()) {
        header("Location: bk_kasus.php?msg=updated"); exit;
    } else {
        echo "<script>alert('Gagal mengupdate!');</script>";
    }
}

// --- 3. HAPUS KASUS ---
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $conn->query("DELETE FROM bk_kasus WHERE kasus_id = $id_hapus");
    header("Location: bk_kasus.php?msg=deleted"); exit;
}

// --- FETCH DATA ---
$query = "SELECT k.*, s.nama_lengkap, s.kelas, s.nisn 
          FROM bk_kasus k 
          JOIN siswa_pribadi s ON k.siswa_id = s.siswa_id 
          ORDER BY k.tanggal DESC"; 
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Kasus Siswa</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <style>
        body { background-color: #f8fafc; font-family: sans-serif; }
        .badge-pelanggaran { background-color: #ef4444; }
        .badge-prestasi { background-color: #22c55e; }
        .badge-masalah { background-color: #f59e0b; }
        .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
        table.dataTable thead th { background-color: #f1f5f9; color: #334155; font-weight: 600; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary sticky-top mb-4">
        <div class="container">
            <span class="navbar-brand h1 mb-0"><i class="bi bi-journal-bookmark-fill me-2"></i>Buku Kasus & Prestasi</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="text-dark">Riwayat Kasus</h4>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKasus">
                <i class="bi bi-plus-lg"></i> Catat Kasus Baru
            </button>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <table id="tabelKasus" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th class="ps-3">Tanggal Kejadian</th>
                            <th>Siswa</th>
                            <th>Kategori</th>
                            <th>Judul & Deskripsi</th>
                            <th>Poin</th>
                            <th class="text-end pe-3" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $badge = 'bg-secondary';
                            if($row['kategori'] == 'Pelanggaran') $badge = 'badge-pelanggaran';
                            if($row['kategori'] == 'Prestasi') $badge = 'badge-prestasi';
                            if($row['kategori'] == 'Masalah Pribadi') $badge = 'badge-masalah';
                        ?>
                        <tr>
                            <td class="ps-3" data-order="<?= strtotime($row['tanggal']) ?>">
                                <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></div>
                                <?php if(!empty($row['updated_at'])): ?>
                                    <div class="text-muted fst-italic" style="font-size: 10px;">
                                        <i class="bi bi-pencil-fill" style="font-size: 9px;"></i> Edit: <?= date('d/m/y H:i', strtotime($row['updated_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $row['nama_lengkap'] ?></div>
                                <small class="text-muted"><?= $row['kelas'] ?> | NISN: <?= $row['nisn'] ?></small>
                            </td>
                            <td><span class="badge <?= $badge ?>"><?= $row['kategori'] ?></span></td>
                            <td>
                                <strong><?= $row['judul_kasus'] ?></strong><br>
                                <small class="text-muted d-inline-block text-truncate" style="max-width: 200px;"><?= $row['deskripsi'] ?></small>
                            </td>
                            <td class="fw-bold text-danger"><?= $row['poin'] > 0 ? '-'.$row['poin'] : '0' ?></td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-light text-info border me-1 btn-detail"
                                        data-tanggal="<?= date('d F Y', strtotime($row['tanggal'])) ?>"
                                        data-siswa="<?= $row['nama_lengkap'] ?> (<?= $row['kelas'] ?>)"
                                        data-kategori="<?= $row['kategori'] ?>"
                                        data-poin="<?= $row['poin'] ?>"
                                        data-judul="<?= htmlspecialchars($row['judul_kasus']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                        data-penanganan="<?= htmlspecialchars($row['penanganan']) ?>"
                                        title="Lihat Detail">
                                    <i class="bi bi-eye-fill"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-light text-primary border me-1 btn-edit"
                                        data-id="<?= $row['kasus_id'] ?>"
                                        data-siswa="<?= $row['siswa_id'] ?>"
                                        data-tanggal="<?= $row['tanggal'] ?>"
                                        data-kategori="<?= $row['kategori'] ?>"
                                        data-poin="<?= $row['poin'] ?>"
                                        data-judul="<?= htmlspecialchars($row['judul_kasus']) ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi']) ?>"
                                        data-penanganan="<?= htmlspecialchars($row['penanganan']) ?>"
                                        title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <a href="?hapus=<?= $row['kasus_id'] ?>" class="btn btn-sm btn-light text-danger border btn-hapus" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahKasus" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Catat Kasus / Prestasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Pilih Siswa</label>
                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Cari Siswa --</option>
                                    <?php 
                                    $siswa = $conn->query("SELECT siswa_id, nama_lengkap, kelas FROM siswa_pribadi ORDER BY nama_lengkap ASC");
                                    $data_siswa = [];
                                    while($s = $siswa->fetch_assoc()){
                                        $data_siswa[] = $s;
                                        echo "<option value='{$s['siswa_id']}'>{$s['nama_lengkap']} ({$s['kelas']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Kejadian</label>
                                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" class="form-select" required>
                                    <option value="Pelanggaran">Pelanggaran Tata Tertib</option>
                                    <option value="Prestasi">Prestasi / Penghargaan</option>
                                    <option value="Masalah Pribadi">Konseling Pribadi</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poin (Jika Pelanggaran)</label>
                                <input type="number" name="poin" class="form-control" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Judul Kasus / Prestasi</label>
                                <input type="text" name="judul" class="form-control" placeholder="Cth: Terlambat Sekolah, Juara Lomba, dll" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi Detail</label>
                                <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Penanganan / Tindak Lanjut</label>
                                <textarea name="penanganan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan_kasus" class="btn btn-primary">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditKasus" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Catatan Kasus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="kasus_id" id="edit_kasus_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Pilih Siswa</label>
                                <select name="siswa_id" id="edit_siswa_id" class="form-select" required>
                                    <option value="">-- Cari Siswa --</option>
                                    <?php foreach($data_siswa as $s){ echo "<option value='{$s['siswa_id']}'>{$s['nama_lengkap']} ({$s['kelas']})</option>"; } ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Kejadian</label>
                                <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="kategori" id="edit_kategori" class="form-select" required>
                                    <option value="Pelanggaran">Pelanggaran Tata Tertib</option>
                                    <option value="Prestasi">Prestasi / Penghargaan</option>
                                    <option value="Masalah Pribadi">Konseling Pribadi</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poin (Jika Pelanggaran)</label>
                                <input type="number" name="poin" id="edit_poin" class="form-control" value="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Judul Kasus / Prestasi</label>
                                <input type="text" name="judul" id="edit_judul" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi Detail</label>
                                <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Penanganan / Tindak Lanjut</label>
                                <textarea name="penanganan" id="edit_penanganan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update_kasus" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailKasus" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle-fill me-2"></i>Detail Kasus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr>
                            <td class="fw-bold text-muted" width="35%">Siswa</td>
                            <td id="det_siswa"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Tanggal</td>
                            <td id="det_tanggal"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Kategori</td>
                            <td id="det_kategori"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Judul Kasus</td>
                            <td id="det_judul" class="fw-bold"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Poin</td>
                            <td id="det_poin" class="text-danger fw-bold"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Deskripsi</td>
                            <td id="det_deskripsi" class="text-break bg-light p-2 rounded"></td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Penanganan</td>
                            <td id="det_penanganan" class="text-break bg-light p-2 rounded"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <script>
        // 1. DATATABLES
        $(document).ready(function() {
            $('#tabelKasus').DataTable({
                responsive: true,
                language: { search: "Cari Kasus:", lengthMenu: "Tampilkan _MENU_ data", info: "Menampilkan _START_-_END_ dari _TOTAL_", paginate: { next: ">>", previous: "<<" } },
                columnDefs: [{ orderable: false, targets: 5 }], order: [[ 0, 'desc' ]]
            });
        });

        // 2. HANDLER NOTIFIKASI
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            let t = 'Aksi berhasil!', i = 'success';
            if (msg === 'added') t = 'Data ditambahkan!';
            if (msg === 'updated') t = 'Data diperbarui!';
            if (msg === 'deleted') { t = 'Data dihapus!'; i = 'warning'; }
            Toast.fire({ icon: i, title: t });
            window.history.replaceState(null, null, window.location.pathname);
        }

        // 3. HANDLER HAPUS
        $(document).on('click', '.btn-hapus', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            Swal.fire({
                title: 'Hapus Data?', text: "Data hilang permanen!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Ya, Hapus!'
            }).then((result) => { if (result.isConfirmed) window.location.href = href; });
        });

        // 4. HANDLER EDIT
        $(document).on('click', '.btn-edit', function() {
            $('#edit_kasus_id').val($(this).data('id'));
            $('#edit_siswa_id').val($(this).data('siswa'));
            $('#edit_tanggal').val($(this).data('tanggal'));
            $('#edit_kategori').val($(this).data('kategori'));
            $('#edit_poin').val($(this).data('poin'));
            $('#edit_judul').val($(this).data('judul'));
            $('#edit_deskripsi').val($(this).data('deskripsi'));
            $('#edit_penanganan').val($(this).data('penanganan'));
            new bootstrap.Modal(document.getElementById('modalEditKasus')).show();
        });

        // 5. HANDLER DETAIL (BARU)
        $(document).on('click', '.btn-detail', function() {
            $('#det_siswa').text($(this).data('siswa'));
            $('#det_tanggal').text($(this).data('tanggal'));
            $('#det_kategori').text($(this).data('kategori'));
            $('#det_judul').text($(this).data('judul'));
            $('#det_poin').text($(this).data('poin') > 0 ? '-' + $(this).data('poin') : '0');
            $('#det_deskripsi').text($(this).data('deskripsi'));
            $('#det_penanganan').text($(this).data('penanganan') || '-');
            new bootstrap.Modal(document.getElementById('modalDetailKasus')).show();
        });
    </script>
</body>
</html>