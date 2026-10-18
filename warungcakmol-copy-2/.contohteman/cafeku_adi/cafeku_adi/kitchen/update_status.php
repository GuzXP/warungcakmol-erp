<?php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'kitchen') {
    header('Location: ../login.php');
    exit;
}

$orderId = isset($_GET['order']) ? (int) $_GET['order'] : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$allowedStatuses = ['Diproses', 'Selesai'];

if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
    $_SESSION['kitchen_error'] = 'Permintaan update status tidak valid.';
    header('Location: pesanan.php');
    exit;
}

$statusColumnExists = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status_pesanan'")) === 1;
if (!$statusColumnExists) {
    $_SESSION['kitchen_error'] = 'Kolom status_pesanan tidak ditemukan di tabel transaksi.';
    header('Location: pesanan.php');
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE transaksi SET status_pesanan = ? WHERE id = ?");
if (!$stmt) {
    $_SESSION['kitchen_error'] = 'Gagal mempersiapkan query update.';
    header('Location: pesanan.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'si', $status, $orderId);
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['kitchen_success'] = 'Status pesanan berhasil diperbarui menjadi ' . htmlspecialchars($status) . '.';
} else {
    $_SESSION['kitchen_error'] = 'Gagal memperbarui status pesanan: ' . mysqli_error($conn);
}

header('Location: pesanan.php');
exit;
