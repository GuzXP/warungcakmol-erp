<?php
// FILE: user/tambah_keranjang.php
session_start();
require '../fungsi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

// Cek apakah produk ini sudah ada di keranjang?
$cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE produk_id = $id");

if (mysqli_num_rows($cek) > 0) {
    // Jika sudah ada, tambah jumlahnya (qty)
    mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE produk_id = $id");
} else {
    // Jika belum ada, masukkan data baru
    mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty) VALUES ($id, 1)");
}

echo "<script>
    alert('Berhasil ditambah ke keranjang!');
    window.location.href = 'index.php';
</script>";
exit;