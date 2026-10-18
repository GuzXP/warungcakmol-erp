<?php
// FILE: user/update_qty.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$aksi = $_GET['aksi'] ?? '';
$qty = intval($_GET['qty'] ?? 1);
$order_id = $_SESSION['order_id'] ?? '';

if ($id && $order_id) {
    if ($aksi == 'tambah') {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + $qty WHERE produk_id = $id AND order_id = '$order_id'");
    } elseif ($aksi == 'kurang') {
        // Cek qty sekarang
        $cek = query("SELECT qty FROM keranjang WHERE produk_id = $id AND order_id = '$order_id'");
        if (!empty($cek) && $cek[0]['qty'] > 1) {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty - $qty WHERE produk_id = $id AND order_id = '$order_id'");
        } else {
            // Jika qty 1, hapus item
            mysqli_query($conn, "DELETE FROM keranjang WHERE produk_id = $id AND order_id = '$order_id'");
        }
    }
}

header("Location: pembayaran.php");
exit;
?>