<?php
require '../config.php';

if (isset($_POST['id'])) {
    $id = clean($_POST['id']);

    // ... Query sama ...
    $query = "SELECT * FROM siswa_pribadi 
              LEFT JOIN siswa_alamat ON siswa_pribadi.siswa_id = siswa_alamat.siswa_id 
              LEFT JOIN siswa_ortu ON siswa_pribadi.siswa_id = siswa_ortu.siswa_id 
              WHERE siswa_pribadi.siswa_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $tgl_lahir = date("d F Y", strtotime($data['tanggal_lahir']));
        $gender = ($data['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan';
        $status_badge = ($data['status'] == 'Aktif') ? 'bg-success' : 'bg-danger';
          // Ambil Tempat Lahir (Handle jika kosong)
        $tempat = !empty($data['tempat_lahir']) ? $data['tempat_lahir'] : '-';

        $formatter = new IntlDateFormatter(
            'id_ID',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Jakarta',
            IntlDateFormatter::GREGORIAN,
            'd MMMM y' // Pola format: d=hari, MMMM=bulan lengkap, y=tahun
        );

        $tgl_konversi = new DateTime($tgl_lahir);
        $tgl_lahir_id = $formatter->format($tgl_konversi);
      

        echo '
        <div class="text-center mb-4">
            <div class="avatar-placeholder mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; background-color: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                ' . strtoupper(substr($data['nama_lengkap'], 0, 1)) . '
            </div>
            <h4 class="fw-bold">' . $data['nama_lengkap'] . '</h4>
            <p class="text-muted mb-1">NISN: ' . $data['nisn'] . ' | Kelas: <span class="badge bg-primary">' . $data['kelas'] . '</span></p>
            <span class="badge ' . $status_badge . ' rounded-pill">' . $data['status'] . '</span>
        </div>

        <div class="row g-3">
            <div class="col-md-4 border-end">
                <h6 class="text-primary fw-bold border-bottom pb-2"><i class="bi bi-person-fill me-1"></i> Data Pribadi</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><strong>NIK:</strong><br> ' . $data['nik'] . '</li>
                    <li class="mb-2"><strong>Jenis Kelamin:</strong><br> ' . $gender . '</li>
                    <li class="mb-2"><strong>Tempat, Tgl Lahir:</strong><br> ' . $tempat . ', ' . $tgl_lahir_id . '</li>
                </ul>
            </div>

            <div class="col-md-4 border-end">
                <h6 class="text-primary fw-bold border-bottom pb-2"><i class="bi bi-geo-alt-fill me-1"></i> Alamat</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><strong>Jalan:</strong><br> ' . $data['alamat_jalan'] . '</li>
                    <li class="mb-2"><strong>Desa/Kel:</strong><br> ' . $data['desa_kelurahan'] . '</li>
                    <li class="mb-2"><strong>Kecamatan:</strong><br> ' . $data['kecamatan'] . '</li>
                    <li class="mb-2"><strong>Kota/Kab:</strong><br> ' . $data['kota_kabupaten'] . '</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-primary fw-bold border-bottom pb-2"><i class="bi bi-people-fill me-1"></i> Orang Tua</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><strong>Ayah:</strong><br> ' . $data['nama_ayah'] . '</li>
                    <li class="mb-2"><strong>Pekerjaan Ayah:</strong><br> ' . $data['pekerjaan_ayah'] . '</li>
                    <li class="mb-2"><strong>Ibu:</strong><br> ' . $data['nama_ibu'] . '</li>
                </ul>
            </div>
        </div>';
    } else {
        echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
    }
}
