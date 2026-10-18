<?php
/**
 * FILE: includes/koneksi.php (CENTRAL DATABASE CONNECTION)
 * UNTUK: Jembatan penghubung utama ke database MySQL/MariaDB dengan setting charset UTF-8.
 */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "nasgorcakmoldb";

// Membuat Koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// Set charset ke utf8mb4 untuk keamanan & emoji/karakter khusus
mysqli_set_charset($koneksi, 'utf8mb4');
?>
