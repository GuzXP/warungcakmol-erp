<?php
// FILE: kasir/index.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

// Konfirmasi pembayaran
if (isset($_POST['konfirmasi_bayar'])) {
    $id = (int)$_POST['id_transaksi'];
    $metode = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);

    mysqli_query($conn, "
        UPDATE transaksi
        SET
            metode_pembayaran = '$metode',
            kasir = '{$_SESSION['nama']}'
        WHERE id = $id
    ");

    echo "
    <script>
        alert('Pembayaran berhasil dikonfirmasi!');
        window.location='index.php';
    </script>
    ";
    exit;
}

// Proses cari pesanan berdasarkan kode
$hasilPencarian = null;
$pesananDitemukan = false;
$detailPesanan = [];
if (isset($_POST['cari_pesanan'])) {
    $kode_cari = mysqli_real_escape_string($conn, $_POST['kode_transaksi']);
    $hasilPencarian = query("
        SELECT * FROM transaksi 
        WHERE kode_transaksi = '$kode_cari'
        ORDER BY tanggal DESC
    ");
    if (!empty($hasilPencarian)) {
        $pesananDitemukan = true;
        // Ambil detail produk dari transaksi
        $id_transaksi = $hasilPencarian[0]['id'];
        $detailPesanan = query("
            SELECT dt.*, p.nama, p.gambar, p.harga as harga_produk
            FROM detail_transaksi dt
            JOIN produk p ON dt.produk_id = p.id
            WHERE dt.transaksi_id = $id_transaksi
        ");
    }
}

// Statistik
$pesananMasuk = query("SELECT COUNT(*) AS total FROM transaksi");
$verifikasi = query("SELECT COUNT(*) AS total FROM transaksi WHERE metode_pembayaran <> ''");
$belumBayar = query("SELECT COUNT(*) AS total FROM transaksi WHERE metode_pembayaran = '' OR metode_pembayaran IS NULL");
$cash = query("SELECT COALESCE(SUM(total), 0) AS total FROM transaksi WHERE metode_pembayaran = 'Cash'");
$qris = query("SELECT COALESCE(SUM(total), 0) AS total FROM transaksi WHERE metode_pembayaran = 'QRIS'");

$pesananCount = $pesananMasuk[0]['total'] ?? 0;
$verifikasiCount = $verifikasi[0]['total'] ?? 0;
$belumBayarCount = $belumBayar[0]['total'] ?? 0;
$cashTotal = $cash[0]['total'] ?? 0;
$qrisTotal = $qris[0]['total'] ?? 0;

// Daftar pesanan
$daftarPesanan = query("
    SELECT *
    FROM transaksi
    ORDER BY tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Kasir - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        /* Tambahan style */
        .search-box {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
            padding: 15px;
            background: #1f1f1f;
            border-radius: 10px;
            border: 1px solid #c08b5c;
        }
        .search-box label {
            color: #c08b5c;
            font-weight: 600;
        }
        .search-box input[type="text"] {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #c08b5c;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
            background: #2a2a2a;
            color: #ddd;
        }
        .search-box input[type="text"]:focus {
            border-color: #c08b5c;
            outline: none;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.2);
        }
        .search-box input[type="text"]::placeholder {
            color: #777;
        }
        .search-box .btn-cari {
            padding: 10px 25px;
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-box .btn-cari:hover {
            background: #333;
            transform: translateY(-2px);
        }
        .search-box .btn-bayar-langsung {
            padding: 10px 25px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .search-box .btn-bayar-langsung:hover {
            background: #219a52;
            transform: translateY(-2px);
        }
        .hasil-pencarian {
            background: #1f1f1f;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #c08b5c;
        }
        .hasil-pencarian .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .hasil-pencarian .item .info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .hasil-pencarian .item .info span {
            font-weight: 600;
            color: #ddd;
        }
        .hasil-pencarian .item .info .label {
            color: #c08b5c;
            font-weight: 400;
        }
        .badge-lunas {
            background: #27ae60;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
        }
        .badge-belum {
            background: #e74c3c;
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 12px;
        }
        .not-found {
            color: #e74c3c;
            font-weight: 700;
            padding: 10px 0;
        }
        .status-lunas {
            color: #27ae60;
            font-weight: 900;
        }
        .status-belum {
            color: #e74c3c;
            font-weight: 900;
        }
        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover {
            background: #333;
            transform: translateY(-2px);
        }
        .card h2 {
            color: #c08b5c !important;
        }
        .card p {
            color: #ddd !important;
        }
        .grid-card .card {
            background: #1f1f1f !important;
        }
        .detail-produk {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #333;
        }
        .detail-produk h4 {
            color: #c08b5c;
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .detail-produk .produk-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .detail-produk .produk-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #2a2a2a;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #333;
            min-width: 150px;
        }
        .detail-produk .produk-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #c08b5c;
        }
        .detail-produk .produk-item .produk-info {
            display: flex;
            flex-direction: column;
        }
        .detail-produk .produk-item .produk-info .nama {
            color: #ddd;
            font-weight: 600;
            font-size: 13px;
        }
        .detail-produk .produk-item .produk-info .qty {
            color: #c08b5c;
            font-size: 12px;
        }
        .detail-produk .produk-item .produk-info .harga {
            color: #27ae60;
            font-size: 12px;
            font-weight: 600;
        }
        .btn-hapus {
            background: #e74c3c;
            color: white;
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
    </style>
</head>

<body>
    <div class="admin-container">

        <a href="../logout.php" class="btn btn-danger btn-logout">Logout</a>
        <div style="clear:both;"></div>

        <h1 style="color:#c08b5c;">Dashboard Kasir</h1>

        <div class="admin-grid">

            <div class="navbar">
                <ul>
                    <li><a href="index.php" style="color:#c08b5c;">Dashboard</a></li>
                    <li><a href="../admin/pembayaran.php" style="color:#c08b5c;">Pesanan Masuk</a></li>
                </ul>
            </div>

            <div class="admin-main">

                <!-- ===== SEARCH PESANAN ===== -->
                <div class="search-box">
                    <label>🔍 Cari Pesanan:</label>
                    <form method="POST" style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
                        <input type="text" name="kode_transaksi" placeholder="Masukkan Kode Transaksi..." value="<?= isset($_POST['kode_transaksi']) ? htmlspecialchars($_POST['kode_transaksi']) : '' ?>">
                        <button type="submit" name="cari_pesanan" class="btn-cari">🔍 Cari</button>
                    </form>
                </div>

                <!-- ===== HASIL PENCARIAN ===== -->
                <?php if (isset($_POST['cari_pesanan'])) : ?>
                    <div class="hasil-pencarian">
                        <?php if ($pesananDitemukan && !empty($hasilPencarian)) : ?>
                            <?php $p = $hasilPencarian[0]; ?>
                            <div class="item">
                                <div class="info">
                                    <span><span class="label">Kode:</span> <?= htmlspecialchars($p['kode_transaksi']) ?></span>
                                    <span><span class="label">Tanggal:</span> <?= $p['tanggal'] ?></span>
                                    <span><span class="label">Total:</span> Rp <?= number_format($p['total'], 0, ',', '.') ?></span>
                                    <span>
                                        <span class="label">Status:</span>
                                        <?php if (!empty($p['metode_pembayaran'])) : ?>
                                            <span class="badge-lunas">✔ Lunas (<?= $p['metode_pembayaran'] ?>)</span>
                                        <?php else : ?>
                                            <span class="badge-belum">⏳ Belum Bayar</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if (empty($p['metode_pembayaran'])) : ?>
                                        <a href="../admin/pembayaran.php?kode=<?= urlencode($p['kode_transaksi']) ?>" class="btn-bayar-langsung">
                                            💳 Bayar Sekarang
                                        </a>
                                    <?php else : ?>
                                        <span style="color:#27ae60; font-weight:900;">✔ Sudah Lunas</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- ===== DETAIL PRODUK ===== -->
                            <?php if (!empty($detailPesanan)) : ?>
                                <div class="detail-produk">
                                    <h4>🛒 Detail Pesanan:</h4>
                                    <div class="produk-list">
                                        <?php foreach ($detailPesanan as $item) : ?>
                                            <div class="produk-item">
                                                <img src="../<?= $item['gambar'] ?>" alt="<?= htmlspecialchars($item['nama']) ?>">
                                                <div class="produk-info">
                                                    <span class="nama"><?= htmlspecialchars($item['nama']) ?></span>
                                                    <span class="qty">Jumlah: <?= $item['qty'] ?> x Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></span>
                                                    <span class="harga">Subtotal: Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else : ?>
                            <div class="not-found">❌ Pesanan dengan kode tersebut tidak ditemukan!</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- ===== CARDS STATISTIK ===== -->
                <div class="grid-card" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:20px; margin-top:20px;">

                    <div class="card" style="padding:20px; background:#1f1f1f; border:1px solid #c08b5c;">
                        <h2>📋 Total Pesanan</h2>
                        <p><?= $pesananCount ?></p>
                    </div>

                    <div class="card" style="padding:20px; background:#1f1f1f; border:1px solid #27ae60;">
                        <h2>✅ Lunas</h2>
                        <p><?= $verifikasiCount ?></p>
                    </div>

                    <div class="card" style="padding:20px; background:#1f1f1f; border:1px solid #e74c3c;">
                        <h2>⏳ Belum Bayar</h2>
                        <p><?= $belumBayarCount ?></p>
                    </div>

                    <div class="card" style="padding:20px; background:#1f1f1f; border:1px solid #f1c40f;">
                        <h2>💰 Cash</h2>
                        <p>Rp <?= number_format($cashTotal, 0, ',', '.') ?></p>
                    </div>

                    <div class="card" style="padding:20px; background:#1f1f1f; border:1px solid #9b59b6;">
                        <h2>📱 QRIS</h2>
                        <p>Rp <?= number_format($qrisTotal, 0, ',', '.') ?></p>
                    </div>

                </div>

                <!-- ===== TOMBOL KASIR ===== -->
                <div style="margin-top:20px;">
                    <a href="../admin/pembayaran.php" class="btn btn-success" style="background:#27ae60; color:white; padding:12px 25px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block;">
                        💳 Buka Kasir dan Cetak Struk
                    </a>
                </div>

                <!-- ===== DAFTAR PESANAN ===== -->
                <div class="card" style="padding:20px; margin-top:20px; background:#1f1f1f; border:1px solid #c08b5c;">

                    <h2 style="color:#c08b5c;">📋 Daftar Pesanan Customer</h2>

                    <div style="overflow-x:auto;">
                        <table width="100%" border="1" cellpadding="10" cellspacing="0" style="border-color:#333;">

                            <tr style="background:#2a2a2a;">
                                <th style="color:#c08b5c;">Kode Transaksi</th>
                                <th style="color:#c08b5c;">Tanggal</th>
                                <th style="color:#c08b5c;">Total</th>
                                <th style="color:#c08b5c;">Metode</th>
                                <th style="color:#c08b5c;">Kasir</th>
                                <th style="color:#c08b5c;">Status</th>
                                <th style="color:#c08b5c;">Aksi</th>
                            </tr>

                            <?php if (empty($daftarPesanan)) : ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; color:#777; padding:30px;">
                                        Belum ada pesanan
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($daftarPesanan as $row) : ?>
                                    <tr style="border-color:#333;">
                                        <td style="color:#ddd;"><?= htmlspecialchars($row['kode_transaksi']) ?></td>
                                        <td style="color:#ddd;"><?= $row['tanggal'] ?></td>
                                        <td style="color:#ddd;">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td style="color:#ddd;"><?= $row['metode_pembayaran'] ?: '-' ?></td>
                                        <td style="color:#ddd;"><?= $row['kasir'] ?: '-' ?></td>
                                        <td>
                                            <?php if (!empty($row['metode_pembayaran'])) : ?>
                                                <span class="status-lunas">✔ Lunas</span>
                                            <?php else : ?>
                                                <span class="status-belum">⏳ Belum Bayar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($row['metode_pembayaran'])) : ?>
                                                <form method="POST" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
                                                    <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                                    <select name="metode_pembayaran" required style="padding:6px 10px; border:1px solid #c08b5c; border-radius:6px; background:#2a2a2a; color:#ddd;">
                                                        <option value="">Pilih</option>
                                                        <option value="Cash">💵 Cash</option>
                                                        <option value="QRIS">📱 QRIS</option>
                                                    </select>
                                                    <button type="submit" name="konfirmasi_bayar" class="btn btn-success btn-sm" style="background:#27ae60; color:white; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-weight:600;">
                                                        ✅ Bayar
                                                    </button>
                                                    <a href="../admin/pembayaran.php?kode=<?= urlencode($row['kode_transaksi']) ?>" class="btn btn-primary btn-sm" style="background:#2a2a2a; color:#c08b5c; border:1px solid #c08b5c; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:600; display:inline-block;">
                                                        💳 Kasir
                                                    </a>
                                                </form>
                                            <?php else : ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                                    <button type="submit" name="hapus_transaksi" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus transaksi <?= htmlspecialchars($row['kode_transaksi']) ?>?')">
                                                        🗑️ Hapus
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </table>
                    </div>

                </div>

            </div>

        </div>

    </div>
</body>

</html>