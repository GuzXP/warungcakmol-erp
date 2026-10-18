<?php
// FILE: admin/laporan_keuangan.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// TOTAL PENJUALAN
$penjualan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as total
    FROM transaksi
"));

// TOTAL UANG MASUK
$uangMasuk = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total
    FROM uang_masuk
"));

// TOTAL UANG KELUAR
$uangKeluar = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total
    FROM uang_keluar
"));

// TOTAL PEMBELIAN
$pembelian = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as total
    FROM pembelian
"));

// TOTAL GAJI
$gaji = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total
    FROM gaji_karyawan
"));

// HITUNG SALDO
$totalPenjualan = $penjualan['total'] ?? 0;
$totalMasuk = $uangMasuk['total'] ?? 0;
$totalKeluar = $uangKeluar['total'] ?? 0;
$totalPembelian = $pembelian['total'] ?? 0;
$totalGaji = $gaji['total'] ?? 0;

$saldo = ($totalPenjualan + $totalMasuk) - ($totalKeluar + $totalPembelian + $totalGaji);

// STATUS KEUANGAN
if ($saldo > 0) {
    $status = "💰 Keuangan Sehat";
    $statusClass = "success";
} elseif ($saldo == 0) {
    $status = "⚖️ Impas";
    $statusClass = "warning";
} else {
    $status = "⚠️ Keuangan Minus";
    $statusClass = "danger";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        /* ===== TEMA GELAP PROFESIONAL ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #0d0d0d;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            color: #ffffff;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #1a1a1a;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }

        /* ===== HEADER ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .page-header h1 {
            color: #c08b5c;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header .subtitle {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover {
            background: #333;
        }

        .btn-danger {
            background: #8B0000;
            color: #fff;
        }
        .btn-danger:hover {
            background: #660000;
        }

        .btn-success {
            background: #27ae60;
            color: #fff;
        }
        .btn-success:hover {
            background: #219a52;
        }

        .btn-info {
            background: #3498db;
            color: #fff;
        }
        .btn-info:hover {
            background: #2980b9;
        }

        .btn-warning {
            background: #f39c12;
            color: #fff;
        }
        .btn-warning:hover {
            background: #d68910;
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }

        /* ===== STATISTIK GRID ===== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #c08b5c;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .stat-card .stat-icon {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #888;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .stat-value {
            color: #ffffff;
            font-size: 24px;
            font-weight: 900;
            margin-top: 5px;
        }

        .stat-card.border-gold::before { background: #c08b5c; }
        .stat-card.border-green::before { background: #27ae60; }
        .stat-card.border-red::before { background: #e74c3c; }
        .stat-card.border-blue::before { background: #3498db; }
        .stat-card.border-purple::before { background: #9b59b6; }
        .stat-card.border-yellow::before { background: #f1c40f; }
        .stat-card.border-orange::before { background: #f39c12; }

        .stat-card .stat-value.gold { color: #c08b5c; }
        .stat-card .stat-value.green { color: #2ecc71; }
        .stat-card .stat-value.red { color: #e74c3c; }
        .stat-card .stat-value.blue { color: #3498db; }
        .stat-card .stat-value.purple { color: #9b59b6; }
        .stat-card .stat-value.yellow { color: #f1c40f; }
        .stat-card .stat-value.orange { color: #f39c12; }

        /* ===== STATUS BOX ===== */
        .status-box {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .status-box .status-label {
            color: #888;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .status-box .status-value {
            font-size: 36px;
            font-weight: 900;
            margin: 10px 0;
        }

        .status-box .status-value.gold { color: #c08b5c; }
        .status-box .status-value.green { color: #2ecc71; }
        .status-box .status-value.red { color: #e74c3c; }
        .status-box .status-value.yellow { color: #f1c40f; }

        .status-box .status-badge {
            display: inline-block;
            padding: 8px 25px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
        }

        .status-badge.success {
            background: rgba(39, 174, 96, 0.15);
            color: #2ecc71;
            border: 1px solid #27ae60;
        }

        .status-badge.warning {
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
            border: 1px solid #f1c40f;
        }

        .status-badge.danger {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }

        /* ===== TABEL ===== */
        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }

        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table-wrapper table thead th {
            background: #2a2a2a;
            color: #c08b5c;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #c08b5c;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-wrapper table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            color: #ddd;
        }

        .table-wrapper table tbody tr:hover {
            background: #2a2a2a;
        }

        .table-wrapper table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-wrapper table tbody tr.total-row td {
            font-weight: 700;
            font-size: 16px;
            color: #c08b5c;
            border-top: 2px solid #c08b5c;
            padding-top: 15px;
        }

        .table-wrapper table tbody td .label-icon {
            margin-right: 8px;
        }

        /* ===== FOOTER BUTTONS ===== */
        .footer-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .footer-actions .btn {
            min-width: 160px;
            justify-content: center;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .admin-container {
                padding: 15px;
            }

            .page-header {
                flex-direction: column;
                text-align: center;
            }

            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }

            .status-box .status-value {
                font-size: 28px;
            }

            table {
                font-size: 12px;
            }

            table thead th,
            table tbody td {
                padding: 8px 10px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .admin-container {
                padding: 10px;
            }

            .stat-grid {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .status-box .status-value {
                font-size: 24px;
            }

            .footer-actions .btn {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="admin-container">
    <!-- ===== HEADER ===== -->
    <div class="page-header">
        <div>
            <h1>💰 Laporan Keuangan</h1>
            <div class="subtitle">Ringkasan seluruh transaksi keuangan Cafeku</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== STATUS KEUANGAN ===== -->
    <div class="status-box">
        <div class="status-label">📊 STATUS KEUANGAN CAFE</div>
        <div class="status-value <?= $saldo > 0 ? 'green' : ($saldo == 0 ? 'yellow' : 'red') ?>">
            Rp <?= number_format($saldo, 0, ',', '.') ?>
        </div>
        <div class="status-badge <?= $statusClass ?>">
            <?= $status ?>
        </div>
    </div>

    <!-- ===== STATISTIK ===== -->
    <div class="stat-grid">
        <div class="stat-card border-gold">
            <div class="stat-icon">📈</div>
            <div class="stat-label">Total Penjualan</div>
            <div class="stat-value gold">Rp <?= number_format($totalPenjualan, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card border-green">
            <div class="stat-icon">📥</div>
            <div class="stat-label">Uang Masuk</div>
            <div class="stat-value green">Rp <?= number_format($totalMasuk, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card border-red">
            <div class="stat-icon">📤</div>
            <div class="stat-label">Uang Keluar</div>
            <div class="stat-value red">Rp <?= number_format($totalKeluar, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card border-blue">
            <div class="stat-icon">🛒</div>
            <div class="stat-label">Total Pembelian</div>
            <div class="stat-value blue">Rp <?= number_format($totalPembelian, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card border-purple">
            <div class="stat-icon">💳</div>
            <div class="stat-label">Total Gaji</div>
            <div class="stat-value purple">Rp <?= number_format($totalGaji, 0, ',', '.') ?></div>
        </div>

        <div class="stat-card border-yellow">
            <div class="stat-icon">🏦</div>
            <div class="stat-label">Saldo Akhir</div>
            <div class="stat-value yellow">Rp <?= number_format($saldo, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- ===== TABEL DETAIL ===== -->
    <div style="background: #1f1f1f; border-radius: 10px; padding: 20px; border: 1px solid #333;">
        <h3 style="color: #c08b5c; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #333;">
            📋 Rincian Keuangan
        </h3>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="label-icon">📈</span> Total Penjualan</td>
                        <td style="text-align: right; color: #2ecc71;">
                            + Rp <?= number_format($totalPenjualan, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="label-icon">📥</span> Total Uang Masuk</td>
                        <td style="text-align: right; color: #2ecc71;">
                            + Rp <?= number_format($totalMasuk, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="label-icon">📤</span> Total Uang Keluar</td>
                        <td style="text-align: right; color: #e74c3c;">
                            - Rp <?= number_format($totalKeluar, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="label-icon">🛒</span> Total Pembelian</td>
                        <td style="text-align: right; color: #e74c3c;">
                            - Rp <?= number_format($totalPembelian, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="label-icon">💳</span> Total Gaji Karyawan</td>
                        <td style="text-align: right; color: #e74c3c;">
                            - Rp <?= number_format($totalGaji, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr class="total-row">
                        <td style="font-weight: 700; font-size: 16px;">
                            🏦 <strong>Saldo Akhir</strong>
                        </td>
                        <td style="text-align: right; font-weight: 700; font-size: 16px; color: <?= $saldo >= 0 ? '#2ecc71' : '#e74c3c' ?>;">
                            <?= $saldo >= 0 ? '+' : '' ?> Rp <?= number_format($saldo, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== TOMBOL AKSI ===== -->
    <div class="footer-actions">
        <a href="cetak_laporan.php" class="btn btn-info">
            🖨️ Cetak Laporan
        </a>
        <a href="cetak_excel.php" class="btn btn-success">
            📊 Export Excel
        </a>
        <a href="cetakPDF.php" class="btn btn-danger">
            📄 Export PDF
        </a>
    </div>

</div>

</body>
</html>