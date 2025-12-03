<?php 
// File dashboard_user.php: Dashboard User (Katalog & Pengajuan)
// Layout: Sidebar Modern (Sama seperti Admin)

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
        }
        echo "<script>alert('Informasi akun berhasil diperbarui!')</script>";
    } else {
        echo "<script>alert('Gagal memperbarui akun. NIK atau Username mungkin sudah terdaftar.')</script>";
    }
    echo "<meta http-equiv='refresh' content='0; url=dashboard_user.php#akun'>";
    exit;
}

// Ambil data user terbaru untuk form (Modul 7: Read)
$q_user_data = mysqli_query($conn, "SELECT * FROM peminjam WHERE id_peminjam='$id_peminjam'");
$user_data = mysqli_fetch_assoc($q_user_data);

// --- METRIK RINGKASAN USER ---
// Hitung pinjaman aktif (Diajukan/Disetujui)
$q_aktif = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE nama_peminjam='$nama_user' AND status IN ('Diajukan', 'Disetujui')");
$count_aktif = mysqli_fetch_assoc($q_aktif)['c'];

// Hitung riwayat selesai (Selesai/Ditolak)
$q_selesai = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE nama_peminjam='$nama_user' AND status IN ('Selesai', 'Ditolak')");
$count_selesai = mysqli_fetch_assoc($q_selesai)['c'];


// --- PROSES PENGAJUAN PINJAMAN ---
if(isset($_POST['ajukan_pinjam'])){
    $id_aset = (int)$_POST['id_aset'];
    $jumlah_pinjam = (int)$_POST['jumlah_pinjam'];
    $tgl_pinjam = mysqli_real_escape_string($conn, $_POST['tanggal_pinjam']);
    $tgl_kembali = mysqli_real_escape_string($conn, $_POST['tanggal_kembali']);
    
    $res_stok = mysqli_query($conn, "SELECT stok_tersedia FROM aset WHERE id_aset='$id_aset'");
    $data_stok = mysqli_fetch_assoc($res_stok);
    $stok_tersedia = $data_stok['stok_tersedia'];

    if($jumlah_pinjam > $stok_tersedia || $jumlah_pinjam <= 0){
        echo "<script>alert('Gagal mengajukan pinjaman! Jumlah pinjam melebihi stok tersedia atau 0.')</script>";
    } else {
        // Status awal: 'Diajukan'
        $sql_pinjam = "INSERT INTO peminjaman (id_aset, nama_peminjam, jumlah_pinjam, tanggal_pinjam, tanggal_kembali, status) 
                       VALUES ('$id_aset', '$nama_user', '$jumlah_pinjam', '$tgl_pinjam', '$tgl_kembali', 'Diajukan')";
        
        // Query UPDATE stok (Mengurangi stok yang tersedia)
        $sql_update_stok = "UPDATE aset SET stok_tersedia = stok_tersedia - $jumlah_pinjam WHERE id_aset = $id_aset";

        if(mysqli_query($conn, $sql_pinjam) && mysqli_query($conn, $sql_update_stok)) {
            echo "<script>alert('Pengajuan pinjaman berhasil! Menunggu persetujuan admin.')</script>";
        } else {
            echo "<script>alert('Pengajuan pinjaman gagal. Silahkan coba lagi.')</script>";
            echo mysqli_error($conn); 
        }
    }
    echo "<meta http-equiv='refresh' content='0; url=dashboard_user.php'>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function openPinjamForm(id, nama, stok) {
            document.getElementById('pinjam_id_aset').value = id;
            document.getElementById('pinjam_nama_aset').innerText = nama;
            document.getElementById('pinjam_stok_tersedia').innerText = stok;
            document.getElementById('modal-pinjam').style.display = 'flex';
            document.getElementById('jumlah_pinjam').setAttribute('max', stok); 
        }
        function closePinjamForm() {
            document.getElementById('modal-pinjam').style.display = 'none';
        }
    </script>
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

        /* SIDEBAR STYLES */
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
            background-color: var(--success-color); /* Hijau untuk User */
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
        .sidebar-menu a:hover, .sidebar-menu a.active {
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

        /* CARD STYLES */
        .card-list { display: flex; flex-direction: column; gap: 15px; margin-top: 1rem; }
        .card-item {
            background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; 
            padding: 0; display: flex; justify-content: space-between; align-items: stretch;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); transition: transform 0.3s;
            overflow: hidden;
        }
        .card-item:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        
        .card-content {
            padding: 20px; flex-grow: 1; display: flex; flex-direction: column; text-align: left;
            border-left: 6px solid var(--primary-color); 
        }
        .card-item.pinjaman .card-content { border-left-color: var(--success-color); }

        .card-content-main {
             display: flex; justify-content: space-between; align-items: flex-start;
             margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #cfd8dc; 
        }
        /* PERBAIKAN: Title lebih tebal */
        .card-title { font-weight: 900; color: var(--primary-color); font-size: 1.3rem; margin: 0; }
        
        /* GRID DETAIL */
        /* Menggunakan Grid 3-kolom untuk katalog */
        .detail-grid {
             display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 10px;
        }
        /* PERBAIKAN: Style Label dan Value */
        .meta-label { font-size: 0.75rem; font-weight: 600; color: var(--text-light); text-transform: uppercase; display: block; text-align: left; }
        .meta-value { font-size: 1rem; font-weight: 800; color: var(--text-color); display: block; text-align: left; }
        
        .card-actions {
            background-color: #f7f7f7; padding: 15px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; border-left: 1px solid #e0e0e0; min-width: 120px; 
        }

        /* METRIC CARDS FOR USER */
        .user-metrics {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;
        }
        .metric-card {
            padding: 20px; border-radius: 8px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .metric-card h3 { font-size: 2rem; margin: 0; font-weight: 900; }
        .metric-card .label { font-size: 0.9rem; font-weight: 500; }
        .metric-blue { background-color: #2196F3; }
        .metric-green { background-color: #4CAF50; }

        /* MODAL STYLES */
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); 
            justify-content: center; align-items: center;
        }
        .modal-content {
            background-color: #fff; padding: 25px; border-radius: 12px; width: 90%; max-width: 500px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); animation: slideDown 0.3s ease;
        }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-btn { float: right; font-size: 24px; font-weight: bold; cursor: pointer; color: #aaa; }
        .close-btn:hover { color: #333; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .sidebar { height: auto; position: relative; }
            .sidebar-menu { display: flex; overflow-x: auto; }
            .sidebar-menu a { border-right: 1px solid #444; border-bottom: none; flex-shrink: 0; }
            /* Perbaikan: Membuat detail card stack di mobile */
            .detail-grid { grid-template-columns: 1fr; }
            .card-item { flex-direction: column; }
            .card-actions { border-left: none; border-top: 1px solid #e0e0e0; flex-direction: row; justify-content: space-around; width: 100%; }
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
                <li><a href="#katalog" class="active">📚 Katalog Aset</a></li>
                <li><a href="#status">📋 Status Pinjaman</a></li>
                <li><a href="#akun">⚙️ Akun Saya</a></li>
                <!-- Tombol Logout di Sidebar -->
                <li style="margin-top: 20px;"><a href="logout.php" style="color: #ff6b6b;">🚪 LOGOUT</a></li>
            </ul>
        </div>
        <!-- END: SIDEBAR -->

        <!-- START: MAIN CONTENT -->
        <div class="main-content">
            <h2 style="font-size: 1.8rem; color: #333; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 0;">Dashboard Peminjam</h2>

            <!-- USER METRICS -->
            <div class="user-metrics">
                <div class="metric-card metric-blue">
                    <span class="label">Pinjaman Aktif (Diajukan/Disetujui)</span>
                    <h3><?php echo $count_aktif; ?></h3>
                </div>
                <div class="metric-card metric-green">
                    <span class="label">Riwayat Selesai/Ditolak</span>
                    <h3><?php echo $count_selesai; ?></h3>
                </div>
            </div>

            <!-- ####################################################### -->
            <!-- BAGIAN 1: KATALOG ASET (READ) -->
            <!-- ####################################################### -->
            <div id="katalog" style="margin-bottom: 40px;">
                <h3 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; display: inline-block;">📚 Katalog Aset Tersedia</h3>
                
                <div class="card-list">
                    <?php
                    $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY nama_aset ASC");
                    
                    if (mysqli_num_rows($query_aset) == 0) {
                         echo "<p style='color: #888;'>Tidak ada aset yang terdaftar.</p>";
                    } else {
                        while($data_aset = mysqli_fetch_array($query_aset)){
                            $stok_tersedia = $data_aset['stok_tersedia'];
                            
                            $status_stok = ($stok_tersedia > 0) ? 
                                "<span class='status-badge status-green'>TERSEDIA</span>" : 
                                "<span class='status-badge status-red'>KOSONG</span>";
                            
                            $btn_disabled = ($stok_tersedia == 0) ? 'disabled' : '';
                            $btn_class = ($stok_tersedia == 0) ? 'btn-secondary' : 'btn-success';
                            $ikon_status = ($stok_tersedia > 0) ? '✅' : '❌';
                    ?>
                        <div class="card-item">
                            <div class="card-content">
                                <div class="card-content-main">
                                    <div class="card-title"><?php echo $ikon_status; ?> <?php echo htmlspecialchars($data_aset['nama_aset']); ?></div>
                                    <div class="status-info">Status: <?php echo $status_stok; ?></div>
                                </div>
                                <small style="color: #666; margin-bottom: 15px; display: block;"><?php echo htmlspecialchars($data_aset['deskripsi']); ?></small>
                                <div class="detail-grid">
                                    <div><span class="meta-label">Total Stok</span><strong class="meta-value"><?php echo $data_aset['stok']; ?></strong></div>
                                    <div><span class="meta-label">Tersedia</span><strong class="meta-value"><?php echo $stok_tersedia; ?></strong></div>
                                    <div><span class="meta-label">Kode Aset</span><strong class="meta-value">#<?php echo $data_aset['id_aset']; ?></strong></div>
                                </div>
                            </div>
                            <div class="card-actions">
                                <button <?php echo $btn_disabled; ?> 
                                    onclick="openPinjamForm(<?php echo $data_aset['id_aset']; ?>, '<?php echo htmlspecialchars($data_aset['nama_aset']); ?>', <?php echo $stok_tersedia; ?>)" 
                                    class="btn <?php echo $btn_class; ?>" style="width: 100%;">Ajukan Pinjaman</button>
                            </div>
                        </div>
                    <?php 
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- ####################################################### -->
            <!-- BAGIAN 2: STATUS PEMINJAMAN (READ TRANSAKSI) -->
            <!-- ####################################################### -->
            <div id="status" style="margin-bottom: 40px;">
                <h3 style="color: var(--success-color); border-bottom: 2px solid var(--success-color); padding-bottom: 5px; display: inline-block;">📋 Status Pinjaman Anda</h3>
                <?php 
                $query_pinjam = mysqli_query($conn, 
                    "SELECT p.*, a.nama_aset 
                     FROM peminjaman p
                     JOIN aset a ON p.id_aset = a.id_aset
                     WHERE p.nama_peminjam = '$nama_user'
                     ORDER BY p.tanggal_pinjam DESC");
                ?>
                
                <div class="card-list">
                    <?php if (mysqli_num_rows($query_pinjam) == 0): ?>
                        <p style='color: #888;'>Anda belum pernah mengajukan pinjaman.</p>
                    <?php else: ?>
                        <?php while($data_pinjam = mysqli_fetch_array($query_pinjam)): 
                            $status = $data_pinjam['status'];
                            $status_class = '';
                            $ikon_pinjam = '';

                            if ($status == 'Diajukan') { $status_class = 'status-red'; $ikon_pinjam = '⏳'; }
                            if ($status == 'Disetujui') { $status_class = 'status-green'; $ikon_pinjam = '✅'; } 
                            if ($status == 'Ditolak') { $status_class = 'status-secondary'; $ikon_pinjam = '❌'; }
                            if ($status == 'Selesai') { $status_class = 'status-secondary'; $ikon_pinjam = '📦'; }
                            
                            $tgl_kembali_tampil = $data_pinjam['tanggal_kembali'] ?: '-';
                        ?>
                            <div class="card-item pinjaman">
                                <div class="card-content">
                                    <div class="card-content-main">
                                        <div class="card-title">
                                            <?php echo htmlspecialchars($data_pinjam['nama_aset']); ?> 
                                            (<?php echo $data_pinjam['jumlah_pinjam']; ?> unit)
                                        </div>
                                        <div class="status-info">
                                            Status: <?php echo "<span class='status-badge {$status_class}'>{$ikon_pinjam} {$status}</span>"; ?>
                                        </div>
                                    </div>
                                    <div class="detail-grid">
                                        <div><span class="meta-label">Tgl Pinjam</span><strong class="meta-value"><?php echo $data_pinjam['tanggal_pinjam']; ?></strong></div>
                                        <div><span class="meta-label">Rencana Kembali</span><strong class="meta-value"><?php echo $tgl_kembali_tampil; ?></strong></div>
                                        <div><span class="meta-label">ID Transaksi</span><strong class="meta-value">TRX-<?php echo $data_pinjam['id_peminjaman']; ?></strong></div>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <span style="font-size: 0.8rem; color: #888; text-align: center;">Tidak ada aksi<br>untuk user</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ####################################################### -->
            <!-- BAGIAN 3: MANAJEMEN AKUN (CRUD UPDATE USER) -->
            <!-- ####################################################### -->
            <div id="akun">
                <h3 style="color: var(--primary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 5px; display: inline-block;">⚙️ Manajemen Akun Saya</h3>
                <div class="card" style="padding: 25px;">
                    <p style="color: #666; margin-top: 0;">Perbarui informasi profil dan keamanan akun Anda.</p>
                    
                    <form action="dashboard_user.php" method="POST">
                        
                        <label for="username">Username (Tidak Bisa Diubah):</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" disabled style="background-color: #eee; border-style: dashed;">
                        
                        <label for="nama_lengkap">Nama Lengkap:</label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>

                        <label for="nomor_hp">Nomor HP:</label>
                        <input type="text" name="nomor_hp" id="nomor_hp" value="<?php echo htmlspecialchars($user_data['nomor_hp']); ?>" required>

                        <label for="nik">NIK:</label>
                        <input type="text" name="nik" id="nik" value="<?php echo htmlspecialchars($user_data['nik']); ?>" required>
                        
                        <h4 style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">Perbarui Password (Opsional)</h4>
                        <p style="font-size: 0.9rem; color: #f44336;">*Kosongkan kolom ini jika Anda tidak ingin mengganti password lama.</p>

                        <label for="password">Password Baru:</label>
                        <input type="password" name="password" id="password" placeholder="Masukkan password baru (min. 6 karakter)">

                        <button type="submit" name="update_akun" class="btn btn-primary" style="margin-top: 15px;">💾 Simpan Perubahan Akun</button>
                    </form>
                </div>
            </div>

        </div>
        <!-- END: MAIN CONTENT -->
    </div>
    
    <!-- Modal Formulir Pengajuan Pinjaman -->
    <div id="modal-pinjam" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closePinjamForm()">&times;</span>
            <h3 style="margin-top: 0; color: var(--primary-color);">Ajukan Pinjaman</h3>
            
            <p style="background: #f9f9f9; padding: 10px; border-radius: 6px;">
                Aset: <strong id="pinjam_nama_aset"></strong><br>
                Stok Tersedia: <strong id="pinjam_stok_tersedia" style="color: var(--success-color);"></strong> unit
            </p>
            
            <form action="dashboard_user.php" method="POST">
                <input type="hidden" name="id_aset" id="pinjam_id_aset">
                
                <label for="jumlah_pinjam">Jumlah Pinjam:</label>
                <input type="number" name="jumlah_pinjam" id="jumlah_pinjam" required min="1" placeholder="Masukkan jumlah">
                
                <label for="tanggal_pinjam">Tanggal Mulai Pinjam:</label>
                <input type="date" name="tanggal_pinjam" required>

                <label for="tanggal_kembali">Tanggal Rencana Kembali:</label>
                <input type="date" name="tanggal_kembali" required>
                
                <button type="submit" name="ajukan_pinjam" class="btn btn-primary" style="margin-top: 1rem; width: 100%;">🚀 Kirim Pengajuan</button>
                <button type="button" onclick="closePinjamForm()" class="btn btn-secondary" style="margin-top: 5px; width: 100%;">Batal</button>
            </form>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>