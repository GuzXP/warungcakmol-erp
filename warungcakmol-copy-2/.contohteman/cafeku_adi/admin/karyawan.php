<?php
// FILE: admin/karyawan.php
include '../fungsi.php';

// Cek login
session_start();
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// TAMBAH KARYAWAN
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $gaji = intval($_POST['gaji']);

    mysqli_query($conn, "
        INSERT INTO karyawan
        (nama, jabatan, no_hp, alamat, gaji)
        VALUES
        (
            '$nama',
            '$jabatan',
            '$no_hp',
            '$alamat',
            '$gaji'
        )
    ");

    echo "
    <script>
        alert('✅ Karyawan berhasil ditambahkan!');
        window.location='karyawan.php';
    </script>
    ";
    exit;
}

// HAPUS KARYAWAN
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);

    mysqli_query($conn, "
        DELETE FROM karyawan
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('🗑️ Data karyawan berhasil dihapus!');
        window.location='karyawan.php';
    </script>
    ";
    exit;
}

// EDIT KARYAWAN
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $gaji = intval($_POST['gaji']);

    mysqli_query($conn, "
        UPDATE karyawan SET
        nama='$nama',
        jabatan='$jabatan',
        no_hp='$no_hp',
        alamat='$alamat',
        gaji='$gaji'
        WHERE id='$id'
    ");

    echo "
    <script>
        alert('✅ Data karyawan berhasil diupdate!');
        window.location='karyawan.php';
    </script>
    ";
    exit;
}

// Ambil jumlah karyawan
$jumlah = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM karyawan
"));

// Ambil data karyawan
$data = mysqli_query($conn, "
    SELECT * FROM karyawan
    ORDER BY id DESC
");

$totalKaryawan = $jumlah['total'] ?? 0;
$editMode = isset($_GET['edit']);
$editData = null;

if ($editMode) {
    $idEdit = intval($_GET['edit']);
    $editData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM karyawan
        WHERE id='$idEdit'
    "));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - Cafeku</title>
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

        .btn-warning {
            background: #f39c12;
            color: #fff;
        }
        .btn-warning:hover {
            background: #d68910;
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
            border: 1px solid #3498db;
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
            color: #3498db;
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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

        .form-group select.form-control {
            appearance: none;
            cursor: pointer;
        }

        .form-group select.form-control option {
            background: #2a2a2a;
            color: #ddd;
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

        .table-wrapper table tbody td .jabatan-tag {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .jabatan-tag.manager { background: #9b59b6; color: #fff; }
        .jabatan-tag.kasir { background: #3498db; color: #fff; }
        .jabatan-tag.barista { background: #2ecc71; color: #1a1a1a; }
        .jabatan-tag.kitchen { background: #e74c3c; color: #fff; }
        .jabatan-tag.waiter { background: #f1c40f; color: #1a1a1a; }
        .jabatan-tag.default { background: #666; color: #fff; }

        .table-wrapper table tbody td .gaji-value {
            color: #c08b5c;
            font-weight: 700;
        }

        /* ===== ACTION BUTTONS ===== */
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-group .btn-edit {
            background: #f39c12;
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

        .action-group .btn-edit:hover {
            background: #d68910;
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
            <h1>👥 Data Karyawan</h1>
            <div class="subtitle">Kelola data karyawan cafe</div>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== TOTAL BOX ===== -->
    <div class="total-box">
        <div class="label">👥 Total Karyawan</div>
        <div class="value"><?= $totalKaryawan ?></div>
        <div class="sub-info">📋 Data karyawan aktif</div>
    </div>

    <!-- ===== FORM TAMBAH/EDIT ===== -->
    <div class="card">
        <div class="card-header">
            <h3><?= $editMode ? '✏️ Edit Karyawan' : '➕ Tambah Karyawan' ?></h3>
            <?php if ($editMode): ?>
                <a href="karyawan.php" class="btn btn-primary btn-sm">↻ Batal Edit</a>
            <?php endif; ?>
        </div>

        <form method="POST">
            <?php if ($editMode && $editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nama">👤 Nama Karyawan</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama karyawan..." required value="<?= $editMode && $editData ? htmlspecialchars($editData['nama']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="jabatan">📌 Jabatan</label>
                    <select id="jabatan" name="jabatan" class="form-control" required>
                        <option value="">-- Pilih Jabatan --</option>
                        <option value="Manager" <?= ($editMode && $editData && $editData['jabatan'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                        <option value="Kasir" <?= ($editMode && $editData && $editData['jabatan'] == 'Kasir') ? 'selected' : '' ?>>Kasir</option>
                        <option value="Barista" <?= ($editMode && $editData && $editData['jabatan'] == 'Barista') ? 'selected' : '' ?>>Barista</option>
                        <option value="Kitchen" <?= ($editMode && $editData && $editData['jabatan'] == 'Kitchen') ? 'selected' : '' ?>>Kitchen</option>
                        <option value="Waiter" <?= ($editMode && $editData && $editData['jabatan'] == 'Waiter') ? 'selected' : '' ?>>Waiter</option>
                        <option value="Admin" <?= ($editMode && $editData && $editData['jabatan'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                        <option value="Owner" <?= ($editMode && $editData && $editData['jabatan'] == 'Owner') ? 'selected' : '' ?>>Owner</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="no_hp">📱 Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" class="form-control" placeholder="Nomor HP..." required value="<?= $editMode && $editData ? htmlspecialchars($editData['no_hp']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="gaji">💰 Gaji</label>
                    <input type="number" id="gaji" name="gaji" class="form-control" placeholder="Gaji..." required value="<?= $editMode && $editData ? $editData['gaji'] : '' ?>">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="alamat">📍 Alamat</label>
                    <textarea id="alamat" name="alamat" class="form-control" placeholder="Alamat karyawan..." rows="3" required><?= $editMode && $editData ? htmlspecialchars($editData['alamat']) : '' ?></textarea>
                </div>
            </div>

            <?php if ($editMode && $editData): ?>
                <button type="submit" name="update" class="btn btn-success btn-block">
                    💾 Update Karyawan
                </button>
            <?php else: ?>
                <button type="submit" name="simpan" class="btn btn-success btn-block">
                    💾 Simpan Karyawan
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- ===== TABEL DATA ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Data Semua Karyawan</h3>
        </div>

        <div class="table-wrapper">
            <?php if (mysqli_num_rows($data) == 0): ?>
                <div class="empty-state">📭 Belum ada data karyawan</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th style="text-align:right;">Gaji</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($d = mysqli_fetch_assoc($data)):
                            $jabatanClass = strtolower($d['jabatan']);
                            $jabatanClassMap = [
                                'manager' => 'manager',
                                'kasir' => 'kasir',
                                'barista' => 'barista',
                                'kitchen' => 'kitchen',
                                'waiter' => 'waiter'
                            ];
                            $class = $jabatanClassMap[$jabatanClass] ?? 'default';
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($d['nama']) ?></strong></td>
                                <td>
                                    <span class="jabatan-tag <?= $class ?>">
                                        <?= htmlspecialchars($d['jabatan']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($d['no_hp']) ?></td>
                                <td><?= htmlspecialchars($d['alamat']) ?></td>
                                <td style="text-align:right;" class="gaji-value">
                                    Rp <?= number_format($d['gaji'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align:center;">
                                    <div class="action-group" style="justify-content:center;">
                                        <a class="btn-edit" href="?edit=<?= $d['id'] ?>">
                                            ✏️ Edit
                                        </a>
                                        <a class="btn-hapus" href="?hapus=<?= $d['id'] ?>" onclick="return confirm('Yakin ingin menghapus data karyawan <?= htmlspecialchars($d['nama']) ?>?')">
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