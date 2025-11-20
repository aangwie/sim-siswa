<?php
require '../config.php'; // Pastikan koneksi database terhubung

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
$keyword = $conn->real_escape_string($keyword); // Mencegah SQL Injection sederhana

// Query Pencarian (Mencari NISN, Nama, atau Kelas)
$query = "SELECT siswa_id, nisn, nik, nama_lengkap, kelas, status 
          FROM siswa_pribadi 
          WHERE 
            nama_lengkap LIKE '%$keyword%' OR 
            nisn LIKE '%$keyword%' OR 
            kelas LIKE '%$keyword%'
          ORDER BY nama_lengkap ASC LIMIT 20";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $badge = ($row['status'] == 'Aktif') ? 'bg-success' : 'bg-danger';
        $inisial = strtoupper(substr($row['nama_lengkap'], 0, 1));
        $colorClass = ($row['status'] == 'Aktif') ? 'text-success border-success' : 'text-danger border-danger';
        $selAktif = ($row['status'] == 'Aktif') ? 'selected' : '';
        $selNon = ($row['status'] == 'Non-Aktif') ? 'selected' : '';

        // Tampilkan baris tabel persis seperti di dashboard.php
        echo "
        <tr>
            <td class='ps-4'>
                <div class='d-flex align-items-center'>
                    <div class='avatar-placeholder me-3'>
                        {$inisial}
                    </div>
                    <div>
                        <div class='fw-bold text-dark'>{$row['nama_lengkap']}</div>
                        <div class='small text-muted'>NIK: {$row['nik']}</div>
                    </div>
                </div>
            </td>
            <td>{$row['nisn']}</td>
            <td><span class='badge bg-light text-dark border'>{$row['kelas']}</span></td>
            <td>
                <select 
                    class='form-select form-select-sm fw-bold $colorClass' 
                    style='width: 120px;'
                    onchange='updateStatus({$row['siswa_id']}, this)'>
                    <option value='Aktif' $selAktif>Aktif</option>
                    <option value='Non-Aktif' $selNon>Non-Aktif</option>
                </select>
            </td>
            <td class='text-end pe-4'>
                <button type='button' class='btn btn-sm btn-light text-info border me-1 btn-detail' 
                        data-id='{$row['siswa_id']}' title='Lihat Detail'>
                    <i class='bi bi-eye-fill'></i>
                </button>
                <a href='edit.php?id={$row['siswa_id']}' class='btn btn-sm btn-light text-primary border' title='Edit'>
                    <i class='bi bi-pencil-square'></i>
                </a>
                <a href='hapus.php?id={$row['siswa_id']}' class='btn btn-sm btn-light text-danger border ms-1 tombol-hapus' title='Hapus'>
                    <i class='bi bi-trash'></i>
                </a>
            </td>
        </tr>
        ";
    }
} else {
    // Jika data tidak ditemukan
    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Data tidak ditemukan untuk kata kunci: <b>$keyword</b></td></tr>";
}
