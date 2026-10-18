<?php
// FILE: user/menu.php
// Halaman Detail Produk

session_start();
require '../fungsi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Buat session_id untuk keranjang
if (!isset($_SESSION['cart_session'])) {
    $_SESSION['cart_session'] = session_id() . '_' . time();
}
$session_id = $_SESSION['cart_session'];

// ===== PROSES TAMBAH ITEM KE KERANJANG =====
if (isset($_POST['tambah_item'])) {
    $produk_id = intval($_POST['produk_id']);
    $qty = intval($_POST['qty'] ?? 1);
    
    $insert = mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty, session_id, selected) VALUES ($produk_id, $qty, '$session_id', 0)");
    
    if ($insert) {
        $_SESSION['cart_message'] = '✅ Item berhasil ditambahkan ke keranjang!';
    } else {
        $_SESSION['cart_message'] = '❌ Gagal menambahkan item!';
    }
    header("Location: index.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Validasi ID
if (!$id) {
    header("Location: index.php");
    exit;
}

// Ambil Data Produk berdasarkan ID
$data = query("SELECT * FROM produk WHERE id = $id");

// Cek apakah data ketemu?
if (empty($data)) {
    echo "<script>alert('Menu tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// Ambil elemen pertama
$mhs = $data[0];

// Hitung item di keranjang
$cartCount = 0;
$cartItems = query("SELECT COUNT(*) as total FROM keranjang WHERE session_id = '$session_id'");
if (!empty($cartItems)) {
    $cartCount = $cartItems[0]['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($mhs['nama']) ?> - Detail</title>
    <link rel="stylesheet" href="../css/style-user.css">
    <style>
        /* ===== STYLE TAMBAHAN ===== */
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px 20px;
            background: #1a1a1a;
            border-bottom: 1px solid #333;
        }
        .header-top .header-left {
            color: #c08b5c;
            font-size: 18px;
            font-weight: 700;
        }
        .header-top .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .btn-cart {
            background: #c08b5c;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-cart:hover {
            background: #a67b5b;
            transform: translateY(-2px);
        }
        .btn-cart .badge {
            background: #e74c3c;
            color: #fff;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 700;
            margin-left: 3px;
        }
        .btn-logout {
            background: #8B0000;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: #660000;
            transform: translateY(-2px);
        }
        .btn-add-cart {
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s;
        }
        .btn-add-cart:hover {
            background: #219a52;
            transform: scale(1.02);
        }
        .btn-add-cart:active {
            transform: scale(0.97);
        }
        .detail-card {
            max-width: 500px;
            margin: 30px auto;
            background: #2a2a2a;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #333;
            text-align: center;
        }
        .detail-card img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        .detail-card h2 {
            color: #c08b5c;
            margin: 15px 0 5px;
        }
        .detail-card .harga {
            font-size: 28px;
            color: #c08b5c;
            font-weight: 700;
            margin: 0 0 20px;
        }
        .detail-card .deskripsi {
            line-height: 1.6;
            color: #888;
            margin-bottom: 20px;
        }
        .detail-card .kategori {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }
        .detail-card .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .btn-outline {
            background: transparent;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-outline:hover {
            background: #c08b5c;
            color: #1a1a1a;
        }
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #333;
        }
        .alert-success {
            background: rgba(39, 174, 96, 0.15);
            color: #2ecc71;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #27ae60;
            margin: 10px 20px;
            text-align: center;
        }
        @media (max-width: 600px) {
            .header-top {
                flex-direction: column;
                text-align: center;
            }
            .detail-card {
                padding: 20px;
                margin: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== HEADER ===== -->
    <div class="header-top">
        <div class="header-left">☕ Cafeku</div>
        <div class="header-right">
            <a href="pembayaran.php" class="btn-cart">
                🛒 Keranjang
                <?php if ($cartCount > 0): ?>
                    <span class="badge"><?= $cartCount ?></span>
                <?php endif; ?>
            </a>
            <a href="../logout.php" class="btn-logout">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== NOTIFIKASI ===== -->
    <?php 
    $message = $_SESSION['cart_message'] ?? '';
    unset($_SESSION['cart_message']);
    if ($message): 
    ?>
        <div class="alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- ===== DETAIL PRODUK ===== -->
    <div class="detail-card">
        <img src="../<?= $mhs['gambar'] ?>" alt="<?= htmlspecialchars($mhs['nama']) ?>">

        <h2><?= htmlspecialchars($mhs['nama']) ?></h2>
        <div class="harga">Rp <?= number_format($mhs['harga'], 0, ',', '.') ?></div>

        <div class="deskripsi">
            <?= nl2br(htmlspecialchars($mhs['deskripsi'] ?? 'Tidak ada deskripsi')) ?>
        </div>

        <div class="kategori">📂 Kategori: <?= strtoupper(htmlspecialchars($mhs['kategori'])) ?></div>

        <!-- ===== FORM TAMBAH KE KERANJANG ===== -->
        <form method="POST" style="display:inline;">
            <input type="hidden" name="produk_id" value="<?= $mhs['id'] ?>">
            <input type="hidden" name="qty" value="1">
            <button type="submit" name="tambah_item" class="btn-add-cart">🛒 Tambah ke Keranjang</button>
        </form>

        <div class="btn-group">
            <a href="index.php?kategori=<?= urlencode($mhs['kategori']) ?>" class="btn-outline">← Kembali</a>
            <a href="index.php" class="btn-primary">🏠 Menu Utama</a>
        </div>
    </div>

</body>
</html>