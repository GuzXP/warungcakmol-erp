<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: add.php (CREATE MENU)
 * UNTUK: Halaman form untuk menambahkan data menu baru ke database.
 * FITUR: Upload gambar, Validasi input, dan Notifikasi hasil operasi.
 */

// 1. STARTER PACK
require 'fungsi.php'; // Memuat pustaka fungsi inti & auth

// 2. PROTEKSI HALAMAN
confirm_admin_login();

// 3. LOGIKA SAAT TOMBOL SUBMIT DITEKAN
if (isset($_POST["submit"])) {
    // Validasi token CSRF untuk mencegah serangan CSRF
    if (!isset($_POST["csrf_token"]) || !check_csrf_token($_POST["csrf_token"])) {
        die("CSRF Token validation failed.");
    }
    
    // Panggil fungsi tambah() dengan mengirim seluruh data form ($_POST)
    if (tambah($_POST) > 0) {
        echo "<script>
            alert('Menu berhasil ditambahkan!'); 
            document.location.href = 'index.php?page=menu';
        </script>";
    } else {
        echo "<script>alert('Gagal menambahkan menu!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu | Admin Cak Mol</title>
    <!-- CSS Utama: Mengandung semua style layout form -->
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body>
    
    <!-- NAVBAR (Navigasi Sederhana) -->
    <nav class="admin-navbar">
        <div class="container nav-content">
            <h1 class="title-medium">Cak Mol</h1>
            <!-- Tombol Batal/Kembali ke Dashboard -->
            <a href="index.php?page=menu" class="btn btn-outline btn-sm">Batal</a>
        </div>
    </nav>

    <!-- WRAPPER KONTEN -->
    <div class="container spacer-y">
        
        <!-- KARTU FORMULIR -->
        <!-- Class 'form-card-container' mengatur lebar max 800px dan margin auto (center) -->
        <div class="card-admin form-card-container">
            
            <!-- HEADER FORMULIR -->
            <!-- Class 'form-header' memberikan jarak bawah -->
            <div class="form-header">
                <!-- Judul Form -->
                <h2 class="title-medium form-title-margin">Tambah Menu Baru</h2>
                <p class="text-muted">Lengkapi formulir di bawah untuk menambah koleksi menu warung.</p>
            </div>

            <!-- FORM INSERT DATA -->
            <!-- enctype="multipart/form-data" WAJIB ada untuk mengizinkan upload file gambar -->
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                
                <!-- GRID SYSTEM (Layout 2 Kolom) -->
                <!-- Class 'form-grid' membuat layout responsif grid -->
                <div class="form-grid">
                    
                    <!-- SISI KIRI: Input Data Teks -->
                    <div>
                        <!-- Input Nama Menu -->
                        <div class="form-group">
                            <label for="nama_menu">Nama Menu</label>
                            <!-- Input required: Mencegah submit jika kosong -->
                            <input type="text" name="nama_menu" id="nama_menu" required placeholder="Contoh: Nasi Goreng Gila">
                        </div>

                        <!-- Input Harga -->
                        <div class="form-group">
                            <label for="harga_jual">Harga (Rp)</label>
                            <!-- Input type number: Memastikan user hanya input angka -->
                            <input type="number" name="harga_jual" id="harga_jual" required placeholder="Contoh: 15000">
                        </div>

                        <!-- Dropdown Kategori -->
                        <div class="form-group">
                            <label for="bahan_utama">Kategori</label>
                            <!-- Select Dropdown untuk standarisasi data kategori di database -->
                            <select name="bahan_utama" id="bahan_utama" required>
                                <option value="" disabled selected>Pilih Kategori...</option>
                                <?php 
                                $kats = query("SELECT * FROM kategori ORDER BY urutan ASC");
                                foreach ($kats as $k):
                                ?>
                                    <option value="<?= sanitize($k['nama_kategori']); ?>"><?= sanitize($k['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- SISI KANAN: Deskripsi & Upload Foto -->
                    <div>
                        <!-- Input Deskripsi Area -->
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat</label>
                            <textarea name="deskripsi" id="deskripsi" required placeholder="Jelaskan menu ini secara menarik..."></textarea>
                        </div>

                        <!-- Input Upload Gambar -->
                        <div class="form-group">
                            <label for="foto_menu">Foto Menu</label>
                            
                            <!-- Area Upload Custom Style -->
                            <!-- Class 'upload-box' memberikan garis putus-putus dan background abu -->
                            <div class="upload-box">
                                <input type="file" name="foto_menu" id="foto_menu" required class="upload-input">
                            </div>
                            
                            <!-- Keterangan format file -->
                            <small class="text-muted text-hint">Format disarankan: JPG/PNG, maksimal 2MB.</small>
                        </div>
                    </div>
                </div>

                <!-- FOOTER FORMULIR: Tombol Submit -->
                <!-- Class 'form-footer' memberikan garis pemisah atas dan align kanan -->
                <div class="form-footer">
                    <!-- Tombol Simpan -->
                    <!-- Class 'btn-submit-wide' membuat tombol lebih lebar dan tinggi -->
                    <button type="submit" name="submit" class="btn btn-primary btn-submit-wide">
                        Simpan Menu Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>