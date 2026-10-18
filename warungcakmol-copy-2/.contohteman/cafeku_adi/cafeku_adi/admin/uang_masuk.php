<?php
// FILE: admin/uang_masuk.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// TAMBAH UANG MASUK
if (isset($_POST['simpan'])) {
    $sumber = mysqli_real_escape_string($conn, $_POST['sumber']);
    $jumlah = intval($_POST['jumlah']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    mysqli_query($conn, "
        INSERT INTO uang_masuk
        (sumber, jumlah, keterangan)
        VALUES
        ('$sumber', '$jumlah', '$keterangan')
    ");

    echo "
    <script>
        alert('✅ Data uang masuk berhasil ditambahkan!');
        window.location='uang_masuk.php';
    </script>
    ";
    exit;
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    mysqli_query($conn, "
        DELETE FROM uang_masuk
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('🗑️ Data berhasil dihapus!');
        window.location='uang_masuk.php';
    </script>
    ";
    exit;
}

// Ambil total uang masuk
$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total
    FROM uang_masuk
"));

// Ambil data uang masuk
$data = mysqli_query($conn, "
    SELECT * FROM uang_masuk
    ORDER BY id DESC
");

$grandTotal = $total['total'] ?? 0;
$totalData = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uang Masuk - Cafeku</title>
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
            max-width: 1000px;
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

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
            padding: 12px;
        }

        /* ===== TOTAL BOX ===== */
        .total-box {
            background: #1f1f1f;
            border: 1px solid #27ae60;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .total-box .label {
            color: #888;
            font-size: 14px;
        }

        .total-box .value {
            color: #2ecc71;
            font-size: 28px;
            font-weight: 900;
        }

        .total-box .sub-info {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
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

        /* ===== FORM ===== */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #ddd;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .form-group .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group .form-control:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }

        .form-group .form-control::placeholder {
            color: #666;
        }

        .form-group textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

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

        .table-wrapper table tbody td .jumlah-hijau {
            color: #2ecc71;
            font-weight: 700;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-group .btn-hapus {
            background: #8B0000;
            color: #fff;
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }

        .action-group .btn-hapus:hover {
            background: #660000;
            transform: translateY(-2px);
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

            table {
                font-size: 12px;
            }

            table thead th,
            table tbody td {
                padding: 8px 10px;
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

            .total-box .value {
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
            <h1>📥 Uang Masuk</h1>
            <div class="subtitle">Catat dan kelola semua pemasukan cafe</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== TOTAL BOX ===== -->
    <div class="total-box">
        <div class="label">💰 Total Uang Masuk</div>
        <div class="value">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
        <div class="sub-info">📋 <?= $totalData ?> transaksi pemasukan</div>
    </div>

    <!-- ===== FORM TAMBAH ===== -->
    <div class="card">
        <div class="card-header">
            <h3>➕ Tambah Uang Masuk</h3>
        </div>
        <form method="POST">
            <div class="form-group">
                <label for="sumber">📌 Sumber Uang</label>
                <input type="text" id="sumber" name="sumber" class="form-control" placeholder="Contoh: Penjualan, Donasi, dll..." required>
            </div>

            <div class="form-group">
                <label for="jumlah">💰 Jumlah Uang</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" placeholder="Masukkan jumlah uang..." required min="1">
            </div>

            <div class="form-group">
                <label for="keterangan">📝 Keterangan</label>
                <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Keterangan tambahan..." rows="3"></textarea>
            </div>

            <button type="submit" name="simpan" class="btn btn-success btn-block">
                💾 Simpan Uang Masuk
            </button>
        </form>
    </div>

    <!-- ===== TABEL DATA ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Riwayat Uang Masuk</h3>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="empty-state">📭 Belum ada data uang masuk</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Sumber</th>
                            <th style="text-align:right;">Jumlah</th>
                            <th>Keterangan</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($d = mysqli_fetch_assoc($data)):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($d['sumber']) ?></strong></td>
                                <td style="text-align:right;" class="jumlah-hijau">
                                    + Rp <?= number_format($d['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td><?= htmlspecialchars($d['keterangan'] ?: '-') ?></td>
                                <td style="text-align:center;">
                                    <div class="action-group" style="justify-content:center;">
                                        <a class="btn-hapus" href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus data uang masuk dari <?= htmlspecialchars($d['sumber']) ?>?')">
                                            🗑️ Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

</body>
</html>