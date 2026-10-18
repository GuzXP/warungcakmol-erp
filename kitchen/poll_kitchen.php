<?php
/**
 * FILE: kitchen/poll_kitchen.php (JSON API)
 * UNTUK: Polling real-time bagi Kitchen Display System (KDS) untuk mendapatkan list pesanan yang harus dimasak.
 */

require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Ambil pesanan yang aktif di dapur (status_dapur: Menunggu atau Dimasak, dan status_pesanan masih Pending)
// Diurutkan berdasarkan waktu_pesan ASC agar pesanan terlama dimasak lebih dulu (FIFO)
$query = "SELECT p.id_pesanan, p.id_meja, p.waktu_pesan, p.nama_pelanggan, p.tipe_pesanan, p.status_dapur, m.nama_meja 
          FROM pesanan p 
          LEFT JOIN meja m ON p.id_meja = m.id_meja 
          WHERE p.status_pesanan = 'Pending' AND p.status_dapur IN ('Menunggu', 'Dimasak')
          ORDER BY p.waktu_pesan ASC";

$result = mysqli_query($koneksi, $query);
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $id_pesanan = $row['id_pesanan'];
    
    // Ambil detail items dari pesanan tersebut
    $stmt_detail = mysqli_prepare($koneksi, "SELECT nama_menu_snapshot, jumlah, level_pedas, catatan FROM detail_pesanan WHERE id_pesanan = ?");
    mysqli_stmt_bind_param($stmt_detail, "i", $id_pesanan);
    mysqli_stmt_execute($stmt_detail);
    $res_detail = mysqli_stmt_get_result($stmt_detail);
    
    $items = [];
    while ($item = mysqli_fetch_assoc($res_detail)) {
        $items[] = [
            'nama' => sanitize($item['nama_menu_snapshot']),
            'jumlah' => (int)$item['jumlah'],
            'level_pedas' => sanitize($item['level_pedas']),
            'catatan' => sanitize($item['catatan'])
        ];
    }
    mysqli_stmt_close($stmt_detail);
    
    $orders[] = [
        'id' => (int)$row['id_pesanan'],
        'nama_meja' => $row['nama_meja'] ? sanitize($row['nama_meja']) : 'Takeaway',
        'waktu' => date('H:i', strtotime($row['waktu_pesan'])),
        'nama_pelanggan' => $row['nama_pelanggan'] ? sanitize($row['nama_pelanggan']) : '',
        'tipe_pesanan' => sanitize($row['tipe_pesanan']),
        'status_dapur' => sanitize($row['status_dapur']),
        'items' => $items
    ];
}

echo json_encode($orders);
?>
