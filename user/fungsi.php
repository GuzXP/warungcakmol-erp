<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: user/fungsi.php (USER CORE LOGIC)
 * UNTUK: FUNGSI BACKEND - HELPER DATABASE & FORMATTING
 * FITUR UTAMA:
 * 1. Helper Database (Query & Formatting)
 */

// 1. KONEKSI DATABASE & UTILITY
// Menggunakan koneksi terpusat agar seluruh modul berbagi koneksi & helper yang sama.
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// -----------------------------------------------------------
// KUMPULAN FUNGSI (HELPER FUNCTIONS)
// -----------------------------------------------------------

/**
 * FUNGSI 1: QUERY (READ DATA - DENGAN PREPARED STATEMENT)
 * Helper standar untuk menjalankan query SQL secara aman dengan parameterisasi.
 * @param string $query - Perintah SQL dengan placeholder (?)
 * @param array $params - Nilai parameter untuk query
 * @param string $types - Tipe data parameter (s = string, i = int, etc)
 * @return array - Array data asosiatif
 */
function query($query, $params = [], $types = '') {
    global $koneksi;
    
    if (empty($params)) {
        $result = mysqli_query($koneksi, $query);
        if (!$result) {
            return [];
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    } else {
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            return [];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            mysqli_stmt_close($stmt);
            return [];
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
