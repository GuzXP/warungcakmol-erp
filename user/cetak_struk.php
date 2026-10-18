<?php
/**
 * FILE: user/cetak_struk.php (PRINT RECEIPT PAGE)
 * UNTUK: Menampilkan struk belanja ramah cetak (thermal size 58mm/80mm) yang otomatis memicu print.
 */

require_once 'fungsi.php';
confirm_kasir_login();

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pesanan <= 0) {
    die("ID Pesanan tidak valid.");
}

// Fetch header pesanan
$stmt = mysqli_prepare($koneksi, "
    SELECT p.*, m.nama_meja 
    FROM pesanan p 
    LEFT JOIN meja m ON p.id_meja = m.id_meja 
    WHERE p.id_pesanan = ?
");
mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Fetch items
$items = query("SELECT * FROM detail_pesanan WHERE id_pesanan = ?", [$id_pesanan], "i");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #CM-<?= $order['id_pesanan']; ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            padding: 10px;
            width: 280px; /* Lebar default ramah thermal printer 58mm */
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .header {
            margin-bottom: 10px;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 10px;
            margin-top: 2px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-table, .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        .info-table td {
            font-size: 11px;
            padding: 1px 0;
        }

        .items-table th, .items-table td {
            text-align: left;
            padding: 2px 0;
            vertical-align: top;
        }

        .item-notes {
            font-size: 10px;
            padding-left: 10px;
            font-style: italic;
        }

        .total-section {
            margin-top: 10px;
        }

        .footer {
            margin-top: 15px;
            font-size: 10px;
        }

        /* Tombol aksi non-print */
        .actions-bar {
            margin-top: 20px;
            text-align: center;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn {
            font-family: sans-serif;
            font-size: 11px;
            padding: 6px 12px;
            cursor: pointer;
            border: 1px solid #000;
            background: #fff;
            color: #000;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }

        .btn-primary {
            background: #000;
            color: #fff;
        }

        @media print {
            .actions-bar {
                display: none;
            }
            body {
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Header Warung -->
    <div class="header text-center">
        <div class="title">NASI GORENG CAK MOL</div>
        <div class="subtitle">Jl. Pulo Mas Barat V No.4, Jakarta</div>
        <div class="subtitle">WhatsApp: 0812-3456-7890</div>
    </div>

    <div class="divider"></div>

    <!-- Info Pesanan -->
    <table class="info-table">
        <tr>
            <td>ID Order: #CM-<?= $order['id_pesanan']; ?></td>
            <td class="text-right"><?= $order['nama_meja'] ? strtoupper($order['nama_meja']) : 'TAKEAWAY'; ?></td>
        </tr>
        <tr>
            <td>Waktu: <?= date('d/m/y H:i', strtotime($order['waktu_pesan'])); ?></td>
            <td class="text-right">Tipe: <?= $order['tipe_pesanan'] === 'Takeaway' ? 'Bawa Pulang' : 'Dine In'; ?></td>
        </tr>
        <?php if (!empty($order['nama_pelanggan'])): ?>
            <tr>
                <td colspan="2">Pelanggan: <?= sanitize($order['nama_pelanggan']); ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <div class="divider"></div>

    <!-- Items List -->
    <table class="items-table">
        <thead>
            <tr class="bold">
                <th style="width: 50%;">Menu</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 35%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div><?= sanitize($item['nama_menu_snapshot']); ?></div>
                        <?php if (isset($item['level_pedas']) && $item['level_pedas'] !== 'Tidak Pedas'): ?>
                            <div class="item-notes">(<?= sanitize($item['level_pedas']); ?>)</div>
                        <?php endif; ?>
                        <?php if (!empty($item['catatan'])): ?>
                            <div class="item-notes">"<?= sanitize($item['catatan']); ?>"</div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><?= $item['jumlah']; ?></td>
                    <td class="text-right">Rp <?= number_format($item['harga_snapshot'] * $item['jumlah'], 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Total Section -->
    <table class="info-table total-section">
        <tr class="bold" style="font-size: 13px;">
            <td>GRAND TOTAL</td>
            <td class="text-right">Rp <?= number_format($order['total_bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Bayar: <?= sanitize($order['metode_bayar']); ?></td>
            <td class="text-right">Status: LUNAS</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Footer -->
    <div class="footer text-center">
        <p class="bold">TERIMA KASIH</p>
        <p>Atas Kunjungan Anda!</p>
        <p style="margin-top: 5px; font-size: 8px;">Powered by Cak Mol POS</p>
    </div>

    <!-- Actions Bar (Hidden on print) -->
    <div class="actions-bar">
        <a href="kasir.php" class="btn">Kembali ke Kasir</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak Ulang</button>
    </div>

    <script>
        // Otomatis trigger print saat halaman siap
        window.addEventListener('DOMContentLoaded', () => {
            // Beri sedikit jeda agar rendering selesai sempurna
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
