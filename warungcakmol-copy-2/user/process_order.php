<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: process_order.php (BACKEND API)
 * UNTUK: Menerima data pesanan (JSON) dari Javascript, menyimpan ke Database secara transaksional,
 *         dan memicu notifikasi FCM ke aplikasi Kasir.
 */

// 1. LOAD MODUL UTAMA
// Memuat koneksi database dan fungsi kirimNotifFCM()
require 'fungsi.php';
require_once 'koneksi.php';
/** @var mysqli $koneksi */

// Set Header Response ke JSON
// Memberi tahu browser bahwa output file ini BUKAN HTML, tapi data JSON.
header('Content-Type: application/json');

// -------------------------------------------------------------------------
// 2. PARSING DATA REQUEST (Menerima Paket Data)
// -------------------------------------------------------------------------
// Mengambil raw data yang dikirim via Fetch API (Method: POST)
$inputJSON = file_get_contents('php://input');
// Decode JSON String menjadi Array PHP Asosiatif
$data = json_decode($inputJSON, true);

// Validasi Input Dasar
// Pastikan ID Meja ada dan Array Menu Items tidak kosong
if (!isset($data['meja_id']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data pesanan tidak lengkap (Meja/Menu kosong)!']);
    exit; // Stop script
}

// Ekstrak Data ke Variabel
$id_meja = (int)$data['meja_id']; // Casting ke integer untuk keamanan
$items = $data['items'];

// -------------------------------------------------------------------------
// 3. DATABASE TRANSACTION (ACID CONCEPT) - LOGIC PENTING UAS
// -------------------------------------------------------------------------
// Kita menggunakan Transaksi Database. 
// Tujuannya: Jika simpan Header berhasil tapi Detail gagal, maka Header harus dibatalkan (Rollback).
// Data Pesanan harus masuk SEMUA atau TIDAK SAMA SEKALI. Jangan sampai ada Pesanan kosong tanpa menu.

mysqli_begin_transaction($koneksi); // Mulai mode transaksi

try {
    // LANGKAH A: Hitung Total Bayar di Sisi Server (Lebih Aman)
    // Jangan percaya 100% total yang dikirim client, hitung ulang dari harga items.
    $total_bayar = 0;
    foreach ($items as $item) {
        $total_bayar += ($item['price'] * $item['qty']); // Harga x Jumlah
    }

    // LANGKAH B: Insert ke Tabel 'pesanan' (Header Pesanan)
    // Mencatat Siapa yang pesan (Meja) dan Total Tagihan.
    // Status default 'Pending' menunggu konfirmasi kasir.
    $query_header = "INSERT INTO pesanan (id_meja, total_bayar, status_pesanan) VALUES ($id_meja, $total_bayar, 'Pending')";
    
    if (!mysqli_query($koneksi, $query_header)) {
        throw new Exception("Gagal menyimpan data utama pesanan (Header Error).");
    }

    // Ambil ID Auto Increment dari pesanan yang baru saja dibuat
    $id_pesanan_baru = mysqli_insert_id($koneksi);

    // LANGKAH C: Insert ke Tabel 'detail_pesanan' (Item Menu)
    foreach ($items as $item) {
        $id_menu = (int)$item['id'];
        
        // Escape String: Mencegah error SQL jika nama menu mengandung tanda petik (misal: "Saos D'Jango")
        $nama_menu = mysqli_real_escape_string($koneksi, $item['name']); 
        
        // Snapshot Harga: Kita simpan harga SAAT INI. 
        // Jika besok harga menu naik, data histori pesanan ini tetap memakai harga lama.
        $harga = (int)$item['price'];    
        $qty = (int)$item['qty'];
        
        // Ekstrak Level Pedas & Catatan dari Payload
        $spice = isset($item['spice']) ? (int)$item['spice'] : 0;
        $level_pedas = ($spice > 0) ? "Level " . $spice : "Tidak Pedas";
        $catatan = isset($item['notes']) ? mysqli_real_escape_string($koneksi, $item['notes']) : '';

        // Query Insert Detail
        $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_menu_snapshot, harga_snapshot, jumlah, level_pedas, catatan) 
                         VALUES ($id_pesanan_baru, $id_menu, '$nama_menu', $harga, $qty, '$level_pedas', '$catatan')";
        
        if (!mysqli_query($koneksi, $query_detail)) {
            // Jika satu item gagal, lempar error agar semua dibatalkan
            throw new Exception("Gagal menyimpan detail menu: " . $nama_menu);
        }
    }

    // LANGKAH D: COMMIT (Simpan Permanen)
    // Jika kode sampai sini tanpa error, berarti semua query sukses. Simpan perubahan ke harddisk server.
    mysqli_commit($koneksi);

    // -------------------------------------------------------------------------
    // 4. KIRIM RESPON SUKSES KE FRONTEND
    // -------------------------------------------------------------------------
    echo json_encode([
        'status' => 'success',
        'message' => 'Pesanan berhasil dikirim ke dapur!',
        'order_id' => $id_pesanan_baru
    ]);

} catch (Exception $e) {
    // EXCEPTION HANDLING (JIKA ADA ERROR)
    // Batalkan semua query INSERT di atas (Rollback). Database bersih dari data sampah/setengah-setengah.
    mysqli_rollback($koneksi);
    
    // Kirim pesan error ke Frontend agar User tahu
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
    ]);
}
?>