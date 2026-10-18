<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: logout.php (DESTROY SESSION)
 * UNTUK: Modul untuk mengakhiri sesi pengguna (Keamanan Logout).
 * LOGIKA: Menghapus variabel session server dan menghapus cookie client.
 */

// 1. AKTIFKAN SESSION
session_start();

// 2. BERSIHKAN VARIABEL SESSION
// Mengosongkan array $_SESSION agar data user hilang dari memori saat ini.
$_SESSION = [];

// Menghapus variabel session dari server.
session_unset();

// Menghancurkan session ID (SID) fisik di server.
session_destroy();

// 3. MATIKAN COOKIE (REMEMBER ME)
// Cara menghapus cookie adalah dengan menimpa cookie lama dengan waktu kadaluarsa masa lalu (time - 3600).
setcookie('id', '', time() - 3600);
setcookie('key', '', time() - 3600);

// 4. ALIHAKAN KE LOGIN
// Redirect user kembali ke halaman login setelah bersih-bersih selesai.
header("Location: login.php");
exit;
?>