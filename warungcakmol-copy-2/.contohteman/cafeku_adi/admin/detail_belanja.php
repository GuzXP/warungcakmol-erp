<?php
// FILE: admin/detail_belanja.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

$tugas_id = isset($_GET['tugas']) ? intval($_GET['tugas']) : 0;

if ($tugas_id == 0) {
    echo "
    <script>
        alert('ID Tugas tidak valid!');
        window.location='tugas_belanja.php';
    </script>
    ";
    exit;
}

// ambil data tugas
$tugas = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM tugas_belanja
    WHERE id='$tugas_id'
"));

if (!$tugas) {
    echo "
    <script>
        alert('Tugas tidak ditemukan!');
        window.location='tugas_belanja.php';
    </script>
    ";
    exit;
}

// TAMBAH DETAIL BELANJA
if (isset($_POST['simpan'])) {
    $barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $qty = intval($_POST['qty']);
    $harga = intval($_POST['harga']);

    $subtotal = $qty * $harga;

    // cek total belanja sekarang
    $cekTotal = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(subtotal) as total
        FROM detail_belanja
        WHERE tugas_id='$tugas_id'
    "));

    $totalSekarang = $cekTotal['total'] ?? 0;
    $totalBaru = $totalSekarang + $subtotal;

    // validasi balance
    if ($totalBaru > $tugas['uang_diberikan']) {
        echo "
        <script>
            alert('⚠️ Uang belanja tidak cukup! Sisa: Rp " . number_format($tugas['uang_diberikan'] - $totalSekarang, 0, ',', '.') . "');
            window.location='detail_belanja.php?tugas=$tugas_id';
        </script>
        ";
        exit;
    } else {
        mysqli_query($conn, "
            INSERT INTO detail_belanja
            (tugas_id, nama_barang, qty, harga, subtotal)
            VALUES
            (
                '$tugas_id',
                '$barang',
                '$qty',
                '$harga',
                '$subtotal'
            )
        ");

        echo "
        <script>
            alert('✅ Data belanja berhasil ditambahkan!');
            window.location='detail_belanja.php?tugas=$tugas_id';
        </script>
        ";
    }
}

// HAPUS DETAIL
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    mysqli_query($conn, "
        DELETE FROM detail_belanja
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('🗑️ Data berhasil dihapus!');
        window.location='detail_belanja.php?tugas=$tugas_id';
    </script>
    ";
    exit;
}

// Ambil total belanja
$totalBelanja = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(subtotal) as total
    FROM detail_belanja
    WHERE tugas_id='$tugas_id'
"));

$dipakai = $totalBelanja['total'] ?? 0;
$sisa = $tugas['uang_diberikan'] - $dipakai;

// Ambil data detail belanja
$data = mysqli_query($conn, "
    SELECT * FROM detail_belanja
    WHERE tugas_id='$tugas_id'
    ORDER BY id DESC
");

$totalData = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Belanja - Cafeku</title>
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

        .container {
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

        .btn-block {
            width: 100%;
            justify-content: center;
            padding: 12px;
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }

        /* ===== INFO BOX ===== */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            text-align: center;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-3px);
            border-color: #c08b5c;
        }

        .info-card .label {
            color: #888;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card .value {
            color: #c08b5c;
            font-size: 24px;
            font-weight: 700;
            margin-top: 5px;
        }

        .info-card .value.green { color: #2ecc71; }
        .info-card .value.red { color: #e74c3c; }
        .info-card .value.gold { color: #c08b5c; }

        .info-card.border-gold { border-color: #c08b5c; }
        .info-card.border-green { border-color: #27ae60; }
        .info-card.border-red { border-color: #e74c3c; }

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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 5px;
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

        .table-wrapper table tbody td .subtotal-value {
            color: #c08b5c;
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
            .container {
                padding: 15px;
            }

            .page-header {
                flex-direction: column;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .info-grid {
                grid-template-columns: 1fr 1fr;
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

            .container {
                padding: 10px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .info-card .value {
                font-size: 20px;
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

<div class="container">
    <!-- ===== HEADER ===== -->
    <div class="page-header">
        <div>
            <h1>📄 Detail Belanja</h1>
            <div class="subtitle">Kelola detail belanja karyawan</div>
        </div>
        <div class="header-actions">
            <a href="tugas_belanja.php" class="btn btn-primary btn-sm">← Kembali</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== INFO BOX ===== -->
    <div class="info-grid">
        <div class="info-card border-gold">
            <div class="label">👤 Nama Karyawan</div>
            <div class="value gold"><?= htmlspecialchars($tugas['nama_karyawan']) ?></div>
        </div>

        <div class="info-card border-green">
            <div class="label">💰 Uang Diberikan</div>
            <div class="value green">Rp <?= number_format($tugas['uang_diberikan'], 0, ',', '.') ?></div>
        </div>

        <div class="info-card <?= $sisa >= 0 ? 'border-green' : 'border-red' ?>">
            <div class="label">💵 Sisa Uang</div>
            <div class="value <?= $sisa >= 0 ? 'green' : 'red' ?>">
                Rp <?= number_format($sisa, 0, ',', '.') ?>
                <?php if ($sisa < 0): ?>
                    <span style="font-size:14px; color:#e74c3c;">(Over)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===== FORM TAMBAH ===== -->
    <div class="card">
        <div class="card-header">
            <h3>➕ Tambah Barang Belanja</h3>
            <span style="color:#888; font-size:13px;">
                Sisa: Rp <?= number_format($sisa, 0, ',', '.') ?>
            </span>
        </div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_barang">📦 Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control" placeholder="Nama barang..." required>
                </div>

                <div class="form-group">
                    <label for="qty">📊 Qty</label>
                    <input type="number" id="qty" name="qty" class="form-control" placeholder="Jumlah..." required min="1">
                </div>

                <div class="form-group">
                    <label for="harga">💰 Harga</label>
                    <input type="number" id="harga" name="harga" class="form-control" placeholder="Harga satuan..." required min="1">
                </div>
            </div>

            <button type="submit" name="simpan" class="btn btn-success btn-block">
                💾 Simpan Belanja
            </button>
        </form>
    </div>

    <!-- ===== TABEL DATA ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Riwayat Belanja</h3>
            <span style="color:#888; font-size:13px;">
                📋 <?= $totalData ?> item
            </span>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="empty-state">📭 Belum ada data belanja</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang</th>
                            <th style="text-align:center;">Qty</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:right;">Subtotal</th>
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
                                <td><strong><?= htmlspecialchars($d['nama_barang']) ?></strong></td>
                                <td style="text-align:center;"><?= $d['qty'] ?></td>
                                <td style="text-align:right;">Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                                <td style="text-align:right;" class="subtotal-value">
                                    Rp <?= number_format($d['subtotal'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align:center;">
                                    <div class="action-group" style="justify-content:center;">
                                        <a class="btn-hapus" href="?tugas=<?= $tugas_id ?>&hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus data belanja <?= htmlspecialchars($d['nama_barang']) ?>?')">
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