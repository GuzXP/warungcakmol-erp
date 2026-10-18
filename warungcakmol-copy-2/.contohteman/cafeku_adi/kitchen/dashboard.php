<?php
// FILE: kitchen/dashboard.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'kitchen') {
    header('Location: ../login.php');
    exit;
}

// Cek kolom status_pesanan
$statusColumn = mysqli_num_rows(mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status_pesanan'")) === 1;
$statusExpr = $statusColumn ? 'status_pesanan' : "'Menunggu' AS status_pesanan";

// ===== STATISTIK HANYA UNTUK MAKANAN =====

// 1. Pesanan Menunggu (Buka Pesanan) - HANYA MAKANAN
$menunggu = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, 'Menunggu')) = 'menunggu'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$menungguCount = $menunggu[0]['total'] ?? 0;

// 2. Pesanan Pending (Kelola Masakan) - HANYA MAKANAN
$pending = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'pending'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$pendingCount = $pending[0]['total'] ?? 0;

// 3. Pesanan Diproses (Kelola Masakan) - HANYA MAKANAN
$diproses = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'diproses'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$diprosesCount = $diproses[0]['total'] ?? 0;

// 4. Pesanan Siap (Kelola Masakan) - HANYA MAKANAN
$siap = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'siap'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$siapCount = $siap[0]['total'] ?? 0;

// 5. Total Kelola Masakan (Pending + Diproses + Siap) - HANYA MAKANAN
$kelolaCount = $pendingCount + $diprosesCount + $siapCount;

// 6. Pesanan Selesai - HANYA MAKANAN
$selesai = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'selesai'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$selesaiCount = $selesai[0]['total'] ?? 0;

// 7. Pesanan Ditolak - HANYA MAKANAN
$ditolak = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'ditolak'
    AND p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$ditolakCount = $ditolak[0]['total'] ?? 0;

// 8. Total Pesanan Aktif (Menunggu + Pending + Diproses + Siap) - HANYA MAKANAN
$activeCount = $menungguCount + $pendingCount + $diprosesCount + $siapCount;

// 9. Total Semua Pesanan (HANYA MAKANAN)
$totalSemua = query("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
");
$totalSemuaCount = $totalSemua[0]['total'] ?? 0;

// 10. Pesanan Terbaru (5 terakhir) - HANYA MAKANAN
$pesanan = query("
    SELECT DISTINCT t.id, t.kode_transaksi, t.total, t.tanggal, 
    COALESCE(t.status_pesanan, 'Menunggu') as status_pesanan 
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE p.kategori IN ('Makanan', 'Snack', 'Pastry', 'Dessert')
    ORDER BY t.tanggal DESC 
    LIMIT 5
");

$statusNote = $statusColumn ? '' : 'Kolom status_pesanan belum tersedia di database.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kitchen - Cafeku</title>
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
        .page-header .page-note {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
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
        .btn-success { background: #27ae60; color: #fff; }
        .btn-success:hover { background: #219a52; transform: translateY(-2px); }
        .btn-danger { background: #8B0000; color: #fff; }
        .btn-danger:hover { background: #660000; transform: translateY(-2px); }
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-full {
            width: 100%;
            text-align: center;
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            margin-top: 10px;
        }
        .btn-full:hover { background: #333; transform: translateY(-2px); }
        .card {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
        }
        .card h2 {
            color: #c08b5c;
            font-size: 16px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .kitchen-note {
            background: rgba(241, 196, 15, 0.1);
            border: 1px solid #f1c40f;
            color: #f1c40f;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .overview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .stat-value {
            font-size: 48px;
            font-weight: 900;
            color: #c08b5c;
            margin: 10px 0;
        }
        .stat-desc {
            color: #888;
            font-size: 14px;
        }
        .empty-state {
            color: #666;
            font-style: italic;
            padding: 15px 0;
        }
        .order-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #333;
            flex-wrap: wrap;
            gap: 8px;
        }
        .order-item:last-child { border-bottom: none; }
        .order-title {
            font-weight: 600;
            color: #ddd;
        }
        .status-chip {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .status-chip.menunggu { background: #f1c40f; color: #1a1a1a; }
        .status-chip.pending { background: #f39c12; color: #1a1a1a; }
        .status-chip.diproses { background: #3498db; color: #fff; }
        .status-chip.siap { background: #2ecc71; color: #1a1a1a; }
        .status-chip.selesai { background: #27ae60; color: #fff; }
        .status-chip.ditolak { background: #e74c3c; color: #fff; }
        
        .order-meta {
            display: flex;
            gap: 15px;
            color: #888;
            font-size: 13px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            text-align: center;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #c08b5c;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: 900;
            color: #c08b5c;
        }
        .stat-card .label {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .stat-card .sub-label {
            color: #666;
            font-size: 12px;
            margin-top: 3px;
        }
        .stat-card.border-menunggu { border-left: 4px solid #f1c40f; }
        .stat-card.border-pending { border-left: 4px solid #f39c12; }
        .stat-card.border-diproses { border-left: 4px solid #3498db; }
        .stat-card.border-siap { border-left: 4px solid #2ecc71; }
        .stat-card.border-selesai { border-left: 4px solid #27ae60; }
        .stat-card.border-ditolak { border-left: 4px solid #e74c3c; }

        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card .badge-ditolak {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 20px;
            padding: 2px 12px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .page-header { flex-direction: column; text-align: center; }
            .overview-grid { grid-template-columns: 1fr; }
            .order-item { flex-direction: column; align-items: flex-start; }
            .stat-row { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>🍳 Dashboard Kitchen</h1>
                <p class="page-note">📌 Hanya menampilkan pesanan <strong style="color:#e74c3c;">MAKANAN</strong> (Makanan, Snack, Pastry, Dessert)</p>
            </div>
            <div class="btn-group">
                <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <?php if ($statusNote): ?>
            <div class="kitchen-note">⚠️ <?= htmlspecialchars($statusNote) ?></div>
        <?php endif; ?>

        <!-- ===== STATISTIK RINGKAS ===== -->
        <div class="stat-row">
            <div class="stat-card border-menunggu">
                <div class="number"><?= $menungguCount ?></div>
                <div class="label">📋 Buka Pesanan</div>
                <div class="sub-label">Menunggu</div>
            </div>
            <div class="stat-card border-pending">
                <div class="number"><?= $pendingCount ?></div>
                <div class="label">⏳ Pending</div>
                <div class="sub-label">Menunggu diproses</div>
            </div>
            <div class="stat-card border-diproses">
                <div class="number"><?= $diprosesCount ?></div>
                <div class="label">🔧 Diproses</div>
                <div class="sub-label">Sedang dimasak</div>
            </div>
            <div class="stat-card border-siap">
                <div class="number"><?= $siapCount ?></div>
                <div class="label">✅ Siap</div>
                <div class="sub-label">Siap diantar</div>
            </div>
            <div class="stat-card border-selesai">
                <div class="number"><?= $selesaiCount ?></div>
                <div class="label">🎉 Selesai</div>
                <div class="sub-label">Pesanan selesai</div>
            </div>
            <div class="stat-card border-ditolak">
                <div class="number"><?= $ditolakCount ?></div>
                <div class="label">❌ Ditolak</div>
                <div class="sub-label">Pesanan ditolak chef</div>
                <?php if ($ditolakCount > 0): ?>
                    <div class="badge-ditolak">⚠️ <?= $ditolakCount ?> pesanan ditolak</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="overview-grid">
            <!-- ===== CARD KIRI: 5 PESANAN TERBARU ===== -->
            <div class="card">
                <h2>🕐 5 Pesanan Makanan Terbaru</h2>
                <?php if (empty($pesanan)): ?>
                    <p class="empty-state">Tidak ada pesanan makanan</p>
                <?php else: ?>
                    <ul class="order-list">
                        <?php foreach ($pesanan as $order): 
                            $status = strtolower($order['status_pesanan'] ?? 'menunggu');
                            $class = 'status-chip';
                            if (in_array($status, ['menunggu', 'pending', 'diproses', 'siap', 'selesai', 'ditolak'])) {
                                $class .= ' ' . $status;
                            } else {
                                $class .= ' menunggu';
                            }
                        ?>
                            <li class="order-item">
                                <div>
                                    <span class="order-title">#<?= htmlspecialchars($order['kode_transaksi']) ?></span>
                                    <span class="<?= $class ?>"><?= ucfirst($order['status_pesanan'] ?? 'Menunggu') ?></span>
                                </div>
                                <div class="order-meta">
                                    <span>Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                                    <span><?= date('d M Y H:i', strtotime($order['tanggal'])) ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- ===== CARD KANAN: AKSI KITCHEN ===== -->
            <div class="card">
                <h2>⚡ Aksi Kitchen</h2>
                <p style="color:#888; font-size:14px; margin-bottom:15px; line-height:1.8;">
                    📋 <strong style="color:#f1c40f;">Buka Pesanan</strong> = Cek & konfirmasi pesanan makanan baru<br>
                    🔧 <strong style="color:#3498db;">Kelola Masakan</strong> = Update status masakan<br>
                    ✅ <strong style="color:#27ae60;">Selesai</strong> = Lihat pesanan makanan yang sudah selesai
                </p>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="pesanan.php" class="btn btn-full" style="border-color:#f1c40f; color:#f1c40f;">
                        📋 Buka Pesanan 
                        <?php if ($menungguCount > 0): ?>
                            <span style="background:#f1c40f; color:#1a1a1a; padding:1px 10px; border-radius:20px; font-size:12px; margin-left:5px;"><?= $menungguCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="pesanan.php?tab=kelola" class="btn btn-full" style="border-color:#3498db; color:#3498db;">
                        🔧 Kelola Masakan
                        <?php if ($kelolaCount > 0): ?>
                            <span style="background:#3498db; color:#fff; padding:1px 10px; border-radius:20px; font-size:12px; margin-left:5px;"><?= $kelolaCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="pesanan.php?tab=selesai" class="btn btn-full" style="border-color:#27ae60; color:#27ae60;">
                        ✅ Selesai
                        <?php if ($selesaiCount > 0): ?>
                            <span style="background:#27ae60; color:#fff; padding:1px 10px; border-radius:20px; font-size:12px; margin-left:5px;"><?= $selesaiCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>
                <div style="margin-top:12px; padding-top:12px; border-top:1px solid #333;">
                    <div style="display:flex; justify-content:space-between; color:#666; font-size:12px;">
                        <span>📊 Total Makanan: <strong style="color:#c08b5c;"><?= $totalSemuaCount ?></strong></span>
                        <span>⚡ Aktif: <strong style="color:#c08b5c;"><?= $activeCount ?></strong></span>
                        <span>❌ Ditolak: <strong style="color:#e74c3c;"><?= $ditolakCount ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../js/kitchen-touch.js"></script>
</body>
</html>