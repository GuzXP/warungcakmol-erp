<?php
// FILE: admin/manager.php
session_start();
require '../fungsi.php';

// Izinkan akses untuk admin, owner, dan manager
if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner', 'manager'])) {
    header('Location: ../login.php');
    exit;
}

$namaManager = $_SESSION['nama'] ?? 'Manager';
$isAdmin = in_array($_SESSION['role'], ['admin', 'owner']);

// ===== CEK DAN TAMBAHKAN DATA KARYAWAN JIKA BELUM ADA =====
$cekBudi = query("SELECT * FROM karyawan WHERE nama = 'Budi'");
if (empty($cekBudi)) {
    mysqli_query($conn, "
        INSERT INTO karyawan (nama, jabatan, no_hp, alamat, gaji) 
        VALUES ('Budi', 'Kasir', '081234567890', 'Jl. Merdeka No. 1', 3000000)
    ");
}

$cekChaf = query("SELECT * FROM karyawan WHERE nama = 'Chaf'");
if (empty($cekChaf)) {
    mysqli_query($conn, "
        INSERT INTO karyawan (nama, jabatan, no_hp, alamat, gaji) 
        VALUES ('Chaf', 'Kitchen', '081298765432', 'Jl. Sudirman No. 5', 3500000)
    ");
}

// ===== AMBIL DATA DARI TABEL KARYAWAN =====
$karyawan = query("SELECT * FROM karyawan ORDER BY jabatan, nama");

// ===== PROSES UPDATE STATUS TUGAS =====
if (isset($_POST['update_status_tugas'])) {
    $tugas_id = intval($_POST['tugas_id']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status_baru']);
    
    mysqli_query($conn, "
        UPDATE tugas_karyawan 
        SET status = '$status_baru' 
        WHERE id = '$tugas_id'
    ");
    
    echo "<script>alert('✅ Status tugas berhasil diupdate!'); window.location='manager.php';</script>";
    exit;
}

// ===== PROSES HAPUS TUGAS =====
if (isset($_GET['hapus_tugas'])) {
    $tugas_id = intval($_GET['hapus_tugas']);
    
    mysqli_query($conn, "DELETE FROM tugas_karyawan WHERE id = '$tugas_id'");
    
    echo "<script>alert('🗑️ Tugas berhasil dihapus!'); window.location='manager.php';</script>";
    exit;
}

// ===== PROSES UPDATE KEHADIRAN SHIFT =====
if (isset($_POST['update_kehadiran'])) {
    $shift_id = intval($_POST['shift_id']);
    $kehadiran = mysqli_real_escape_string($conn, $_POST['kehadiran']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan'] ?? '');
    
    mysqli_query($conn, "
        UPDATE shift_karyawan 
        SET status = '$kehadiran', 
            alasan_ketidakhadiran = '$alasan' 
        WHERE id = '$shift_id'
    ");
    
    echo "<script>alert('✅ Status kehadiran berhasil diupdate!'); window.location='manager.php';</script>";
    exit;
}

// ===== PROSES EDIT SHIFT =====
if (isset($_POST['edit_shift'])) {
    $shift_id = intval($_POST['shift_id']);
    $nama_karyawan = mysqli_real_escape_string($conn, $_POST['nama_karyawan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $shift = mysqli_real_escape_string($conn, $_POST['shift']);
    
    if ($shift == 'Pagi') {
        $jam_masuk = '07:00:00';
        $jam_pulang = '15:00:00';
    } else {
        $jam_masuk = '15:00:00';
        $jam_pulang = '22:00:00';
    }
    
    mysqli_query($conn, "
        UPDATE shift_karyawan 
        SET nama_karyawan = '$nama_karyawan',
            tanggal = '$tanggal',
            shift = '$shift',
            jam_masuk = '$jam_masuk',
            jam_pulang = '$jam_pulang'
        WHERE id = '$shift_id'
    ");
    
    echo "<script>alert('✅ Shift berhasil diedit!'); window.location='manager.php';</script>";
    exit;
}

// ===== PROSES HAPUS SHIFT =====
if (isset($_GET['hapus_shift'])) {
    $shift_id = intval($_GET['hapus_shift']);
    
    mysqli_query($conn, "DELETE FROM shift_karyawan WHERE id = '$shift_id'");
    
    echo "<script>alert('🗑️ Shift berhasil dihapus!'); window.location='manager.php';</script>";
    exit;
}

// Proses tambah libur
if (isset($_POST['tambah_libur'])) {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $alasan_libur = mysqli_real_escape_string($conn, $_POST['alasan_libur']);
    $nama_karyawan = mysqli_real_escape_string($conn, $_POST['nama_karyawan']);
    
    $cek = query("SELECT COUNT(*) as total FROM jadwal_libur WHERE tanggal = '$tanggal'");
    if ($cek[0]['total'] >= 2) {
        echo "<script>alert('⚠️ Tanggal ini sudah penuh (maksimal 2 orang libur)!'); window.location='manager.php';</script>";
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO jadwal_libur (nama_karyawan, tanggal, alasan_libur, ditentukan_oleh) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $nama_karyawan, $tanggal, $alasan_libur, $namaManager);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('✅ Libur berhasil ditambahkan!'); window.location='manager.php';</script>";
    exit;
}

// Proses tambah tugas
if (isset($_POST['tambah_tugas'])) {
    $nama_karyawan = mysqli_real_escape_string($conn, $_POST['nama_karyawan']);
    $nama_tugas = mysqli_real_escape_string($conn, $_POST['nama_tugas']);
    $uang_diberikan = intval($_POST['uang_diberikan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    $stmt = mysqli_prepare($conn, "INSERT INTO tugas_karyawan (nama_karyawan, nama_tugas, uang_diberikan, tanggal, ditentukan_oleh, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
    mysqli_stmt_bind_param($stmt, "ssiss", $nama_karyawan, $nama_tugas, $uang_diberikan, $tanggal, $namaManager);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('✅ Tugas berhasil ditambahkan!'); window.location='manager.php';</script>";
    exit;
}

// Proses tambah shift
if (isset($_POST['tambah_shift'])) {
    $nama_karyawan = mysqli_real_escape_string($conn, $_POST['nama_karyawan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $shift = mysqli_real_escape_string($conn, $_POST['shift']);
    
    if ($shift == 'Pagi') {
        $jam_masuk = '07:00:00';
        $jam_pulang = '15:00:00';
    } else {
        $jam_masuk = '15:00:00';
        $jam_pulang = '22:00:00';
    }
    
    $cek = query("SELECT * FROM shift_karyawan WHERE nama_karyawan = '$nama_karyawan' AND tanggal = '$tanggal'");
    if (!empty($cek)) {
        echo "<script>alert('⚠️ Karyawan sudah memiliki shift di tanggal ini!'); window.location='manager.php';</script>";
        exit;
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO shift_karyawan (nama_karyawan, tanggal, shift, jam_masuk, jam_pulang, status) VALUES (?, ?, ?, ?, ?, 'Hadir')");
    mysqli_stmt_bind_param($stmt, "sssss", $nama_karyawan, $tanggal, $shift, $jam_masuk, $jam_pulang);
    mysqli_stmt_execute($stmt);
    echo "<script>alert('✅ Shift berhasil ditambahkan!'); window.location='manager.php';</script>";
    exit;
}

// Ambil data lainnya
$libur = query("SELECT * FROM jadwal_libur ORDER BY tanggal DESC");
$tugas = query("SELECT * FROM tugas_karyawan ORDER BY tanggal DESC");
$shift = query("SELECT * FROM shift_karyawan ORDER BY tanggal DESC");

// Ringkasan libur per karyawan
$liburCount = [];
foreach ($libur as $l) {
    $nama = $l['nama_karyawan'];
    if (!isset($liburCount[$nama])) {
        $liburCount[$nama] = 0;
    }
    $liburCount[$nama]++;
}

// ===== KELOMPOKKAN KARYAWAN BERDASARKAN JABATAN =====
$karyawanByJabatan = [];
foreach ($karyawan as $k) {
    $jabatan = $k['jabatan'] ?? 'Lainnya';
    if (!isset($karyawanByJabatan[$jabatan])) {
        $karyawanByJabatan[$jabatan] = [];
    }
    $karyawanByJabatan[$jabatan][] = $k['nama'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manager - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #1a1a1a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: #1f1f1f;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
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
        .page-header .subtitle {
            color: #888;
            font-size: 14px;
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
        .btn-primary {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .btn-primary:hover { background: #333; transform: translateY(-2px); }
        .btn-warning {
            background: #f39c12;
            color: #fff;
        }
        .btn-warning:hover {
            background: #d68910;
            transform: translateY(-2px);
        }
        .btn-sm { padding: 6px 14px; font-size: 12px; }
        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-info { background: #3498db; color: #fff; }
        .btn-info:hover { background: #2980b9; transform: translateY(-2px); }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
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
        .card label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .card input, .card select, .card textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #333;
            border-radius: 6px;
            background: #1f1f1f;
            color: #ddd;
            font-size: 14px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }
        .card input:focus, .card select:focus, .card textarea:focus {
            outline: none;
            border-color: #c08b5c;
        }
        .card textarea { min-height: 60px; resize: vertical; }
        .card .btn-block { width: 100%; padding: 12px; }
        
        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table th {
            background: #2a2a2a;
            color: #c08b5c;
            padding: 8px 10px;
            text-align: left;
            border-bottom: 2px solid #c08b5c;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #333;
            color: #ddd;
            font-size: 12px;
            vertical-align: middle;
        }
        table tr:hover {
            background: #2a2a2a;
        }
        table td .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }
        
        .badge-pagi { background: #f1c40f; color: #1a1a1a; }
        .badge-siang { background: #3498db; color: #fff; }
        .badge-libur { background: #e74c3c; color: #fff; }
        .badge-selesai { background: #27ae60; color: #fff; }
        .badge-pending { background: #f39c12; color: #1a1a1a; }
        .badge-hadir { background: #27ae60; color: #fff; }
        .badge-tidak-hadir { background: #e74c3c; color: #fff; }
        
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 15px;
            border: 1px solid #333;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 900;
            color: #c08b5c;
        }
        .stat-card .label {
            color: #888;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .role-badge {
            background: #2a2a2a;
            padding: 5px 15px;
            border-radius: 20px;
            color: #c08b5c;
            font-size: 13px;
            border: 1px solid #c08b5c;
        }

        .karyawan-tag {
            display: inline-block;
            background: #1f1f1f;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: #ddd;
            border: 1px solid #333;
            margin: 3px;
        }
        .karyawan-tag.manager { border-color: #9b59b6; color: #9b59b6; }
        .karyawan-tag.kasir { border-color: #3498db; color: #3498db; }
        .karyawan-tag.barista { border-color: #2ecc71; color: #2ecc71; }
        .karyawan-tag.kitchen { border-color: #e74c3c; color: #e74c3c; }
        .karyawan-tag.waiter { border-color: #f1c40f; color: #f1c40f; }
        .karyawan-tag.admin { border-color: #c08b5c; color: #c08b5c; }
        .karyawan-tag.owner { border-color: #e67e22; color: #e67e22; }
        .karyawan-tag.default { border-color: #666; color: #666; }
        
        /* ===== AKSI TUGAS ===== */
        .action-tugas {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
        .action-tugas .btn-selesai,
        .action-tugas .btn-pending,
        .action-tugas .btn-hapus-tugas {
            padding: 3px 10px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
        }
        .action-tugas .btn-selesai {
            background: #27ae60;
            color: #fff;
        }
        .action-tugas .btn-selesai:hover {
            background: #219a52;
            transform: scale(1.05);
        }
        .action-tugas .btn-pending {
            background: #f39c12;
            color: #fff;
        }
        .action-tugas .btn-pending:hover {
            background: #d68910;
            transform: scale(1.05);
        }
        .action-tugas .btn-hapus-tugas {
            background: #8B0000;
            color: #fff;
        }
        .action-tugas .btn-hapus-tugas:hover {
            background: #660000;
            transform: scale(1.05);
        }

        /* ===== AKSI SHIFT ===== */
        .action-shift {
            display: flex;
            gap: 3px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
        .action-shift .btn-hadir,
        .action-shift .btn-tidak-hadir,
        .action-shift .btn-edit-shift,
        .action-shift .btn-hapus-shift {
            padding: 3px 8px;
            border: none;
            border-radius: 3px;
            font-weight: 600;
            font-size: 9px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            white-space: nowrap;
            line-height: 1.4;
        }
        .action-shift .btn-hadir {
            background: #27ae60;
            color: #fff;
        }
        .action-shift .btn-hadir:hover {
            background: #219a52;
            transform: scale(1.05);
        }
        .action-shift .btn-tidak-hadir {
            background: #e74c3c;
            color: #fff;
        }
        .action-shift .btn-tidak-hadir:hover {
            background: #c0392b;
            transform: scale(1.05);
        }
        .action-shift .btn-edit-shift {
            background: #3498db;
            color: #fff;
        }
        .action-shift .btn-edit-shift:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
        .action-shift .btn-hapus-shift {
            background: #8B0000;
            color: #fff;
        }
        .action-shift .btn-hapus-shift:hover {
            background: #660000;
            transform: scale(1.05);
        }

        .action-shift .alasan-text {
            color: #e74c3c;
            font-size: 10px;
            font-style: italic;
            max-width: 100px;
            display: block;
            word-wrap: break-word;
            line-height: 1.2;
        }

        /* ===== MODAL ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-box {
            background: #1f1f1f;
            border: 1px solid #c08b5c;
            border-radius: 12px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .modal-box h3 {
            color: #c08b5c;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .modal-box .form-group {
            margin-bottom: 12px;
        }
        .modal-box .form-group label {
            display: block;
            color: #ddd;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .modal-box .form-group .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #333;
            border-radius: 6px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 14px;
            box-sizing: border-box;
        }
        .modal-box .form-group textarea.form-control {
            resize: vertical;
            min-height: 60px;
        }
        .modal-box .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .modal-box .modal-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .modal-box .modal-actions .btn-batal {
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
        }
        .modal-box .modal-actions .btn-batal:hover {
            background: #333;
        }
        .modal-box .modal-actions .btn-simpan {
            background: #27ae60;
            color: #fff;
            flex: 2;
        }
        .modal-box .modal-actions .btn-simpan:hover {
            background: #219a52;
        }

        @media (max-width: 900px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            table { font-size: 10px; }
            table th, table td { padding: 5px 6px; }
            .action-tugas, .action-shift {
                flex-direction: column;
                align-items: stretch;
                gap: 2px;
            }
            .action-tugas .btn-selesai,
            .action-tugas .btn-pending,
            .action-tugas .btn-hapus-tugas,
            .action-shift .btn-hadir,
            .action-shift .btn-tidak-hadir,
            .action-shift .btn-edit-shift,
            .action-shift .btn-hapus-shift {
                font-size: 9px;
                padding: 2px 6px;
                text-align: center;
            }
            .action-shift .alasan-text {
                max-width: 60px;
                font-size: 9px;
            }
        }
        @media (max-width: 600px) {
            .stat-row { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            table { font-size: 9px; }
            table th, table td { padding: 4px 4px; }
            .action-tugas .btn-selesai,
            .action-tugas .btn-pending,
            .action-tugas .btn-hapus-tugas,
            .action-shift .btn-hadir,
            .action-shift .btn-tidak-hadir,
            .action-shift .btn-edit-shift,
            .action-shift .btn-hapus-shift {
                font-size: 8px;
                padding: 2px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>👨‍💼 Dashboard Manager</h1>
                <p class="subtitle">Kelola jadwal libur, tugas, dan shift karyawan</p>
                <span class="role-badge"><?= ucfirst($_SESSION['role'] ?? 'Manager') ?></span>
            </div>
            <div class="btn-group">
                <a href="index.php" class="btn btn-primary">← Dashboard</a>
                <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <!-- ===== STATISTIK ===== -->
        <div class="stat-row">
            <div class="stat-card">
                <div class="number"><?= count($karyawan) ?></div>
                <div class="label">👥 Total Karyawan</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= count($libur) ?></div>
                <div class="label">📅 Total Libur</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= count($tugas) ?></div>
                <div class="label">📋 Total Tugas</div>
            </div>
            <div class="stat-card">
                <div class="number"><?= count($shift) ?></div>
                <div class="label">🕐 Total Shift</div>
            </div>
        </div>

        <!-- ===== DAFTAR KARYAWAN BERDASARKAN JABATAN ===== -->
        <div class="card" style="margin-bottom:20px;">
            <h3>👥 Daftar Karyawan</h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; margin-top:10px;">
                <?php foreach ($karyawanByJabatan as $jabatan => $namaList): 
                    $class = strtolower($jabatan);
                    $classMap = [
                        'manager' => 'manager',
                        'kasir' => 'kasir',
                        'barista' => 'barista',
                        'kitchen' => 'kitchen',
                        'waiter' => 'waiter',
                        'admin' => 'admin',
                        'owner' => 'owner'
                    ];
                    $class = $classMap[$class] ?? 'default';
                ?>
                    <div style="background:#1f1f1f; padding:15px; border-radius:8px; border-left:4px solid <?= $class == 'manager' ? '#9b59b6' : ($class == 'kasir' ? '#3498db' : ($class == 'barista' ? '#2ecc71' : ($class == 'kitchen' ? '#e74c3c' : ($class == 'waiter' ? '#f1c40f' : '#666')))) ?>;">
                        <div style="color:#c08b5c; font-weight:700; margin-bottom:8px;"><?= htmlspecialchars($jabatan) ?></div>
                        <div>
                            <?php foreach ($namaList as $nama): ?>
                                <span class="karyawan-tag <?= $class ?>"><?= htmlspecialchars($nama) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== FORM TAMBAH ===== -->
        <div class="grid-3">
            <!-- Form Tambah Libur -->
            <div class="card">
                <h3>📅 Tambah Jadwal Libur</h3>
                <form method="POST">
                    <label>Nama Karyawan</label>
                    <select name="nama_karyawan" required>
                        <option value="">Pilih Karyawan</option>
                        <?php foreach ($karyawan as $k): ?>
                            <option value="<?= htmlspecialchars($k['nama']) ?>"><?= htmlspecialchars($k['nama']) ?> (<?= $k['jabatan'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Tanggal Libur</label>
                    <input type="date" name="tanggal" required>
                    
                    <label>Alasan Libur</label>
                    <textarea name="alasan_libur" placeholder="Contoh: Tanggal Merah, Sakit, Libur Tahunan..." required></textarea>
                    
                    <button type="submit" name="tambah_libur" class="btn btn-success btn-block">➕ Tambah Libur</button>
                </form>
                <div style="margin-top:10px; color:#888; font-size:12px;">
                    ⚠️ Maksimal 2 orang libur per hari
                </div>
            </div>

            <!-- Form Tambah Tugas -->
            <div class="card">
                <h3>📋 Tambah Tugas</h3>
                <form method="POST">
                    <label>Nama Karyawan</label>
                    <select name="nama_karyawan" required>
                        <option value="">Pilih Karyawan</option>
                        <?php foreach ($karyawan as $k): ?>
                            <option value="<?= htmlspecialchars($k['nama']) ?>"><?= htmlspecialchars($k['nama']) ?> (<?= $k['jabatan'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Nama Tugas</label>
                    <input type="text" name="nama_tugas" placeholder="Contoh: Belanja bahan baku" required>
                    
                    <label>Uang Diberikan</label>
                    <input type="number" name="uang_diberikan" placeholder="0" required>
                    
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" required>
                    
                    <button type="submit" name="tambah_tugas" class="btn btn-primary btn-block">📋 Tambah Tugas</button>
                </form>
            </div>

            <!-- Form Tambah Shift -->
            <div class="card">
                <h3>🕐 Tambah Shift Kerja</h3>
                <form method="POST">
                    <label>Nama Karyawan</label>
                    <select name="nama_karyawan" required>
                        <option value="">Pilih Karyawan</option>
                        <?php foreach ($karyawan as $k): ?>
                            <option value="<?= htmlspecialchars($k['nama']) ?>"><?= htmlspecialchars($k['nama']) ?> (<?= $k['jabatan'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" required>
                    
                    <label>Shift</label>
                    <select name="shift" required>
                        <option value="Pagi">🌅 Pagi (07:00 - 15:00)</option>
                        <option value="Siang">🌇 Siang (15:00 - 22:00)</option>
                    </select>
                    
                    <button type="submit" name="tambah_shift" class="btn btn-success btn-block">➕ Tambah Shift</button>
                </form>
            </div>
        </div>

        <!-- ===== TABEL LIBUR ===== -->
        <div class="card" style="margin-top:20px;">
            <h3>📅 Daftar Jadwal Libur</h3>
            <div class="table-wrapper">
                <?php if (empty($libur)): ?>
                    <p style="color:#888;">Belum ada jadwal libur</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Karyawan</th>
                                <th>Alasan</th>
                                <th>Ditentukan Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($libur as $l): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($l['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($l['nama_karyawan']) ?></td>
                                    <td><?= htmlspecialchars($l['alasan_libur'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($l['ditentukan_oleh'] ?? 'Manager') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== TABEL SHIFT ===== -->
        <div class="card" style="margin-top:20px;">
            <h3>🕐 Daftar Shift Kerja Karyawan</h3>
            <div class="table-wrapper">
                <?php if (empty($shift)): ?>
                    <p style="color:#888;">Belum ada shift kerja</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:75px;">Tanggal</th>
                                <th style="width:90px;">Karyawan</th>
                                <th style="width:55px;">Shift</th>
                                <th style="width:60px;">Masuk</th>
                                <th style="width:60px;">Pulang</th>
                                <th style="width:75px;">Status</th>
                                <th style="width:100px;">Alasan</th>
                                <th style="width:160px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shift as $s): 
                                $status = strtolower($s['status'] ?? 'hadir');
                                $isHadir = $status == 'hadir';
                                $isTidakHadir = $status == 'tidak hadir';
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($s['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($s['nama_karyawan']) ?></td>
                                    <td>
                                        <span class="badge <?= $s['shift'] == 'Pagi' ? 'badge-pagi' : 'badge-siang' ?>">
                                            <?= $s['shift'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('H:i', strtotime($s['jam_masuk'])) ?></td>
                                    <td><?= date('H:i', strtotime($s['jam_pulang'])) ?></td>
                                    <td>
                                        <?php if ($isHadir): ?>
                                            <span class="badge badge-hadir">✅ Hadir</span>
                                        <?php elseif ($isTidakHadir): ?>
                                            <span class="badge badge-tidak-hadir">❌ Tidak</span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#f39c12; color:#1a1a1a;">⏳ <?= $s['status'] ?? 'Pending' ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['alasan_ketidakhadiran'])): ?>
                                            <span class="alasan-text"><?= htmlspecialchars($s['alasan_ketidakhadiran']) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="action-shift" style="justify-content:center;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="shift_id" value="<?= $s['id'] ?>">
                                                <input type="hidden" name="kehadiran" value="Hadir">
                                                <input type="hidden" name="alasan" value="">
                                                <button type="submit" name="update_kehadiran" class="btn-hadir" onclick="return confirm('Konfirmasi hadir?')">✅</button>
                                            </form>
                                            
                                            <button type="button" class="btn-tidak-hadir" onclick="openTidakHadirModal(<?= $s['id'] ?>, '<?= htmlspecialchars($s['nama_karyawan']) ?>')">❌</button>
                                            
                                            <button type="button" class="btn-edit-shift" onclick="openEditShiftModal(
                                                <?= $s['id'] ?>,
                                                '<?= htmlspecialchars($s['nama_karyawan']) ?>',
                                                '<?= $s['tanggal'] ?>',
                                                '<?= $s['shift'] ?>'
                                            )">✏️</button>
                                            
                                            <a href="?hapus_shift=<?= $s['id'] ?>" class="btn-hapus-shift" onclick="return confirm('Yakin hapus?')">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== TABEL TUGAS ===== -->
        <div class="card" style="margin-top:20px;">
            <h3>📋 Daftar Tugas Karyawan</h3>
            <div class="table-wrapper">
                <?php if (empty($tugas)): ?>
                    <p style="color:#888;">Belum ada tugas</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:80px;">Tanggal</th>
                                <th style="width:100px;">Karyawan</th>
                                <th>Tugas</th>
                                <th style="width:100px; text-align:right;">Uang</th>
                                <th style="width:80px;">Status</th>
                                <th style="width:100px;">Ditentukan</th>
                                <th style="width:130px; text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tugas as $t): 
                                $status = strtolower($t['status'] ?? 'pending');
                                $isSelesai = $status == 'selesai';
                            ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($t['nama_karyawan']) ?></td>
                                    <td><?= htmlspecialchars($t['nama_tugas']) ?></td>
                                    <td style="text-align:right;">Rp <?= number_format($t['uang_diberikan'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($isSelesai): ?>
                                            <span class="badge badge-selesai">✅ Selesai</span>
                                        <?php else: ?>
                                            <span class="badge badge-pending">⏳ Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['ditentukan_oleh'] ?? '-') ?></td>
                                    <td style="text-align:center;">
                                        <div class="action-tugas" style="justify-content:center;">
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="tugas_id" value="<?= $t['id'] ?>">
                                                <?php if ($isSelesai): ?>
                                                    <button type="submit" name="update_status_tugas" class="btn-pending" value="Pending">
                                                        ⏳ Belum
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" name="update_status_tugas" class="btn-selesai" value="Selesai">
                                                        ✅ Selesai
                                                    </button>
                                                <?php endif; ?>
                                                <input type="hidden" name="status_baru" value="<?= $isSelesai ? 'Pending' : 'Selesai' ?>">
                                            </form>
                                            <a href="?hapus_tugas=<?= $t['id'] ?>" class="btn-hapus-tugas" onclick="return confirm('Yakin hapus?')">🗑️</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== RINGKASAN LIBUR PER KARYAWAN ===== -->
        <div class="card" style="margin-top:20px; border-color:#e74c3c;">
            <h3>📊 Ringkasan Libur per Karyawan</h3>
            <?php if (empty($liburCount)): ?>
                <p style="color:#888;">Belum ada data libur</p>
            <?php else: ?>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:10px;">
                    <?php foreach ($liburCount as $nama => $total): ?>
                        <div style="background:#1f1f1f; padding:10px 15px; border-radius:8px; border-left:4px solid #e74c3c;">
                            <div style="color:#ddd; font-weight:600;"><?= htmlspecialchars($nama) ?></div>
                            <div style="color:#e74c3c; font-weight:700;"><?= $total ?> hari libur</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ===== MODAL TIDAK HADIR ===== -->
    <div class="modal-overlay" id="modalTidakHadir">
        <div class="modal-box">
            <h3>❌ Tidak Hadir</h3>
            <p style="color:#888; margin-bottom:15px;">Berikan alasan ketidakhadiran karyawan</p>
            <form method="POST">
                <input type="hidden" name="shift_id" id="tidak_hadir_shift_id">
                <input type="hidden" name="kehadiran" value="Tidak Hadir">
                
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <input type="text" id="tidak_hadir_nama" class="form-control" disabled style="opacity:0.7;">
                </div>
                
                <div class="form-group">
                    <label>Alasan Tidak Hadir</label>
                    <textarea name="alasan" id="tidak_hadir_alasan" class="form-control" placeholder="Masukkan alasan ketidakhadiran..." required></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-batal" onclick="closeTidakHadirModal()">Batal</button>
                    <button type="submit" name="update_kehadiran" class="btn-simpan">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDIT SHIFT ===== -->
    <div class="modal-overlay" id="modalEditShift">
        <div class="modal-box">
            <h3>✏️ Edit Shift</h3>
            <form method="POST">
                <input type="hidden" name="shift_id" id="edit_shift_id">
                
                <div class="form-group">
                    <label>Nama Karyawan</label>
                    <select name="nama_karyawan" id="edit_shift_karyawan" class="form-control" required>
                        <option value="">Pilih Karyawan</option>
                        <?php foreach ($karyawan as $k): ?>
                            <option value="<?= htmlspecialchars($k['nama']) ?>"><?= htmlspecialchars($k['nama']) ?> (<?= $k['jabatan'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" id="edit_shift_tanggal" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Shift</label>
                    <select name="shift" id="edit_shift_shift" class="form-control" required>
                        <option value="Pagi">🌅 Pagi (07:00 - 15:00)</option>
                        <option value="Siang">🌇 Siang (15:00 - 22:00)</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-batal" onclick="closeEditShiftModal()">Batal</button>
                    <button type="submit" name="edit_shift" class="btn-simpan">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== MODAL TIDAK HADIR =====
        function openTidakHadirModal(shiftId, namaKaryawan) {
            document.getElementById('tidak_hadir_shift_id').value = shiftId;
            document.getElementById('tidak_hadir_nama').value = namaKaryawan;
            document.getElementById('tidak_hadir_alasan').value = '';
            document.getElementById('modalTidakHadir').classList.add('active');
        }
        
        function closeTidakHadirModal() {
            document.getElementById('modalTidakHadir').classList.remove('active');
        }
        
        document.getElementById('modalTidakHadir').addEventListener('click', function(e) {
            if (e.target === this) closeTidakHadirModal();
        });

        // ===== MODAL EDIT SHIFT =====
        function openEditShiftModal(id, nama, tanggal, shift) {
            document.getElementById('edit_shift_id').value = id;
            document.getElementById('edit_shift_karyawan').value = nama;
            document.getElementById('edit_shift_tanggal').value = tanggal;
            document.getElementById('edit_shift_shift').value = shift;
            document.getElementById('modalEditShift').classList.add('active');
        }
        
        function closeEditShiftModal() {
            document.getElementById('modalEditShift').classList.remove('active');
        }
        
        document.getElementById('modalEditShift').addEventListener('click', function(e) {
            if (e.target === this) closeEditShiftModal();
        });
    </script>

</body>
</html>