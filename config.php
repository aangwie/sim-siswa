<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "smp_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi bantu untuk membersihkan input
function clean($data) {
    global $conn;
    return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
}
?>