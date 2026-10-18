<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: user/fungsi.php (USER CORE LOGIC)
 * UNTUK: FUNGSI BACKEND - HELPER DATABASE & FORMATTING
 * FITUR UTAMA:
 * 1. Helper Database (Query & Formatting)
 */

// 1. KONEKSI DATABASE
// Menggunakan koneksi.php agar User menggunakan satu jalur database yang sama.
require_once 'koneksi.php';

// -----------------------------------------------------------
// KUMPULAN FUNGSI (HELPER FUNCTIONS)
// -----------------------------------------------------------

/**
 * FUNGSI 1: QUERY (READ DATA)
 * Helper standar untuk menjalankan query SQL dan mengambil hasil array.
 * @param string $query - Perintah SQL
 * @return array - Array data asosiatif
 */
function query($query) {
    global $koneksi; // Akses variabel global dari koneksi.php
    
    $result = mysqli_query($koneksi, $query);
    $rows = [];
    
    // Fetch data baris per baris
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

/**
 * FUNGSI 2: FORMAT RUPIAH
 * Mengubah angka integer (cth: 15000) menjadi string "Rp 15.000".
 * @param int $angka
 * @return string
 */
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
