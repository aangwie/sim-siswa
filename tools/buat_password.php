<?php
// tools/buat_password.php
// Jalankan file ini di browser: localhost/smp_sys/tools/buat_password.php
?>
<!DOCTYPE html>
<html lang="id">
<head><title>Password Hasher</title></head>
<body style="font-family: sans-serif; padding: 20px;">
    <h3>Generator Password Admin</h3>
    <form method="POST">
        <input type="text" name="pass_input" placeholder="Masukkan password..." required>
        <button type="submit">Generate Hash</button>
    </form>

    <?php
    if (isset($_POST['pass_input'])) {
        $pass = $_POST['pass_input'];
        // Algoritma default PHP (Bcrypt)
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        
        echo "<hr>";
        echo "<p>Password Asli: <b>$pass</b></p>";
        echo "<p>Hash Database: <br><textarea cols='60' rows='3'>$hash</textarea></p>";
        echo "<p><i>Copy kode Hash di atas dan masukkan ke tabel 'admin' kolom 'password' via phpMyAdmin.</i></p>";
    }
    ?>
</body>
</html>