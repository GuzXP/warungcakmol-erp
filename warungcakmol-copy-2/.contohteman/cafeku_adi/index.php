<?php
// FILE: index.php (ROOT)
// Landing page untuk user biasa - redirect ke user/index.php
session_start();
require 'fungsi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    // Jika belum login, redirect ke login
    header("Location: login.php");
    exit;
}

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
        case 'owner':
            header("Location: admin/index.php");
            break;
        case 'kasir':
            header("Location: kasir/index.php");
            break;
        case 'kitchen':
            header("Location: kitchen/dashboard.php");
            break;
        case 'karyawan':
            header("Location: karyawan/index.php");
            break;
        default:
            header("Location: user/index.php");
            break;
    }
    exit;
}
