<?php
require '../config.php';

if (isset($_POST['id'])) {
    $id = clean($_POST['id']);

    // 1. Ambil Data Profil (Query Lama)
    $query = "SELECT * FROM siswa_pribadi 
              LEFT JOIN siswa_alamat ON siswa_pribadi.siswa_id = siswa_alamat.siswa_id 
              LEFT JOIN siswa_ortu ON siswa_pribadi.siswa_id = siswa_ortu.siswa_id 
              WHERE siswa_pribadi.siswa_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    // 2. Ambil Riwayat BK (Query Baru)
    $query_bk = "SELECT * FROM bk_kasus WHERE siswa_id = ? ORDER BY tanggal DESC";
    $stmt_bk = $conn->prepare($query_bk);
    $stmt_bk->bind_param("i", $id);
    $stmt_bk->execute();
    $res_bk = $stmt_bk->get_result();

    // --- RENDER TAMPILAN ---
    if ($data) {
        $tgl_lahir = date("d F Y", strtotime($data['tanggal_lahir']));
        $status_badge = ($data['status'] == 'Aktif') ? 'bg-success' : 'bg-danger';

        echo '
        <div class="text-center mb-4">
            <div class="avatar-placeholder mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.5rem; background-color: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                '.strtoupper(substr($data['nama_lengkap'], 0, 1)).'
            </div>
            <h4 class="fw-bold mb-0">'.$data['nama_lengkap'].'</h4>
            <p class="text-muted small">'.$data['kelas'].' | '.$data['nisn'].'</p>
        </div>

        <!-- TAB NAVIGASI -->
        <ul class="nav nav-tabs mb-3" id="detailTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profil" type="button">Profil Siswa</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bk" type="button">Riwayat BK & Poin</button></li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: PROFIL (Isi lama) -->
            <div class="tab-pane fade show active" id="profil">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Data Pribadi</h6>
                        <ul class="list-unstyled small">
                            <li><strong>NIK:</strong> '.$data['nik'].'</li>
                            <li><strong>TTL:</strong> '.$data['tempat_lahir'].', '.$tgl_lahir.'</li>
                            <li><strong>Alamat:</strong> '.$data['alamat_jalan'].', '.$data['desa_kelurahan'].'</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary">Orang Tua</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Ayah:</strong> '.$data['nama_ayah'].' ('.$data['pekerjaan_ayah'].')</li>
                            <li><strong>Ibu:</strong> '.$data['nama_ibu'].'</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB 2: RIWAYAT BK (Fitur Baru) -->
            <div class="tab-pane fade" id="bk">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tgl</th>
                                <th>Kategori</th>
                                <th>Kasus</th>
                                <th>Poin</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
                        if($res_bk->num_rows > 0){
                            $total_poin = 0;
                            while($bk = $res_bk->fetch_assoc()){
                                $total_poin += $bk['poin'];
                                echo "<tr>
                                    <td>".date('d/m/y', strtotime($bk['tanggal']))."</td>
                                    <td>{$bk['kategori']}</td>
                                    <td>{$bk['judul_kasus']}</td>
                                    <td class='text-center text-danger'>".($bk['poin'] > 0 ? '-'.$bk['poin'] : '-')."</td>
                                </tr>";
                            }
                            echo "<tr><td colspan='3' class='text-end fw-bold'>Total Poin Pelanggaran</td><td class='text-center fw-bold text-danger'>-$total_poin</td></tr>";
                        } else {
                            echo "<tr><td colspan='4' class='text-center text-muted'>Belum ada catatan BK.</td></tr>";
                        }

        echo '          </tbody>
                    </table>
                </div>
            </div>
        </div>';
    } else {
        echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
    }
}
?>