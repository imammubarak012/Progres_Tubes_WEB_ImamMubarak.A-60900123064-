<?php 
// File user_akun.php: Halaman Terpisah untuk Manajemen Akun User
// Memindahkan logika update akun dari dashboard_user.php

session_start();
include 'config.php'; 

// Cek otentikasi dan role User
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("location: login.php");
    exit;
}
$nama_user = $_SESSION['nama_lengkap'];
$id_peminjam = $_SESSION['id_peminjam'];

// --- BLOK LOGIKA UPDATE AKUN (Modul 8: Update CRUD) ---
if(isset($_POST['update_akun'])){
    $new_nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $new_hp = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    $new_nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);

    // Mulai query UPDATE (Modul 8)
    $sql_update = "UPDATE peminjam SET 
                   nama_lengkap='$new_nama', 
                   nomor_hp='$new_hp', 
                   nik='$new_nik'";
                   
    if (!empty($new_password)) {
        // Jika password diisi, update password juga
        $sql_update .= ", password='$new_password'";
    }

    $sql_update .= " WHERE id_peminjam='$id_peminjam'";

    if(mysqli_query($conn, $sql_update)) {
        // Jika nama berubah, update juga session dan tabel peminjaman
        if ($new_nama !== $nama_user) {
             // Update nama di tabel transaksi (penting untuk konsistensi data)
             mysqli_query($conn, "UPDATE peminjaman SET nama_peminjam='$new_nama' WHERE nama_peminjam='$nama_user'");
             $_SESSION['nama_lengkap'] = $new_nama; // Update Session
             $nama_user = $new_nama; // Update variabel lokal
        }
        echo "<script>alert('Informasi akun berhasil diperbarui!')</script>";
    } else {
        echo "<script>alert('Gagal memperbarui akun. NIK atau Username mungkin sudah terdaftar.')</script>";
    }
    echo "<meta http-equiv='refresh' content='0; url=user_akun.php'>";
    exit;
}

// Ambil data user terbaru untuk form (Modul 7: Read)
$q_user_data = mysqli_query($conn, "SELECT * FROM peminjam WHERE id_peminjam='$id_peminjam'");
$user_data = mysqli_fetch_assoc($q_user_data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Akun - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Menggunakan style dasar yang sama dengan Admin Dashboard */
        body { margin: 0; padding: 0; background-color: #f4f4f4; }
        
        .dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr; 
            min-height: 100vh;
            max-width: 100%;
            margin: 0;
        }

        /* SIDEBAR STYLES (DIPINDAHKAN DARI DASHBOARD_USER.PHP) */
        .sidebar {
            background-color: #2c3e50;
            color: white;
            padding: 0;
            overflow-y: auto;
            height: 100vh;
            position: sticky;
            top: 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
        }
        .sidebar-profile {
            padding: 25px 20px;
            background-color: #34495e; 
            border-bottom: 3px solid var(--primary-light);
            text-align: left;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .sidebar-profile .user-icon {
            width: 40px;
            height: 40px;
            background-color: var(--success-color); 
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .sidebar-profile strong { display: block; font-size: 1rem; margin: 0; color: #ecf0f1; }
        .sidebar-profile small { font-size: 0.8rem; color: #bdc3c7; }
        
        .sidebar-menu { list-style: none; padding: 10px 0; margin: 0; }
        .sidebar-menu a {
            display: flex; align-items: center; color: #ecf0f1;
            padding: 12px 20px; text-decoration: none;
            transition: background-color 0.2s; font-size: 0.95rem; gap: 10px; font-weight: 600;
        }
        /* Style untuk menandai menu Akun di halaman ini */
        .sidebar-menu a[href*="user_akun.php"] { 
            background-color: var(--primary-color);
            color: #ffffff;
            border-left: 5px solid var(--success-color);
            padding-left: 15px; 
        }

        /* CONTENT AREA */
        .main-content {
            padding: 30px;
            background-color: #f4f4f4;
            overflow-x: hidden;
        }
        
        /* Form Card */
        .account-card {
            max-width: 650px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .account-card h3 {
            color: var(--primary-color);
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
        }
        
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- START: SIDEBAR -->
        <div class="sidebar">
            <div class="sidebar-profile">
                <div class="user-icon">👤</div>
                <div class="profile-details">
                    <strong><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></strong>
                    <small>PEMINJAM / USER</small>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard_user.php#katalog">📚 Katalog Aset</a></li>
                <li><a href="dashboard_user.php#status">📋 Status Pinjaman</a></li>
                <li><a href="user_akun.php">⚙️ Akun Saya</a></li>
                <!-- Tombol Logout di Sidebar -->
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff6b6b;">🚪 LOGOUT</a></li>
            </ul>
        </div>
        <!-- END: SIDEBAR -->

        <!-- START: MAIN CONTENT -->
        <div class="main-content">
            <h2 style="font-size: 1.8rem; color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;">Manajemen Akun Peminjam</h2>

            <div class="account-card">
                <h3>Perbarui Informasi Dasar</h3>
                
                <form action="user_akun.php" method="POST" enctype="multipart/form-data">
                    
                    <label for="username">Username (Tidak Bisa Diubah):</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled style="background-color: #eee; border-style: dashed;">
                    
                    <label for="nama_lengkap">Nama Lengkap:</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>

                    <label for="nomor_hp">Nomor HP:</label>
                    <input type="text" name="nomor_hp" id="nomor_hp" value="<?php echo htmlspecialchars($user_data['nomor_hp']); ?>" required>

                    <label for="nik">NIK:</label>
                    <input type="text" name="nik" id="nik" value="<?php echo htmlspecialchars($user_data['nik']); ?>" required>
                    
                    <h4 style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px; color: var(--primary-color);">Perbarui Password (Opsional)</h4>
                    <p style="font-size: 0.9rem; color: #f44336;">*Kosongkan kolom ini jika Anda tidak ingin mengganti password lama.</p>

                    <label for="password">Password Baru:</label>
                    <input type="password" name="password" id="password" placeholder="Masukkan password baru (min. 6 karakter)">

                    <button type="submit" name="update_akun" class="btn btn-primary" style="margin-top: 15px; width: 100%;">💾 Simpan Perubahan Akun</button>
                </form>
            </div>

        </div>
        <!-- END: MAIN CONTENT -->
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>