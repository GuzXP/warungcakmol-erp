<?php
// FILE: admin/tugas_belanja.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// TAMBAH TUGAS BELANJA
if (isset($_POST['simpan'])) {
    $nama_karyawan = mysqli_real_escape_string($conn, $_POST['nama_karyawan']);
    $uang_diberikan = intval($_POST['uang_diberikan']);

    mysqli_query($conn, "
        INSERT INTO tugas_belanja
        (nama_karyawan, uang_diberikan, status)
        VALUES
        (
            '$nama_karyawan',
            '$uang_diberikan',
            'Belum Selesai'
        )
    ");

    // otomatis masuk uang keluar
    mysqli_query($conn, "
        INSERT INTO uang_keluar
        (keperluan, jumlah, keterangan)
        VALUES
        (
            'Uang Belanja Karyawan',
            '$uang_diberikan',
            'Uang belanja untuk $nama_karyawan'
        )
    ");

    echo "
    <script>
        alert('✅ Tugas belanja berhasil dibuat!');
        window.location='tugas_belanja.php';
    </script>
    ";
    exit;
}

// ===== HAPUS TUGAS =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    // Hapus detail belanja untuk tugas ini saja
    mysqli_query($conn, "DELETE FROM detail_belanja WHERE tugas_id = '$id'");
    
    // Hapus tugas belanja ini saja
    mysqli_query($conn, "DELETE FROM tugas_belanja WHERE id = '$id'");

    echo "
    <script>
        alert('🗑️ Tugas dan detail belanjanya berhasil dihapus!');
        window.location='tugas_belanja.php';
    </script>
    ";
    exit;
}

// UPDATE STATUS
if (isset($_GET['selesai'])) {
    $id = intval($_GET['selesai']);

    mysqli_query($conn, "
        UPDATE tugas_belanja
        SET status='Selesai'
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('✅ Status tugas berhasil diupdate!');
        window.location='tugas_belanja.php';
    </script>
    ";
    exit;
}

// Ambil total uang belanja
$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(uang_diberikan) as total
    FROM tugas_belanja
"));

// ===== PERBAIKAN: Ambil data karyawan dari tabel karyawan =====
$karyawan = mysqli_query($conn, "
    SELECT * FROM karyawan
    ORDER BY nama ASC
");

// Ambil data tugas
$data = mysqli_query($conn, "
    SELECT * FROM tugas_belanja
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
    <title>Tugas Belanja Karyawan - Cafeku</title>
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
            max-width: 1200px;
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
            border: 1px solid #c08b5c;
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
            color: #c08b5c;
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
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .form-group select,
        .form-group input {
            flex: 1;
            min-width: 200px;
            padding: 10px 14px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }

        .form-group select option {
            background: #2a2a2a;
            color: #ddd;
        }

        /* ===== TABEL ===== */
        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table thead th {
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

        table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            color: #ddd;
            vertical-align: middle;
        }

        table tbody tr:hover {
            background: #2a2a2a;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        /* ===== STATUS BADGE ===== */
        .status-belum {
            display: inline-block;
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #f1c40f;
        }

        .status-selesai {
            display: inline-block;
            background: rgba(39, 174, 96, 0.15);
            color: #2ecc71;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #27ae60;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .action-group .btn-detail {
            background: #3498db;
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
        }
        .action-group .btn-detail:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .action-group .btn-selesai {
            background: #27ae60;
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
        }
        .action-group .btn-selesai:hover {
            background: #219a52;
            transform: translateY(-2px);
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
            padding: 30px 0;
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

            .form-group {
                flex-direction: column;
            }

            .form-group select,
            .form-group input {
                width: 100%;
                min-width: unset;
            }

            table {
                font-size: 12px;
            }

            table thead th,
            table tbody td {
                padding: 8px 10px;
            }

            .action-group {
                flex-direction: column;
                align-items: stretch;
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

<div class="container">
    <!-- ===== HEADER ===== -->
    <div class="page-header">
        <div>
            <h1>📋 Tugas Belanja Karyawan</h1>
            <div class="subtitle">Kelola tugas belanja dan pantau pengeluaran</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== TOTAL BOX ===== -->
    <div class="total-box">
        <div class="label">💰 Total Uang Belanja</div>
        <div class="value">Rp <?= number_format($grandTotal, 0, ',', '.') ?></div>
        <div class="sub-info">📋 <?= $totalData ?> tugas belanja</div>
    </div>

    <!-- ===== FORM TAMBAH ===== -->
    <div class="card">
        <div class="card-header">
            <h3>➕ Buat Tugas Belanja</h3>
        </div>
        <form method="POST">
            <div class="form-group">
                <!-- ===== PERBAIKAN: Dropdown dari tabel karyawan ===== -->
                <select name="nama_karyawan" required>
                    <option value="">-- Pilih Karyawan --</option>
                    <?php while ($k = mysqli_fetch_assoc($karyawan)): ?>
                        <option value="<?= htmlspecialchars($k['nama']) ?>">
                            <?= htmlspecialchars($k['nama']) ?> - <?= htmlspecialchars($k['jabatan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input
                    type="number"
                    name="uang_diberikan"
                    placeholder="Jumlah Uang Belanja"
                    required
                    min="1"
                >

                <button type="submit" name="simpan" class="btn btn-success">
                    💾 Simpan Tugas
                </button>
            </div>
        </form>
    </div>

    <!-- ===== TABEL DATA ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Data Tugas Belanja</h3>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="empty-state">📭 Belum ada tugas belanja</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Karyawan</th>
                            <th style="text-align:right;">Uang Diberikan</th>
                            <th style="text-align:right;">Total Belanja</th>
                            <th style="text-align:right;">Sisa Uang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($d = mysqli_fetch_assoc($data)):
                            $detail = mysqli_fetch_assoc(mysqli_query($conn, "
                                SELECT SUM(subtotal) as total
                                FROM detail_belanja
                                WHERE tugas_id='" . $d['id'] . "'
                            "));

                            $totalBelanja = $detail['total'] ?? 0;
                            $sisa = $d['uang_diberikan'] - $totalBelanja;
                            $isBelumSelesai = $d['status'] == 'Belum Selesai';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($d['tanggal'])) ?></td>
                                <td><?= htmlspecialchars($d['nama_karyawan']) ?></td>
                                <td style="text-align:right;">Rp <?= number_format($d['uang_diberikan'], 0, ',', '.') ?></td>
                                <td style="text-align:right;">Rp <?= number_format($totalBelanja, 0, ',', '.') ?></td>
                                <td style="text-align:right; <?= $sisa >= 0 ? 'color:#2ecc71;' : 'color:#e74c3c;' ?>">
                                    Rp <?= number_format($sisa, 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if ($isBelumSelesai): ?>
                                        <span class="status-belum">⏳ <?= $d['status'] ?></span>
                                    <?php else: ?>
                                        <span class="status-selesai">✅ <?= $d['status'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a class="btn-detail" href="detail_belanja.php?tugas=<?= $d['id'] ?>">
                                            📄 Detail
                                        </a>

                                        <?php if ($isBelumSelesai): ?>
                                            <a class="btn-selesai" href="?selesai=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menandai tugas ini selesai?')">
                                                ✅ Selesai
                                            </a>
                                        <?php endif; ?>

                                        <a class="btn-hapus" href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus tugas ini? (Detail belanja juga akan terhapus)')">
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