<?php
// FILE: user/hapus_keranjang.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$order_id = $_SESSION['order_id'] ?? '';
if ($order_id) {
    mysqli_query($conn, "DELETE FROM keranjang WHERE order_id = '$order_id'");
    unset($_SESSION['order_id']);
}

header("Location: pembayaran.php");
exit;
?>