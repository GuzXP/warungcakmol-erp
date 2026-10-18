<?php
/**
 * FILE: includes/helpers.php (CENTRAL UTILITY & HELPER FUNCTIONS)
 * UNTUK: Sanitasi, proteksi CSRF, formatting keuangan, dan fungsi penunjang lainnya.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mengubah angka integer menjadi string format rupiah (cth: 20000 -> "Rp 20.000")
 * @param int $angka
 * @return string
 */
function formatRupiah($angka) {
    return "Rp " . number_format((int)$angka, 0, ',', '.');
}

/**
 * Membuat dan mendapatkan token CSRF baru untuk dimasukkan ke form
 * @return string
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Memvalidasi apakah token CSRF yang dikirim dari POST valid
 * @param string $token
 * @return bool
 */
function check_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Menyaring input teks agar bersih dari ancaman XSS
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
