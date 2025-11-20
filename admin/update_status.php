<?php
session_start();
require '../config.php';

// Cek Login & Request Method
if (!isset($_SESSION['admin_logged_in']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? clean($_POST['status']) : '';

if ($id > 0 && in_array($status, ['Aktif', 'Non-Aktif'])) {
    $stmt = $conn->prepare("UPDATE siswa_pribadi SET status = ? WHERE siswa_id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
}
?>