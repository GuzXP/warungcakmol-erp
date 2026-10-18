<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: registrasi.php (ADMIN REGISTRATION)
 * UNTUK: Halaman pendaftaran admin baru.
 * LOGIKA: Menerima input user -> Validasi Password Sama -> Hash Password -> Insert DB.
 */

// 1. IMPORT FUNGSI & CEK AUTH
require 'fungsi.php';

// Jika sudah login, jangan tampilkan halaman pendaftaran
if (isset($_SESSION["login"]) && $_SESSION["login"] === true) {
    header("Location: index.php");
    exit;
}

// 2. PROSES REGISTRASI (SUBMIT)
if( isset($_POST["register"]) ) {
    
    // Panggil fungsi registrasi() dari fungsi.php
    // Fungsi ini akan melakukan validasi dan hashing password sebelum insert ke DB.
    if( registrasi($_POST) > 0 ) {
        // Jika insert berhasil (return affected_rows > 0)
        echo "<script>
                alert('User admin baru berhasil didaftarkan!');
                document.location.href = 'index.php'; 
              </script>";
    } else {
        // Jika gagal (misal username sudah ada, atau password tidak cocok)
        echo "<script>
                alert('Gagal mendaftarkan user admin baru! Silahkan periksa inputan Anda.');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin | Cak Mol</title>
    <!-- CSS Shared -->
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body class="auth-page">

    <!-- WRAPPER TENGAH -->
    <div class="auth-card">
        <div class="card-admin">
            
            <!-- HEADER -->
            <div class="auth-header">
                <!-- Class 'auth-title-margin' -->
                <h1 class="title-large auth-title-margin">Cak Mol</h1>
                <p class="text-muted">Daftar Akun Admin Baru</p>
            </div>

            <!-- FORM REGISTRASI -->
            <form action="" method="post">
                <!-- Input Nama Lengkap (Opsional untuk tampilan dashboard) -->
                <div class="form-group">
                    <label for="fullname">Nama Lengkap</label>
                    <input type="text" name="fullname" id="fullname" required placeholder="Masukkan nama asli..." autocomplete="off">
                </div>
                
                <!-- Input Username (Unik) -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required placeholder="Buat username..." autocomplete="off">
                </div>
                
                <!-- Input Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Buat password minimal 8 karakter">
                </div>
                
                <!-- Konfirmasi Password (Harus sama dengan atas) -->
                <div class="form-group">
                    <label for="password2">Konfirmasi Password</label>
                    <input type="password" name="password2" id="password2" required placeholder="Ketik ulang password">
                </div>
                
                <!-- Tombol Submit -->
                <!-- Menggunakan class 'btn-login' (reusable style dari halaman login) -->
                <button type="submit" name="register" class="btn btn-primary w-full btn-login">
                    Daftar Sekarang
                </button>
            </form>
            
            <!-- Link ke Login -->
            <!-- Class 'auth-footer' & 'auth-link' -->
            <div class="auth-footer">
                Sudah punya akun? <a href="login.php" class="auth-link">Login di sini</a>
            </div>
        </div>
    </div>

</body>
</html>