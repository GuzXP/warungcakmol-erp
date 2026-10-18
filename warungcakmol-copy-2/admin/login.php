<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: login.php (ADMIN LOGIN)
 * UNTUK: Halaman otentikasi admin.
 * FITUR KEAMANAN: Session Management, Cookie (Remember Me), dan Password Hashing (Argon2/Bcrypt).
 */

// 1. MEMULAI SESSION
session_start(); // Wajib ada di awal untuk mengakses $_SESSION

// 2. LOAD DEPENDENCIES
require 'fungsi.php'; // Memuat koneksi DB

// 3. LOGIKA COOKIE (REMEMBER ME)
// Cek apakah browser mengirim cookie 'id' dan 'key' (hash username)
if( isset($_COOKIE['id']) && isset($_COOKIE['key']) ) {
    $id = $_COOKIE['id']; // Ambil ID dari cookie
    $key = $_COOKIE['key']; // Ambil key hash dari cookie

    // Ambil username berdasarkan ID tersebut dari database
    $result = mysqli_query($koneksi, "SELECT username FROM user WHERE id = $id");
    $row = mysqli_fetch_assoc($result);

    // Cek kecocokan cookie: Apakah key yang dikirim sama dengan hash username di DB?
    // Ini mencegah user memalsukan cookie hanya dengan menebak ID.
    if( $key === hash('sha256', $row['username']) ) {
        $_SESSION['login'] = true; // Jika valid, set session login
    }
}

// 4. CEK SESSION (JIKA SUDAH LOGIN)
// Jika user sudah memiliki session login aktif, langsung lempar ke index.php
if( isset($_SESSION["login"]) ) {
    header("Location: index.php");
    exit;
}

// 5. PROSES FORM LOGIN (SUBMIT)
if ( isset($_POST["login"]) ) {
    $username = $_POST["username"];
    $password = $_POST["password"]; // Password plain text inputan user

    // Cari user berdasarkan username di database
    $result = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");

    // Cek ketersediaan user (rows harus 1)
    if( mysqli_num_rows($result) === 1 ) {
        
        // Ambil data user
        $row = mysqli_fetch_assoc($result);
        
        // VERIFIKASI PASSWORD
        // password_verify(): membandingkan string plain ($password) dengan Hash ($row["password"]).
        // Ini jauh lebih aman daripada sekadar membandingkan string == string.
        if( password_verify($password, $row["password"]) ) {
            
            // Set Session Login
            $_SESSION["login"] = true;

            // CEK REMEMBER ME
            if( isset($_POST['remember']) ) {
                // Buat Cookie yang berlaku selama 1 jam (3600 detik)
                setcookie('id', $row['id'], time() + 3600 );
                // Enkripsi username dengan SHA256 sebagai key validasi cookie
                setcookie('key', hash('sha256', $row['username']), time() + 3600 );
            }

            // Redirect ke Dashboard
            header("Location: index.php");
            exit;
        }
    }

    // Jika kode sampai sini, berarti salah username/password
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Cak Mol</title>
    <!-- CSS Shared -->
    <link rel="stylesheet" href="../css/style-admin.css">
</head>
<body class="auth-page">

    <!-- WRAPPER TENGAH (CENTERED CARD) -->
    <div class="auth-card">
        <div class="card-admin">
            
            <!-- HEADER LOGIN -->
            <div class="auth-header">
                <!-- Class 'auth-title-margin' -->
                <h1 class="title-large auth-title-margin">Cak Mol</h1>
                <p class="text-muted">Masuk ke Dashboard Admin</p>
            </div>

            <!-- PESAN ERROR -->
            <!-- Muncul hanya jika variabel $error ter-set (gagal login) -->
            <?php if( isset($error) ) : ?>
                <!-- Class 'alert-danger' untuk styling kotak merah -->
                <div class="alert-danger">
                    Username atau Password salah!
                </div>
            <?php endif; ?>

            <!-- FORM START -->
            <form action="" method="post">
                <!-- Input Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required placeholder="admin123" autocomplete="off">
                </div>
                
                <!-- Input Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Masukkan password">
                </div>
                
                <!-- Checkbox Remember Me -->
                <!-- Class 'checkbox-wrapper' mengatur flex row -->
                <div class="form-group checkbox-wrapper">
                    <!-- Class 'checkbox-input' mengatur width auto -->
                    <input type="checkbox" name="remember" id="remember" class="checkbox-input">
                    <label for="remember" class="checkbox-label">Ingat saya</label>
                </div>
                
                <!-- Tombol Submit -->
                <!-- Class 'btn-login' & 'w-full' -->
                <button type="submit" name="login" class="btn btn-primary w-full btn-login">
                    Masuk
                </button>            
            </form>

            <!-- FOOTER LINK (Register) -->
            <div class="auth-footer">
                Belum punya akun? <a href="registrasi.php" class="auth-link">Registrasi Admin</a>
            </div>
        </div>
    </div>

</body>
</html>