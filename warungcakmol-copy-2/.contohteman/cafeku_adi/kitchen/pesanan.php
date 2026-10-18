<?php
// FILE: kitchen/pesanan.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'kitchen') {
    header('Location: ../login.php');
    exit;
}

// ===== CEK KOLOM =====
$cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status_pesanan'");
$statusColumn = mysqli_num_rows($cekKolom) === 1;

// Cek kolom alasan_tolak
$cekAlasan = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'alasan_tolak'");
if (mysqli_num_rows($cekAlasan) === 0 && $statusColumn) {
    mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN alasan_tolak TEXT NULL");
}

// ===== TAB YANG AKTIF =====
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'buka';
if (!in_array($tab, ['buka', 'kelola', 'selesai'])) {
    $tab = 'buka';
}

// ===== PROSES TERIMA PESANAN =====
if (isset($_POST['terima_pesanan'])) {
    $id = (int)$_POST['id_transaksi'];
    $status = 'Pending';
    
    if ($statusColumn) {
        $stmt = mysqli_prepare($conn, "UPDATE transaksi SET status_pesanan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        echo "<script>alert('✅ Pesanan makanan diterima! Status: Pending (menunggu diproses)'); window.location='pesanan.php?tab=kelola';</script>";
    } else {
        echo "<script>alert('Kolom status_pesanan belum ada!');</script>";
    }
    exit;
}

// ===== PROSES TOLAK PESANAN =====
if (isset($_POST['tolak_pesanan'])) {
    $id = (int)$_POST['id_transaksi'];
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan_tolak']);
    $status = 'Ditolak';
    
    if (empty($alasan)) {
        echo "<script>alert('⚠️ Harap isi alasan penolakan!'); window.location='pesanan.php?tab=buka';</script>";
        exit;
    }
    
    if ($statusColumn) {
        $stmt = mysqli_prepare($conn, "UPDATE transaksi SET status_pesanan = ?, alasan_tolak = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssi", $status, $alasan, $id);
        mysqli_stmt_execute($stmt);
        echo "<script>alert('❌ Pesanan makanan ditolak! Alasan: " . addslashes($alasan) . "'); window.location='pesanan.php?tab=buka';</script>";
    } else {
        echo "<script>alert('Kolom status_pesanan belum ada!');</script>";
    }
    exit;
}

// ===== PROSES HAPUS TRANSAKSI =====
if (isset($_POST['hapus_transaksi'])) {
    $id = (int)$_POST['id_transaksi'];
    
    mysqli_query($conn, "DELETE FROM detail_transaksi WHERE transaksi_id = $id");
    mysqli_query($conn, "DELETE FROM transaksi WHERE id = $id");
    
    echo "<script>alert('🗑️ Transaksi berhasil dihapus!'); window.location='pesanan.php?tab=" . ($tab) . "';</script>";
    exit;
}

// ===== UPDATE STATUS MASAKAN =====
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id_transaksi'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if ($statusColumn) {
        $stmt = mysqli_prepare($conn, "UPDATE transaksi SET status_pesanan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        mysqli_stmt_execute($stmt);
        
        if ($status == 'Selesai') {
            echo "<script>alert('✅ Masakan selesai! Pesanan masuk ke daftar selesai.'); window.location='pesanan.php?tab=selesai';</script>";
        } else {
            echo "<script>alert('✅ Status masakan berhasil diupdate!'); window.location='pesanan.php?tab=kelola';</script>";
        }
    } else {
        echo "<script>alert('Kolom status_pesanan belum ada!');</script>";
    }
    exit;
}

// ============================================================ //
// ===== KATEGORI MAKANAN ===== //
// ============================================================ //
$kategoriMakanan = "('Makanan', 'Snack', 'Pastry', 'Dessert')";

// ============================================================ //
// ===== AMBIL DATA ===== //
// ============================================================ //

// TAB 1: BUKA PESANAN (HANYA status Menunggu dan MAKANAN)
$pesananBuka = query("
    SELECT DISTINCT t.*, 
    COALESCE(t.status_pesanan, 'Menunggu') as status_pesanan,
    COALESCE(t.alasan_tolak, '') as alasan_tolak
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, 'Menunggu')) = 'menunggu'
    AND p.kategori IN $kategoriMakanan
    ORDER BY t.tanggal DESC
");

// Detail produk untuk BUKA PESANAN
$detailProdukBuka = [];
foreach ($pesananBuka as $row) {
    $id_trans = $row['id'];
    $detailProdukBuka[$id_trans] = query("
        SELECT dt.*, p.nama, p.gambar, p.harga as harga_produk, p.kategori
        FROM detail_transaksi dt
        JOIN produk p ON dt.produk_id = p.id
        WHERE dt.transaksi_id = $id_trans
        AND p.kategori IN $kategoriMakanan
    ");
}

// TAB 2: KELOLA MASAKAN (Pending, Diproses, Siap dan MAKANAN)
$pesananKelola = query("
    SELECT DISTINCT t.*, 
    COALESCE(t.status_pesanan, 'Pending') as status_pesanan
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, 'Pending')) IN ('pending', 'diproses', 'siap')
    AND p.kategori IN $kategoriMakanan
    ORDER BY t.tanggal DESC
");

// TAB 3: PESANAN SELESAI (status Selesai dan MAKANAN)
$pesananSelesai = query("
    SELECT DISTINCT t.*, 
    COALESCE(t.status_pesanan, 'Menunggu') as status_pesanan,
    COALESCE(t.alasan_tolak, '') as alasan_tolak
    FROM transaksi t
    JOIN detail_transaksi dt ON t.id = dt.transaksi_id
    JOIN produk p ON dt.produk_id = p.id
    WHERE LOWER(COALESCE(t.status_pesanan, '')) = 'selesai'
    AND p.kategori IN $kategoriMakanan
    ORDER BY t.tanggal DESC
");

// Detail produk untuk PESANAN SELESAI
$detailProdukSelesai = [];
foreach ($pesananSelesai as $row) {
    $id_trans = $row['id'];
    $detailProdukSelesai[$id_trans] = query("
        SELECT dt.*, p.nama, p.gambar, p.harga as harga_produk, p.kategori
        FROM detail_transaksi dt
        JOIN produk p ON dt.produk_id = p.id
        WHERE dt.transaksi_id = $id_trans
        AND p.kategori IN $kategoriMakanan
    ");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Makanan - Kitchen</title>
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
        .page-header .subtitle {
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
        .btn-warning { background: #f39c12; color: #fff; }
        .btn-warning:hover { background: #d68910; transform: translateY(-2px); }
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-sm { padding: 6px 14px; font-size: 12px; }
        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-hapus {
            background: #e74c3c;
            color: #fff;
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-hapus:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        /* ===== TAB ===== */
        .tab-wrapper {
            display: flex;
            gap: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 12px 25px;
            background: transparent;
            color: #888;
            border: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        .tab-btn:hover {
            color: #ddd;
        }
        .tab-btn.active {
            color: #c08b5c;
            border-bottom-color: #c08b5c;
        }
        .tab-btn .badge {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 50%;
            padding: 0 8px;
            font-size: 11px;
            margin-left: 5px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        .table-wrapper { overflow-x: auto; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; }
        table th {
            background: #2a2a2a;
            color: #c08b5c;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #c08b5c;
            font-size: 13px;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #333;
            color: #ddd;
            font-size: 13px;
        }
        table tr:hover { background: #2a2a2a; }
        
        select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #333;
            background: #1f1f1f;
            color: #ddd;
            font-size: 13px;
        }
        select:focus {
            outline: none;
            border-color: #c08b5c;
        }
        select option { background: #1f1f1f; }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
        }
        .status-menunggu { background: #f1c40f; color: #1a1a1a; }
        .status-pending { background: #f39c12; color: #1a1a1a; }
        .status-diproses { background: #3498db; color: #fff; }
        .status-siap { background: #2ecc71; color: #1a1a1a; }
        .status-selesai { background: #27ae60; color: #fff; }
        .status-ditolak { background: #e74c3c; color: #fff; }
        
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px 0;
            font-style: italic;
        }
        
        .alasan-text {
            color: #e74c3c;
            font-size: 12px;
            font-style: italic;
        }
        
        /* ===== DETAIL PRODUK ===== */
        .detail-produk {
            margin-top: 5px;
            padding: 8px 10px;
            background: #2a2a2a;
            border-radius: 6px;
            border: 1px solid #333;
        }
        .detail-produk .produk-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #333;
        }
        .detail-produk .produk-item:last-child {
            border-bottom: none;
        }
        .detail-produk .produk-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #c08b5c;
        }
        .detail-produk .produk-item .info {
            display: flex;
            flex-direction: column;
        }
        .detail-produk .produk-item .info .nama {
            color: #ddd;
            font-weight: 600;
            font-size: 13px;
        }
        .detail-produk .produk-item .info .harga {
            color: #c08b5c;
            font-size: 12px;
        }
        .detail-produk .produk-item .info .kategori-tag {
            color: #888;
            font-size: 10px;
            font-weight: 400;
        }
        
        /* ===== MODAL TOLAK ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #1f1f1f;
            border: 1px solid #c08b5c;
            border-radius: 12px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .modal-box h3 {
            color: #c08b5c;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .modal-box label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .modal-box textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 14px;
            min-height: 80px;
            resize: vertical;
            box-sizing: border-box;
        }
        .modal-box textarea:focus {
            outline: none;
            border-color: #c08b5c;
        }
        .modal-box .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .modal-box .modal-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .info-box {
            background: #2a2a2a;
            border: 1px solid #333;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .info-box .item {
            color: #888;
            font-size: 13px;
        }
        .info-box .item strong {
            color: #c08b5c;
        }
        
        .aksi-buka {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .status-text {
            font-weight: 600;
        }
        .status-text.pending { color: #f39c12; }
        .status-text.diproses { color: #3498db; }
        .status-text.siap { color: #2ecc71; }
        .status-text.selesai { color: #27ae60; }
        .status-text.ditolak { color: #e74c3c; }
        
        /* Badge count */
        .badge-count {
            display: inline-block;
            background: #e74c3c;
            color: #fff;
            border-radius: 50%;
            padding: 2px 9px;
            font-size: 11px;
            margin-left: 5px;
            font-weight: 700;
        }
        
        /* Label Makanan */
        .label-makanan {
            display: inline-block;
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            font-size: 10px;
            padding: 1px 8px;
            border-radius: 10px;
            font-weight: 600;
            margin-left: 5px;
        }
        
        @media (max-width: 600px) {
            .page-header { flex-direction: column; text-align: center; }
            table { font-size: 13px; }
            table th, table td { padding: 8px 6px; }
            .tab-wrapper { flex-direction: column; border-bottom: none; }
            .tab-btn { border-bottom: 1px solid #333; }
            .tab-btn.active { border-bottom-color: #c08b5c; }
            .info-box { flex-direction: column; gap: 5px; }
            .detail-produk .produk-item { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>🍳 Dapur Kitchen</h1>
                <p class="subtitle">📌 Khusus <strong style="color:#e74c3c;">MAKANAN</strong> (Makanan, Snack, Pastry, Dessert)</p>
            </div>
            <div class="btn-group">
                <a href="dashboard.php" class="btn btn-primary">← Dashboard</a>
                <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <!-- ===== TAB ===== -->
        <div class="tab-wrapper">
            <button class="tab-btn <?= $tab == 'buka' ? 'active' : '' ?>" onclick="switchTab('buka')">
                📋 Buka Pesanan
                <?php if (count($pesananBuka) > 0): ?>
                    <span class="badge-count"><?= count($pesananBuka) ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-btn <?= $tab == 'kelola' ? 'active' : '' ?>" onclick="switchTab('kelola')">
                🔧 Kelola Masakan
                <?php if (count($pesananKelola) > 0): ?>
                    <span class="badge-count"><?= count($pesananKelola) ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-btn <?= $tab == 'selesai' ? 'active' : '' ?>" onclick="switchTab('selesai')">
                ✅ Selesai
                <?php if (count($pesananSelesai) > 0): ?>
                    <span class="badge-count"><?= count($pesananSelesai) ?></span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ============================================================ -->
        <!-- ===== TAB 1: BUKA PESANAN (HANYA Menunggu & MAKANAN) ===== -->
        <!-- ============================================================ -->
        <div class="tab-content <?= $tab == 'buka' ? 'active' : '' ?>" id="tab-buka">
            <div style="margin-bottom:15px;">
                <span style="color:#888; font-size:14px;">📌 Pesanan <strong style="color:#e74c3c;">MAKANAN</strong> baru masuk. <strong style="color:#f1c40f;">Cek ketersediaan bahan</strong> sebelum menerima pesanan.</span>
            </div>
            
            <div class="table-wrapper">
                <?php if (empty($pesananBuka)): ?>
                    <div class="empty-state">✅ Tidak ada pesanan makanan baru masuk</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Detail Pesanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesananBuka as $row): 
                                $detail = $detailProdukBuka[$row['id']] ?? [];
                            ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($row['kode_transaksi']) ?></strong></td>
                                    <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                    <td><?= $row['metode_pembayaran'] ?: '-' ?></td>
                                    <td>
                                        <span class="status-badge status-menunggu">🟡 Menunggu</span>
                                    </td>
                                    <td>
                                        <div class="detail-produk">
                                            <?php foreach ($detail as $item): ?>
                                                <div class="produk-item">
                                                    <img src="../<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                                                    <div class="info">
                                                        <span class="nama"><?= htmlspecialchars($item['nama']) ?></span>
                                                        <span class="kategori-tag">📂 <?= htmlspecialchars($item['kategori']) ?></span>
                                                        <span class="harga"><?= $item['qty'] ?> x Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="aksi-buka">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                                <button type="submit" name="terima_pesanan" class="btn btn-success btn-sm" onclick="return confirm('Cek bahan sudah tersedia? Terima pesanan ini?')">
                                                    ✅ Terima
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="openTolakModal(<?= $row['id'] ?>)">
                                                ❌ Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- ===== TAB 2: KELOLA MASAKAN (Pending, Diproses, Siap & MAKANAN) ===== -->
        <!-- ============================================================ -->
        <div class="tab-content <?= $tab == 'kelola' ? 'active' : '' ?>" id="tab-kelola">
            <div class="info-box">
                <div class="item">🟠 <strong>Pending</strong> = Menunggu diproses</div>
                <div class="item">🔵 <strong>Diproses</strong> = Sedang dimasak</div>
                <div class="item">🟢 <strong>Siap</strong> = Masakan siap diantar</div>
                <div class="item">✅ <strong>Selesai</strong> = Pesanan selesai (otomatis pindah ke tab Selesai)</div>
            </div>

            <div class="table-wrapper">
                <?php if (empty($pesananKelola)): ?>
                    <div class="empty-state">Tidak ada pesanan makanan yang perlu dikelola</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesananKelola as $row): 
                                $status = strtolower($row['status_pesanan'] ?? 'pending');
                                $class = 'status-' . $status;
                                if (!in_array($status, ['pending', 'diproses', 'siap'])) {
                                    $class = 'status-pending';
                                }
                                $displayStatus = ucfirst($row['status_pesanan'] ?? 'Pending');
                            ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($row['kode_transaksi']) ?></strong></td>
                                    <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                    <td><?= $row['metode_pembayaran'] ?: '-' ?></td>
                                    <td>
                                        <span class="status-badge <?= $class ?>">
                                            <?= $displayStatus ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:flex; gap:5px; flex-wrap:wrap; align-items:center;">
                                            <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                            <select name="status">
                                                <option value="Pending" <?= ($row['status_pesanan'] ?? '') == 'Pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                                <option value="Diproses" <?= ($row['status_pesanan'] ?? '') == 'Diproses' ? 'selected' : '' ?>>🔧 Diproses</option>
                                                <option value="Siap" <?= ($row['status_pesanan'] ?? '') == 'Siap' ? 'selected' : '' ?>>✅ Siap</option>
                                                <option value="Selesai" <?= ($row['status_pesanan'] ?? '') == 'Selesai' ? 'selected' : '' ?>>🎉 Selesai</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-success btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- ===== TAB 3: PESANAN SELESAI (MAKANAN) ===== -->
        <!-- ============================================================ -->
        <div class="tab-content <?= $tab == 'selesai' ? 'active' : '' ?>" id="tab-selesai">
            <div style="margin-bottom:15px;">
                <span style="color:#888; font-size:14px;">✅ Pesanan <strong style="color:#e74c3c;">MAKANAN</strong> yang sudah selesai dimasak akan muncul di sini secara otomatis.</span>
            </div>

            <div class="table-wrapper">
                <?php if (empty($pesananSelesai)): ?>
                    <div class="empty-state">Belum ada pesanan makanan yang selesai</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Detail Pesanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesananSelesai as $row): 
                                $detail = $detailProdukSelesai[$row['id']] ?? [];
                            ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($row['kode_transaksi']) ?></strong></td>
                                    <td><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
                                    <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                    <td><?= $row['metode_pembayaran'] ?: '-' ?></td>
                                    <td>
                                        <span class="status-badge status-selesai">✅ Selesai</span>
                                    </td>
                                    <td>
                                        <div class="detail-produk">
                                            <?php foreach ($detail as $item): ?>
                                                <div class="produk-item">
                                                    <img src="../<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                                                    <div class="info">
                                                        <span class="nama"><?= htmlspecialchars($item['nama']) ?></span>
                                                        <span class="kategori-tag">📂 <?= htmlspecialchars($item['kategori']) ?></span>
                                                        <span class="harga"><?= $item['qty'] ?> x Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="aksi-buka">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                                <button type="submit" name="hapus_transaksi" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus transaksi #<?= htmlspecialchars($row['kode_transaksi']) ?>?')">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ===== MODAL TOLAK ===== -->
    <div class="modal-overlay" id="modalTolak">
        <div class="modal-box">
            <h3>❌ Tolak Pesanan Makanan</h3>
            <p style="color:#888; margin-bottom:15px;">Berikan alasan mengapa pesanan makanan ini ditolak</p>
            <form method="POST">
                <input type="hidden" name="id_transaksi" id="tolak_id">
                <label for="alasan_tolak">Alasan Penolakan</label>
                <textarea name="alasan_tolak" id="alasan_tolak" placeholder="Contoh: Bahan habis, menu tidak tersedia, dll..." required></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn btn-primary" onclick="closeTolakModal()" style="flex:1;">Batal</button>
                    <button type="submit" name="tolak_pesanan" class="btn btn-danger" style="flex:2;">❌ Tolak Pesanan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== SWITCH TAB =====
        function switchTab(tab) {
            if (history.pushState) {
                var url = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tab;
                window.history.pushState({path: url}, '', url);
            }
            
            document.querySelectorAll('.tab-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.remove('active');
            });
            
            document.querySelectorAll('.tab-btn').forEach(function(btn) {
                if (btn.getAttribute('onclick') === "switchTab('" + tab + "')") {
                    btn.classList.add('active');
                }
            });
            document.getElementById('tab-' + tab).classList.add('active');
        }

        // ===== MODAL TOLAK =====
        function openTolakModal(id) {
            document.getElementById('tolak_id').value = id;
            document.getElementById('modalTolak').classList.add('active');
            document.getElementById('alasan_tolak').value = '';
        }
        
        function closeTolakModal() {
            document.getElementById('modalTolak').classList.remove('active');
        }
        
        document.getElementById('modalTolak').addEventListener('click', function(e) {
            if (e.target === this) closeTolakModal();
        });
    </script>
</body>
</html>