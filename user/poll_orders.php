<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: poll_orders.php (POLLING ENDPOINT - SECURE)
 * UNTUK: Endpoint ringan yang dipanggil kasir.php setiap 8 detik
 *        untuk mengecek jumlah pesanan Pending terbaru dengan proteksi session.
 */

require 'fungsi.php';

// Set header: JSON, no cache
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Proteksi Session untuk AJAX
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Hitung jumlah pesanan berstatus 'Pending' secara aman
$result = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pesanan WHERE status_pesanan = 'Pending'");
$row    = mysqli_fetch_assoc($result);
$count  = (int) $row['jumlah'];

// Hitung hash dari status dapur pesanan pending untuk mendeteksi perubahan status dapur secara realtime
$result_hash = mysqli_query($koneksi, "SELECT GROUP_CONCAT(CONCAT(id_pesanan, '-', status_dapur) ORDER BY id_pesanan ASC) AS status_string FROM pesanan WHERE status_pesanan = 'Pending'");
$row_hash    = mysqli_fetch_assoc($result_hash);
$status_hash = md5($row_hash['status_string'] ?? '');

echo json_encode([
    'pending_count' => $count,
    'status_hash'   => $status_hash,
    'timestamp'     => time()
]);
?>
