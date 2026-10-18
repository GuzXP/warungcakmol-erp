<?php
/**
 * FILE: admin/cetakLaporan.php (FINANCIAL PDF REPORT GENERATOR)
 * UNTUK: Mengekspor Laporan Keuangan (Pemasukan, Pengeluaran, Laba/Rugi) ke PDF menggunakan mPDF.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require 'fungsi.php';
confirm_admin_login();

// 1. DATA ACQUISITION
// Pemasukan dari pesanan selesai
$pemasukan_orders = query("
    SELECT id_pesanan AS ref_id, 'Pesanan Kasir' AS sumber, total_bayar AS jumlah, DATE(waktu_pesan) AS tanggal, tipe_pesanan AS keterangan 
    FROM pesanan 
    WHERE status_pesanan = 'Selesai'
");

// Pemasukan manual
$pemasukan_manual = query("
    SELECT id AS ref_id, sumber, jumlah, tanggal, keterangan 
    FROM uang_masuk
");

// Gabungkan pemasukan
$all_pemasukan = array_merge($pemasukan_orders, $pemasukan_manual);
// Sort desc by tanggal
usort($all_pemasukan, function($a, $b) {
    return strcmp($b['tanggal'], $a['tanggal']);
});

// Pengeluaran
$all_pengeluaran = query("SELECT * FROM uang_keluar ORDER BY tanggal DESC");

// Total Hitungan
$tot_pemasukan = 0;
foreach($all_pemasukan as $p) $tot_pemasukan += $p['jumlah'];

$tot_pengeluaran = 0;
foreach($all_pengeluaran as $p) $tot_pengeluaran += $p['jumlah'];

$laba_rugi = $tot_pemasukan - $tot_pengeluaran;

// 2. MPDF INSTANTIATION
$mpdf = new \Mpdf\Mpdf(['orientation' => 'P']); // Portrait orientation

// 3. HTML CONTENT
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Keuangan POS</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #333; }
        h1 { text-align: center; font-size: 16pt; text-transform: uppercase; margin-bottom: 2px; }
        .sub-header { text-align: center; margin-bottom: 20px; font-size: 9pt; color: #555; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9pt; }
        .info-table td { padding: 3px 0; }
        .divider { border-top: 2px solid #000; margin: 10px 0; }
        
        /* Summary Card Styling */
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .summary-title { font-weight: bold; font-size: 11pt; color: #1e293b; margin-bottom: 10px; text-transform: uppercase; }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; vertical-align: top; }
        .data-table th { background-color: #f1f5f9; font-weight: bold; text-align: left; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .success-text { color: #16a34a; }
        .danger-text { color: #dc2626; }
    </style>
</head>
<body>

    <!-- KOP LAPORAN -->
    <h1>Nasi Goreng Cak Mol</h1>
    <div class="sub-header">Jalan Pulo Mas Barat V No.4, Kayu Putih, Jakarta Timur</div>
    
    <table class="info-table">
        <tr>
            <td width="50%"><strong>LAPORAN KEUANGAN (LABA / RUGI)</strong></td>
            <td width="50%" class="text-right">Tanggal Cetak: ' . date('d/m/Y H:i') . ' WIB</td>
        </tr>
        <tr>
            <td>Dicetak Oleh: ' . sanitize($_SESSION['nama']) . ' (Admin)</td>
            <td class="text-right">Periode: Semua Data</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <!-- RINGKASAN LABA RUGI -->
    <div class="summary-box">
        <div class="summary-title">Ikhtisar Keuangan</div>
        <table style="width: 100%;">
            <tr>
                <td width="40%">Total Pemasukan (Omzet & Lainnya)</td>
                <td width="5%">:</td>
                <td width="55%" class="bold success-text">Rp ' . number_format($tot_pemasukan, 0, ',', '.') . '</td>
            </tr>
            <tr>
                <td>Total Pengeluaran (Belanja & Operasional)</td>
                <td>:</td>
                <td class="bold danger-text">Rp ' . number_format($tot_pengeluaran, 0, ',', '.') . '</td>
            </tr>
            <tr style="border-top: 1px solid #e2e8f0;">
                <td class="bold" style="padding-top: 8px;">NET LABA / RUGI</td>
                <td class="bold" style="padding-top: 8px;">:</td>
                <td class="bold" style="padding-top: 8px; font-size: 12pt; color: ' . ($laba_rugi >= 0 ? '#16a34a' : '#dc2626') . ';">
                    Rp ' . number_format($laba_rugi, 0, ',', '.') . '
                </td>
            </tr>
        </table>
    </div>

    <!-- DETAIL PEMASUKAN -->
    <h3 style="margin-bottom: 8px; font-size: 11pt; color: #1e293b;">A. Rincian Pemasukan</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="50%">Sumber / Keterangan</th>
                <th width="30%" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>';
        
        $i = 1;
        foreach ($all_pemasukan as $p) {
            $html .= '
            <tr>
                <td class="text-center">' . $i++ . '</td>
                <td>' . date('d/m/Y', strtotime($p['tanggal'])) . '</td>
                <td>
                    <div class="bold">' . sanitize($p['sumber']) . '</div>
                    <div style="font-size: 8pt; color: #64748b;">' . sanitize($p['keterangan']) . '</div>
                </td>
                <td class="text-right bold success-text">Rp ' . number_format($p['jumlah'], 0, ',', '.') . '</td>
            </tr>';
        }
        
$html .= '
        </tbody>
    </table>

    <!-- DETAIL PENGELUARAN -->
    <h3 style="margin-bottom: 8px; font-size: 11pt; color: #1e293b;">B. Rincian Pengeluaran</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Tanggal</th>
                <th width="50%">Jenis & Keterangan</th>
                <th width="30%" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>';
        
        $i = 1;
        foreach ($all_pengeluaran as $p) {
            $html .= '
            <tr>
                <td class="text-center">' . $i++ . '</td>
                <td>' . date('d/m/Y', strtotime($p['tanggal'])) . '</td>
                <td>
                    <div class="bold">' . sanitize($p['jenis']) . '</div>
                    <div style="font-size: 8pt; color: #64748b;">' . sanitize($p['keterangan']) . '</div>
                </td>
                <td class="text-right bold danger-text">Rp ' . number_format($p['jumlah'], 0, ',', '.') . '</td>
            </tr>';
        }
        
        if (empty($all_pengeluaran)) {
            $html .= '<tr><td colspan="4" class="text-center" style="color:#64748b; font-style:italic;">Tidak ada data pengeluaran.</td></tr>';
        }

$html .= '
        </tbody>
    </table>

</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output('Laporan-Keuangan-' . date('Ymd') . '.pdf', \Mpdf\Output\Destination::INLINE);
?>
