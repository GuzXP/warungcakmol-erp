<?php
require '../fungsi.php';
$id = $_GET['id'];
$cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE produk_id = $id");
if (mysqli_num_rows($cek) > 0) {
    mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE produk_id = $id");
} else {
    mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty) VALUES ($id, 1)");
}
header("Location: pembayaran.php");