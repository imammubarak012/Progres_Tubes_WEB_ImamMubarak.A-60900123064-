<?php 
// File login.php: Halaman Login (Modul 5)
session_start();

// Jika sudah login, redirect sesuai role
if(isset($_SESSION['login']) && $_SESSION['login'] === 'aktif') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("location: manajemen_simas.php"); 
    } elseif (isset($_SESSION['role']) && isset($_SESSION['role']) === 'user') {
        header("location: dashboard_user.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Mengubah background body dengan GAMBAR dan efek blur */
        body {
            /* MENGGANTI PLACEHOLDER GAMBAR DENGAN URL YANG LEBIH BAIK (Sama seperti home.php) */
            background-image: url('https://images.unsplash.com/photo-1510511459019-5beef1451c22?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); 
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        
        /* Memberi lapisan transparan gelap di atas gambar agar tulisan terlihat */
        .auth-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            /* Overlay dan Blur DITERAPKAN LANGSUNG DI AUTH-WRAPPER */
            background-color: rgba(201, 201, 201, 0.77); 
            backdrop-filter: blur(8px);
            position: relative; /* Penting: agar tombol home-button-top diposisikan relatif terhadap viewport, bukan auth-wrapper */
        }
        
        .auth-box {
            background: white;
            padding: 3rem 2.5rem; /* Padding lebih besar */
            border-radius: 16px; /* Lebih rounded */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5); /* Shadow lebih dramatis */
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            z-index: 10; /* Pastikan di atas background */
        }
        
        .auth-box h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 2rem; 
            font-weight: 900; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Efek garis bawah biru yang menonjol */
        .auth-box h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary-light);
            margin: 10px auto 0;
            border-radius: 2px;
        }
        
        /* Mengatur tata letak form agar lebih kompak */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        label {
            font-weight: 600;
            color: var(--text-color);
            margin-top: 5px;
        }
        
        .btn-register-link {
            color: var(--primary-color);
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .btn-register-link:hover {
            color: var(--primary-light);
        }

        /* Gaya Tombol Kembali ke Home (DIPOSISIKAN RELATIF TERHADAP VIEWPONT) */
        .home-button-top {
            position: fixed; /* Menggunakan fixed agar tidak bergerak saat scroll */
            top: 30px; /* Jarak dari atas */
            right: 30px; /* Jarak dari kanan */
            text-decoration: none;
            color: white; /* Warna Putih agar kontras dengan background gelap */
            font-weight: bold;
            padding: 10px 15px;
            border-radius: 8px;
            background-color: var(--primary-color); /* Warna dasar tombol */
            transition: background-color 0.2s;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .home-button-top:hover {
            background-color: var(--primary-light);
            box-shadow: 0 6px 15px rgba(36, 35, 35, 0.4);
        }
        
    </style>
</head>
<body>
    <div class="auth-wrapper">
        
        <!-- Tombol Kembali ke Home di Kanan Atas (DI LUAR CARD) -->
        <!-- Menggunakan position: fixed (didefinisikan di style) agar selalu di pojok kanan atas viewport -->
        <a href="home.php" class="home-button-top">kembali ke home</a>

        <div class="auth-box">
            
            <h2>Login SIMAS</h2>
            
            <?php 
            if(isset($_GET['pesan']) && $_GET['pesan'] == "gagal") {
                // Error Handling (Modul 5)
                echo "<p style='color: var(--danger-color); text-align: center; margin-bottom: 15px; font-weight: bold;'>Login gagal! Username atau password salah.</p>";
            }
            ?>

            <form action="ceklogin.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Masukkan Username" required>
                
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">LOGIN</button>
            </form>

            <p class="text-center" style="margin-top: 2rem; font-size: 0.95rem;">
                Belum punya akun peminjam? 
                <a href="register.php" class="btn-register-link">Daftar di sini</a>
            </p>
            <p class="text-center" style="margin-top: 1rem; font-size: 0.95rem;">
                <!-- Link katalog publik di bawah tombol daftar (Opsional) -->
            </p>
        </div>
    </div>
</body>
</html>