<?php 
// File aset_tambah.php: Form CREATE Aset (Modul 7)
session_start();
include 'config.php'; 

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Aset - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMAS - Tambah Aset</h1>
    </div>

    <div class="container card">
        <h2>Formulir Tambah Aset Baru</h2>
        
        <form action="aset_insert.php" method="POST">
            <label for="nama_aset">Nama Aset:</label>
            <input type="text" id="nama_aset" name="nama_aset" placeholder="Masukkan nama aset..." required>
        
            <label for="deskripsi">Deskripsi Aset:</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Masukkan deskripsi aset..." required></textarea>
        
            <label for="stok">Stok Total:</label>
            <input type="number" id="stok" name="stok" placeholder="Masukkan jumlah stok total..." required min="1">
            
            <button type="submit" name="simpan" class="btn btn-primary">Simpan Aset</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='manajemen_simas.php';">Batal</button>
        </form>
    </div>
</body>
</html>