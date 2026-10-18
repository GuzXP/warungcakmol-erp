<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: koneksi.php (DATABASE CONNECTION)
 * UNTUK: Jembatan penghubung utama antara User App dengan Database MySQL.
 */

// Konfigurasi Database
$host = "localhost"; // Server Database
$user = "root";      // Username Default XAMPP
$pass = "";          // Password Default XAMPP (Kosong)
$db   = "nasgorcakmoldb"; // Nama Database Project

// Membuat Koneksi
// Menggunakan mysqli_connect() yang merupakan driver native PHP untuk MySQL.
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi (Error Handling)
// Jika variabel $koneksi bernilai false (gagal terhubung), matikan skrip.
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>
