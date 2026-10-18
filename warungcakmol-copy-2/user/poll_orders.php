<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: poll_orders.php (POLLING ENDPOINT)
 * UNTUK: Endpoint ringan yang dipanggil kasir.php setiap 8 detik
 *        untuk mengecek jumlah pesanan Pending terbaru.
 *
 * CARA KERJA:
 *   1. Kasir.php memuat halaman → catat jumlah pending awal di JS.
 *   2. Setiap 8 detik, JS fetch ke sini → dapat angka terbaru.
 *   3. Jika angka berubah → location.reload() → antrian tampil ter-update.
 *
 * KEUNGGULAN:
 *   - Tidak membutuhkan Firebase / push notification / service worker.
 *   - Tidak membutuhkan koneksi internet eksternal.
 *   - Response sangat kecil (~30 byte JSON), hemat bandwidth.
 */

require_once 'koneksi.php';
/** @var mysqli $koneksi */

// Set header: JSON, no cache
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Hitung jumlah pesanan berstatus 'Pending'
$result = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pesanan WHERE status_pesanan = 'Pending'");
$row    = mysqli_fetch_assoc($result);

echo json_encode([
    'pending_count' => (int) $row['jumlah'],
    'timestamp'     => time()
]);
?>
