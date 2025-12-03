<?php 
// File aset_edit.php: Form UPDATE Aset (Modul 8)
session_start();
include 'config.php'; 

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

$id_aset = $_GET['id'];
$sql = "SELECT * FROM aset WHERE id_aset='$id_aset'";
$query_edit = mysqli_query($conn, $sql);

if(mysqli_num_rows($query_edit) == 0){
    echo "<script>alert('Data aset tidak ditemukan!')</script>";
    echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    exit;
}

$data_aset = mysqli_fetch_array($query_edit);
mysqli_close($conn); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aset - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMAS - Edit Aset</h1>
    </div>

    <div class="container card">
        <h2>Formulir Edit Aset</h2>
        
        <form action="aset_update.php" method="POST">
            <!-- Hidden ID Aset (Penting untuk Query UPDATE) -->
            <input type="hidden" name="id_aset" value="<?php echo $data_aset['id_aset']; ?>">

            <label for="nama_aset">Nama Aset:</label>
            <input type="text" id="nama_aset" name="nama_aset" value="<?php echo htmlspecialchars($data_aset['nama_aset']); ?>" required>
        
            <label for="deskripsi">Deskripsi Aset:</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($data_aset['deskripsi']); ?></textarea>
            
            <label for="stok">Stok Total:</label>
            <input type="number" id="stok" name="stok" value="<?php echo $data_aset['stok']; ?>" required min="1">
        
            <label for="stok_tersedia">Stok Tersedia:</label>
            <input type="number" id="stok_tersedia" name="stok_tersedia" value="<?php echo $data_aset['stok_tersedia']; ?>" required min="0">
            
            <button type="submit" name="update" class="btn btn-primary">Update Data</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='manajemen_simas.php';">Batal</button>
        </form>
    </div>
</body>
</html>