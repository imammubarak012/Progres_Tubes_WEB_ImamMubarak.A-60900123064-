<?php
session_start();
include 'config.php';

if(isset($_POST['register'])){
    // Mengambil data dari form
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $hp = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);

    // Query INSERT data peminjam (Modul 7: Create)
    $sql = "INSERT INTO peminjam (username, password, nama_lengkap, nomor_hp, nik) 
            VALUES ('$username', '$password', '$nama', '$hp', '$nik')";
    
    $query = mysqli_query($conn, $sql);

    // Error Handling (Modul 7)
    if($query) {
        echo "<script>alert('Pendaftaran berhasil! Silahkan login.')</script>";
        echo "<meta http-equiv='refresh' content='0; url=login.php'>";
    } else {
        $error = mysqli_error($conn);
        echo "<script>alert('Pendaftaran gagal. Username atau NIK mungkin sudah terdaftar. Error: $error')</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-box">
            <h2>Daftar Akun Peminjam</h2>
            
            <form action="register.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Buat username unik" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama sesuai KTP" required>

                <label for="nomor_hp">Nomor HP:</label>
                <input type="text" id="nomor_hp" name="nomor_hp" placeholder="Contoh: 0812xxxxxx" required>
                
                <label for="nik">NIK (Nomor Induk Kependudukan):</label>
                <input type="text" id="nik" name="nik" placeholder="16 digit NIK" required>
                
                <button type="submit" name="register" class="btn btn-primary">Daftar Akun</button>
            </form>

            <p class="text-center" style="margin-top: 1rem;">Sudah punya akun? <a href="login.php" style="color: var(--primary-color);">Login di sini</a></p>
        </div>
    </div>
</body>
</html>