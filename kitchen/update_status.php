<?php
/**
 * FILE: kitchen/update_status.php (JSON API)
 * UNTUK: Memperbarui status dapur suatu pesanan (Menunggu -> Dimasak -> Siap).
 */

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

// Pastikan request menggunakan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Ambil input JSON
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

$id_pesanan = isset($data['id_pesanan']) ? (int)$data['id_pesanan'] : 0;
$status_dapur = isset($data['status_dapur']) ? trim($data['status_dapur']) : '';

// Validasi input
if ($id_pesanan <= 0 || !in_array($status_dapur, ['Dimasak', 'Siap'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid.']);
    exit;
}

// Lakukan update menggunakan prepared statement
$stmt = mysqli_prepare($koneksi, "UPDATE pesanan SET status_dapur = ? WHERE id_pesanan = ? AND status_pesanan = 'Pending'");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error.']);
    exit;
}

mysqli_stmt_bind_param($stmt, "si", $status_dapur, $id_pesanan);
if (mysqli_stmt_execute($stmt)) {
    // Jika status diubah menjadi Siap, kita juga bisa set status_meja menjadi 'Terisi' jika bertipe Dine In
    if ($status_dapur === 'Siap') {
        // Ambil info pesanan untuk mendapatkan id_meja
        $stmt_meja = mysqli_prepare($koneksi, "SELECT id_meja, tipe_pesanan FROM pesanan WHERE id_pesanan = ?");
        mysqli_stmt_bind_param($stmt_meja, "i", $id_pesanan);
        mysqli_stmt_execute($stmt_meja);
        $res = mysqli_stmt_get_result($stmt_meja);
        $order = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt_meja);
        
        if ($order && $order['tipe_pesanan'] === 'Dine In' && $order['id_meja'] > 0) {
            // Update status meja menjadi Terisi karena pesanan sudah diantar/siap
            $stmt_update_meja = mysqli_prepare($koneksi, "UPDATE meja SET status_meja = 'Terisi' WHERE id_meja = ?");
            mysqli_stmt_bind_param($stmt_update_meja, "i", $order['id_meja']);
            mysqli_stmt_execute($stmt_update_meja);
            mysqli_stmt_close($stmt_update_meja);
        }
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Status dapur berhasil diperbarui.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui status.']);
}

mysqli_stmt_close($stmt);
?>
