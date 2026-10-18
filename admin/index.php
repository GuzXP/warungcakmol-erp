<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: admin/index.php (ADMIN CENTRAL DASHBOARD & MANAGEMENT)
 * FITUR:
 * 1. Sidebar Navigasi Dinamis (?page=dashboard, ?page=menu, ?page=keuangan, ?page=pengaturan)
 * 2. Dashboard Analytics (Omzet, Keuangan, Grafik Penjualan Mingguan, Top Menu)
 * 3. Menu CRUD (Pencarian, Edit, Hapus, Tambah terintegrasi)
 * 4. Keuangan (Pemasukan otomatis/manual, Pengeluaran dengan upload receipt & preview modal)
 * 5. Pengaturan Warung (Edit data warung ke database)
 */

require 'fungsi.php';
confirm_admin_login();

// Buat direktori receipt jika belum ada
if (!is_dir('../img/receipts')) {
    mkdir('../img/receipts', 0777, true);
}

// Tentukan halaman aktif
$page = isset($_GET['page']) ? sanitize($_GET['page']) : 'dashboard';
if (!in_array($page, ['dashboard', 'menu', 'keuangan', 'pengaturan'])) {
    $page = 'dashboard';
}

// CSRF check helper for POST actions
function validate_post_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !check_csrf_token($_POST['csrf_token'])) {
            die("CSRF Token validation failed.");
        }
    }
}

// --------------------------------------------------------------------------
// PROSES POST ACTIONS
// --------------------------------------------------------------------------
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_post_csrf();
    
    // 1. TAMBAH UANG MASUK (MANUAL)
    if (isset($_POST['tambah_uang_masuk'])) {
        $sumber = sanitize($_POST['sumber']);
        $jumlah = (int)$_POST['jumlah'];
        $tanggal = sanitize($_POST['tanggal']);
        $keterangan = sanitize($_POST['keterangan']);
        
        $stmt = mysqli_prepare($koneksi, "INSERT INTO uang_masuk (sumber, jumlah, tanggal, keterangan) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "siss", $sumber, $jumlah, $tanggal, $keterangan);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Uang masuk berhasil dicatat!";
            } else {
                $error_msg = "Gagal mencatat uang masuk.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // 2. TAMBAH UANG KELUAR (MANUAL EXPENSE WITH UPLOAD)
    if (isset($_POST['tambah_uang_keluar'])) {
        $jenis = sanitize($_POST['jenis']);
        $jumlah = (int)$_POST['jumlah'];
        $tanggal = sanitize($_POST['tanggal']);
        $keterangan = sanitize($_POST['keterangan']);
        
        // Upload Gambar
        $foto_struk = null;
        if (isset($_FILES['foto_struk']) && $_FILES['foto_struk']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_struk']['tmp_name'];
            $file_name = $_FILES['foto_struk']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $new_file_name = uniqid() . '.' . $ext;
                $dest = '../img/receipts/' . $new_file_name;
                if (move_uploaded_file($file_tmp, $dest)) {
                    $foto_struk = $new_file_name;
                }
            } else {
                $error_msg = "Format struk tidak valid. Gunakan JPG/PNG.";
            }
        }
        
        if (empty($error_msg)) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO uang_keluar (jenis, jumlah, tanggal, keterangan, foto_struk) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sisss", $jenis, $jumlah, $tanggal, $keterangan, $foto_struk);
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Pengeluaran belanja/operasional berhasil dicatat!";
                } else {
                    $error_msg = "Gagal menyimpan pengeluaran.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    // 3. SIMPAN PENGATURAN WARUNG
    if (isset($_POST['simpan_pengaturan'])) {
        $settings = [
            'nama_warung' => sanitize($_POST['nama_warung']),
            'alamat' => sanitize($_POST['alamat']),
            'nomor_hp' => sanitize($_POST['nomor_hp']),
            'ppn_persen' => sanitize($_POST['ppn_persen']),
            'jam_buka' => sanitize($_POST['jam_buka']),
            'jam_tutup' => sanitize($_POST['jam_tutup'])
        ];
        
        $error = false;
        foreach ($settings as $key => $val) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "sss", $key, $val, $val);
                if (!mysqli_stmt_execute($stmt)) {
                    $error = true;
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = true;
            }
        }
        
        if (!$error) {
            $success_msg = "Pengaturan warung berhasil disimpan!";
        } else {
            $error_msg = "Terjadi kesalahan saat menyimpan pengaturan.";
        }
    }
}

// --------------------------------------------------------------------------
// PERSAPAN DATA VIEW
// --------------------------------------------------------------------------

// VIEW: DASHBOARD
if ($page === 'dashboard') {
    // 1. STATS: Omzet Selesai
    $res_omzet = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM pesanan WHERE status_pesanan = 'Selesai'");
    $row_omzet = mysqli_fetch_assoc($res_omzet);
    $omzet = (int)$row_omzet['total'];
    
    // 2. STATS: Total Transaksi Selesai
    $res_trans = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pesanan = 'Selesai'");
    $row_trans = mysqli_fetch_assoc($res_trans);
    $total_transaksi = (int)$row_trans['total'];
    
    // 3. STATS: Total Pengeluaran
    $res_expense = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM uang_keluar");
    $row_expense = mysqli_fetch_assoc($res_expense);
    $total_pengeluaran = (int)$row_expense['total'];

    // 4. STATS: Tambahan Uang Masuk Manual
    $res_income = mysqli_query($koneksi, "SELECT SUM(jumlah) AS total FROM uang_masuk");
    $row_income = mysqli_fetch_assoc($res_income);
    $total_pemasukan_manual = (int)$row_income['total'];
    
    // Saldo Bersih
    $saldo = ($omzet + $total_pemasukan_manual) - $total_pengeluaran;
    
    // 5. Best Seller Menu (Top 5)
    $best_seller = query("
        SELECT nama_menu_snapshot, SUM(jumlah) AS total_qty 
        FROM detail_pesanan 
        GROUP BY nama_menu_snapshot 
        ORDER BY total_qty DESC 
        LIMIT 5
    ");
    
    // 6. Recent Orders (Last 5)
    $recent_orders = query("
        SELECT p.*, m.nama_meja 
        FROM pesanan p 
        LEFT JOIN meja m ON p.id_meja = m.id_meja 
        ORDER BY p.waktu_pesan DESC 
        LIMIT 5
    ");

    // 7. Grafik data: omzet mingguan (7 hari terakhir)
    $weekly_data = query("
        SELECT DATE(waktu_pesan) AS tanggal, SUM(total_bayar) AS total 
        FROM pesanan 
        WHERE status_pesanan = 'Selesai' 
          AND waktu_pesan >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(waktu_pesan) 
        ORDER BY tanggal ASC
    ");
}

// VIEW: MENU
if ($page === 'menu') {
    // default fetch menu
    $menu = query("SELECT * FROM menu ORDER BY id_menu DESC"); 
    if(isset($_POST["search"])) {
        $menu = search($_POST["keyword"]); 
    }
}

// VIEW: KEUANGAN
if ($page === 'keuangan') {
    // Pemasukan: Pesanan Selesai
    $pemasukan_orders = query("
        SELECT id_pesanan AS ref_id, 'Pesanan Kasir' AS sumber, total_bayar AS jumlah, DATE(waktu_pesan) AS tanggal, tipe_pesanan AS keterangan 
        FROM pesanan 
        WHERE status_pesanan = 'Selesai' 
        ORDER BY waktu_pesan DESC
    ");
    
    // Pemasukan: Manual
    $pemasukan_manual = query("
        SELECT id AS ref_id, sumber, jumlah, tanggal, keterangan 
        FROM uang_masuk 
        ORDER BY tanggal DESC
    ");
    
    // Gabung Pemasukan
    $all_pemasukan = array_merge($pemasukan_orders, $pemasukan_manual);
    // Sort descending by tanggal
    usort($all_pemasukan, function($a, $b) {
        return strcmp($b['tanggal'], $a['tanggal']);
    });
    
    // Pengeluaran
    $all_pengeluaran = query("
        SELECT * FROM uang_keluar ORDER BY tanggal DESC
    ");
    
    // Total Hitungan
    $tot_pemasukan = 0;
    foreach($all_pemasukan as $p) $tot_pemasukan += $p['jumlah'];
    
    $tot_pengeluaran = 0;
    foreach($all_pengeluaran as $p) $tot_pengeluaran += $p['jumlah'];
    
    $laba_rugi = $tot_pemasukan - $tot_pengeluaran;
}

// VIEW: PENGATURAN
// Ambil setting database
$config_db = query("SELECT * FROM pengaturan");
$configs = [];
foreach ($config_db as $c) {
    $configs[$c['kunci']] = $c['nilai'];
}
$nama_warung = $configs['nama_warung'] ?? 'Warung Nasi Goreng Cak Mol';
$alamat = $configs['alamat'] ?? '';
$nomor_hp = $configs['nomor_hp'] ?? '';
$ppn_persen = $configs['ppn_persen'] ?? '0';
$jam_buka = $configs['jam_buka'] ?? '17:00';
$jam_tutup = $configs['jam_tutup'] ?? '23:00';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= sanitize($nama_warung); ?></title>
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="../css/style-admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* CSS INLINE UNTUK DESAIN SIDEBAR & GRID PREMIUM */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .admin-sidebar {
            width: 260px;
            background-color: #0f172a;
            color: #f8fafc;
            border-right: 1px solid #1e293b;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 0.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-brand-name {
            font-weight: 800;
            font-size: 1.125rem;
            background: linear-gradient(to right, #f59e0b, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.925rem;
            transition: all 0.2s;
        }

        .sidebar-link:hover {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .sidebar-link.active {
            background-color: #f59e0b;
            color: #0f172a;
        }

        .sidebar-link svg {
            width: 18px;
            height: 18px;
        }

        .sidebar-footer {
            border-top: 1px solid #1e293b;
            padding-top: 1rem;
        }

        .sidebar-logout-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            background: none;
            border: none;
            color: #f87171;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
            transition: background-color 0.2s;
        }

        .sidebar-logout-btn:hover {
            background-color: rgba(239, 68, 68, 0.1);
        }

        /* Content Container */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            padding: 2rem;
            min-width: 0; /* Mencegah flexbox stretch */
        }

        /* Stat Grid */
        .stat-grid-premium {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card-premium {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .stat-card-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card-primary::before { background-color: #f59e0b; }
        .stat-card-success::before { background-color: #22c55e; }
        .stat-card-danger::before { background-color: #ef4444; }
        .stat-card-info::before { background-color: #3b82f6; }

        .stat-card-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card-value {
            font-size: 1.875rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 0.5rem;
            letter-spacing: -0.025em;
        }

        /* Dashboard Flex Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Simple CSS Bar Chart */
        .chart-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);
        }

        .chart-bars-wrapper {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 200px;
            margin-top: 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .chart-bar-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            gap: 8px;
        }

        .chart-bar {
            width: 24px;
            background: linear-gradient(to top, #d97706, #f59e0b);
            border-radius: 4px 4px 0 0;
            position: relative;
            transition: height 0.5s ease;
            min-height: 2px;
        }

        .chart-bar:hover::after {
            content: attr(data-val);
            position: absolute;
            top: -24px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #0f172a;
            color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .chart-bar-date {
            font-size: 0.65rem;
            color: #64748b;
            font-weight: 600;
        }

        /* Toast Message styling */
        .alert-toast {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div>
                <div class="sidebar-brand">
                    <i data-lucide="chef-hat" style="color: #f59e0b; width: 24px; height: 24px;"></i>
                    <span class="sidebar-brand-name">CAK MOL POS</span>
                </div>
                
                <nav class="sidebar-menu">
                    <a href="index.php?page=dashboard" class="sidebar-link <?= ($page === 'dashboard') ? 'active' : ''; ?>">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                    <a href="index.php?page=menu" class="sidebar-link <?= ($page === 'menu') ? 'active' : ''; ?>">
                        <i data-lucide="utensils-crossed"></i> Manajemen Menu
                    </a>
                    <a href="index.php?page=keuangan" class="sidebar-link <?= ($page === 'keuangan') ? 'active' : ''; ?>">
                        <i data-lucide="wallet"></i> Keuangan (Laba/Rugi)
                    </a>
                    <a href="index.php?page=pengaturan" class="sidebar-link <?= ($page === 'pengaturan') ? 'active' : ''; ?>">
                        <i data-lucide="settings"></i> Pengaturan
                    </a>
                </nav>
            </div>
            
            <div class="sidebar-footer">
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.75rem; padding: 0 1rem; line-height: 1.4;">
                    Admin Aktif:<br>
                    <strong style="color: #e2e8f0; font-size: 0.85rem;"><?= isset($_SESSION["nama"]) ? sanitize($_SESSION["nama"]) : "Bagus Ramadhan"; ?></strong>
                </div>
                <a href="logout.php" class="sidebar-logout-btn" onclick="return confirm('Logout dari sistem?')">
                    <i data-lucide="log-out"></i> Logout
                </a>
            </div>
        </aside>
        
        <!-- MAIN CONTENT AREA -->
        <main class="admin-main">
            
            <!-- Alert Notifications -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert-toast alert-success">
                    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                    <span><?= $success_msg; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert-toast alert-danger">
                    <i data-lucide="alert-triangle" style="width: 20px; height: 20px;"></i>
                    <span><?= $error_msg; ?></span>
                </div>
            <?php endif; ?>

            <!-- ----------------------------------------------------------------------- -->
            <!-- VIEW: DASHBOARD (DEFAULT)                                               -->
            <!-- ----------------------------------------------------------------------- -->
            <?php if ($page === 'dashboard'): ?>
                <div class="header-action-row" style="margin-bottom: 1.5rem;">
                    <div>
                        <h2 class="title-large" style="margin-bottom: 0.25rem;">Dashboard Ringkasan</h2>
                        <p class="text-muted">Analytics operasional & omzet Warung Nasi Goreng Cak Mol.</p>
                    </div>
                </div>

                <!-- STAT CARDS -->
                <div class="stat-grid-premium">
                    <div class="stat-card-premium stat-card-primary">
                        <span class="stat-card-label">Omzet Penjualan</span>
                        <div class="stat-card-value">Rp <?= number_format($omzet, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card-premium stat-card-success">
                        <span class="stat-card-label">Total Transaksi</span>
                        <div class="stat-card-value"><?= $total_transaksi; ?></div>
                    </div>
                    <div class="stat-card-premium stat-card-danger">
                        <span class="stat-card-label">Total Pengeluaran</span>
                        <div class="stat-card-value">Rp <?= number_format($total_pengeluaran, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card-premium stat-card-info">
                        <span class="stat-card-label">Saldo Kasir</span>
                        <div class="stat-card-value" style="color: <?= $saldo >= 0 ? '#10b981' : '#ef4444'; ?>;">
                            Rp <?= number_format($saldo, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <!-- FLEX GRID -->
                <div class="dashboard-grid">
                    <!-- Chart & Kehadiran Penjualan -->
                    <div class="chart-container">
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 0.25rem;">Grafik Omzet Mingguan</h3>
                        <p style="font-size: 0.8rem; color: #64748b;">Menampilkan tren penjualan harian selama 7 hari terakhir.</p>
                        
                        <div class="chart-bars-wrapper">
                            <?php 
                            // Render Chart Bars
                            $max_total = 1;
                            foreach ($weekly_data as $wd) {
                                if ((int)$wd['total'] > $max_total) {
                                    $max_total = (int)$wd['total'];
                                }
                            }
                            
                            // Map 7 hari terakhir secara dinamis
                            $days = [];
                            for ($j = 6; $j >= 0; $j--) {
                                $days[date('Y-m-d', strtotime("-$j days"))] = 0;
                            }
                            foreach ($weekly_data as $wd) {
                                $days[$wd['tanggal']] = (int)$wd['total'];
                            }

                            foreach ($days as $date => $total): 
                                $percentHeight = ($total / $max_total) * 100;
                            ?>
                                <div class="chart-bar-col">
                                    <div class="chart-bar" style="height: <?= max($percentHeight, 2); ?>%;" data-val="Rp <?= number_format($total, 0, ',', '.'); ?>"></div>
                                    <span class="chart-bar-date"><?= date('d M', strtotime($date)); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Best Sellers -->
                    <div class="card-admin" style="padding: 1.5rem;">
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 1rem;">Menu Paling Laris</h3>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php if (empty($best_seller)): ?>
                                <p style="font-size: 0.85rem; color: #64748b; font-style: italic;">Belum ada data penjualan.</p>
                            <?php endif; ?>
                            <?php foreach ($best_seller as $index => $item): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <span style="font-weight: 800; font-size: 1.1rem; color: <?= $index === 0 ? '#f59e0b' : '#64748b'; ?>;">#<?= $index + 1; ?></span>
                                        <span style="font-weight: 600; font-size: 0.95rem; color: #0f172a;"><?= sanitize($item['nama_menu_snapshot']); ?></span>
                                    </div>
                                    <span style="background: #f1f5f9; color: #334155; font-size: 0.75rem; font-weight: 700; padding: 4px 8px; border-radius: 9999px;"><?= $item['total_qty']; ?> Porsi</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- RECENT ORDERS -->
                <div class="card-admin" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 1rem;">Pesanan Terakhir (Selesai/Pending)</h3>
                    <div class="table-wrapper" style="box-shadow: none; border: none; margin-bottom: 0;">
                        <table style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>ID Order</th>
                                    <th>Meja/Type</th>
                                    <th>Waktu</th>
                                    <th>Nama Cust</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #64748b; font-style: italic; padding: 2rem;">Tidak ada pesanan.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($recent_orders as $ro): ?>
                                    <tr>
                                        <td style="font-weight: 700;">#CM-<?= $ro['id_pesanan']; ?></td>
                                        <td><?= $ro['nama_meja'] ? sanitize($ro['nama_meja']) : 'TAKEAWAY'; ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($ro['waktu_pesan'])); ?></td>
                                        <td><?= $ro['nama_pelanggan'] ? sanitize($ro['nama_pelanggan']) : '-'; ?></td>
                                        <td class="price-text">Rp <?= number_format($ro['total_bayar'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span style="font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; background: <?= $ro['status_pesanan'] === 'Selesai' ? '#dcfce7; color: #15803d;' : '#fef3c7; color: #d97706;'; ?>">
                                                <?= $ro['status_pesanan']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- ----------------------------------------------------------------------- -->
            <!-- VIEW: MANAJEMEN MENU                                                    -->
            <!-- ----------------------------------------------------------------------- -->
            <?php elseif ($page === 'menu'): ?>
                <div class="header-action-row">
                    <div>
                        <h2 class="title-large header-title-margin">Manajemen Data Menu</h2>
                        <p class="text-muted">Pantau dan kelola data menu makanan/minuman warung.</p>
                    </div>
                    
                    <div class="header-btn-group">
                        <a href="cetakPDF.php" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;"><i data-lucide="file-text" style="width: 16px; height: 16px;"></i> Cetak Menu (PDF)</a>
                        <a href="add.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem;"><i data-lucide="plus" style="width: 16px; height: 16px;"></i> Tambah Menu Baru</a>
                    </div>
                </div>

                <!-- SEARCH & FILTER -->
                <div class="card-admin filter-card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
                    <form action="" method="post" class="filter-form">
                        <input type="text" name="keyword" placeholder="Cari nama menu, harga, atau kategori..." autocomplete="off" class="input-search-no-margin">
                        <button type="submit" name="search" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem;"><i data-lucide="search" style="width: 16px; height: 16px;"></i> Cari</button>
                        <?php if(isset($_POST["search"])): ?>
                            <a href="index.php?page=menu" class="btn btn-outline">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- TABLE LIST MENU -->
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th class="col-no">No</th>
                                <th class="col-img">Foto</th>
                                <th>Menu & Kategori</th>
                                <th>Harga Satuan</th>
                                <th>Deskripsi</th>
                                <th class="col-action">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($menu)) : ?>
                                <tr>
                                    <td colspan="6" class="cell-no-empty text-muted">
                                        Tidak ada data menu ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php $i = 1; foreach ($menu as $data) : ?>
                            <tr>
                                <td class="cell-no"><?= $i++; ?></td>
                                <td>
                                    <?php if(isset($data['gambar']) && $data['gambar']): ?>
                                        <img src="../img/<?= sanitize($data['gambar']); ?>" class="admin-img-thumb" alt="<?= sanitize($data['nama_menu']); ?>">
                                    <?php else: ?>
                                        <div class="img-placeholder" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background-color: #f1f5f9; border-radius: 6px;"><i data-lucide="image" style="color: #94a3b8;"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="menu-name"><?= sanitize($data['nama_menu']); ?></div>
                                    <div class="category-wrapper">
                                        <span class="badge-category"><?= sanitize($data['kategori']); ?></span>
                                    </div>
                                    <?php if(!empty($data['badge'])): ?>
                                        <span class="badge-status-green" style="margin-top: 4px; display: inline-block; font-size: 0.65rem; background-color: #dcfce7; color: #15803d; padding: 1px 6px; border-radius: 4px; font-weight: 700;"><?= sanitize($data['badge']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="price-text">Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <div class="desc-text" title="<?= sanitize($data['deskripsi']); ?>"><?= sanitize($data['deskripsi']); ?></div>
                                </td>
                                <td class="action-cell">
                                    <div class="flex-center-gap">
                                        <a href="edit.php?id=<?= $data['id_menu']; ?>" class="btn btn-outline btn-sm-custom" style="display: inline-flex; align-items: center; gap: 0.25rem;"><i data-lucide="edit" style="width: 14px; height: 14px;"></i> Edit</a>
                                        
                                        <!-- Post-based secure delete form -->
                                        <form action="delete.php" method="post" onsubmit="return confirm('Hapus menu <?= sanitize($data['nama_menu']); ?>?')" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                                            <input type="hidden" name="id" value="<?= $data['id_menu']; ?>">
                                            <button type="submit" class="btn btn-delete-custom btn-sm-custom" style="display: inline-flex; align-items: center; gap: 0.25rem;"><i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <!-- ----------------------------------------------------------------------- -->
            <!-- VIEW: KEUANGAN (LABA/RUGI)                                              -->
            <!-- ----------------------------------------------------------------------- -->
            <?php elseif ($page === 'keuangan'): ?>
                <div class="header-action-row">
                    <div>
                        <h2 class="title-large header-title-margin">Manajemen Keuangan</h2>
                        <p class="text-muted">Pantau arus uang masuk (pemasukan) dan uang keluar (pengeluaran/belanja).</p>
                    </div>
                    
                    <div class="header-btn-group">
                        <a href="cetakLaporan.php" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;"><i data-lucide="printer" style="width: 16px; height: 16px;"></i> Cetak Laporan (PDF)</a>
                        <button type="button" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.35rem;" onclick="openModal('modal-uang-masuk')"><i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Uang Masuk</button>
                        <button type="button" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.35rem;" onclick="openModal('modal-uang-keluar')"><i data-lucide="minus-circle" style="width: 16px; height: 16px;"></i> Catat Pengeluaran</button>
                    </div>
                </div>

                <!-- RINGKASAN LABA RUGI -->
                <div class="stat-grid-premium">
                    <div class="stat-card-premium stat-card-success">
                        <span class="stat-card-label">Total Pemasukan</span>
                        <div class="stat-card-value">Rp <?= number_format($tot_pemasukan, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card-premium stat-card-danger">
                        <span class="stat-card-label">Total Pengeluaran</span>
                        <div class="stat-card-value">Rp <?= number_format($tot_pengeluaran, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-card-premium stat-card-info">
                        <span class="stat-card-label">Net Laba / Rugi</span>
                        <div class="stat-card-value" style="color: <?= $laba_rugi >= 0 ? '#10b981' : '#ef4444'; ?>;">
                            Rp <?= number_format($laba_rugi, 0, ',', '.'); ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
                    <!-- TABLE PEMASUKAN -->
                    <div class="card-admin" style="padding: 1.5rem;">
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="trending-up" style="color: #22c55e;"></i> Riwayat Pemasukan</h3>
                        <div class="table-wrapper" style="box-shadow: none; border: none; margin-bottom: 0;">
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Sumber</th>
                                        <th style="text-align: right;">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($all_pemasukan)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: #64748b; font-style: italic; padding: 2rem;">Tidak ada pemasukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($all_pemasukan as $p): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($p['tanggal'])); ?></td>
                                            <td>
                                                <div style="font-weight: 700; font-size: 0.9rem;"><?= sanitize($p['sumber']); ?></div>
                                                <div style="font-size: 0.75rem; color: #64748b;"><?= sanitize($p['keterangan']); ?></div>
                                            </td>
                                            <td style="text-align: right; font-weight: 700; color: #16a34a;">Rp <?= number_format($p['jumlah'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TABLE PENGELUARAN -->
                    <div class="card-admin" style="padding: 1.5rem;">
                        <h3 style="font-weight: 700; font-size: 1.1rem; color: #0f172a; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="trending-down" style="color: #ef4444;"></i> Riwayat Pengeluaran (Belanja)</h3>
                        <div class="table-wrapper" style="box-shadow: none; border: none; margin-bottom: 0;">
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis & Keterangan</th>
                                        <th style="text-align: right;">Jumlah</th>
                                        <th style="text-align: center;">Struk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($all_pengeluaran)): ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; color: #64748b; font-style: italic; padding: 2rem;">Tidak ada pengeluaran.</td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php foreach ($all_pengeluaran as $p): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($p['tanggal'])); ?></td>
                                            <td>
                                                <div style="font-weight: 700; font-size: 0.9rem;"><span style="font-size: 0.75rem; background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px; margin-right: 4px;"><?= sanitize($p['jenis']); ?></span></div>
                                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;"><?= sanitize($p['keterangan']); ?></div>
                                            </td>
                                            <td style="text-align: right; font-weight: 700; color: #b91c1c;">Rp <?= number_format($p['jumlah'], 0, ',', '.'); ?></td>
                                            <td style="text-align: center;">
                                                <?php if(!empty($p['foto_struk'])): ?>
                                                    <button type="button" style="background: none; border: none; cursor: pointer; color: #2563eb;" onclick="openReceiptModal('../img/receipts/<?= sanitize($p['foto_struk']); ?>')">
                                                        <i data-lucide="eye" style="width: 18px; height: 18px;"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8; font-size: 0.75rem;">Tidak ada</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <!-- ----------------------------------------------------------------------- -->
            <!-- VIEW: PENGATURAN WARUNG                                                 -->
            <!-- ----------------------------------------------------------------------- -->
            <?php elseif ($page === 'pengaturan'): ?>
                <div class="header-action-row" style="margin-bottom: 1.5rem;">
                    <div>
                        <h2 class="title-large" style="margin-bottom: 0.25rem;">Pengaturan Warung</h2>
                        <p class="text-muted">Atur profil warung, waktu operasional, dan parameter PPN POS.</p>
                    </div>
                </div>

                <div class="card-admin" style="max-width: 600px;">
                    <form action="" method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                        
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">Nama Warung</label>
                            <input type="text" name="nama_warung" value="<?= sanitize($nama_warung); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem;" required>
                        </div>
                        
                        <div style="margin-bottom: 1.25rem;">
                            <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem; font-family: inherit;" required><?= sanitize($alamat); ?></textarea>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                            <div>
                                <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">Nomor HP / WhatsApp</label>
                                <input type="text" name="nomor_hp" value="<?= sanitize($nomor_hp); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;" required>
                            </div>
                            <div>
                                <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">PPN (%)</label>
                                <input type="number" name="ppn_persen" value="<?= sanitize($ppn_persen); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.75rem;">
                            <div>
                                <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">Jam Buka</label>
                                <input type="time" name="jam_buka" value="<?= sanitize($jam_buka); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;" required>
                            </div>
                            <div>
                                <label style="font-weight: 700; font-size: 0.9rem; display: block; margin-bottom: 0.5rem; color: #334155;">Jam Tutup</label>
                                <input type="time" name="jam_tutup" value="<?= sanitize($jam_tutup); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; outline: none;" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="simpan_pengaturan" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i data-lucide="check-check"></i> Simpan Konfigurasi
                        </button>
                    </form>
                </div>
            <?php endif; ?>
            
        </main>
    </div>

    <!-- MODAL: INPUT UANG MASUK MANUAL -->
    <div id="modal-uang-masuk" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; color: #0f172a; padding: 1.75rem; border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                <h3 style="font-weight: 800; font-size: 1.15rem;">Tambah Uang Masuk</h3>
                <button type="button" onclick="closeModal('modal-uang-masuk')" style="background: none; border: none; cursor: pointer; color: #64748b;"><i data-lucide="x" style="width: 20px; height: 20px;"></i></button>
            </div>
            
            <form action="" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Sumber Pemasukan</label>
                    <input type="text" name="sumber" placeholder="Contoh: Catering PT ABC, Modal Awal" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                </div>
                
                <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Nominal (Rp)</label>
                        <input type="number" name="jumlah" placeholder="Rp" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Keterangan / Catatan</label>
                    <textarea name="keterangan" rows="2" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; font-family: inherit;"></textarea>
                </div>
                
                <button type="submit" name="tambah_uang_masuk" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 700;">Simpan Pemasukan</button>
            </form>
        </div>
    </div>

    <!-- MODAL: INPUT UANG KELUAR MANUAL (BELANJA) -->
    <div id="modal-uang-keluar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem;">
        <div style="background: #ffffff; color: #0f172a; padding: 1.75rem; border-radius: 12px; width: 100%; max-width: 440px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; margin-bottom: 1.25rem;">
                <h3 style="font-weight: 800; font-size: 1.15rem;">Catat Pengeluaran</h3>
                <button type="button" onclick="closeModal('modal-uang-keluar')" style="background: none; border: none; cursor: pointer; color: #64748b;"><i data-lucide="x" style="width: 20px; height: 20px;"></i></button>
            </div>
            
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Jenis Pengeluaran</label>
                    <select name="jenis" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none; background: #fff;" required>
                        <option value="Belanja Bahan">Belanja Bahan Makanan</option>
                        <option value="Operasional">Operasional (Gas/Listrik/Gaji)</option>
                        <option value="Lainnya">Lain-lain</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Nominal (Rp)</label>
                        <input type="number" name="jumlah" placeholder="Rp" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Tanggal</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d'); ?>" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                    </div>
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Beli Ayam 5kg, Listrik Mei" style="width: 100%; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;" required>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem; color: #475569;">Upload Foto Struk / Nota (Optional)</label>
                    <input type="file" name="foto_struk" accept="image/*" style="width: 100%; font-size: 0.85rem;">
                </div>
                
                <button type="submit" name="tambah_uang_keluar" class="btn btn-primary" style="width: 100%; padding: 0.75rem; font-weight: 700;">Simpan Pengeluaran</button>
            </form>
        </div>
    </div>

    <!-- MODAL: IMAGE PREVIEW (ZOOM STRUK) -->
    <div id="modal-receipt-preview" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px); z-index: 1100; align-items: center; justify-content: center; padding: 1rem;" onclick="closeModal('modal-receipt-preview')">
        <div style="background: #ffffff; padding: 6px; border-radius: 12px; position: relative; max-width: 90%; max-height: 90%;" onclick="event.stopPropagation()">
            <button type="button" onclick="closeModal('modal-receipt-preview')" style="position: absolute; top: -15px; right: -15px; background: #ef4444; border: none; cursor: pointer; color: #fff; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"><i data-lucide="x" style="width: 16px; height: 16px;"></i></button>
            <img id="img-receipt-preview-src" src="" alt="Receipt Zoom" style="max-width: 100%; max-height: 80vh; border-radius: 8px; display: block; object-fit: contain;">
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function openReceiptModal(src) {
            const img = document.getElementById('img-receipt-preview-src');
            img.src = src;
            openModal('modal-receipt-preview');
        }
    </script>
</body>
</html>