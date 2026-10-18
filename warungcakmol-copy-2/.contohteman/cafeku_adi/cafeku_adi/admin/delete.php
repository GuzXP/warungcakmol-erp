<?php
// FILE: admin/delete.php
// FUNGSI: Menghapus Data

session_start();

if (!isset($_SESSION["login"])) {
    header("Location: login.php");
    exit;
}

require '../fungsi.php';

// Ambil ID dari URL (delete.php?id=5)
$id = $_GET["id"];

// Jalankan fungsi hapus()
if (hapus($id) > 0) {
    echo "
        <script>
            alert('Data Berhasil Dihapus!');
            document.location.href = 'index.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Data Gagal Dihapus!');
            document.location.href = 'index.php';
        </script>
    ";
}
?>