<?php
session_start();
require '../config.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // ON DELETE CASCADE di database akan otomatis menghapus data di tabel alamat & ortu
    $stmt = $conn->prepare("DELETE FROM siswa_pribadi WHERE siswa_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
//header("Location: dashboard.php");
echo "<script>
    // Pastikan library ini dimuat, atau redirect dgn parameter GET
    window.location = 'dashboard.php?pesan=hapus_sukses';
</script>";
?>