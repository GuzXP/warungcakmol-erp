<?php
// FILE: user/pembayaran.php
session_start();
require '../fungsi.php';

// ===== FUNGSI FORMAT RUPIAH UNTUK PHP =====
function formatRp($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Pastikan session_id ada
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id() . '_' . time();
}
$session_id = $_SESSION['cart_session'];

// ===== PROSES HAPUS ITEM =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id AND session_id = '$session_id'");
    header("Location: pembayaran.php");
    exit;
}

// ===== PROSES HAPUS SEMUA =====
if (isset($_POST['reset_order'])) {
    mysqli_query($conn, "DELETE FROM keranjang WHERE session_id = '$session_id'");
    header("Location: pembayaran.php");
    exit;
}

// ===== PROSES BAYAR =====
if (isset($_POST['bayar_pesanan'])) {
    $selected_items = $_POST['selected_items'] ?? [];
    
    if (empty($selected_items)) {
        echo "<script>alert('⚠️ Pilih minimal 1 item untuk dibayar!'); window.location='pembayaran.php';</script>";
        exit;
    }
    
    // Mark item yang dipilih sebagai selected
    $ids = implode(',', array_map('intval', $selected_items));
    mysqli_query($conn, "UPDATE keranjang SET selected = 1 WHERE id IN ($ids) AND session_id = '$session_id'");
    
    // ===== TAMPILKAN NOTIFIKASI "HARAP BAYAR DI KASIR" =====
    echo "<script>
        alert('✅ Pesanan berhasil dibuat!\\n\\n📌 HARAP BAYAR DI KASIR\\n\\nSilakan menuju kasir untuk melakukan pembayaran.');
        window.location='../admin/pembayaran.php?session_id=" . urlencode($session_id) . "';
    </script>";
    exit;
}

// ===== UPDATE QTY =====
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $aksi = $_GET['aksi'];
    
    if ($aksi == 'tambah') {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE id = $id AND session_id = '$session_id'");
    } elseif ($aksi == 'kurang') {
        $cek = query("SELECT qty FROM keranjang WHERE id = $id AND session_id = '$session_id'");
        if (!empty($cek) && $cek[0]['qty'] > 1) {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty - 1 WHERE id = $id AND session_id = '$session_id'");
        } else {
            mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id AND session_id = '$session_id'");
        }
    }
    header("Location: pembayaran.php");
    exit;
}

// ===== AMBIL SEMUA ITEM DI KERANJANG =====
$items = query("
    SELECT k.*, p.nama, p.harga, p.gambar 
    FROM keranjang k 
    JOIN produk p ON k.produk_id = p.id 
    WHERE k.session_id = '$session_id'
    ORDER BY k.id ASC
");

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}

$ppn = round($subtotal * 0.1);
$total = $subtotal + $ppn;
$itemCount = count($items);
$selectedCount = 0;
foreach ($items as $item) {
    if ($item['selected'] == 1) $selectedCount++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        .user-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
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
        .btn-sm { padding: 6px 14px; font-size: 12px; }
        .btn-back {
            display: inline-block;
            padding: 8px 16px;
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: #333;
            transform: translateY(-2px);
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
            font-size: 16px;
        }
        
        #order-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        #order-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #333;
            gap: 8px;
        }
        #order-list li .item-nama {
            flex: 1;
            color: #ddd;
            font-weight: 600;
            font-size: 14px;
        }
        #order-list li .item-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #order-list li .item-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #27ae60;
            cursor: pointer;
        }
        #order-list li .item-check label {
            color: #ddd;
            cursor: pointer;
        }
        .qty-control {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .qty-btn {
            background: rgba(192,139,92,0.2);
            border: 1px solid #c08b5c;
            color: #c08b5c;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            line-height: 1;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qty-btn:hover {
            background: #c08b5c;
            color: #1a1a1a;
        }
        .qty-control span {
            min-width: 24px;
            text-align: center;
            color: #ddd;
            font-weight: 600;
        }
        .item-total {
            min-width: 90px;
            text-align: right;
            color: #c08b5c;
            font-weight: 600;
            font-size: 14px;
        }
        .item-actions {
            display: flex;
            gap: 5px;
        }
        .total-box {
            border-top: 1px solid #333;
            padding-top: 15px;
            margin-top: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 14px;
            padding: 4px 0;
        }
        .total-row.grand {
            color: #c08b5c;
            font-weight: 700;
            font-size: 18px;
            padding-top: 8px;
            border-top: 1px solid #333;
            margin-top: 5px;
        }
        .empty-cart {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 30px 0;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        .btn-group .btn {
            flex: 1;
        }
        .info-bayar {
            background: rgba(192, 139, 92, 0.1);
            border: 1px solid #c08b5c;
            border-radius: 8px;
            padding: 12px 15px;
            margin-top: 10px;
            color: #c08b5c;
            font-weight: 600;
            text-align: center;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            #order-list li {
                flex-wrap: wrap;
                gap: 5px;
            }
            #order-list li .item-nama {
                width: 100%;
                order: 1;
            }
            #order-list li .item-check {
                order: 2;
            }
            #order-list li .qty-control {
                order: 3;
            }
            #order-list li .item-total {
                order: 4;
            }
            #order-list li .item-actions {
                order: 5;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="user-container">
    <!-- HEADER -->
    <div class="page-header">
        <h1>🛒 Keranjang</h1>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="index.php" class="btn-back">← Kembali Menu</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- KERANJANG -->
    <div class="card">
        <h3>🧾 Pesanan <?= $itemCount > 0 ? '(' . $itemCount . ' item)' : '' ?></h3>

        <?php if (empty($items)): ?>
            <div class="empty-cart">🛒 Keranjang kosong</div>
        <?php else: ?>
            <form method="POST">
                <ul id="order-list">
                    <?php foreach ($items as $item): ?>
                        <?php 
                        $itemTotal = $item['harga'] * $item['qty'];
                        $isSelected = $item['selected'] == 1;
                        ?>
                        <li>
                            <div class="item-check">
                                <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" 
                                       id="item_<?= $item['id'] ?>" <?= $isSelected ? 'checked' : '' ?>>
                                <label for="item_<?= $item['id'] ?>">
                                    <img src="../<?= $item['gambar'] ?>" style="width:30px; height:30px; object-fit:cover; border-radius:4px; vertical-align:middle; margin-right:5px;">
                                </label>
                            </div>
                            <span class="item-nama"><?= htmlspecialchars($item['nama']) ?></span>
                            
                            <div class="qty-control">
                                <a href="?id=<?= $item['id'] ?>&aksi=kurang" class="qty-btn">−</a>
                                <span><?= $item['qty'] ?></span>
                                <a href="?id=<?= $item['id'] ?>&aksi=tambah" class="qty-btn">+</a>
                            </div>
                            
                            <span class="item-total"><?= formatRp($itemTotal) ?></span>
                            
                            <div class="item-actions">
                                <a href="?hapus=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus item ini?')">🗑️</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- TOTAL -->
                <div class="total-box">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span><?= formatRp($subtotal) ?></span>
                    </div>
                    <div class="total-row">
                        <span>PPN 10%</span>
                        <span><?= formatRp($ppn) ?></span>
                    </div>
                    <div class="total-row grand">
                        <span>TOTAL</span>
                        <span><?= formatRp($total) ?></span>
                    </div>
                </div>

                <!-- ===== INFO BAYAR DI KASIR ===== -->
                <div class="info-bayar">
                    💳 Pembayaran hanya dapat dilakukan di <strong>KASIR</strong>
                </div>

                <!-- BUTTONS -->
                <div class="btn-group">
                    <button type="submit" name="bayar_pesanan" class="btn btn-success">
                        💳 Bayar di Kasir (<?= $selectedCount ?> item)
                    </button>
                    <button type="submit" name="reset_order" class="btn btn-danger" onclick="return confirm('Kosongkan semua pesanan?')">
                        🗑️ Kosongkan
                    </button>
                </div>
                <div style="margin-top:8px; color:#666; font-size:12px; text-align:center;">
                    ✅ Centang item yang ingin dibayar
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>