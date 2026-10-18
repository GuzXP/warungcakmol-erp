<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: edit.php (UPDATE MENU)
 * UNTUK: Halaman form untuk mengubah data menu yang sudah ada (Fitur Update CRUD).
 * LOGIKA: Mengambil data lama berdasarkan ID, menampilkannya di form, dan menyimpan perubahannya.
 */

// 1. STARTER PACK & AUTH CHECK
require 'fungsi.php';
confirm_admin_login();

// 2. AMBIL DATA LAMA (READ SECURE)
$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

// Query data menu berdasarkan ID secara aman menggunakan prepared statements
$makanan_list = query("SELECT * FROM menu WHERE id_menu = ?", [$id], "i");

if (empty($makanan_list)) {
    echo "<script>
        alert('Menu tidak ditemukan!');
        document.location.href = 'index.php?page=menu';
    </script>";
    exit;
}

$makanan = $makanan_list[0];

// 3. PROSES SAAT TOMBOL SIMPAN DITEKAN
if (isset($_POST["submit"])) {
    // Validasi CSRF Token
    if (!isset($_POST["csrf_token"]) || !check_csrf_token($_POST["csrf_token"])) {
        die("CSRF Token validation failed.");
    }
    
    // Jalankan fungsi edit()
    if (edit($_POST) > 0) {
        echo "<script>
            alert('Menu berhasil diubah!'); 
            document.location.href = 'index.php?page=menu';
        </script>";
    } else {
        echo "<script>
            alert('Data berhasil disimpan! (Tidak ada perubahan atau sukses update)'); 
            document.location.href = 'index.php?page=menu';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Menu | Admin Cak Mol</title>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body>
    
    <!-- NAVBAR (Navigasi Kembali) -->
    <nav class="admin-navbar">
        <div class="container nav-content">
            <h1 class="title-medium">Cak Mol</h1>
            <a href="index.php?page=menu" class="btn btn-outline btn-sm">Batal</a>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container spacer-y">
        
        <!-- CARD ADMIN -->
        <!-- Menggunakan class 'form-card-container' untuk layout center max-800px -->
        <div class="card-admin form-card-container">
            
            <!-- HEADER HALAMAN -->
            <div class="form-header">
                <h2 class="title-medium form-title-margin">Ubah Data Menu</h2>
                <p class="text-muted">
                    Perbarui informasi menu "<strong><?= $makanan['nama_menu']; // Menampilkan nama menu yang sedang diedit ?></strong>" Anda.
                </p>
            </div>

            <!-- FORM UPDATE -->
            <!-- enctype multipart wajib karena ada fitur ganti foto -->
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                
                <!-- INPUT HIDDEN: Menyimpan ID dan Nama Foto Lama -->
                <!-- Penting agar fungsi PHP tahu baris mana yang diupdate dan foto apa yang harus dihapus jika diganti -->
                <input type="hidden" name="id_produk" value="<?= $makanan['id_menu']; ?>">
                <input type="hidden" name="foto_lama" value="<?= $makanan['gambar']; ?>">

                <!-- GRID LAYOUT -->
                <div class="form-grid">
                    
                    <!-- SISI KIRI: Data Teks -->
                    <div>
                        <!-- Input Nama -->
                        <div class="form-group">
                            <label for="nama_menu">Nama Menu</label>
                            <!-- Value diisi data dari DB ($makanan['nama_menu']) agar User tidak perlu ketik ulang -->
                            <input type="text" name="nama_menu" id="nama_menu" required value="<?= $makanan['nama_menu']; ?>">
                        </div>

                        <!-- Input Harga -->
                        <div class="form-group">
                            <label for="harga_jual">Harga (Rp)</label>
                            <input type="number" name="harga_jual" id="harga_jual" required value="<?= $makanan['harga']; ?>">
                        </div>

                        <!-- Select Kategori -->
                        <div class="form-group">
                            <label for="bahan_utama">Kategori</label>
                            <select name="bahan_utama" id="bahan_utama" required>
                                <?php 
                                $kats = query("SELECT * FROM kategori ORDER BY urutan ASC");
                                foreach ($kats as $k):
                                ?>
                                    <option value="<?= sanitize($k['nama_kategori']); ?>" <?= $makanan['kategori'] == $k['nama_kategori'] ? 'selected' : ''; ?>><?= sanitize($k['nama_kategori']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- SISI KANAN: Deskripsi & Foto -->
                    <div>
                        <!-- Input Deskripsi -->
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi Singkat</label>
                            <!-- Textarea tidak punya atribut value, kontennya ditaruh di antara tag pembuka dan penutup -->
                            <textarea name="deskripsi" id="deskripsi" required><?= $makanan['deskripsi']; ?></textarea>
                        </div>

                        <!-- Bagian Foto -->
                        <div class="form-group">
                            <label>Foto Saat Ini</label>
                            
                            <!-- Preview Foto Lama -->
                            <!-- Class 'preview-img-wrapper' mengatur layout gambar dan teks -->
                            <div class="preview-img-wrapper">
                                <!-- Menampilkan thumbnail gambar lama -->
                                <img src="../img/<?= $makanan['gambar']; ?>" class="admin-img-thumb preview-img-lg">
                                <!-- Nama file gambar lama -->
                                <p class="text-muted preview-filename">File: <?= $makanan['gambar']; ?></p>
                            </div>

                            <label for="foto_menu">Ganti Foto (Opsional)</label>
                            
                            <!-- Upload Box Baru (Compact) -->
                            <!-- Class 'upload-box-compact' memberikan style border dashed yang lebih tipis/kecil -->
                            <div class="upload-box-compact">
                                <!-- Input file tidak required, karena user boleh hanya update nama/harga tanpa ganti foto -->
                                <input type="file" name="foto_menu" id="foto_menu" class="upload-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER FORM -->
                <div class="form-footer">
                    <button type="submit" name="submit" class="btn btn-primary btn-submit-wide">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>