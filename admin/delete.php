<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: delete.php (DELETE ACTION)
 * UNTUK: Script eksekutor untuk menghapus data secara aman menggunakan POST dan CSRF Token.
 */

// 1. LOAD LIBRARY & AUTH
require 'fungsi.php';

// 2. PROTEKSI LOGIN
confirm_admin_login();

// 3. VALIDASI METODE POST & CSRF TOKEN
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Tendang balik jika mencoba akses via GET
    header("Location: index.php?page=menu");
    exit;
}

if (!isset($_POST["csrf_token"]) || !check_csrf_token($_POST["csrf_token"])) {
    die("CSRF Token validation failed.");
}

// 4. TANGKAP DATA ID & CAST KE INTEGER (SECURITY)
$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;

if ($id <= 0) {
    echo "
        <script>
            alert('ID tidak valid!');
            document.location.href = 'index.php?page=menu';
        </script>
    ";
    exit;
}

// 5. JALANKAN FUNGSI HAPUS (SECURE PREPARED STATEMENT)
if (hapus($id) > 0) {
    echo "
        <script>
            alert('Data berhasil dihapus selamanya!');
            document.location.href = 'index.php?page=menu';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Data Gagal Dihapus! (Mungkin data tidak ditemukan atau ada masalah server)');
            document.location.href = 'index.php?page=menu';
        </script>
    ";
}
?>