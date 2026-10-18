<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: cetakPDF.php (REPORT GENERATOR)
 * UNTUK: Fitur mencetak laporan data menu ke dalam format PDF.
 * LIBRARY: Menggunakan mPDF (via Composer) untuk konversi HTML ke PDF.
 */

// 1. IMPORT LIBRARY MPDF
// Mengakses autoload.php dari folder vendor di root project.
// __DIR__ digunakan agar path relatif terhadap file ini (admin/) tetap akurat.
require_once __DIR__ . '/../vendor/autoload.php';

// 2. LOAD DATA DARI DATABASE & AUTH CHECK
require 'fungsi.php';
confirm_admin_login();

// Eksekusi Query
// Mengambil seluruh data menu diurutkan berdasarkan ID (ASC/Naik).
$menu = query("SELECT * FROM menu ORDER BY id_menu ASC");

// 3. INSTANSIASI OBJEK MPDF
// Membuat objek baru dari class mPDF.
// Konfigurasi ['orientation' => 'L'] artinya Landscape (Melebar), cocok untuk tabel kolom banyak.
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']); 

// 4. MENYUSUN STRUKTUR HTML (STRING)
// Kita membangun tampilan PDF menggunakan HTML standar yang disimpan dalam variabel string $html.
// Style CSS diletakkan di dalam tag <style> agar mPDF dapat merender-nya dengan sempurna.
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Daftar Menu</title>
    <style>
        /* Reset & Font Dasar */
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; }
        
        /* Judul Laporan */
        h1 { 
            text-align: center; 
            margin-bottom: 5px; 
            text-transform: uppercase;
        }
        
        /* Sub-Judul (Alamat) */
        .sub-header { text-align: center; margin-bottom: 20px; font-size: 10pt; color: #555; }

        /* Kotak Info Pencetak */
        .info-cetak { 
            margin-bottom: 15px; 
            font-size: 10pt; 
            border-bottom: 2px solid black; 
            padding-bottom: 10px; 
        }

        /* Styling Tabel */
        table { 
            width: 100%; 
            border-collapse: collapse; /* Hilangkan jarak antar border */
            margin-top: 10px; 
        }

        th, td { 
            border: 1px solid #333; /* Garis tabel hitam tipis */
            padding: 8px; /* Jarak isi sel */
            text-align: left; 
            vertical-align: top; /* Teks selalu di atas */
        }

        th { 
            background-color: #e2e8f0; /* Header tabel abu-abu muda */
            font-weight: bold; 
            text-align: center;
        }

        /* Utility Helper Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    
    <!-- KOP LAPORAN -->
    <h1>Nasi Goreng Cak Mol</h1>
    <div class="sub-header">Jalan Pulo Mas Barat V No.4, Kayu Putih, Jakarta Timur</div>

    <!-- INFO METADATA CETAK -->
    <div class="info-cetak">
        <strong>Laporan Data Menu</strong> <br>
        Dicetak Oleh : Admin (Bagus Ramadhan) <br>
        Tanggal      : ' . date('d F Y, H:i') . ' WIB <br>
        Total Item   : ' . count($menu) . ' Menu
    </div>

    <!-- TABEL DATA -->
    <table>
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Foto</th>
                <th width="25%">Nama Menu</th>
                <th width="15%">Harga</th>
                <th width="40%">Deskripsi & Kategori</th>
            </tr>
        </thead>
        <tbody>';

// 5. LOGIKA LOOPING (FOREACH)
// Menggabungkan (concatenate) baris HTML untuk setiap data menu ke variabel $html
$i = 1; // Iterator nomor
foreach ($menu as $row) {
    
    // Persiapan Gambar
    // Path: ../img/namafile.jpg
    $pathGambar = '../img/' . $row["gambar"];
    
    // Cek Eksistensi File Gambar
    // Jika file ada, tampilkan tag img. Jika tidak, tampilkan teks "No Image".
    if (file_exists($pathGambar) && !empty($row["gambar"])) {
        $imgTag = '<img src="' . $pathGambar . '" width="60" style="border-radius:5px;">';
    } else {
        $imgTag = '<span style="font-size:8pt; color:red;">No Image</span>';
    }

    // Append baris tabel (Row)
    $html .= '<tr>
        <td class="text-center">' . $i++ . '</td>
        <td class="text-center">' . $imgTag . '</td>
        <td>
            <strong>' . htmlspecialchars($row["nama_menu"]) . '</strong>
        </td>
        <td>Rp ' . number_format($row["harga"], 0, ',', '.') . '</td>
        <td>
            <div style="font-size: 8pt; color: #555; margin-bottom: 4px;">[' . htmlspecialchars($row["kategori"]) . ']</div>
            ' . htmlspecialchars($row["deskripsi"]) . '
        </td>
    </tr>';
}

// TUTUP TABLE & TAMBAH TANDA TANGAN
$html .= '
        </tbody>
    </table>
    
    <!-- Area Tanda Tangan (Footer) -->
    <div style="text-align: right; margin-top: 30px; font-size: 10pt;">
        <p>Mengetahui,</p>
        <br><br><br> <!-- Space untuk tanda tangan basah -->
        <p><strong>Owner Cak Mol</strong></p>
    </div>

</body>
</html>';


// 6. GENERATE OUTPUT PDF
// WriteHTML(): Proses konversi string HTML + CSS menjadi binary PDF.
$mpdf->WriteHTML($html);

// Output(): Mengirim file PDF ke browser.
// Parameter 1: Nama file output.
// Parameter 2: 'I' = Inline (Tampil di browser), 'D' = Download (Langsung unduh).
$mpdf->Output('Laporan-Menu-CakMol.pdf', 'I');

?>