<?php 
// File manajemen_simas.php: Dashboard Admin Terpadu (CRUD Aset & Transaksi)
// Memastikan semua teori Modul 5-8 diterapkan (Session, CRUD, Error Handling)

session_start();
include 'config.php'; 

// Cek otentikasi dan role Admin (Modul 5)
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
$nama_admin = $_SESSION['nama_lengkap'];

// --- BLOK PERHITUNGAN METRICS (Modul 6: SINKRONISASI DATA) ---
$total_aset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM aset"))['count'];
$total_peminjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjam"))['count'];

// Transaksi berdasarkan status (Sinkronisasi dengan kondisi di tabel)
$transaksi_diajukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE status='Diajukan'"))['count'];
$transaksi_disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE status='Disetujui'"))['count'];
$transaksi_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE status='Selesai'"))['count'];
// --- END METRICS CALCULATION ---


// --- BLOK LOGIKA CRUD TRANSAKSI (Modul 8: Update/Delete) ---
if(isset($_GET['action']) && isset($_GET['id_pinjam'])){
    $id_pinjam = (int)$_GET['id_pinjam'];
    $action = $_GET['action'];
    
    $res = mysqli_query($conn, "SELECT id_aset, jumlah_pinjam, status FROM peminjaman WHERE id_peminjaman=$id_pinjam");
    
    if (!$res || mysqli_num_rows($res) == 0) {
        echo "<script>alert('Error: Transaksi tidak ditemukan atau database error.')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
        exit;
    }
    
    $data_transaksi = mysqli_fetch_assoc($res);
    $id_aset = $data_transaksi['id_aset'];
    $jumlah_pinjam = $data_transaksi['jumlah_pinjam'];

    $refresh = "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>"; 

    if ($action == 'approve') {
        // Aksi: Setujui
        $sql_update = "UPDATE peminjaman SET status='Disetujui' WHERE id_peminjaman=$id_pinjam";
        mysqli_query($conn, $sql_update);
        echo "<script>alert('Pengajuan pinjaman berhasil disetujui.')</script>";
        echo $refresh;
        exit;
        
    } elseif ($action == 'reject') {
        // Aksi: Tolak (Kembalikan stok jika statusnya masih Diajukan)
        if ($data_transaksi['status'] == 'Diajukan') { 
            $sql_revert_stok = "UPDATE aset SET stok_tersedia = stok_tersedia + $jumlah_pinjam WHERE id_aset = $id_aset";
            mysqli_query($conn, $sql_revert_stok);
        }

        $sql_update = "UPDATE peminjaman SET status='Ditolak' WHERE id_peminjaman=$id_pinjam";
        mysqli_query($conn, $sql_update);
        
        echo "<script>alert('Pengajuan pinjaman berhasil ditolak. Stok telah dikembalikan.')</script>";
        echo $refresh;
        exit;
        
    } elseif ($action == 'delete') {
        // Aksi: Hapus (Kembalikan stok jika statusnya masih aktif/diajukan)
        if ($data_transaksi['status'] === 'Diajukan' || $data_transaksi['status'] === 'Disetujui') {
             $sql_revert_stok = "UPDATE aset SET stok_tersedia = stok_tersedia + $jumlah_pinjam WHERE id_aset = $id_aset";
            mysqli_query($conn, $sql_revert_stok);
        }

        $sql_delete = "DELETE FROM peminjaman WHERE id_peminjaman=$id_pinjam";
        mysqli_query($conn, $sql_delete);

        echo "<script>alert('Transaksi berhasil dihapus.')</script>";
        echo $refresh;
        exit;
    }
}
// -----------------------------------------------------------------

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen SIMAS - ADMIN</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* CSS Tambahan untuk Layout Sidebar */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4; 
        }
        .header {
            background-color: #3f51b5;
            color: white;
            padding: 1rem 3rem; 
            display: flex;
            justify-content: space-between;
            align-items: center; 
            width: 100%;
            z-index: 100;
        }
        
        .dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr; 
            min-height: calc(100vh - 54px); 
            max-width: 100%;
            margin: 0;
        }

        /* SIDEBAR STYLES */
        .sidebar {
            background-color: #2c3e50; /* Warna gelap sidebar */
            color: white;
            padding: 0;
            overflow-y: auto;
            height: 100%;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3); /* Tambahkan shadow sidebar */
        }
        .sidebar-profile {
            padding: 25px 20px; /* Padding ditingkatkan */
            background-color: #34495e; 
            border-bottom: 3px solid var(--primary-light); /* Garis pemisah tebal */
            text-align: left; /* Rata kiri */
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .sidebar-profile .admin-icon {
            width: 40px;
            height: 40px;
            background-color: var(--warning-color);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .sidebar-profile .profile-details {
            text-align: left;
        }
        .sidebar-profile strong {
            display: block;
            font-size: 1rem;
            margin: 0;
            color: #ecf0f1;
        }
        .sidebar-profile small {
            font-size: 0.8rem;
            color: #bdc3c7;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 10px 0;
            margin: 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: #ecf0f1;
            padding: 12px 20px; /* Padding disesuaikan */
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s;
            font-size: 0.95rem;
            gap: 10px;
            font-weight: 600; /* Sidebar font lebih tebal */
        }
        /* PERBAIKAN HOVER SIDEBAR */
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary-color); /* Warna hover primary */
            color: #ffffff;
            border-left: 5px solid var(--warning-color);
            padding-left: 15px; 
        }
        
        /* CONTENT AREA */
        .main-content {
            padding: 30px;
            background-color: #f4f4f4;
            overflow-x: hidden;
        }

        /* METRIC CARD STYLING */
        .metric-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            color: white; /* Default text color for metrics */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
            /* Default background untuk card jika tidak ada warna spesifik */
            background-color: #ccc; 
        }
        .metric-card .label {
            font-size: 1rem;
            font-weight: 500;
        }
        .metric-card h3 {
            font-size: 2.5rem; 
            margin: 0;
            font-weight: 900;
        }

        /* Specific Metric Card Colors */
        .metric-card.green { background-color: #4CAF50; } /* Success Green */
        .metric-card.blue { background-color: #2196F3; } /* Info Blue */
        .metric-card.orange { background-color: #FF9800; } /* Warning Orange */
        .metric-card.red { background-color: #F44336; } /* Danger Red */
        
        /* --- PERBAIKAN FINAL TAMPILAN CARD KONTEN --- */
        .card-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 1rem;
        }

        .card-item {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px; 
            padding: 0; 
            display: flex;
            justify-content: space-between;
            align-items: stretch;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); 
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        .card-item:hover {
            transform: translateY(-5px); 
            box-shadow: 0 8px 20px rgba(63, 81, 181, 0.2); 
        }
        
        .card-content {
            padding: 20px; 
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            text-align: left;
            border-left: 6px solid var(--primary-color); 
        }
        .card-item.transaksi .card-content {
            border-left-color: var(--success-color); /* Hijau untuk Transaksi */
        }

        .card-content-main {
             display: flex;
             justify-content: space-between;
             align-items: flex-start;
             margin-bottom: 10px;
             padding-bottom: 10px;
             border-bottom: 1px solid #cfd8dc; 
        }
        
        .card-title {
            font-weight: 900; 
            color: var(--primary-color);
            font-size: 1.3rem; 
            margin: 0;
            text-align: left; 
            flex-basis: 70%;
        }
        .card-item.transaksi .card-title {
            color: var(--text-color); 
            font-size: 1.2rem;
            font-weight: 800; 
        }

        /* PERBAIKAN: Menggunakan Grid 3-kolom untuk data detail */
        .asset-meta-grid {
             display: grid;
             grid-template-columns: repeat(3, 1fr); /* 3 kolom ringkas */
             gap: 15px 10px; 
             margin-top: 10px;
        }
        
        .transaction-meta-grid {
             display: grid;
             grid-template-columns: repeat(4, 1fr); /* 4 kolom ringkas untuk transaksi */
             gap: 15px 10px; 
             margin-top: 10px;
        }
        /* END PERBAIKAN */


        .meta-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-light); 
            text-transform: uppercase;
            margin-bottom: 3px;
            display: block;
            text-align: left; /* Rata kiri label */
        }
        .meta-value {
            font-size: 1rem;
            font-weight: 800; 
            color: var(--primary-color);
            display: block;
            text-align: left; /* Rata kiri nilai */
        }
        .asset-meta-grid .meta-value {
            color: var(--text-color); 
        }

        .card-actions {
            background-color: #f7f7f7; 
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border-left: 1px solid #e0e0e0;
            min-width: 120px; 
        }
        .card-actions a.btn {
            width: 100%;
            display: block;
            text-align: center;
        }
        
        /* MOBILE RESPONSIVE FIXES */
        @media (max-width: 768px) {
            .header {
                padding: 1rem 1.5rem;
                justify-content: center;
            }
            .dashboard-layout {
                grid-template-columns: 1fr;
            }
            .sidebar {
                width: 100%;
                height: auto;
            }
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px; 
            }
            .sidebar-menu a {
                border-right: 1px solid #444;
                border-bottom: none;
                flex-shrink: 0;
            }
            .sidebar-profile {
                display: flex; /* Biarkan profil tetap ada di mobile */
            }
            .main-content {
                padding: 15px;
            }
            /* Menyesuaikan Grid Meta di Mobile */
            .asset-meta-grid, .transaction-meta-grid {
                 grid-template-columns: 1fr; /* Stack Vertikal di Mobile */
            }
        }

        /* --- PERBAIKAN JUDUL SECTION --- */
        .section-heading {
            font-size: 1.8rem;
            font-weight: 900;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .section-description {
            color: var(--text-light);
            margin-top: 0;
            margin-bottom: 1.5rem; /* Memberi ruang di bawah deskripsi */
        }
        
        /* Tombol Tambah Aset Baru - Diberi Shadow Menonjol */
        .btn-tambah-aset {
            background-color: var(--primary-color);
            color: white;
            font-weight: 700;
            padding: 12px 25px;
            border: none; /* Hilangkan border */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(63, 81, 181, 0.4);
            transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
            text-decoration: none; /* Hilangkan underline */
            display: inline-flex; /* Agar ikon dan teks sejajar */
            align-items: center;
            gap: 8px; /* Jarak antara ikon dan teks */
            font-size: 1rem; /* Ukuran font yang pas */
        }
        .btn-tambah-aset:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(63, 81, 181, 0.6);
        }
        
        /* --- PERBAIKAN TOMBOL AKSI TRANSAKSI (Flexbox untuk Mobile/Desktop) --- */
        .transaction-actions-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
        }
        .transaction-actions-group .btn {
            padding: 8px 10px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        /* --- STYLING TABEL MANAJEMEN PENGGUNA --- */
        .user-table-wrapper {
            margin-bottom: 30px;
            overflow-x: auto;
        }
        .user-table-wrapper table {
            width: 100%;
            min-width: 700px; /* Lebar minimum untuk tabel agar tidak terlalu sempit di desktop */
            border-collapse: collapse;
            border-spacing: 0;
            border-radius: 8px; /* Tambahkan border radius */
            overflow: hidden; /* Penting untuk border-radius */
        }
        /* Gaya Header Tabel UNGU/PRIMARY */
        .user-table-wrapper th {
            background-color: var(--primary-color);
            color: white;
            text-transform: uppercase;
            font-weight: 600;
            padding: 12px 15px;
            border: none; /* Hilangkan border individual */
        }
        .user-table-wrapper td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .user-table-wrapper tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        /* Gaya Hover Tabel */
        .user-table-wrapper tr:hover {
             background-color: var(--secondary-color); /* Warna hover: Biru muda/ungu muda */
        }
        /* Perbaikan: Membuat garis border bawah tabel terakhir lebih jelas */
        .user-table-wrapper table tbody tr:last-child td {
            border-bottom: 2px solid #ddd; 
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>SIMAS - Panel Kontrol Admin</h1>
        <!-- TOMBOL LOGOUT HILANG DARI HEADER, ADA DI SIDEBAR -->
    </div>
    
    <div class="dashboard-layout">
        <!-- START: SIDEBAR NAVIGATION -->
        <div class="sidebar">
            <div class="sidebar-profile">
                <div class="admin-icon">👤</div>
                <div class="profile-details">
                    <strong><?php echo htmlspecialchars($nama_admin); ?></strong>
                    <small>ADMINISTRATOR</small>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active">🏠 Dashboard Utama</a></li>
                <li><a href="#aset">📦 Manajemen Aset</a></li>
                <li><a href="#transaksi">🧾 Kelola Transaksi</a></li>
                <!-- NEW ITEM: MANAJEMEN PENGGUNA -->
                <li><a href="#pengguna">👥 Manajemen Pengguna</a></li>
                <!-- TOMBOL LOGOUT DIPERTAHINKAN DI SIDEBAR -->
                <li style="margin-top: 20px;"><a href="logout.php" style="color: var(--danger-color);">🚪 LOGOUT</a></li>
            </ul>
        </div>
        <!-- END: SIDEBAR NAVIGATION -->

        <!-- START: MAIN CONTENT AREA -->
        <div class="main-content">

            <h2 style="font-size: 1.8rem; color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;" id="dashboard">Dashboard Overview</h2>
            
            <!-- METRICS SUMMARY -->
            <div class="metric-card-grid">
                <!-- Card 1: Total Jenis Aset (Hijau) -->
                <div class="metric-card green">
                    <span class="label">Total Jenis Aset</span>
                    <h3><?php echo $total_aset; ?></h3>
                </div>
                <!-- Card 2: Total Pengguna (Biru) -->
                <div class="metric-card blue">
                    <span class="label">Total Pengguna</span>
                    <h3><?php echo $total_peminjam; ?></h3>
                </div>
                <!-- Card 3: Pengajuan Baru (Pending) (Oranye) -->
                <div class="metric-card orange">
                    <span class="label">Pengajuan Baru (Pending)</span>
                    <h3><?php echo $transaksi_diajukan; ?></h3>
                </div>
                <!-- Card 4: Pinjaman Aktif (Merah) -->
                <div class="metric-card red">
                    <span class="label">Pinjaman Aktif</span>
                    <h3><?php echo $transaksi_disetujui; ?></h3>
                </div>
            </div>
            <!-- END: METRICS SUMMARY -->

            <!-- ####################################################### -->
            <!-- BAGIAN 1: MANAJEMEN ASET (CRUD ASET) - CARD LAYOUT -->
            <!-- ####################################################### -->
            <div class="card aset" id="aset">
                <h2 class="section-heading">Manajemen Aset (CRUD)</h2>
                <p class="section-description">Kelola daftar inventaris aset. Data ini yang dilihat dan dipinjam oleh user.</p>
                
                <a href="aset_tambah.php" class="btn-tambah-aset">
                    <span style="font-size: 1.2em;">➕</span> Tambah Aset Baru
                </a>

                <div class="card-list">
                    <?php
                    $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY id_aset DESC");
                    
                    if (mysqli_num_rows($query_aset) == 0) {
                         echo "<p class='text-center' style='color: var(--text-light);'>Tidak ada data aset yang terdaftar.</p>";
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
                                <small style="color: var(--text-light); margin-bottom: 15px; display: block;"><?php echo htmlspecialchars($data_aset['deskripsi']); ?></small>

                                <div class="asset-meta-grid">
                                    <div class="meta-item"><span class="meta-label">Total Stok</span><strong class="meta-value"><?php echo $data_aset['stok']; ?></strong></div>
                                    <div class="meta-item"><span class="meta-label">Stok Tersedia</span><strong class="meta-value"><?php echo $stok_tersedia; ?></strong></div>
                                    <div class="meta-item"><span class="meta-label">ID Aset</span><strong class="meta-value"><?php echo $data_aset['id_aset']; ?></strong></div>
                                </div>
                            </div>
                            <div class="card-actions">
                                <a href="aset_edit.php?id=<?php echo $data_aset['id_aset']; ?>" class="btn btn-secondary" style="font-size: 0.8rem;">✏️ Edit</a>
                                <a href="aset_hapus.php?id=<?php echo $data_aset['id_aset']; ?>" onclick="return confirm('Yakin menghapus aset ini?')" class="btn btn-danger" style="font-size: 0.8rem;">🗑️ Hapus</a>
                            </div>
                        </div>
                    <?php 
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- ####################################################### -->
            <!-- BAGIAN 2: MANAJEMEN TRANSAKSI (CRUD TRANSAKSI) - CARD LAYOUT -->
            <!-- ####################################################### -->
            <div class="card transaksi" id="transaksi">
                <h2 class="section-heading">Kelola Transaksi Peminjaman</h2>
                <p class="section-description">Lakukan persetujuan, penolakan, atau konfirmasi pengembalian aset.</p>

                <?php 
                $query_pinjam = mysqli_query($conn, 
                    "SELECT p.*, a.nama_aset, pm.nama_lengkap AS peminjam_nama 
                     FROM peminjaman p
                     JOIN aset a ON p.id_aset = a.id_aset
                     JOIN peminjam pm ON p.nama_peminjam = pm.nama_lengkap 
                     ORDER BY p.tanggal_pinjam DESC");
                ?>
                
                <div class="card-list">
                    <?php if (mysqli_num_rows($query_pinjam) == 0): ?>
                        <p class='text-center' style='color: var(--text-light);'>Tidak ada transaksi peminjaman yang tercatat.</p>
                    <?php else: ?>
                        <?php while($data_pinjam = mysqli_fetch_array($query_pinjam)): 
                            $status = $data_pinjam['status'];
                            $status_class = '';
                            $ikon_pinjam = '';
                            
                            if ($status == 'Diajukan') { $status_class = 'status-red'; $ikon_pinjam = '⏳'; }
                            if ($status == 'Disetujui') { $status_class = 'status-green'; $ikon_pinjam = '✅'; } 
                            if ($status == 'Ditolak') { $status_class = 'status-secondary'; $ikon_pinjam = '❌'; }
                            if ($status == 'Selesai') { $status_class = 'status-secondary'; $ikon_pinjam = '📦'; }
                            
                            $tgl_kembali_tampil = $data_pinjam['tanggal_kembali'] ?: 'Menunggu';
                        ?>
                            <div class="card-item transaksi">
                                <div class="card-content">
                                    <div class="card-content-main">
                                        <!-- Judul Aset dengan unit --><div class="card-title" style="color: var(--success-color);">
                                            <?php echo htmlspecialchars($data_pinjam['nama_aset']); ?> 
                                            (<?php echo $data_pinjam['jumlah_pinjam']; ?> unit)
                                        </div>
                                        <div class="status-info">
                                            Status: <?php echo "<span class='status-badge {$status_class}'>{$ikon_pinjam} {$status}</span>"; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="transaction-meta-grid">
                                        <div class="meta-group">
                                            <div class="meta-item"><span class="meta-label">PEMINJAM</span><strong class="meta-value"><?php echo htmlspecialchars($data_pinjam['peminjam_nama']); ?></strong></div>
                                        </div>
                                        <div class="meta-group">
                                            <div class="meta-item"><span class="meta-label">TGL PINJAM</span><strong class="meta-value"><?php echo $data_pinjam['tanggal_pinjam']; ?></strong></div>
                                        </div>
                                        <div class="meta-group">
                                            <div class="meta-item"><span class="meta-label">RENCANA KEMBALI</span><strong class="meta-value"><?php echo $tgl_kembali_tampil; ?></strong></div>
                                        </div>
                                        <div class="meta-group">
                                             <div class="meta-item"><span class="meta-label">ID TRANSAKSI</span><strong class="meta-value"><?php echo $data_pinjam['id_peminjaman']; ?></strong></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <!-- Aksi Tindak Lanjut -->
                                    <div class="transaction-actions-group">
                                        <?php if ($status == 'Diajukan'): ?>
                                            <a href="?action=approve&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" class="btn btn-success">👍 Setujui</a>
                                            <a href="?action=reject&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Tolak pinjaman ini?')" class="btn btn-danger">👎 Tolak</a>
                                        <?php elseif ($status == 'Disetujui'): ?>
                                            <a href="?action=return&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Konfirmasi pengembalian aset ini?')" class="btn btn-primary">↩️ Kembalikan</a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Tombol Hapus (untuk semua status non-aktif) -->
                                    <?php if ($status == 'Ditolak' || $status == 'Selesai' || $status == 'Diajukan'): ?>
                                        <a href="?action=delete&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('PERINGATAN: Yakin hapus transaksi ini?')" class="btn btn-danger">🗑️ Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ####################################################### -->
            <!-- BAGIAN 3: MANAJEMEN PENGGUNA -->
            <!-- ####################################################### -->
            <div class="card pengguna" id="pengguna">
                <h2 class="section-heading">Manajemen Pengguna</h2>
                <p class="section-description">Kelola data pengguna/peminjam yang terdaftar.</p>
                
                <?php
                // Query untuk mengambil semua data peminjam (USER)
                $query_peminjam = mysqli_query($conn, "SELECT * FROM peminjam ORDER BY id_peminjam DESC");
                
                // Query untuk mengambil semua data admin (ADMINISTRATOR)
                $query_admin = mysqli_query($conn, "SELECT * FROM admin ORDER BY id_admin ASC");
                
                // Cek jika ada aksi penghapusan pengguna
                if (isset($_GET['delete_user_id'])) {
                    $delete_id = (int)$_GET['delete_user_id'];
                    // Logic untuk memastikan user tidak memiliki pinjaman aktif sebelum dihapus (Error Handling)
                    $cek_active_loans = mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE nama_peminjam=(SELECT nama_lengkap FROM peminjam WHERE id_peminjam=$delete_id) AND status IN ('Diajukan', 'Disetujui')");
                    $active_loans_count = mysqli_fetch_assoc($cek_active_loans)['count'];
                    
                    if ($active_loans_count > 0) {
                        echo "<script>alert('Gagal menghapus pengguna! Pengguna ini masih memiliki $active_loans_count pinjaman aktif/diajukan.')</script>";
                    } else {
                        // Hapus pengguna
                        $sql_delete_user = "DELETE FROM peminjam WHERE id_peminjam=$delete_id";
                        if (mysqli_query($conn, $sql_delete_user)) {
                            echo "<script>alert('Pengguna berhasil dihapus.')</script>";
                        } else {
                            echo "<script>alert('Gagal menghapus pengguna.')</script>";
                        }
                    }
                    echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php#pengguna'>";
                    exit;
                }
                ?>

                <!-- Tabel Data Peminjam (USER) -->
                <h3 style="color: var(--primary-color); margin-top: 0; margin-bottom: 10px;">Daftar Peminjam (User)</h3>
                <div class="user-table-wrapper">
                    <table class="user-data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>No HP</th>
                                <th>NIK</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_peminjam) == 0): ?>
                                <tr><td colspan="6" class="text-center">Tidak ada data peminjam terdaftar.</td></tr>
                            <?php else: ?>
                                <?php while($data_peminjam = mysqli_fetch_array($query_peminjam)): ?>
                                <tr>
                                    <td><?php echo $data_peminjam['id_peminjam']; ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjam['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjam['username']); ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjam['nomor_hp']); ?></td>
                                    <td><?php echo htmlspecialchars($data_peminjam['nik']); ?></td>
                                    <td>
                                        <a href="?delete_user_id=<?php echo $data_peminjam['id_peminjam']; ?>#pengguna" 
                                           onclick="return confirm('Yakin hapus pengguna ini?')" 
                                           class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">🗑️ Hapus</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tabel Data Administrator -->
                <h3 style="color: var(--primary-color); margin-top: 25px; margin-bottom: 10px;">Daftar Administrator</h3>
                 <div class="user-table-wrapper">
                    <table class="user-data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($data_admin = mysqli_fetch_array($query_admin)): ?>
                            <tr>
                                <td><?php echo $data_admin['id_admin']; ?></td>
                                <td><?php echo htmlspecialchars($data_admin['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data_admin['username']); ?></td>
                                <td><span class="status-badge status-green">Super Admin</span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <!-- END: MAIN CONTENT AREA --></div>
</body>
</html>

<?php mysqli_close($conn); ?>