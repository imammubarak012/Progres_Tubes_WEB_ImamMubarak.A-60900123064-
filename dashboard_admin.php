<?php 
session_start();
include 'config.php'; 

// Cek otentikasi dan role Admin
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
$nama_admin = $_SESSION['nama_lengkap'];

// --- Proses Update Status Pinjaman (Modul 8: Update) ---
if(isset($_GET['action']) && isset($_GET['id_pinjam'])){
    $id_pinjam = (int)$_GET['id_pinjam'];
    $action = $_GET['action'];
    $new_status = "";
    
    // Tentukan status baru dan logika stok
    if ($action == 'approve') {
        $new_status = 'Disetujui';
        // Stok sudah dikurangi saat user mengajukan, jadi hanya perlu update status
        $sql_update = "UPDATE peminjaman SET status='$new_status' WHERE id_peminjaman=$id_pinjam";
        mysqli_query($conn, $sql_update);
        echo "<script>alert('Pengajuan pinjaman berhasil disetujui.')</script>";
        echo "<meta http-equiv='refresh' content='0; url=transaksi_admin.php'>";
        exit;
        
    } elseif ($action == 'reject') {
        $new_status = 'Ditolak';
        
        // Ambil data jumlah pinjam dan id_aset untuk mengembalikan stok
        $res = mysqli_query($conn, "SELECT id_aset, jumlah_pinjam FROM peminjaman WHERE id_peminjaman=$id_pinjam");
        $data_reject = mysqli_fetch_assoc($res);
        $id_aset = $data_reject['id_aset'];
        $jumlah_pinjam = $data_reject['jumlah_pinjam'];

        // Kembalikan stok yang sebelumnya dikurangi saat pengajuan
        $sql_revert_stok = "UPDATE aset SET stok_tersedia = stok_tersedia + $jumlah_pinjam WHERE id_aset = $id_aset";
        mysqli_query($conn, $sql_revert_stok);

        // Update status pinjaman
        $sql_update = "UPDATE peminjaman SET status='$new_status' WHERE id_peminjaman=$id_pinjam";
        mysqli_query($conn, $sql_update);
        
        echo "<script>alert('Pengajuan pinjaman berhasil ditolak. Stok telah dikembalikan.')</script>";
        echo "<meta http-equiv='refresh' content='0; url=transaksi_admin.php'>";
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
    <title>Manajemen Transaksi - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="header">
        <h1>SIMAS - Manajemen Transaksi</h1>
        <div>
            <!-- Navigasi Admin -->
            <a href="dashboard_admin.php">Manajemen Aset</a> 
            <a href="transaksi_admin.php">Manajemen Transaksi</a> 
            Selamat Datang, **<?php echo htmlspecialchars($nama_admin); ?>** (ADMIN)
            <a href="logout.php">Keluar</a> 
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h2>Daftar Semua Transaksi Peminjaman</h2>
            
            <?php 
            $query_pinjam = mysqli_query($conn, 
                "SELECT p.*, a.nama_aset, pm.nama_lengkap AS peminjam_nama 
                 FROM peminjaman p
                 JOIN aset a ON p.id_aset = a.id_aset
                 JOIN peminjam pm ON p.nama_peminjam = pm.nama_lengkap 
                 ORDER BY p.tanggal_pinjam DESC");
            ?>
            
            <!-- Tabel Daftar Transaksi -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Peminjam</th>
                        <th>Aset</th>
                        <th>Jumlah</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th>Status</th>
                        <th>Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query_pinjam) == 0): ?>
                        <tr><td colspan="8" class="text-center">Tidak ada transaksi peminjaman yang tercatat.</td></tr>
                    <?php else: ?>
                        <?php while($data_pinjam = mysqli_fetch_array($query_pinjam)): 
                            $status = $data_pinjam['status'];
                            $status_class = '';
                            if ($status == 'Dipinjam') $status_class = 'status-red';
                            if ($status == 'Disetujui') $status_class = 'status-green';
                            if ($status == 'Ditolak') $status_class = 'status-secondary'; // Menggunakan sekunder untuk ditolak
                        ?>
                            <tr>
                                <td><?php echo $data_pinjam['id_peminjaman']; ?></td>
                                <td><?php echo htmlspecialchars($data_pinjam['peminjam_nama']); ?></td>
                                <td><?php echo htmlspecialchars($data_pinjam['nama_aset']); ?></td>
                                <td><?php echo $data_pinjam['jumlah_pinjam']; ?></td>
                                <td><?php echo $data_pinjam['tanggal_pinjam']; ?></td>
                                <td><?php echo $data_pinjam['tanggal_kembali']; ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                <td>
                                    <?php if ($status == 'Dipinjam'): ?>
                                        <a href="?action=approve&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 0.8rem;">Setujui</a>
                                        <a href="?action=reject&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Tolak pinjaman ini? Stok akan dikembalikan.')" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.8rem;">Tolak</a>
                                    <?php else: ?>
                                        <span class="status-badge status-secondary">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>