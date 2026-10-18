<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: process_order.php (BACKEND API - SECURE)
 * UNTUK: Menerima data pesanan (JSON) dari Javascript, memvalidasi harga dengan database,
 *         dan menyimpan ke Database secara transaksional dengan Prepared Statements.
 */

// 1. LOAD MODUL UTAMA (fungsi.php mengimpor koneksi & helpers terpusat)
require 'fungsi.php';

// Set Header Response ke JSON
header('Content-Type: application/json');

// 2. PARSING DATA REQUEST (Menerima Paket Data)
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Validasi Input Dasar
if (!isset($data['meja_id']) || empty($data['items'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data pesanan tidak lengkap (Meja/Menu kosong)!']);
    exit;
}

$id_meja = (int)$data['meja_id']; 
$items = $data['items'];

// 3. DATABASE TRANSACTION (ACID CONCEPT)
mysqli_begin_transaction($koneksi);

try {
    // Persiapan statement untuk mengambil detail menu dari database (mencegah manipulasi harga dari client)
    $stmt_menu = mysqli_prepare($koneksi, "SELECT nama_menu, harga FROM menu WHERE id_menu = ?");
    if (!$stmt_menu) {
        throw new Exception("Sistem error saat memproses pengecekan menu.");
    }

    // LANGKAH A: Hitung Total Bayar di Sisi Server dari database resmi
    $total_bayar = 0;
    foreach ($items as $item) {
        $id_menu = (int)$item['id'];
        $qty = (int)$item['qty'];
        if ($qty <= 0) {
            throw new Exception("Jumlah pesanan tidak valid.");
        }

        mysqli_stmt_bind_param($stmt_menu, "i", $id_menu);
        mysqli_stmt_execute($stmt_menu);
        $res = mysqli_stmt_get_result($stmt_menu);
        $menu_db = mysqli_fetch_assoc($res);

        if (!$menu_db) {
            throw new Exception("Menu tidak ditemukan.");
        }

        $total_bayar += ((int)$menu_db['harga'] * $qty);
    }

    // Ambil nama_pelanggan dan tipe_pesanan dari request payload secara aman
    $nama_pelanggan = isset($data['nama_pelanggan']) ? trim($data['nama_pelanggan']) : '';
    $nama_pelanggan_val = !empty($nama_pelanggan) ? sanitize($nama_pelanggan) : null;
    
    $tipe_pesanan = isset($data['tipe_pesanan']) ? trim($data['tipe_pesanan']) : 'Dine In';
    if (!in_array($tipe_pesanan, ['Dine In', 'Takeaway'])) {
        $tipe_pesanan = 'Dine In';
    }

    // Validasi berdasarkan tipe pesanan
    if ($tipe_pesanan === 'Takeaway') {
        $meja_val = null;
        if (empty($nama_pelanggan_val)) {
            throw new Exception("Nama pelanggan wajib diisi untuk pemesanan bawa pulang (Takeaway).");
        }
    } else {
        if ($id_meja <= 0) {
            throw new Exception("Nomor meja wajib dipilih untuk makan di tempat (Dine In).");
        }
        $meja_val = $id_meja;
    }

    // LANGKAH B: Insert ke Tabel 'pesanan' (Header Pesanan) menggunakan Prepared Statement
    $stmt_header = mysqli_prepare($koneksi, "INSERT INTO pesanan (id_meja, total_bayar, status_pesanan, nama_pelanggan, tipe_pesanan) VALUES (?, ?, 'Pending', ?, ?)");
    if (!$stmt_header) {
        throw new Exception("Gagal mempersiapkan data utama pesanan.");
    }
    mysqli_stmt_bind_param($stmt_header, "iiss", $meja_val, $total_bayar, $nama_pelanggan_val, $tipe_pesanan);
    if (!mysqli_stmt_execute($stmt_header)) {
        throw new Exception("Gagal menyimpan data utama pesanan.");
    }
    $id_pesanan_baru = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt_header);

    // LANGKAH C: Insert ke Tabel 'detail_pesanan' (Item Menu) menggunakan Prepared Statement
    $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_menu_snapshot, harga_snapshot, jumlah, level_pedas, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_detail) {
        throw new Exception("Gagal mempersiapkan penyimpanan detail menu.");
    }

    foreach ($items as $item) {
        $id_menu = (int)$item['id'];
        $qty = (int)$item['qty'];

        // Dapatkan snapshot detail resmi dari database
        mysqli_stmt_bind_param($stmt_menu, "i", $id_menu);
        mysqli_stmt_execute($stmt_menu);
        $res = mysqli_stmt_get_result($stmt_menu);
        $menu_db = mysqli_fetch_assoc($res);

        $nama_menu_snapshot = $menu_db['nama_menu'];
        $harga_snapshot = (int)$menu_db['harga'];

        // Ekstrak Level Pedas & Catatan
        $spice = isset($item['spice']) ? (int)$item['spice'] : 0;
        $level_pedas = ($spice > 0) ? "Level " . $spice : "Tidak Pedas";
        $catatan = isset($item['notes']) ? sanitize($item['notes']) : '';

        mysqli_stmt_bind_param($stmt_detail, "iisiiss", $id_pesanan_baru, $id_menu, $nama_menu_snapshot, $harga_snapshot, $qty, $level_pedas, $catatan);
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception("Gagal menyimpan detail menu: " . $nama_menu_snapshot);
        }
    }

    mysqli_stmt_close($stmt_detail);
    mysqli_stmt_close($stmt_menu);

    // LANGKAH D: COMMIT (Simpan Permanen jika semua lancar)
    mysqli_commit($koneksi);

    // Kirim respon sukses
    echo json_encode([
        'status' => 'success',
        'message' => 'Pesanan berhasil dikirim ke dapur!',
        'order_id' => $id_pesanan_baru
    ]);

} catch (Exception $e) {
    // Rollback jika terjadi kegagalan
    mysqli_rollback($koneksi);
    
    // Log error secara internal (dapat ditambah di masa depan) dan kirim pesan generic aman ke client
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan sistem saat memproses pesanan Anda. Silakan coba lagi.'
    ]);
}
?>