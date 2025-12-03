<?php 
// File home.php: Dashboard Utama / Landing Page (Publik)
session_start();
include 'config.php'; 

// Cek apakah user sudah login, jika ya, arahkan ke dashboard yang sesuai
if(isset($_SESSION['login']) && $_SESSION['login'] === 'aktif') {
    if ($_SESSION['role'] === 'admin') {
        header("location: manajemen_simas.php"); 
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header("location: dashboard_user.php");
        exit;
    }
}
// Jika belum login, tampilkan katalog publik
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAS - Dashboard Utama</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Mengubah background body dengan GAMBAR dan efek blur */
        body {
            /* MENGGANTI PLACEHOLDER GAMBAR DENGAN URL YANG LEBIH BAIK */
            background-image: url('https://images.unsplash.com/photo-1510511459019-5beef1451c22?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D'); 
            background-size: cover;
            background-attachment: fixed;
            background-position: center;
        }
        
        /* Memberi lapisan transparan gelap di atas gambar agar tulisan terlihat */
        .page-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.55); /* Overlay lebih gelap */
            backdrop-filter: blur(8px); /* Efek blur lebih kuat */
            z-index: -1;
        }
        
        /* Menyesuaikan container utama agar menonjol */
        .container {
            background-color: rgba(255, 255, 255, 0.98); /* Lebih solid putih */
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 30px;
            margin-top: 50px;
            margin-bottom: 50px;
            max-width: 1100px; /* Dibuat lebih lebar untuk grid card */
        }
        
        /* Katalog Header */
        .section-header {
            background-color: var(--primary-light);
            color: white;
            padding: 15px 25px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 1.5rem -30px; 
            font-size: 1.3rem;
            font-weight: bold;
        }

        /* --- CARD BARU: Model Persegi Panjang Vertikal (Full-Width) --- */
        .card-list {
            display: grid;
            gap: 20px;
            /* Layout Grid: 1 kolom di mobile, 2 kolom di tablet, 3 kolom di desktop besar */
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            margin-top: 1rem;
        }
        
        .card-item {
            background-color: #ffffff;
            border: 1px solid #cfd8dc;
            border-left: 6px solid var(--primary-color); 
            border-radius: 8px;
            padding: 20px; 
            display: flex;
            flex-direction: column; 
            justify-content: space-between;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); /* Shadow lebih halus */
            transition: transform 0.3s, box-shadow 0.3s; 
        }
        .card-item:hover {
            transform: translateY(-8px); /* Efek angkat yang dramatis */
            box-shadow: 0 15px 30px rgba(63, 81, 181, 0.3); /* Shadow hover yang menonjol */
        }
        
        .card-content-main {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             margin-bottom: 10px;
             padding-bottom: 10px;
             border-bottom: 2px solid var(--secondary-color); 
        }
        .card-title {
            font-weight: 900; 
            color: var(--primary-color);
            font-size: 1.4rem; 
            margin: 0;
            text-align: left; 
            flex-basis: 70%;
        }
        
        .card-details {
            display: flex;
            flex-wrap: wrap; 
            margin-top: 15px;
            gap: 10px 30px; 
        }
        .detail-item {
            font-weight: 500;
        }
        .card-actions {
            margin-top: 20px; 
            align-self: flex-end; 
            width: 100%;
        }
        
        .status-info {
            text-align: right;
        }

        /* Gaya Khusus untuk Bagian Sambutan */
        .welcome-box {
            position: relative;
            z-index: 10;
            background-color: var(--primary-color);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .welcome-box h2 {
            font-size: 2.2rem;
            font-weight: 900;
            color: white;
            border-bottom: none;
            margin-bottom: 10px;
        }
        .welcome-box p {
            font-size: 1.15rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .card-list {
                 grid-template-columns: 1fr; /* 1 kolom di mobile */
            }
        }
    </style>
</head>
<body>
    <div class="page-overlay"></div> <!-- Lapisan Overlay -->
    
    <div class="header">
        <h1>SIMAS - Dashboard Utama</h1>
        <div>
            <!-- Tombol Login dan Daftar untuk akses penuh -->
            <a href="login.php" class="btn btn-primary">👤 Login</a> 
            <a href="register.php" class="btn btn-success">📝 Daftar Akun</a> 
        </div>
    </div>

    <div class="container">
        <!-- Pengenalan Singkat (Diubah menjadi welcome-box) -->
        <div class="welcome-box">
            <h2>Selamat Datang di SIMAS</h2>
            <p>Lihat katalog aset yang tersedia di bawah. Silahkan Login atau Daftar untuk mengajukan pinjaman.</p>
        </div>
        
        <!-- Katalog Aset Publik -->
        <div class="katalog" id="katalog">
            <div class="section-header">Katalog Aset Publik</div>
            
            <div class="card-list">
                <?php
                $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY nama_aset ASC");
                
                if (mysqli_num_rows($query_aset) == 0) {
                     echo "<p class='text-center' style='color: var(--text-light);'>Tidak ada aset yang terdaftar.</p>";
                } else {
                    while($data_aset = mysqli_fetch_array($query_aset)){
                        $stok_tersedia = $data_aset['stok_tersedia'];
                        
                        $status_stok = ($stok_tersedia > 0) ? 
                            "<span class='status-badge status-green'>TERSEDIA</span>" : 
                            "<span class='status-badge status-red'>KOSONG</span>";
                        
                        $ikon_status = ($stok_tersedia > 0) ? '✅' : '❌';
                ?>
                    <div class="card-item">
                        <div class="card-content">
                            <div class="card-content-main">
                                <div class="card-title"><?php echo $ikon_status; ?> <?php echo htmlspecialchars($data_aset['nama_aset']); ?></div>
                                <div class="status-info">
                                    Status Aset: <?php echo $status_stok; ?>
                                </div>
                            </div>
                            <!-- Teks Deskripsi (Warna lebih lembut untuk estetika) -->
                            <small style="color: var(--text-light); margin-bottom: 15px; display: block;"><?php echo htmlspecialchars($data_aset['deskripsi']); ?></small>
                            
                            <div class="card-details">
                                <div class="detail-item"><span class="detail-label">Stok Total</span><strong><?php echo $data_aset['stok']; ?></strong></div>
                                <div class="detail-item"><span class="detail-label">Stok Tersedia</span><strong><?php echo $stok_tersedia; ?></strong></div>
                                <div class="detail-item"><span class="detail-label">ID Aset</span><strong><?php echo $data_aset['id_aset']; ?></strong></div>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <a href="login.php" class="btn btn-primary" style="font-size: 0.9rem; width: 100%;">Ajukan Pinjaman (Login)</a>
                        </div>
                    </div>
                <?php 
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>