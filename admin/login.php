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
if( isset($_COOKIE['id']) && isset($_COOKIE['key']) ) {
    $id = (int)$_COOKIE['id']; 
    $key = $_COOKIE['key']; 

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if( $row && $key === hash('sha256', $row['username']) ) {
            init_user_session($row['id'], $row['username'], $row['nama']);
        }
    }
}

// 4. CEK SESSION (JIKA SUDAH LOGIN)
if( isset($_SESSION["login"]) && $_SESSION["login"] === true ) {
    header("Location: index.php");
    exit;
}

// 5. PROSES FORM LOGIN (SUBMIT)
if ( isset($_POST["login"]) ) {
    $username = $_POST["username"];
    $password = $_POST["password"]; 

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE username = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if( $row && password_verify($password, $row["password"]) ) {
            init_user_session($row['id'], $row['username'], $row['nama']);

            // CEK REMEMBER ME
            if( isset($_POST['remember']) ) {
                setcookie('id', $row['id'], time() + 3600, "/");
                setcookie('key', hash('sha256', $row['username']), time() + 3600, "/");
            }

            header("Location: index.php");
            exit;
        }
    }

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