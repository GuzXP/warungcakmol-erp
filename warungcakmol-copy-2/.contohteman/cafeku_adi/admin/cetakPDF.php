<?php
// FILE: admin/cetakPDF.php
require_once __DIR__ . '/../vendor/autoload.php';
require '../fungsi.php';

// PERBAIKAN: Typo 'kategari' sudah diganti jadi 'kategori'
$produk = query("SELECT * FROM produk ORDER BY kategori ASC");

$mpdf = new \Mpdf\Mpdf();

$html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Menu Cafeku</title>
    <style>
        body { font-family: sans-serif; }
        h1 { text-align: center; color: #333; margin-bottom: 20px; }
        .tabel-data { width: 100%; border-collapse: collapse; font-size: 12px; }
        .tabel-data th { background-color: #c08b5c; color: #fff; padding: 10px; border: 1px solid #333; }
        .tabel-data td { padding: 8px; border: 1px solid #333; vertical-align: middle; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Daftar Menu - Cafeku</h1>
    <p>Tanggal Cetak: ' . date("d F Y") . '</p>
    
    <table class="tabel-data">
        <tr>
            <th>No.</th>
            <th>Gambar</th>
            <th>Nama Menu</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Deskripsi</th>
        </tr>';

$i = 1;
foreach ($produk as $row) {
    $pathGambar = __DIR__ . '/../' . $row["gambar"];
    
    if (file_exists($pathGambar)) {
        $imgTag = '<img src="' . $pathGambar . '" width="50" height="50" style="object-fit:cover">';
    } else {
        $imgTag = 'No Image';
    }

    $html .= '<tr>
                <td class="text-center">' . $i++ . '</td>
                <td class="text-center">' . $imgTag . '</td>
                <td>' . $row["nama"] . '</td>
                <td class="text-center">' . ucfirst($row["kategori"]) . '</td>
                <td class="text-right">Rp ' . number_format($row["harga"], 0, ',', '.') . '</td>
                <td>' . $row["deskripsi"] . '</td>
              </tr>';
}

$html .= '</table></body></html>';

$mpdf->WriteHTML($html);
$mpdf->Output('daftar-menu-cafeku.pdf', \Mpdf\Output\Destination::INLINE);
?>