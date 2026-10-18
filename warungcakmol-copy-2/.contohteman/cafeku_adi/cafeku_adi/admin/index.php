<?php
// FILE: admin/index.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner', 'manager'])) {
    header('Location: ../login.php');
    exit;
}

$isAdmin = in_array($_SESSION['role'], ['admin', 'owner']);
$isManager = $_SESSION['role'] === 'manager';

// Ambil data statistik
$totalUsers = query("SELECT COUNT(*) as total FROM users");
$totalTransaksi = query("SELECT COUNT(*) as total FROM transaksi");
$totalProduk = query("SELECT COUNT(*) as total FROM produk");

$userCount = $totalUsers[0]['total'] ?? 0;
$transaksiCount = $totalTransaksi[0]['total'] ?? 0;
$produkCount = $totalProduk[0]['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #1a1a1a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #1f1f1f;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
        }
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
        }
        .page-header .role-badge {
            background: #2a2a2a;
            padding: 5px 15px;
            border-radius: 20px;
            color: #c08b5c;
            font-size: 13px;
            border: 1px solid #c08b5c;
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
            display: inline-block;
        }
        .btn-danger { background: #8B0000; color: #fff; }
        .btn-danger:hover { background: #660000; transform: translateY(-2px); }
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-success { background: #27ae60; color: #fff; }
        .btn-success:hover { background: #219a52; transform: translateY(-2px); }
        .btn-purple {
            background: #9b59b6;
            color: #fff;
        }
        .btn-purple:hover { background: #8e44ad; transform: translateY(-2px); }
        .btn-warning { background: #f39c12; color: #fff; }
        .btn-warning:hover { background: #d68910; transform: translateY(-2px); }
        .btn-info { background: #3498db; color: #fff; }
        .btn-info:hover { background: #2980b9; transform: translateY(-2px); }
        
        .admin-grid {
            display: flex;
            gap: 20px;
        }
        .navbar {
            width: 220px;
            flex-shrink: 0;
            background: #2a2a2a;
            border-radius: 10px;
            padding: 10px 0;
            border: 1px solid #333;
        }
        .navbar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .navbar ul li a {
            display: block;
            padding: 12px 20px;
            color: #c08b5c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }
        .navbar ul li a:hover {
            background: rgba(192, 139, 92, 0.1);
            padding-left: 28px;
        }
        .navbar ul li a.active {
            background: rgba(192, 139, 92, 0.15);
            border-right: 3px solid #c08b5c;
        }
        
        .admin-main {
            flex: 1;
        }
        .card {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
        }
        .card h3 {
            color: #c08b5c;
            margin-top: 0;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 900;
            color: #c08b5c;
        }
        .stat-card .label {
            color: #888;
            font-size: 13px;
            margin-top: 5px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .menu-item {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
        }
        .menu-item:hover {
            transform: translateY(-3px);
            border-color: #c08b5c;
        }
        .menu-item .icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        .menu-item .title {
            color: #ddd;
            font-weight: 600;
            font-size: 14px;
        }
        .menu-item .desc {
            color: #888;
            font-size: 11px;
            margin-top: 3px;
        }
        @media (max-width: 768px) {
            .admin-grid { flex-direction: column; }
            .navbar { width: 100%; }
            .menu-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1><?= $isManager ? '👨‍💼 Dashboard Manager' : '☕ Dashboard Admin' ?></h1>
                <span class="role-badge"><?= ucfirst($_SESSION['role'] ?? 'Admin') ?></span>
            </div>
            <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>

        <div class="admin-grid">
            <div class="navbar">
                <ul>
                    <li><a href="index.php" class="active">📊 Dashboard</a></li>
                    <?php if ($isAdmin): ?>
                        <li><a href="pembayaran.php">🧾 Kasir</a></li>
                        <li><a href="produk.php">📦 Produk</a></li>
                        <li><a href="laporan.php">📊 Laporan</a></li>
                        <li><a href="laporan_keuangan.php">💰 Laporan Keuangan</a></li>
                        <li><a href="pembelian.php">🛒 Pembelian</a></li>
                        <li><a href="uang_masuk.php">📥 Uang Masuk</a></li>
                        <li><a href="uang_keluar.php">📤 Uang Keluar</a></li>
                        <li><a href="gaji.php">💳 Gaji</a></li>
                        <li><a href="karyawan.php">👥 Karyawan</a></li>
                        <li><a href="tugas_belanja.php">📋 Tugas Belanja</a></li>
                        <li><a href="cetak_excel.php">📊 Cetak Excel</a></li>
                        <li><a href="cetakPDF.php">📄 Cetak PDF</a></li>
                        <li><a href="cetak_laporan.php">🖨️ Cetak Laporan</a></li>
                    <?php endif; ?>
                    <?php if ($isAdmin || $isManager): ?>
                        <li><a href="manager.php">👨‍💼 Manager</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="admin-main">
                <!-- Statistik -->
                <div class="stat-row">
                    <div class="stat-card">
                        <div class="number"><?= $userCount ?></div>
                        <div class="label">👥 Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="number"><?= $transaksiCount ?></div>
                        <div class="label">🧾 Transaksi</div>
                    </div>
                    <div class="stat-card">
                        <div class="number"><?= $produkCount ?></div>
                        <div class="label">📦 Produk</div>
                    </div>
                </div>

                <!-- Menu Cepat -->
                <div class="card">
                    <h3>⚡ Menu Cepat</h3>
                    <div class="menu-grid">
                        <!-- Kasir -->
                        <a href="pembayaran.php" class="menu-item">
                            <div class="icon">🧾</div>
                            <div class="title">Kasir</div>
                            <div class="desc">Buka kasir & cetak struk</div>
                        </a>

                        <!-- Manager -->
                        <a href="manager.php" class="menu-item">
                            <div class="icon">👨‍💼</div>
                            <div class="title">Manager</div>
                            <div class="desc">Kelola libur, tugas & shift</div>
                        </a>

                        <!-- Produk -->
                        <a href="produk.php" class="menu-item">
                            <div class="icon">📦</div>
                            <div class="title">Produk</div>
                            <div class="desc">Tambah/edit menu</div>
                        </a>

                        <!-- Laporan -->
                        <a href="laporan.php" class="menu-item">
                            <div class="icon">📊</div>
                            <div class="title">Laporan</div>
                            <div class="desc">Lihat laporan penjualan</div>
                        </a>

                        <!-- Laporan Keuangan -->
                        <a href="laporan_keuangan.php" class="menu-item">
                            <div class="icon">💰</div>
                            <div class="title">Laporan Keuangan</div>
                            <div class="desc">Laporan keuangan toko</div>
                        </a>

                        <!-- Pembelian -->
                        <a href="pembelian.php" class="menu-item">
                            <div class="icon">🛒</div>
                            <div class="title">Pembelian</div>
                            <div class="desc">Kelola pembelian barang</div>
                        </a>

                        <!-- Uang Masuk -->
                        <a href="uang_masuk.php" class="menu-item">
                            <div class="icon">📥</div>
                            <div class="title">Uang Masuk</div>
                            <div class="desc">Catat pemasukan</div>
                        </a>

                        <!-- Uang Keluar -->
                        <a href="uang_keluar.php" class="menu-item">
                            <div class="icon">📤</div>
                            <div class="title">Uang Keluar</div>
                            <div class="desc">Catat pengeluaran</div>
                        </a>

                        <!-- Gaji -->
                        <a href="gaji.php" class="menu-item">
                            <div class="icon">💳</div>
                            <div class="title">Gaji</div>
                            <div class="desc">Kelola gaji karyawan</div>
                        </a>

                        <!-- Karyawan -->
                        <a href="karyawan.php" class="menu-item">
                            <div class="icon">👥</div>
                            <div class="title">Karyawan</div>
                            <div class="desc">Kelola data karyawan</div>
                        </a>

                        <!-- Tugas Belanja -->
                        <a href="tugas_belanja.php" class="menu-item">
                            <div class="icon">📋</div>
                            <div class="title">Tugas Belanja</div>
                            <div class="desc">Kelola tugas belanja</div>
                        </a>

                        <!-- Cetak Excel -->
                        <a href="cetak_excel.php" class="menu-item">
                            <div class="icon">📊</div>
                            <div class="title">Cetak Excel</div>
                            <div class="desc">Export data ke Excel</div>
                        </a>

                        <!-- Cetak PDF -->
                        <a href="cetakPDF.php" class="menu-item">
                            <div class="icon">📄</div>
                            <div class="title">Cetak PDF</div>
                            <div class="desc">Export data ke PDF</div>
                        </a>

                        <!-- Cetak Laporan -->
                        <a href="cetak_laporan.php" class="menu-item">
                            <div class="icon">🖨️</div>
                            <div class="title">Cetak Laporan</div>
                            <div class="desc">Cetak laporan toko</div>
                        </a>
                    </div>
                </div>

                <!-- Info -->
                <div class="card" style="margin-top:20px; border-color:#9b59b6;">
                    <h3>📌 Informasi</h3>
                    <div style="color:#888; font-size:14px; line-height:1.8;">
                        <p>👨‍💼 <strong style="color:#9b59b6;">Manager</strong> - Kelola jadwal libur, tugas karyawan, dan shift kerja</p>
                        <p>📋 <strong style="color:#f1c40f;">Shift Kerja</strong> - Pagi (07:00-15:00) | Siang (15:00-22:00)</p>
                        <p>📅 <strong style="color:#e74c3c;">Libur</strong> - Maksimal 2 orang per hari</p>
                        <p>📦 <strong style="color:#27ae60;">Produk</strong> - Kelola menu makanan & minuman</p>
                        <p>💰 <strong style="color:#3498db;">Keuangan</strong> - Kelola uang masuk, uang keluar, dan gaji</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>