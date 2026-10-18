<?php
// FILE: admin/edit.php
session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require '../fungsi.php';

$id = $_GET["id"];
$mhs = query("SELECT * FROM produk WHERE id = $id")[0];

if (isset($_POST["ubah"])) {
    if (ubah($_POST) > 0) {
        echo "<script>
                alert('Data Berhasil Diubah!');
                document.location.href = 'index.php';
            </script>";
    } else {
        echo "<script>
                alert('Data Gagal Diubah (atau tidak ada perubahan)!');
                document.location.href = 'index.php';
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Menu - Cafeku Admin</title>
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body>

    <div class="admin-container">
        <div class="page-header">
            <h1>Ubah Data Menu</h1>
        </div>

        <div class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $mhs["id"]; ?>">
                <input type="hidden" name="gambarLama" value="<?= $mhs["gambar"]; ?>">

                <label for="nama">Nama Menu</label>
                <input type="text" name="nama" id="nama" required value="<?= $mhs["nama"]; ?>">

                <label for="harga">Harga (Rp)</label>
                <input type="number" name="harga" id="harga" required value="<?= $mhs["harga"]; ?>">

                <label for="kategori">Kategori</label>
                <select name="kategori" id="kategori" required>
                    <option value="coffee" <?= ($mhs['kategori'] == 'coffee') ? 'selected' : ''; ?>>Coffee</option>
                    <option value="noncoffee" <?= ($mhs['kategori'] == 'noncoffee') ? 'selected' : ''; ?>>Non Coffee</option>
                    <option value="pastry" <?= ($mhs['kategori'] == 'pastry') ? 'selected' : ''; ?>>Pastry</option>
                    <option value="nonalcohol" <?= ($mhs['kategori'] == 'nonalcohol') ? 'selected' : ''; ?>>Mocktail (Non Alcohol)</option>
                    <option value="alcohol" <?= ($mhs['kategori'] == 'alcohol') ? 'selected' : ''; ?>>Alcohol (Premium)</option>
                </select>

                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="4" required><?= $mhs["deskripsi"]; ?></textarea>

                <label for="gambar">Gambar</label>
                <div class="page-note image-preview">
                    <img src="../<?= $mhs['gambar']; ?>" class="preview-image" alt="Preview Gambar">
                </div>
                <input type="file" name="gambar" id="gambar">
                <small class="page-note">*Kosongkan jika tidak ingin mengubah gambar</small>

                <div class="form-actions">
                    <button type="submit" name="ubah" class="btn btn-full">Simpan Perubahan</button>
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>