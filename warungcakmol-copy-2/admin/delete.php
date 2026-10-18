<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: delete.php (DELETE ACTION)
 * UNTUK: Script eksekutor untuk menghapus data. 
 * LOGIKA: Menerima ID via URL (GET), Hapus File Fisik, Hapus Record Database.
 */

// 1. START SESSION
session_start();

// 2. PROTEKSI LOGIN
// Hanya admin yang login yang boleh menghapus data.
if( !isset($_SESSION["login"]) ) {
    header("Location: login.php");
    exit;
}

// 3. LOAD LIBRARY FUNGSI
require 'fungsi.php';

// 4. TANGKAP DATA ID
// Mengambil 'id' yang dikirim melalui URL saat tombol hapus di index.php diklik.
// Contoh URL: admin/delete.php?id=5
$id = $_GET["id"];

// 5. JALANKAN FUNGSI HAPUS
// Memanggil fungsi hapus() di fungsi.php.
// Fungsi tersebut menghandle: unlink gambar & query DELETE SQL.
if (hapus($id) > 0) {
    // Jika return > 0 (berhasil), tampilkan alert & balik ke dashboard.
    echo "
        <script>
            alert('Data berhasil dihapus selamanya!');
            document.location.href = 'index.php'; // Redirect JS
        </script>
    ";
} else {
    // Jika return 0/-1 (gagal), beri tahu user.
    echo "
        <script>
            alert('Data Gagal Dihapus! (Mungkin data tidak ditemukan atau ada masalah server)');
            document.location.href = 'index.php';
        </script>
    ";
}

?>