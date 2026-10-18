<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// 1. Hapus Session
$_SESSION = [];
session_unset();
session_destroy();

// 2. Hapus Cookie (Set waktu ke masa lalu)
setcookie('id', '', time() - 3600, '/');
setcookie('key', '', time() - 3600, '/');

// 3. Lempar ke Login
header("Location: login.php");
exit;
?>