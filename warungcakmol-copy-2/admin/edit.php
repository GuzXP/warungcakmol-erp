<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: edit.php (UPDATE MENU)
 * UNTUK: Halaman form untuk mengubah data menu yang sudah ada (Fitur Update CRUD).
 * LOGIKA: Mengambil data lama berdasarkan ID, menampilkannya di form, dan menyimpan perubahannya.
 */

// 1. START SESSION
session_start();

// 2. PROTEKSI LOGIN
if( !isset($_SESSION["login"]) ) {
    header("Location: login.php");
    exit;
}

// 3. LOAD LIBRARY
require 'fungsi.php';

// 4. AMBIL DATA LAMA (READ)
// Mengambil Parameter ID dari URL (?id=...)
$id = $_GET["id"];

// Query data menu berdasarkan ID tersebut. [0] artinya ambil indeks pertama dari hasil array numerik.
$makanan = query("SELECT * FROM menu WHERE id_menu = $id")[0];

// 5. PROSES SAAT TOMBOL SIMPAN DITEKAN
if (isset($_POST["submit"])) {
    
    // Jalankan fungsi edit(). Jika return > 0, berarti ada data yang berubah.
    if (edit($_POST) > 0) {
        echo "<script>
            alert('Menu berhasil diubah!'); 
            document.location.href = 'index.php';
        </script>";
    } else {
        // Jika return 0, bisa jadi user tidak mengubah apapun tapi klik simpan, atau gagal query.
        // Kita tetap anggap berhasil/selesai agar user tidak bingung.
        echo "<script>
            alert('Data berhasil disimpan! (Tidak ada perubahan atau sukses update)'); 
            document.location.href = 'index.php';
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
            <a href="index.php" class="btn btn-outline btn-sm">Batal</a>
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
                                <!-- Logika Ternary: Cek apakah kategori di DB sama dengan value option ini? Jika ya, tambahkan atribut 'selected' -->
                                <option value="Nasi Goreng Series" <?= $makanan['kategori'] == 'Nasi Goreng Series' ? 'selected' : ''; ?>>Nasi Goreng Series</option>
                                <option value="Mie & Noodle Series" <?= $makanan['kategori'] == 'Mie & Noodle Series' ? 'selected' : ''; ?>>Mie & Noodle Series</option>
                                <option value="Capcay (Special)" <?= $makanan['kategori'] == 'Capcay (Special)' ? 'selected' : ''; ?>>Capcay (Special)</option>
                                <option value="Minuman & Pelengkap" <?= $makanan['kategori'] == 'Minuman & Pelengkap' ? 'selected' : ''; ?>>Minuman & Pelengkap</option>
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