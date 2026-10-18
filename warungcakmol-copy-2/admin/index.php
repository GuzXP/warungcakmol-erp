<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: index.php (ADMIN DASHBOARD)
 * UNTUK: Halaman utama manajemen data menu aplikasi Nasi Goreng Cak Mol.
 * FUNGSI UTAMA: Menampilkan daftar menu (READ), Pencarian (SEARCH), dan link CRUD lainnya.
 */

// 1. MEMULAI SESSION
// Dibutuhkan untuk mengecek apakah admin sudah login atau belum melalui variabel session.
session_start();

// 2. CEK STATUS LOGIN
// Jika variabel session 'login' tidak ada (belum login).
if( !isset($_SESSION["login"]) ) {
    header("Location: login.php"); // Redirect paksa user ke halaman login
    exit; // Hentikan eksekusi script agar kode di bawah tidak dijalankan
}

// 3. IMPORT LOGIKA DATABASE
// Menghubungkan ke fungsi.php yang berisi query mysqli dan koneksi database.
require 'fungsi.php';

// 4. MENGAMBIL DATA MENU (DEFAULT)
// Menjalankan fungsi query() untuk mengambil seluruh data dari tabel 'menu'.
// Diurutkan berdasarkan id_menu secara menurun (DESC) agar menu terbaru ada di atas.
$menu = query("SELECT * FROM menu ORDER BY id_menu DESC"); 

// 5. LOGIKA PENCARIAN (IF SEARCH SUBMITTED)
// Cek apakah tombol 'Search' (name="search") pada form telah ditekan.
if(isset($_POST["search"])) {
    // Timpa variabel $menu dengan hasil pencarian berdasarkan keyword inputan.
    $menu = search($_POST["keyword"]); 
}

$i = 1; // Inisialisasi variabel iterator untuk nomor urut tabel (dummy number)
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"> <!-- Set encoding karakter UTF-8 agar support simbol -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Viewport mobile friendly -->
    <title>Dashboard Admin | Cak Mol</title>
    <!-- Import File CSS Utama untuk styling seluruh halaman admin -->
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body>
    
    <!-- ----------------------------------------------------------------------- -->
    <!-- NAVBAR ADMIN (Header Section)                                           -->
    <!-- ----------------------------------------------------------------------- -->
    <nav class="admin-navbar">
        <div class="container nav-content">
            <!-- Branding / Judul Dashboard -->
            <h1 class="title-medium">Dashboard Admin</h1>
            
            <!-- User Info & Logout Button -->
            <!-- Class 'nav-user-wrapper' untuk layout flex user info -->
            <div class="nav-user-wrapper">
                <!-- Info statis pemilik -->
                <span class="nav-user-text">Owner: Bagus Ramadhan</span>
                
                <!-- Tombol Logout -->
                <!-- Mengirim request ke logout.php saat diklik -->
                <!-- onclick return confirm: Mencegah logout tidak sengaja -->
                <a href="logout.php" class="btn btn-destructive btn-sm" onclick="return confirm('Logout dari sistem?')">Logout</a>
            </div>
        </div>
    </nav>

    <!-- ----------------------------------------------------------------------- -->
    <!-- MAIN CONTENT AREA                                                       -->
    <!-- ----------------------------------------------------------------------- -->
    <div class="container spacer-y">
        
        <!-- HEADER HALAMAN: Judul & Tombol Aksi -->
        <div class="header-action-row">
            <div>
                <!-- Judul Halaman -->
                <h2 class="title-large header-title-margin">Manajemen Menu</h2>
                <p class="text-muted">Pantau dan kelola stok menu makanan/minuman warung Anda.</p>
            </div>
            
            <!-- Group Tombol Aksi (Cetak & Tambah) -->
            <div class="header-btn-group">
                <!-- Link ke cetakPDF.php, target="_blank" untuk membuka di tab baru -->
                <a href="cetakPDF.php" target="_blank" class="btn btn-outline">Cetak Menu</a>
                <!-- Link ke halaman tambah data (Create) -->
                <a href="add.php" class="btn btn-primary">+ Tambah Menu</a>
            </div>
        </div>

        <!-- FORM PENCARIAN & FILTER -->
        <div class="card-admin filter-card">
            <!-- Form mengirim data ke halaman ini sendiri (action kosong) dengan method POST -->
            <form action="" method="post" class="filter-form">
                <!-- Input Keyword Pencarian -->
                <!-- autocomplete="off" agar browser tidak menampilkan histori input -->
                <input type="text" name="keyword" placeholder="Cari nama menu, harga, atau kategori..." autocomplete="off" class="input-search-no-margin">
                
                <!-- Tombol Submit Pencarian -->
                <button type="submit" name="search" class="btn btn-primary">Cari Data</button>
                
                <!-- Tombol Reset (Hanya muncul jika sedang dalam mode pencarian) -->
                <?php if(isset($_POST["search"])): // Cek apakah $_POST search ada ?>
                    <a href="index.php" class="btn btn-outline">Reset</a> <!-- Reset dengan reload halaman bersih -->
                <?php endif; ?>
            </form>
        </div>

        <!-- TABEL DATA UTAMA -->
        <!-- Wrapper tabel untuk styling responsive/overflow scroll -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <!-- Header Kolom 1: Nomor -->
                        <th class="col-no">No</th>
                        
                        <!-- Header Kolom 2: Foto Menu -->
                        <th class="col-img">Foto</th>
                        
                        <!-- Header Kolom 3: Info Menu -->
                        <th>Menu & Kategori</th>
                        
                        <!-- Header Kolom 4: Harga -->
                        <th>Harga Satuan</th>
                        
                        <!-- Header Kolom 5: Deskripsi Singkat -->
                        <th>Deskripsi</th>
                        
                        <!-- Header Kolom 6: Tombol Aksi (Edit/Hapus) -->
                        <th class="col-action">Opsi</th>
                    </tr>
                </thead>
                <tbody>
                    
                    <!-- LOGIKA: CEK KEKOSONGAN DATA -->
                    <?php if(empty($menu)) : // Jika array $menu hasil query kosong ?>
                        <tr>
                            <!-- Tampilkan pesan 'Tidak ada data' yang merge 6 kolom -->
                            <td colspan="6" class="cell-no-empty text-muted">
                                Tidak ada data menu ditemukan di database.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <!-- LOGIKA: LOOP DATA MENU -->
                    <!-- Melakukan perulangan foreach untuk setiap baris data ($row) di $menu -->
                    <?php foreach ($menu as $data) : ?>
                    <tr>
                        <!-- Kolom 1: Nomor Urut -->
                        <td class="cell-no">
                            <?= $i; // Output nomor urut ?>
                        </td>
                        
                        <!-- Kolom 2: Gambar -->
                        <td>
                            <!-- Menampilkan gambar dari folder ../img/ dengan nama file dari DB -->
                            <img src="../img/<?= $data['gambar']; // Echo nama file gambar ?>" class="admin-img-thumb">
                        </td>
                        
                        <!-- Kolom 3: Nama & Kategori -->
                        <td>
                            <!-- Nama Menu -->
                            <div class="cell-menu-title">
                                <?= $data['nama_menu']; // Echo nama menu ?>
                            </div>
                            
                            <!-- Wrapper Kategori -->
                            <div class="cell-menu-cat-wrapper">
                                <!-- Badge Kategori -->
                                <span class="cell-menu-badge">
                                    <?= $data['kategori']; // Echo kategori ?>
                                </span>
                            </div>
                        </td>
                        
                        <!-- Kolom 4: Harga -->
                        <td class="cell-price">
                            <!-- Format angka menjadi format Rupiah (tanpa desimal, pemisah ribuan titik) -->
                            Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                        </td>
                        
                        <!-- Kolom 5: Deskripsi -->
                        <td>
                            <!-- Output deskripsi dengan title popup saat hover, CSS membatasi panjang teks -->
                            <div class="cell-desc" title="<?= $data['deskripsi']; ?>">
                                <?= $data['deskripsi']; // Echo deskripsi ?>
                            </div>
                        </td>
                        
                        <!-- Kolom 6: Aksi -->
                        <td class="cell-action-btn-wrapper">
                            <!-- Tombol Edit: Mengirim parameter id_menu via query string URL -->
                            <a href="edit.php?id=<?= $data['id_menu']; ?>" class="btn btn-outline btn-sm btn-edit-table">Edit</a>
                            
                            <!-- Tombol Hapus: Mengirim parameter id_menu + Konfirmasi JS (Confirm Dialog) -->
                            <a href="delete.php?id=<?= $data['id_menu']; ?>" class="btn btn-outline btn-sm btn-delete-table" 
                               onclick="return confirm('Hapus menu ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php $i++; // Hapus style inline, gunakan variabel increment $i untuk nomor baris selanjutnya ?>
                    <?php endforeach; // Akhir looping ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>