<?php
// FILE: admin/gaji.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// ===== AMBIL DATA KARYAWAN DARI TABEL KARYAWAN =====
$karyawan = mysqli_query($conn, "
    SELECT * FROM karyawan 
    ORDER BY jabatan, nama
");

// BAYAR GAJI
if (isset($_POST['bayar'])) {
    $karyawan_id = intval($_POST['karyawan_id']);
    $jumlah = intval($_POST['jumlah']);
    $tanggal = date('Y-m-d');

    // simpan data gaji
    mysqli_query($conn, "
        INSERT INTO gaji_karyawan
        (karyawan_id, tanggal, jumlah, status)
        VALUES
        (
            '$karyawan_id',
            '$tanggal',
            '$jumlah',
            'Lunas'
        )
    ");

    // otomatis masuk uang keluar
    mysqli_query($conn, "
        INSERT INTO uang_keluar
        (keperluan, jumlah, keterangan)
        VALUES
        (
            'Gaji Karyawan',
            '$jumlah',
            'Pembayaran gaji karyawan'
        )
    ");

    echo "
    <script>
        alert('✅ Gaji berhasil dibayarkan!');
        window.location='gaji.php';
    </script>
    ";
    exit;
}

// HAPUS RIWAYAT
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    mysqli_query($conn, "
        DELETE FROM gaji_karyawan
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('🗑️ Riwayat gaji berhasil dihapus!');
        window.location='gaji.php';
    </script>
    ";
    exit;
}

// Ambil total gaji
$totalGaji = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) as total
    FROM gaji_karyawan
"));

// ===== PERBAIKAN: Ambil data gaji dengan JOIN ke tabel karyawan =====
$data = mysqli_query($conn, "
    SELECT 
        gaji_karyawan.*,
        karyawan.nama,
        karyawan.jabatan
    FROM gaji_karyawan
    JOIN karyawan
    ON gaji_karyawan.karyawan_id = karyawan.id
    ORDER BY gaji_karyawan.id DESC
");

$grandTotal = $totalGaji['total'] ?? 0;
$totalData = mysqli_num_rows($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gaji Karyawan - Cafeku</title>
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
            max-width: 1100px;
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
            border: 1px solid #9b59b6;
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
            color: #9b59b6;
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

        .form-group select.form-control {
            appearance: none;
            cursor: pointer;
        }

        .form-group select.form-control option {
            background: #2a2a2a;
            color: #ddd;
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

        .table-wrapper table tbody td .status-lunas {
            display: inline-block;
            background: rgba(39, 174, 96, 0.15);
            color: #2ecc71;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #27ae60;
        }

        .table-wrapper table tbody td .jumlah-gaji {
            color: #9b59b6;
            font-weight: 700;
        }

        .table-wrapper table tbody td .jabatan-tag {
            display: inline-block;
            background: #2a2a2a;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            border: 1px solid #333;
            color: #c08b5c;
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
            <h1>💳 Gaji Karyawan</h1>
            <div class="subtitle">Kelola pembayaran gaji karyawan cafe</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== TOTAL BOX ===== -->
    <div class="total-box">
        <div class="label">💰 Total Gaji Dibayar</div>
        <div class="value">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
        <div class="sub-info">📋 <?= $totalData ?> kali pembayaran gaji</div>
    </div>

    <!-- ===== FORM BAYAR GAJI ===== -->
    <div class="card">
        <div class="card-header">
            <h3>➕ Bayar Gaji Karyawan</h3>
        </div>
        <form method="POST">
            <div class="form-group">
                <label for="karyawan_id">👤 Pilih Karyawan</label>
                <!-- ===== PERBAIKAN: Dropdown dari tabel karyawan ===== -->
                <select id="karyawan_id" name="karyawan_id" class="form-control" required>
                    <option value="">-- Pilih Karyawan --</option>
                    <?php 
                    mysqli_data_seek($karyawan, 0);
                    while ($k = mysqli_fetch_assoc($karyawan)): 
                    ?>
                        <option value="<?= $k['id'] ?>">
                            <?= htmlspecialchars($k['nama']) ?> - <?= htmlspecialchars($k['jabatan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="jumlah">💰 Jumlah Gaji</label>
                <input type="number" id="jumlah" name="jumlah" class="form-control" placeholder="Masukkan jumlah gaji..." required min="1">
            </div>

            <button type="submit" name="bayar" class="btn btn-success btn-block">
                💾 Bayar Gaji
            </button>
        </form>
    </div>

    <!-- ===== TABEL RIWAYAT ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Riwayat Pembayaran Gaji</h3>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="empty-state">📭 Belum ada riwayat pembayaran gaji</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th style="text-align:right;">Jumlah</th>
                            <th>Status</th>
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
                                <td><?= date('d-m-Y', strtotime($d['tanggal'])) ?></td>
                                <td><strong><?= htmlspecialchars($d['nama']) ?></strong></td>
                                <td>
                                    <span class="jabatan-tag"><?= htmlspecialchars($d['jabatan'] ?? '-') ?></span>
                                </td>
                                <td style="text-align:right;" class="jumlah-gaji">
                                    Rp <?= number_format($d['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <span class="status-lunas">✅ <?= $d['status'] ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <div class="action-group" style="justify-content:center;">
                                        <a class="btn-hapus" href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus riwayat gaji <?= htmlspecialchars($d['nama']) ?>?')">
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