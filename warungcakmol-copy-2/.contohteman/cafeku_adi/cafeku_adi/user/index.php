<?php
// FILE: user/index.php
// Halaman Utama Katalog

// Panggil koneksi (keluar 1 folder dari user/)
session_start();
require '../fungsi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Jika sudah login tapi bukan customer, redirect sesuai role
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'owner':
            header("Location: ../admin/index.php");
            exit;
        case 'kasir':
            header("Location: ../kasir/index.php");
            exit;
        case 'kitchen':
            header("Location: ../kitchen/dashboard.php");
            exit;
        case 'karyawan':
            header("Location: ../karyawan/index.php");
            exit;
    }
}

// Cek Filter Kategori
// Jika ada ?kategori=coffee di URL, kita ambil datanya
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : "all";

if ($kategori == "all") {
    // Ambil semua data
    $produk = query("SELECT * FROM produk ORDER BY id DESC");
    $judul = "All Menu";
} else {
    // Ambil sesuai kategori
    $produk = cari($kategori); // Kita bisa pakai fungsi cari() atau query manual
    // Biar lebih spesifik, kita query manual saja di sini:
    $kategori = mysqli_real_escape_string($conn, $kategori);
    $produk = query("SELECT * FROM produk WHERE kategori = '$kategori'");

    // Rapikan judul (misal: noncoffee -> Noncoffee)
    $judul = ucfirst($kategori);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Cafeku</title>
    <link rel="stylesheet" href="../css/style-user.css">
</head>

<body>

    <header>
        <div class="header-left">Cafeku Galin Widya</div>

    </header>

    <div class="nav">
        <a href="?kategori=all" class="<?= ($kategori == 'all') ? 'active' : '' ?>">All Menu</a>
        <a href="?kategori=coffee" class="<?= ($kategori == 'coffee') ? 'active' : '' ?>">Coffee</a>
        <a href="?kategori=noncoffee" class="<?= ($kategori == 'noncoffee') ? 'active' : '' ?>">Non Coffee</a>
        <a href="?kategori=pastry" class="<?= ($kategori == 'pastry') ? 'active' : '' ?>">Pastry</a>
        <a href="?kategori=nonalcohol" class="<?= ($kategori == 'nonalcohol') ? 'active' : '' ?>">Mocktail</a>
        <a href="?kategori=alcohol" class="<?= ($kategori == 'alcohol') ? 'active' : '' ?>">Alcohol</a>
    </div>

    <div style="display: flex; justify-content: space-between; gap: 10px; margin: 15px 10px 0 0;">
        <a href="pembayaran.php" class="btn btn-success" style="padding: 8px 14px;">Keranjang</a>
        <a href="../logout.php" class="btn btn-danger" style="padding: 8px 12px; font-size: 0.95rem;">Logout</a>
    </div>

    <div class="container">

        <h2 class="judul-kategori"><?= $judul ?></h2>

        <div class="menu-grid">
            <?php foreach ($produk as $row) : ?>
                <a href="menu.php?id=<?= $row['id'] ?>" class="card">
                    <img src="../<?= $row['gambar'] ?>" alt="<?= $row['nama'] ?>" loading="lazy">

                    <div class="card-content">
                        <span class="kategori-label"><?= $row['kategori'] ?></span>
                        <h3><?= $row['nama'] ?></h3>
                        <span class="harga">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($produk)) : ?>
            <p style="text-align: center; margin-top: 50px; opacity: 0.7;">
                Belum ada menu di kategori ini.
            </p>
        <?php endif; ?>

    </div>

</body>

</html>