<?php
// FILE: admin/laporan.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// FILTER TANGGAL
$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$where = "";
if ($dari != '' && $sampai != '') {
    $where = "WHERE DATE(tanggal) BETWEEN '$dari' AND '$sampai'";
}

// TOTAL TRANSAKSI
$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as grand_total,
           COUNT(*) as jumlah_transaksi
    FROM transaksi
    $where
"));

// Ambil data transaksi
$query = mysqli_query($conn, "
    SELECT * FROM transaksi
    $where
    ORDER BY id DESC
");

$grandTotal = $total['grand_total'] ?? 0;
$jumlahTransaksi = $total['jumlah_transaksi'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Cafeku</title>
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

        /* ===== CARD ===== */
        .card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }

        .card-header h3 {
            color: #c08b5c;
            font-size: 18px;
            font-weight: 600;
        }

        /* ===== FILTER ===== */
        .filter-form {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            color: #ddd;
            font-weight: 600;
            font-size: 13px;
        }

        .filter-group input[type="date"] {
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-group input[type="date"]:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }

        .filter-group .btn-filter {
            background: #c08b5c;
            color: #fff;
            padding: 10px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .filter-group .btn-filter:hover {
            background: #a67b5b;
            transform: translateY(-2px);
        }

        .filter-group .btn-reset {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .filter-group .btn-reset:hover {
            background: #333;
            transform: translateY(-2px);
        }

        /* ===== SUMMARY ===== */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 25px;
            border: 1px solid #333;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            border-color: #c08b5c;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .summary-card .icon {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .summary-card .label {
            color: #888;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .value {
            color: #ffffff;
            font-size: 28px;
            font-weight: 900;
            margin-top: 5px;
        }

        .summary-card .value.gold { color: #c08b5c; }
        .summary-card .value.green { color: #2ecc71; }
        .summary-card .value.blue { color: #3498db; }

        .summary-card.border-gold::before { background: #c08b5c; }
        .summary-card.border-green::before { background: #27ae60; }
        .summary-card.border-blue::before { background: #3498db; }

        /* ===== TABEL ===== */
        .table-wrapper {
            overflow-x: auto;
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
            white-space: nowrap;
        }

        .table-wrapper table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            color: #ddd;
            vertical-align: middle;
        }

        .table-wrapper table tbody tr:hover {
            background: #2a2a2a;
        }

        .table-wrapper table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-wrapper table tbody td .status-success {
            display: inline-block;
            background: rgba(39, 174, 96, 0.15);
            color: #2ecc71;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #27ae60;
        }

        .table-wrapper table tbody td .kode-transaksi {
            color: #c08b5c;
            font-weight: 600;
        }

        /* ===== FOOTER ===== */
        .footer-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #333;
        }

        .footer-actions .btn {
            min-width: 160px;
            justify-content: center;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px 0;
            font-style: italic;
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

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                width: 100%;
            }

            .filter-group input[type="date"] {
                width: 100%;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .summary-card .value {
                font-size: 22px;
            }

            table {
                font-size: 12px;
            }

            table thead th,
            table tbody td {
                padding: 8px 10px;
            }

            .footer-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .footer-actions .btn {
                min-width: unset;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .admin-container {
                padding: 10px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            table {
                font-size: 11px;
            }

            table thead th,
            table tbody td {
                padding: 6px 8px;
            }
        }
    </style>
</head>

<body>

<div class="admin-container">
    <!-- ===== HEADER ===== -->
    <div class="page-header">
        <div>
            <h1>📊 Laporan Penjualan</h1>
            <div class="subtitle">Lihat dan filter laporan transaksi penjualan</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== FILTER ===== -->
    <div class="card">
        <div class="card-header">
            <h3>🔍 Filter Laporan</h3>
        </div>
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="dari">📅 Tanggal Dari</label>
                <input type="date" id="dari" name="dari" value="<?= htmlspecialchars($dari) ?>">
            </div>

            <div class="filter-group">
                <label for="sampai">📅 Tanggal Sampai</label>
                <input type="date" id="sampai" name="sampai" value="<?= htmlspecialchars($sampai) ?>">
            </div>

            <div class="filter-group" style="flex-direction:row; gap:10px;">
                <button type="submit" class="btn-filter">🔍 Tampilkan</button>
                <a href="laporan.php" class="btn-reset">↻ Reset</a>
            </div>
        </form>
    </div>

    <!-- ===== SUMMARY ===== -->
    <div class="summary-grid">
        <div class="summary-card border-gold">
            <div class="icon">💰</div>
            <div class="label">Total Penjualan</div>
            <div class="value gold">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
        </div>

        <div class="summary-card border-blue">
            <div class="icon">🧾</div>
            <div class="label">Jumlah Transaksi</div>
            <div class="value blue"><?= $jumlahTransaksi ?></div>
        </div>
    </div>

    <!-- ===== TABEL ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Data Transaksi</h3>
            <?php if ($dari && $sampai): ?>
                <span style="color:#888; font-size:13px;">
                    Periode: <?= date('d M Y', strtotime($dari)) ?> - <?= date('d M Y', strtotime($sampai)) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($query) == 0): ?>
                <div class="empty-state">📭 Belum ada transaksi</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:right;">Bayar</th>
                            <th style="text-align:right;">Kembalian</th>
                            <th>Metode</th>
                            <th>Kasir</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($d = mysqli_fetch_assoc($query)):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="kode-transaksi">#<?= htmlspecialchars($d['kode_transaksi']) ?></span></td>
                                <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])) ?></td>
                                <td style="text-align:right; font-weight:600; color:#fff;">
                                    Rp <?= number_format($d['total'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align:right;">
                                    Rp <?= number_format($d['bayar'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align:right;">
                                    Rp <?= number_format($d['kembalian'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($d['metode_pembayaran'] == 'Cash'): ?>
                                        💵 <?= htmlspecialchars($d['metode_pembayaran']) ?>
                                    <?php elseif ($d['metode_pembayaran'] == 'QRIS'): ?>
                                        📱 <?= htmlspecialchars($d['metode_pembayaran']) ?>
                                    <?php else: ?>
                                        <?= htmlspecialchars($d['metode_pembayaran'] ?: '-') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($d['kasir'] ?: '-') ?></td>
                                <td>
                                    <span class="status-success">✅ Sukses</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- ===== FOOTER ACTIONS ===== -->
        <div class="footer-actions">
            <a href="cetak_laporan.php?dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>" target="_blank" class="btn btn-info">
                🖨️ Cetak Laporan
            </a>
            <a href="cetak_excel.php?dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>" class="btn btn-success">
                📊 Export Excel
            </a>
            <a href="cetakPDF.php?dari=<?= urlencode($dari) ?>&sampai=<?= urlencode($sampai) ?>" class="btn btn-danger">
                📄 Export PDF
            </a>
        </div>
    </div>

</div>

</body>
</html>