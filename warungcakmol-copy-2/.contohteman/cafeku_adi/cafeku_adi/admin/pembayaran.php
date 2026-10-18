<?php
// FILE: admin/pembayaran.php
session_start();
if (!isset($_SESSION["login"]) || !in_array($_SESSION['role'], ['admin', 'kasir'])) {
    header("Location: ../login.php");
    exit;
}

require '../fungsi.php';

$paymentSuccess = false;
$notice = '';
$noticeClass = '';

// ===== CEK APAKAH ADA ORDER DARI USER =====
if (isset($_GET['session_id'])) {
    $session_id = mysqli_real_escape_string($conn, $_GET['session_id']);
    
    $items = query("
        SELECT k.*, p.nama, p.harga, p.gambar 
        FROM keranjang k 
        JOIN produk p ON k.produk_id = p.id 
        WHERE k.session_id = '$session_id' AND k.selected = 1
    ");
    
    if (!empty($items)) {
        mysqli_query($conn, "DELETE FROM keranjang WHERE session_id IS NULL OR session_id = ''");
        foreach ($items as $item) {
            $produk_id = $item['produk_id'];
            $qty = $item['qty'];
            
            $cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE produk_id = $produk_id AND (session_id IS NULL OR session_id = '')");
            if (mysqli_num_rows($cek) > 0) {
                mysqli_query($conn, "UPDATE keranjang SET qty = qty + $qty WHERE produk_id = $produk_id AND (session_id IS NULL OR session_id = '')");
            } else {
                mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty, session_id, selected) VALUES ($produk_id, $qty, NULL, 0)");
            }
        }
        mysqli_query($conn, "DELETE FROM keranjang WHERE session_id = '$session_id' AND selected = 1");
        
        $notice = '✅ Pesanan dari user berhasil diterima di kasir!';
        $noticeClass = 'notice-success';
    } else {
        $notice = '⚠️ Tidak ada item yang dipilih untuk dibayar!';
        $noticeClass = 'notice-error';
    }
}

// ===== CEK APAKAH ADA KODE TRANSAKSI YANG DIKIRIM (DARI DASHBOARD) =====
$kode_transaksi_dari_dashboard = '';
$transaksi_ditemukan = false;
$detail_transaksi = [];
$dataTransaksi = null;
$id_transaksi_existing = 0;

if (isset($_GET['kode']) && !empty($_GET['kode'])) {
    $kode_transaksi_dari_dashboard = mysqli_real_escape_string($conn, $_GET['kode']);
    
    $cariTransaksi = query("SELECT * FROM transaksi WHERE kode_transaksi = '$kode_transaksi_dari_dashboard'");
    
    if (!empty($cariTransaksi)) {
        $transaksi_ditemukan = true;
        $dataTransaksi = $cariTransaksi[0];
        $id_transaksi_existing = $dataTransaksi['id'];
        
        if (!empty($dataTransaksi['metode_pembayaran'])) {
            $notice = '✅ Pesanan ini sudah lunas!';
            $noticeClass = 'notice-success';
        } else {
            $detail_transaksi = query("
                SELECT dt.*, p.nama, p.gambar, p.harga as harga_produk
                FROM detail_transaksi dt
                JOIN produk p ON dt.produk_id = p.id
                WHERE dt.transaksi_id = $id_transaksi_existing
            ");
            
            if (!empty($detail_transaksi)) {
                mysqli_query($conn, "DELETE FROM keranjang");
                
                foreach ($detail_transaksi as $item) {
                    $produk_id = $item['produk_id'];
                    $qty = $item['qty'];
                    
                    $cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE produk_id = $produk_id");
                    if (mysqli_num_rows($cek) > 0) {
                        mysqli_query($conn, "UPDATE keranjang SET qty = qty + $qty WHERE produk_id = $produk_id");
                    } else {
                        mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty) VALUES ($produk_id, $qty)");
                    }
                }
                
                $notice = '✅ Pesanan dengan kode <strong>' . $kode_transaksi_dari_dashboard . '</strong> sudah dimasukkan ke kasir. Silakan lanjutkan pembayaran.';
                $noticeClass = 'notice-success';
            }
        }
    } else {
        $notice = '❌ Pesanan dengan kode <strong>' . $kode_transaksi_dari_dashboard . '</strong> tidak ditemukan!';
        $noticeClass = 'notice-error';
    }
}

// ===== PROSES PEMBAYARAN =====
if (isset($_POST['pay'])) {
    $orderData = json_decode($_POST['order_data'] ?? '[]', true);
    $metode = $_POST['metode'] ?? '';
    $bayar_uang = intval($_POST['bayar_uang'] ?? 0);
    $kode_existing = $_POST['kode_existing'] ?? '';

    if (empty($orderData) || !is_array($orderData)) {
        $notice = 'Pesanan kosong. Tambahkan item terlebih dahulu.';
        $noticeClass = 'notice-error';
    } elseif ($metode === '') {
        $notice = 'Pilih metode pembayaran terlebih dahulu.';
        $noticeClass = 'notice-error';
    } else {
        $subtotal = 0;
        foreach ($orderData as $item) {
            $subtotal += intval($item['harga']) * intval($item['qty']);
        }

        $ppn = round($subtotal * 0.1);
        $grandTotal = $subtotal + $ppn;

        if ($metode === 'Cash' && $bayar_uang < $grandTotal) {
            $notice = 'Uang bayar tidak cukup.';
            $noticeClass = 'notice-error';
        } else {
            $kembalian = ($metode === 'Cash') ? ($bayar_uang - $grandTotal) : 0;
            $kasir = htmlspecialchars($_SESSION['nama'] ?? 'Kasir', ENT_QUOTES);

            if (!empty($kode_existing)) {
                $kodeTransaksi = $kode_existing;
                
                $stmt = mysqli_prepare($conn, "
                    UPDATE transaksi 
                    SET total = ?, bayar = ?, kembalian = ?, metode_pembayaran = ?, kasir = ? 
                    WHERE kode_transaksi = ?
                ");
                mysqli_stmt_bind_param($stmt, "iiisss", $grandTotal, $bayar_uang, $kembalian, $metode, $kasir, $kodeTransaksi);
                mysqli_stmt_execute($stmt);
                
                $cariId = query("SELECT id FROM transaksi WHERE kode_transaksi = '$kodeTransaksi'");
                $transaksiId = $cariId[0]['id'];
                
                mysqli_query($conn, "DELETE FROM detail_transaksi WHERE transaksi_id = $transaksiId");
                
                foreach ($orderData as $produkId => $item) {
                    $produkId = intval($produkId);
                    $qty = intval($item['qty']);
                    $harga = intval($item['harga']);
                    $subtotalItem = $qty * $harga;

                    mysqli_query($conn, "INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, harga, subtotal) VALUES ($transaksiId, $produkId, $qty, $harga, $subtotalItem)");
                }
                
                $notice = '✅ Pembayaran berhasil! Kode: ' . $kodeTransaksi;
                $noticeClass = 'notice-success';
                
            } else {
                $kodeTransaksi = 'TRX' . time() . rand(100, 999);

                $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (kode_transaksi, total, bayar, kembalian, metode_pembayaran, kasir) VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "siiiss", $kodeTransaksi, $grandTotal, $bayar_uang, $kembalian, $metode, $kasir);
                mysqli_stmt_execute($stmt);

                $transaksiId = mysqli_insert_id($conn);

                foreach ($orderData as $produkId => $item) {
                    $produkId = intval($produkId);
                    $qty = intval($item['qty']);
                    $harga = intval($item['harga']);
                    $subtotalItem = $qty * $harga;

                    mysqli_query($conn, "INSERT INTO detail_transaksi (transaksi_id, produk_id, qty, harga, subtotal) VALUES ($transaksiId, $produkId, $qty, $harga, $subtotalItem)");
                }
                
                $notice = '✅ Transaksi berhasil disimpan. Kode: ' . $kodeTransaksi;
                $noticeClass = 'notice-success';
            }

            mysqli_query($conn, "DELETE FROM keranjang");
            $paymentSuccess = true;
        }
    }
}

// Ambil data produk untuk menu di sebelah kiri
$produk = query("SELECT * FROM produk ORDER BY kategori ASC, nama ASC");

// Ambil data keranjang dari database
$data_keranjang = query("SELECT k.produk_id, p.nama, p.harga, k.qty 
                         FROM keranjang k 
                         JOIN produk p ON k.produk_id = p.id");

$json_keranjang = json_encode($data_keranjang);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
    /* ===== WARNA COKLAT - TANPA PUTIH ===== */
    :root {
        --coklat-tua: #3E2723;
        --coklat-sedang: #5D4037;
        --coklat-muda: #8D6E63;
        --coklat-sangat-muda: #D7CCC8;
        --coklat-krem: #EFEBE9;
        --coklat-emas: #A67B5B;
        --coklat-gelap: #2C1810;
        --putih: #FFFFFF;
        --hitam: #1a1a1a;
    }

    /* ===== MODAL PEMBAYARAN ===== */
    .modal-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(44, 24, 16, 0.7);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-box {
        background: linear-gradient(145deg, var(--coklat-tua), var(--coklat-gelap));
        border-radius: 16px;
        padding: 30px;
        max-width: 420px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 15px 50px rgba(44, 24, 16, 0.5);
        animation: modalSlide 0.3s ease;
        border: 1px solid var(--coklat-emas);
    }

    @keyframes modalSlide {
        from {
            transform: translateY(-50px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .modal-box h3 {
        margin-top: 0;
        text-align: center;
        font-size: 24px;
        color: var(--putih);
        font-weight: 900;
    }

    .modal-box .subtitle {
        text-align: center;
        color: var(--coklat-sangat-muda);
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .modal-metode {
        display: flex;
        gap: 15px;
        margin: 20px 0;
    }

    .modal-metode button {
        flex: 1;
        padding: 18px 10px;
        border: 2px solid var(--coklat-emas);
        border-radius: 12px;
        background: transparent;
        cursor: pointer;
        font-size: 16px;
        font-weight: 900;
        transition: all 0.3s;
        color: var(--putih);
    }

    .modal-metode button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.05);
    }

    .modal-metode button.active-cash {
        border-color: var(--putih);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
    }

    .modal-metode button.active-qris {
        border-color: var(--putih);
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
    }

    /* Area Cash di Modal */
    .area-cash {
        display: none;
        margin: 15px 0;
    }

    .area-cash label {
        font-weight: 700;
        display: block;
        margin-bottom: 5px;
        font-size: 14px;
        color: var(--putih);
    }

    .nominal-cepat-modal {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .nominal-cepat-modal button {
        padding: 8px 16px;
        border: 1px solid var(--coklat-emas);
        border-radius: 6px;
        background: transparent;
        cursor: pointer;
        font-size: 13px;
        transition: all 0.2s;
        color: var(--putih);
        font-weight: 700;
    }

    .nominal-cepat-modal button:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--putih);
        transform: scale(1.02);
    }

    .area-cash input[type="number"] {
        width: 100%;
        padding: 12px;
        border: 2px solid var(--coklat-emas);
        border-radius: 8px;
        font-size: 18px;
        box-sizing: border-box;
        background: rgba(255, 255, 255, 0.1);
        color: var(--putih);
        font-weight: 700;
        transition: all 0.3s;
    }

    .area-cash input[type="number"]:focus {
        border-color: var(--putih);
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.15);
    }

    .area-cash input[type="number"]::placeholder {
        color: var(--coklat-sangat-muda);
    }

    .kembalian-box-modal {
        display: none;
        margin-top: 12px;
        padding: 12px;
        border-radius: 8px;
        border: 2px solid var(--putih);
        background: rgba(255, 255, 255, 0.05);
        color: var(--putih);
        font-weight: 900;
        font-size: 16px;
        text-align: center;
    }

    .kembalian-box-modal.kurang {
        border-color: #FF6B6B;
        background: rgba(255, 107, 107, 0.1);
        color: #FF6B6B;
    }

    /* Area QRIS di Modal */
    .area-qris {
        display: none;
        margin: 15px 0;
        text-align: center;
    }

    .area-qris .total-qris {
        font-weight: 900;
        font-size: 20px;
        margin-bottom: 12px;
        color: var(--putih);
    }

    .area-qris .qr-container {
        padding: 15px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: inline-block;
        border: 2px solid var(--coklat-emas);
    }

    .area-qris .qr-container img {
        display: block;
        width: 180px;
        height: 180px;
        object-fit: contain;
    }

    .area-qris .qr-note {
        margin-top: 10px;
        font-size: 13px;
        color: var(--coklat-sangat-muda);
        font-weight: 600;
    }

    .area-qris .qr-note small {
        color: var(--coklat-muda);
    }

    .area-qris .qr-confirm {
        margin-top: 12px;
        padding: 10px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        font-size: 13px;
        color: var(--putih);
        font-weight: 700;
        border: 1px solid var(--coklat-emas);
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .modal-actions button {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 15px;
        font-weight: 900;
        transition: all 0.3s;
    }

    .modal-actions .btn-batal {
        background: var(--coklat-sangat-muda);
        color: var(--coklat-tua);
    }

    .modal-actions .btn-batal:hover {
        background: var(--coklat-muda);
        color: var(--putih);
    }

    .modal-actions .btn-bayar {
        background: var(--putih);
        color: var(--coklat-tua);
        flex: 2;
    }

    .modal-actions .btn-bayar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
    }

    .modal-actions .btn-bayar:disabled {
        background: var(--coklat-muda);
        color: var(--coklat-sangat-muda);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Tombol Bayar utama */
    .btn-bayar-utama {
        display: block;
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, var(--coklat-tua), var(--coklat-gelap));
        color: var(--putih);
        border: 2px solid var(--coklat-emas);
        border-radius: 10px;
        font-size: 18px;
        font-weight: 900;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 15px;
        box-shadow: 0 4px 15px rgba(62, 39, 35, 0.3);
        letter-spacing: 1px;
    }

    .btn-bayar-utama:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 25px rgba(255, 255, 255, 0.1);
        background: linear-gradient(135deg, var(--coklat-gelap), #1a0f0a);
        border-color: var(--putih);
    }

    .btn-bayar-utama:active {
        transform: translateY(0);
    }

    /* Style untuk struk */
    .struk-panel .total-box {
        background: transparent;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
        border: 1px solid var(--coklat-emas);
    }

    .struk-panel .total-row {
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
        font-size: 14px;
        color: var(--putih);
        font-weight: 700;
    }

    .struk-panel .total-row.grand {
        font-weight: 900;
        font-size: 18px;
        border-top: 2px solid var(--coklat-emas);
        padding-top: 10px;
        margin-top: 5px;
        color: var(--putih);
    }

    .struk-panel .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .struk-panel .btn-group button {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 900;
        font-size: 14px;
        transition: all 0.3s;
    }

    .struk-panel .btn-group .btn-cetak {
        background: var(--coklat-sedang);
        color: var(--putih);
        border: 1px solid var(--coklat-emas);
    }

    .struk-panel .btn-group .btn-cetak:hover {
        background: var(--coklat-tua);
        transform: translateY(-2px);
        border-color: var(--putih);
    }

    .struk-panel .btn-group .btn-reset {
        background: #8B0000;
        color: var(--putih);
        border: 1px solid #8B0000;
    }

    .struk-panel .btn-group .btn-reset:hover {
        background: #660000;
        transform: translateY(-2px);
        border-color: #FF6B6B;
    }

    .notice {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .notice-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .notice-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
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
        padding: 8px 0;
        border-bottom: 1px solid var(--coklat-emas);
    }

    #order-list .empty-cart {
        justify-content: center;
        color: var(--coklat-sangat-muda);
        padding: 20px 0;
        font-style: italic;
        font-weight: 600;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .qty-control .qty-btn {
        width: 28px;
        height: 28px;
        border: 1px solid var(--coklat-emas);
        border-radius: 50%;
        background: transparent;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        color: var(--putih);
        font-weight: 900;
    }

    .qty-control .qty-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--putih);
        transform: scale(1.05);
    }

    .qty-control span {
        min-width: 20px;
        text-align: center;
        font-weight: 900;
        color: var(--putih);
    }

    .item-nama {
        flex: 1;
        font-weight: 700;
        color: var(--putih);
        font-size: 14px;
    }

    /* ===== STRUK PANEL ===== */
    .struk-panel {
        flex: 1.2;
        background: transparent;
        border-radius: 12px;
        padding: 20px;
        max-height: 75vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .struk-panel::-webkit-scrollbar {
        width: 6px;
    }

    .struk-panel::-webkit-scrollbar-track {
        background: var(--coklat-sangat-muda);
        border-radius: 10px;
    }

    .struk-panel::-webkit-scrollbar-thumb {
        background: var(--coklat-muda);
        border-radius: 10px;
    }

    .struk-panel h2 {
        color: var(--putih);
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 20px;
        border-bottom: 2px solid var(--coklat-emas);
        padding-bottom: 10px;
        font-weight: 900;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 900px) {
        .kasir-wrapper {
            flex-direction: column;
        }
    }

    @media (max-width: 500px) {
        .page-header {
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            text-align: center;
        }

        .page-header h1 {
            font-size: 18px;
        }

        .modal-box {
            padding: 20px;
        }

        .modal-metode {
            flex-direction: column;
        }
    }
</style>
</head>
<body>

<div class="admin-container">
    <div class="page-header page-header-split">
        <a href="../kasir/index.php" class="btn">← Dashboard</a>
        <h1>Kasir / Pembayaran</h1>
        <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="kasir-wrapper">
        <!-- PANEL KIRI: Menu Produk -->
        <div class="menu-panel">
            <div class="menu-list">
                <?php foreach ($produk as $item) : ?>
                    <div class="menu-item" onclick="window.location.href='tambah_item_kasir.php?id=<?= $item['id'] ?>'">
                        <img src="../<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nama'], ENT_QUOTES) ?>">
                        <div class="menu-item-info">
                            <div class="nama"><?= $item['nama'] ?></div>
                            <div class="harga">Rp <?= number_format($item['harga'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PANEL KANAN: Pesanan & Pembayaran -->
        <div class="struk-panel">
            <h2>🧾 Pesanan</h2>

            <?php if ($notice) : ?>
                <div class="notice <?= htmlspecialchars($noticeClass) ?>">
                    <?= htmlspecialchars($notice) ?>
                </div>
            <?php endif; ?>

            <?php if ($transaksi_ditemukan && !empty($detail_transaksi)) : ?>
                <div style="background:#2a2a2a; padding:15px; border-radius:10px; border:1px solid #c08b5c; margin-bottom:15px;">
                    <div style="color:#c08b5c; font-weight:700; margin-bottom:8px;">📋 Detail Pesanan dari Kode: <?= htmlspecialchars($kode_transaksi_dari_dashboard) ?></div>
                    <div style="display:flex; flex-wrap:wrap; gap:15px;">
                        <?php foreach ($detail_transaksi as $item) : ?>
                            <div style="display:flex; align-items:center; gap:10px; background:#1f1f1f; padding:8px 12px; border-radius:8px; border:1px solid #333;">
                                <img src="../<?= $item['gambar'] ?>" style="width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #c08b5c;">
                                <div>
                                    <div style="color:#ddd; font-weight:600; font-size:13px;"><?= htmlspecialchars($item['nama']) ?></div>
                                    <div style="color:#c08b5c; font-size:12px;"><?= $item['qty'] ?> x Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <ul id="order-list">
                <li class="empty-cart">Belum ada item dipilih</li>
            </ul>

            <div class="total-box" id="total-box" style="display:none;">
                <div class="total-row"><span>Subtotal</span><span id="subtotal-val">Rp 0</span></div>
                <div class="total-row"><span>PPN 10%</span><span id="ppn-val">Rp 0</span></div>
                <div class="total-row grand"><span>TOTAL</span><span id="total-val">Rp 0</span></div>

                <!-- ============ TOMBOL BAYAR ============ -->
                <button class="btn-bayar-utama" onclick="openModal()">
                    💳 Bayar Sekarang
                </button>

                <div class="btn-group">
                    <button class="btn-cetak" onclick="cetakStruk()">🖨️ Cetak Struk</button>
                    <button class="btn-reset" onclick="resetOrder()">🗑️ Reset</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ MODAL PEMBAYARAN ============ -->
<div class="modal-overlay" id="modalPayment">
    <div class="modal-box">
        <h3>💳 Pembayaran</h3>
        <p class="subtitle">Pilih metode pembayaran di bawah ini</p>

        <!-- Pilihan Metode -->
        <div class="modal-metode">
            <button id="btnCash" onclick="pilihMetode('cash')">💵 Cash</button>
            <button id="btnQris" onclick="pilihMetode('qris')">📱 QRIS</button>
        </div>

        <!-- Area Cash -->
        <div class="area-cash" id="areaCash">
            <label>Nominal Cepat</label>
            <div class="nominal-cepat-modal" id="nominalCepat"></div>

            <label>Uang Bayar</label>
            <input type="number" id="inputBayar" placeholder="Masukkan jumlah uang..." oninput="hitungKembalian()">

            <div class="kembalian-box-modal" id="kembalianBox">
                Kembalian: <span id="kembalianVal">Rp 0</span>
            </div>
        </div>

        <!-- Area QRIS -->
        <div class="area-qris" id="areaQris">
            <div class="total-qris" id="totalQris">Total: Rp 0</div>
            <div class="qr-container">
                <img src="../poto/qris.jpeg" alt="QRIS">
            </div>
            <p class="qr-note">Scan QR di atas untuk membayar<br><small>* Ganti dengan QRIS resmi Anda</small></p>
            <div class="qr-confirm">✅ Konfirmasi pembayaran sudah diterima sebelum melanjutkan</div>
        </div>

        <!-- Tombol Aksi -->
        <div class="modal-actions">
            <button class="btn-batal" onclick="tutupModal()">Batal</button>
            <button class="btn-bayar" id="btnBayarModal" onclick="prosesBayar()">✅ Bayar Sekarang</button>
        </div>
    </div>
</div>

<!-- Form tersembunyi untuk submit -->
<form method="post" id="formPayment" style="display:none;">
    <input type="hidden" name="order_data" id="orderData">
    <input type="hidden" name="metode" id="metodeHidden">
    <input type="hidden" name="bayar_uang" id="bayarHidden">
    <input type="hidden" name="kode_existing" id="kodeExisting">
    <input type="hidden" name="pay" value="1">
</form>

<script>
    // ===== DATA DARI DATABASE =====
    const dataDariDb = <?= $json_keranjang ?>;
    let order = {};
    let metodeTerpilih = null;
    let totalBayar = 0;
    let kodeExisting = '<?= $kode_transaksi_dari_dashboard ?>';

    // Konversi data DB ke object
    dataDariDb.forEach(item => {
        order[item.produk_id] = {
            nama: item.nama,
            harga: parseInt(item.harga),
            qty: parseInt(item.qty)
        };
    });

    // ===== RENDER PESANAN =====
    document.addEventListener('DOMContentLoaded', renderOrder);

    function renderOrder() {
        const list = document.getElementById('order-list');
        const totalBox = document.getElementById('total-box');
        const keys = Object.keys(order);

        list.innerHTML = '';

        if (keys.length === 0) {
            list.innerHTML = '<li class="empty-cart">Belum ada item dipilih</li>';
            totalBox.style.display = 'none';
            return;
        }

        let subtotal = 0;

        keys.forEach(id => {
            const item = order[id];
            const totalItem = item.harga * item.qty;
            subtotal += totalItem;

            list.innerHTML += `
                <li>
                    <span class="item-nama">${item.nama}</span>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(${id}, 'kurang')">−</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${id}, 'tambah')">+</button>
                    </div>
                    <span style="min-width:90px; text-align:right; font-size:13px; font-weight:600; color:var(--putih);">
                        Rp ${totalItem.toLocaleString('id-ID')}
                    </span>
                </li>
            `;
        });

        const ppn = Math.round(subtotal * 0.1);
        const total = subtotal + ppn;
        totalBayar = total;

        document.getElementById('subtotal-val').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('ppn-val').textContent = 'Rp ' + ppn.toLocaleString('id-ID');
        document.getElementById('total-val').textContent = 'Rp ' + total.toLocaleString('id-ID');

        totalBox.style.display = 'block';
    }

    // ===== UPDATE QTY =====
    function updateQty(id, aksi) {
        window.location.href = `update_qty_admin.php?id=${id}&aksi=${aksi}`;
    }

    // ===== RESET ORDER =====
    function resetOrder() {
        if (confirm('Kosongkan semua pesanan?')) {
            window.location.href = 'reset_keranjang_admin.php';
        }
    }

    // ===== HITUNG TOTAL =====
    function getTotal() {
        let subtotal = 0;
        Object.keys(order).forEach(id => {
            subtotal += order[id].harga * order[id].qty;
        });
        return subtotal + Math.round(subtotal * 0.1);
    }

    // ===== FORMAT RUPIAH =====
    function formatRp(n) {
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    // ===== MODAL =====
    function openModal() {
        const keys = Object.keys(order);
        if (keys.length === 0) {
            alert('Tidak ada pesanan!');
            return;
        }

        document.getElementById('modalPayment').classList.add('active');
        metodeTerpilih = null;

        // Reset tampilan
        document.getElementById('areaCash').style.display = 'none';
        document.getElementById('areaQris').style.display = 'none';
        document.getElementById('btnCash').className = '';
        document.getElementById('btnQris').className = '';
        document.getElementById('kembalianBox').style.display = 'none';
        document.getElementById('inputBayar').value = '';

        // Update total QRIS
        const total = getTotal();
        document.getElementById('totalQris').textContent = 'Total: ' + formatRp(total);

        // Buat nominal cepat
        buatNominalCepat(total);

        // Enable tombol bayar
        document.getElementById('btnBayarModal').disabled = false;
        document.getElementById('btnBayarModal').textContent = '✅ Bayar Sekarang';
    }

    function tutupModal() {
        document.getElementById('modalPayment').classList.remove('active');
    }

    // ===== PILIH METODE =====
    function pilihMetode(metode) {
        metodeTerpilih = metode;

        const btnCash = document.getElementById('btnCash');
        const btnQris = document.getElementById('btnQris');
        const areaCash = document.getElementById('areaCash');
        const areaQris = document.getElementById('areaQris');

        if (metode === 'cash') {
            btnCash.className = 'active-cash';
            btnQris.className = '';
            areaCash.style.display = 'block';
            areaQris.style.display = 'none';
            hitungKembalian();
        } else {
            btnQris.className = 'active-qris';
            btnCash.className = '';
            areaQris.style.display = 'block';
            areaCash.style.display = 'none';
        }
    }

    // ===== NOMINAL CEPAT =====
    function buatNominalCepat(total) {
        const container = document.getElementById('nominalCepat');
        container.innerHTML = '';

        const bases = [50000, 100000, 200000, 500000];
        const hasil = [total];

        bases.forEach(base => {
            const nominal = Math.ceil(total / base) * base;
            if (!hasil.includes(nominal) && nominal <= 1000000) {
                hasil.push(nominal);
            }
        });

        hasil.slice(0, 5).forEach(n => {
            const btn = document.createElement('button');
            btn.textContent = n === total ? 'Pas ' + formatRp(n) : formatRp(n);
            btn.onclick = () => {
                document.getElementById('inputBayar').value = n;
                hitungKembalian();
            };
            container.appendChild(btn);
        });
    }

    // ===== HITUNG KEMBALIAN =====
    function hitungKembalian() {
        if (metodeTerpilih !== 'cash') return;

        const total = getTotal();
        const bayar = parseInt(document.getElementById('inputBayar').value) || 0;
        const kembalian = bayar - total;
        const box = document.getElementById('kembalianBox');
        const val = document.getElementById('kembalianVal');

        if (bayar > 0) {
            box.style.display = 'block';
            if (kembalian >= 0) {
                box.className = 'kembalian-box-modal';
                val.textContent = formatRp(kembalian);
            } else {
                box.className = 'kembalian-box-modal kurang';
                val.textContent = '(Kurang ' + formatRp(Math.abs(kembalian)) + ')';
            }
        } else {
            box.style.display = 'none';
        }
    }

    // ===== PROSES BAYAR =====
    function prosesBayar() {
        const keys = Object.keys(order);
        if (keys.length === 0) {
            alert('Tidak ada pesanan!');
            return;
        }

        if (!metodeTerpilih) {
            alert('Pilih metode pembayaran terlebih dahulu!');
            return;
        }

        const total = getTotal();
        let bayar = 0;

        if (metodeTerpilih === 'cash') {
            bayar = parseInt(document.getElementById('inputBayar').value) || 0;
            if (bayar < total) {
                alert('Uang pembayaran kurang!');
                return;
            }
        } else {
            if (!confirm('Apakah pembayaran QRIS sudah diterima?')) {
                return;
            }
            bayar = total;
        }

        // Tutup modal
        tutupModal();

        // Submit form
        document.getElementById('orderData').value = JSON.stringify(order);
        document.getElementById('metodeHidden').value = metodeTerpilih === 'cash' ? 'Cash' : 'QRIS';
        document.getElementById('bayarHidden').value = bayar;
        document.getElementById('kodeExisting').value = kodeExisting;
        document.getElementById('formPayment').submit();
    }

    // ===== CETAK STRUK =====
    function cetakStruk() {
        const keys = Object.keys(order);
        if (keys.length === 0) {
            alert('Tidak ada pesanan!');
            return;
        }

        const subtotal = Object.values(order).reduce((total, item) => {
            return total + (item.harga * item.qty);
        }, 0);

        const ppn = Math.round(subtotal * 0.1);
        const grandTotal = subtotal + ppn;

        let itemHtml = '';
        keys.forEach(id => {
            const item = order[id];
            const totalItem = item.harga * item.qty;
            itemHtml += `
                <tr>
                    <td>${item.nama}</td>
                    <td style="text-align:center;">${item.qty}</td>
                    <td style="text-align:right;">Rp ${totalItem.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });

        const waktu = new Date().toLocaleString('id-ID');

        const printWindow = window.open('', 'Print', 'width=400,height=700');
        printWindow.document.write(`
            <html>
            <head>
                <title>Struk Pembayaran</title>
                <style>
                    body { font-family: monospace; padding: 20px; color: #000; }
                    h2 { text-align: center; margin-bottom: 5px; }
                    .alamat { text-align: center; font-size: 12px; margin-bottom: 15px; }
                    table { width: 100%; border-collapse: collapse; font-size: 13px; }
                    td { padding: 4px 0; vertical-align: top; }
                    .line { border-top: 1px dashed #000; margin: 10px 0; }
                    .right { text-align: right; }
                    .center { text-align: center; }
                    .total { font-weight: bold; font-size: 14px; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                <h2>☕ CAFEKU</h2>
                <div class="alamat">Jln. Raya Pulogadung No. 20<br>Telp: 0893-3210-22558</div>
                <div class="line"></div>
                <table>
                    <tr><td>Waktu</td><td class="right">${waktu}</td></tr>
                    <tr><td>Kasir</td><td class="right"><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir', ENT_QUOTES) ?></td></tr>
                </table>
                <div class="line"></div>
                <table>
                    <thead>
                        <tr>
                            <th align="left">Item</th>
                            <th>Qty</th>
                            <th align="right">Total</th>
                        </tr>
                    </thead>
                    <tbody>${itemHtml}</tbody>
                </table>
                <div class="line"></div>
                <table>
                    <tr><td>Subtotal</td><td class="right">Rp ${subtotal.toLocaleString('id-ID')}</td></tr>
                    <tr><td>PPN 10%</td><td class="right">Rp ${ppn.toLocaleString('id-ID')}</td></tr>
                    <tr class="total"><td>TOTAL</td><td class="right">Rp ${grandTotal.toLocaleString('id-ID')}</td></tr>
                </table>
                <div class="line"></div>
                <div class="center">Terima Kasih ☕<br>Selamat Menikmati</div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    // Klik di luar modal untuk menutup
    document.getElementById('modalPayment').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupModal();
        }
    });
</script>

</body>
</html>