<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: logout.php (DESTROY SESSION)
 * UNTUK: Modul untuk mengakhiri sesi pengguna secara aman dan menyeluruh.
 */

// 1. AKTIFKAN SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. BERSIHKAN VARIABEL SESSION
$_SESSION = [];

// Menghapus cookie session dari browser (PHPSESSID)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan session di server
session_destroy();

// 3. MATIKAN COOKIE (REMEMBER ME) - Set path ke "/"
setcookie('id', '', time() - 3600, "/");
setcookie('key', '', time() - 3600, "/");

// 4. ALIHAKAN KE LOGIN
header("Location: login.php");
exit;
?>