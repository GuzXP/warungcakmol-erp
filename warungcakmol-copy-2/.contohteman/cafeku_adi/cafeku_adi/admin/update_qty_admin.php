<?php
require '../fungsi.php';
$id = $_GET['id'];
$aksi = $_GET['aksi'];
if ($aksi == 'tambah') {
    mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE produk_id = $id");
} else {
    $res = mysqli_query($conn, "SELECT qty FROM keranjang WHERE produk_id = $id");
    $d = mysqli_fetch_assoc($res);
    if ($d['qty'] > 1) {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty - 1 WHERE produk_id = $id");
    } else {
        mysqli_query($conn, "DELETE FROM keranjang WHERE produk_id = $id");
    }
}
header("Location: pembayaran.php");