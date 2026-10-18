<?php
// FILE: admin/add.php
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require 'fungsi.php';

if (isset($_POST["tambah"])) {
    if (tambah($_POST) > 0) {
        echo "<script>
                alert('Data Berhasil Ditambahkan!');
                document.location.href = 'index.php';
            </script>";
    } else {
        echo "<script>
                alert('Data Gagal Ditambahkan!');
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Menu - Cafeku Admin</title>
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body>

    <div class="admin-container">
        <div class="page-header">
            <h1>Tambah Menu Baru</h1>
        </div>

        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <label for="nama">Nama Menu</label>
                <input type="text" name="nama" id="nama" required placeholder="Contoh: Kopi Susu Gula Aren">

                <label for="harga">Harga (Rp)</label>
                <input type="number" name="harga" id="harga" required placeholder="Contoh: 18000">

                <label for="kategori">Kategori</label>
                <select name="kategori" id="kategori" required>
                    <option value="coffee">Coffee</option>
                    <option value="noncoffee">Non Coffee</option>
                    <option value="pastry">Pastry</option>
                    <option value="nonalcohol">Mocktail (Non Alcohol)</option>
                    <option value="alcohol">Alcohol (Premium)</option>
                </select>

                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" required placeholder="Jelaskan rasa dan komposisi menu..."></textarea>

                <label for="gambar">Upload Gambar</label>
                <input type="file" name="gambar" id="gambar" required>

                <div class="form-actions">
                    <button type="submit" name="tambah" class="btn btn-full">Simpan Data</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>