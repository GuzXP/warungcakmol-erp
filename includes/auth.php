<?php
/**
 * FILE: includes/auth.php (CENTRAL AUTHENTICATION HELPER)
 * UNTUK: Session management dan proteksi halaman.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memastikan user sudah login sebagai Admin/Kasir.
 * Jika belum login, redirect ke halaman login admin.
 */
function confirm_admin_login() {
    if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Memastikan kasir sudah login (karena menggunakan akun admin yang sama, panggil check yang sama).
 */
function confirm_kasir_login() {
    if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
        // Redirect ke login admin jika belum masuk
        header("Location: ../admin/login.php");
        exit;
    }
}

/**
 * Menginisialisasi session user dengan data lengkap
 */
function init_user_session($id, $username, $nama) {
    // Regenerasi session ID untuk mencegah Session Fixation
    session_regenerate_id(true);
    $_SESSION["login"] = true;
    $_SESSION["user_id"] = $id;
    $_SESSION["username"] = $username;
    $_SESSION["nama"] = $nama;
}
?>
