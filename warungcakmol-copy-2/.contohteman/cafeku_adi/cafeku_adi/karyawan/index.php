<?php
// FILE: karyawan/index.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: ../login.php');
    exit;
}

$namaKaryawan = $_SESSION['nama'] ?? '';
$namaKaryawanSafe = mysqli_real_escape_string($conn, $namaKaryawan);

// Ambil semua karyawan beserta role-nya dari tabel karyawan
$semuaKaryawan = query("SELECT * FROM karyawan ORDER BY jabatan, nama");

// Ambil tugas ALL karyawan (agar semua tugas terlihat)
$semuaTugas = query("SELECT * FROM tugas_karyawan ORDER BY tanggal DESC");

// Ambil tugas untuk karyawan yang login
$tugasSaya = query("SELECT * FROM tugas_karyawan WHERE LOWER(nama_karyawan) = LOWER('$namaKaryawanSafe') ORDER BY tanggal DESC");
$tugasCount = count($tugasSaya);

// Ambil jadwal karyawan hari ini
$tanggalHariIni = date('Y-m-d');
$jadwalHariIni = query("
    SELECT * FROM jadwal_karyawan 
    WHERE tanggal = '$tanggalHariIni' 
    AND LOWER(nama_karyawan) = LOWER('$namaKaryawanSafe')
");

// Ambil shift hari ini
$shiftHariIni = query("
    SELECT * FROM shift_karyawan 
    WHERE tanggal = '$tanggalHariIni' 
    AND LOWER(nama_karyawan) = LOWER('$namaKaryawanSafe')
");

// Ambil jam masuk dan pulang
$jamMasuk = '07:00';
$jamPulang = '22:00';
$shiftNama = '-';

if (!empty($shiftHariIni)) {
    $shiftData = $shiftHariIni[0];
    $shiftNama = $shiftData['shift'] ?? '-';
    $jamMasuk = $shiftData['jam_masuk'] ?? '07:00';
    $jamPulang = $shiftData['jam_pulang'] ?? '22:00';
} elseif (!empty($jadwalHariIni)) {
    $jamMasuk = $jadwalHariIni[0]['jam_masuk'] ?? '07:00';
    $jamPulang = $jadwalHariIni[0]['jam_pulang'] ?? '22:00';
}

// Cek apakah hari ini libur
$liburHariIni = query("
    SELECT * FROM jadwal_libur 
    WHERE tanggal = '$tanggalHariIni' 
    AND LOWER(nama_karyawan) = LOWER('$namaKaryawanSafe')
");
$isLibur = !empty($liburHariIni);

// Hitung tugas selesai dan pending untuk karyawan yang login
$tugasSelesai = 0;
$tugasPending = 0;
foreach ($tugasSaya as $task) {
    if (strtolower($task['status'] ?? '') == 'selesai') {
        $tugasSelesai++;
    } else {
        $tugasPending++;
    }
}

// Kelompokkan karyawan berdasarkan jabatan
$karyawanByJabatan = [];
foreach ($semuaKaryawan as $k) {
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
    <title>Dashboard Karyawan - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #1a1a1a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .admin-container {
            max-width: 1200px;
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
        .page-header .page-note {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
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
        .btn-full {
            width: 100%;
            text-align: center;
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            margin-top: 10px;
        }
        .btn-full:hover { background: #333; transform: translateY(-2px); }
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
        .card .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #2a2a2a;
            color: #ddd;
        }
        .card .info-row:last-child {
            border-bottom: none;
        }
        .card .info-row .label {
            color: #888;
        }
        .card .info-row .value {
            color: #ddd;
            font-weight: 600;
        }
        .status-libur {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e74c3c;
            font-weight: 600;
            text-align: center;
        }
        .status-kerja {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #2ecc71;
            font-weight: 600;
            text-align: center;
        }
        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table th {
            background: #1f1f1f;
            color: #c08b5c;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #c08b5c;
            font-size: 12px;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #333;
            color: #ddd;
            font-size: 12px;
        }
        table tr:hover {
            background: #1f1f1f;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-selesai { background: #27ae60; color: #fff; }
        .badge-pending { background: #f39c12; color: #1a1a1a; }
        .badge-proses { background: #3498db; color: #fff; }
        .badge-libur { background: #e74c3c; color: #fff; }
        .badge-kerja { background: #2ecc71; color: #1a1a1a; }
        .badge-pagi { background: #f1c40f; color: #1a1a1a; }
        .badge-siang { background: #3498db; color: #fff; }

        .nav-link {
            display: block;
            padding: 12px 20px;
            color: #c08b5c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .nav-link:hover {
            background: rgba(192, 139, 92, 0.1);
            padding-left: 28px;
        }
        .nav-link.active {
            background: rgba(192, 139, 92, 0.15);
            border-right: 3px solid #c08b5c;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        /* ===== KARYAWAN TAG ===== */
        .karyawan-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
        }
        .karyawan-tag {
            background: #1f1f1f;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #ddd;
            border: 1px solid #333;
        }
        .karyawan-tag.manager { border-color: #9b59b6; color: #9b59b6; }
        .karyawan-tag.kasir { border-color: #3498db; color: #3498db; }
        .karyawan-tag.barista { border-color: #2ecc71; color: #2ecc71; }
        .karyawan-tag.kitchen { border-color: #e74c3c; color: #e74c3c; }
        .karyawan-tag.waiter { border-color: #f1c40f; color: #f1c40f; }
        .karyawan-tag.admin { border-color: #c08b5c; color: #c08b5c; }
        .karyawan-tag.owner { border-color: #e67e22; color: #e67e22; }
        .karyawan-tag.default { border-color: #666; color: #666; }

        .karyawan-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .karyawan-group {
            background: #1f1f1f;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #666;
        }
        .karyawan-group .group-title {
            color: #c08b5c;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 8px;
        }
        .karyawan-group .group-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            color: #ddd;
            border: 1px solid #333;
            margin: 2px;
        }

        .jabatan-manager .group-title { color: #9b59b6; }
        .jabatan-kasir .group-title { color: #3498db; }
        .jabatan-barista .group-title { color: #2ecc71; }
        .jabatan-kitchen .group-title { color: #e74c3c; }
        .jabatan-waiter .group-title { color: #f1c40f; }
        .jabatan-admin .group-title { color: #c08b5c; }
        .jabatan-owner .group-title { color: #e67e22; }
        .jabatan-default .group-title { color: #888; }

        @media (max-width: 600px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; text-align: center; }
            .karyawan-section { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 400px) {
            .karyawan-section { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>📊 Dashboard Karyawan</h1>
                <p class="page-note">Pantau tugas, shift, dan daftar karyawan</p>
            </div>
            <div>
                <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <div class="admin-grid">
            <div class="navbar">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li><a href="index.php" class="nav-link active">📊 Dashboard</a></li>
                    <li><a href="jadwal.php" class="nav-link">📋 Jadwal Shift</a></li>
                    <li><a href="libur.php" class="nav-link">📅 Jadwal Libur</a></li>
                </ul>
            </div>

            <div class="admin-main" style="flex:1;">

                <!-- ===== STATUS HARI INI ===== -->
                <div class="card" style="border:1px solid #c08b5c;">
                    <h3>📌 Status Hari Ini</h3>
                    <?php if ($isLibur): ?>
                        <div class="status-libur">🎉 Hari ini Anda libur! Selamat beristirahat.</div>
                    <?php else: ?>
                        <div class="status-kerja">✅ Anda sedang bertugas hari ini</div>
                        <div style="margin-top:15px;">
                            <div class="info-row">
                                <span class="label">🔄 Shift</span>
                                <span class="value">
                                    <?php if ($shiftNama != '-'): ?>
                                        <span class="badge-status <?= $shiftNama == 'Pagi' ? 'badge-pagi' : 'badge-siang' ?>">
                                            <?= $shiftNama ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#888;">Belum ditentukan</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">🕐 Jam Masuk</span>
                                <span class="value"><?= date('H:i', strtotime($jamMasuk)) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">🕐 Jam Pulang</span>
                                <span class="value"><?= date('H:i', strtotime($jamPulang)) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">📅 Tanggal</span>
                                <span class="value"><?= date('d M Y') ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ===== DAFTAR SEMUA KARYAWAN ===== -->
                <div class="card" style="margin-top:20px;">
                    <h3>👥 Daftar Semua Karyawan</h3>
                    <div class="karyawan-section">
                        <?php 
                        $colorMap = [
                            'manager' => '#9b59b6',
                            'kasir' => '#3498db',
                            'barista' => '#2ecc71',
                            'kitchen' => '#e74c3c',
                            'waiter' => '#f1c40f',
                            'admin' => '#c08b5c',
                            'owner' => '#e67e22'
                        ];
                        foreach ($karyawanByJabatan as $jabatan => $namaList): 
                            $class = strtolower($jabatan);
                            $classMap = [
                                'manager' => 'jabatan-manager',
                                'kasir' => 'jabatan-kasir',
                                'barista' => 'jabatan-barista',
                                'kitchen' => 'jabatan-kitchen',
                                'waiter' => 'jabatan-waiter',
                                'admin' => 'jabatan-admin',
                                'owner' => 'jabatan-owner'
                            ];
                            $class = $classMap[$class] ?? 'jabatan-default';
                            $borderColor = $colorMap[strtolower($jabatan)] ?? '#666';
                        ?>
                            <div class="karyawan-group <?= $class ?>" style="border-left-color: <?= $borderColor ?>;">
                                <div class="group-title"><?= htmlspecialchars($jabatan) ?></div>
                                <?php foreach ($namaList as $nama): ?>
                                    <span class="group-tag" style="border-color: <?= $borderColor ?>; color: <?= $borderColor ?>;">
                                        <?= htmlspecialchars($nama) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ===== DAFTAR TUGAS ===== -->
                <div class="card" style="margin-top:20px;">
                    <h3>📋 Daftar Tugas Karyawan</h3>
                    <?php if (empty($semuaTugas)): ?>
                        <p style="color:#888; padding:10px 0;">Belum ada tugas yang diberikan</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Karyawan</th>
                                        <th>Tugas</th>
                                        <th style="text-align:right;">Uang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($semuaTugas as $task): 
                                        $status = strtolower($task['status'] ?? 'pending');
                                        $badgeClass = 'badge-pending';
                                        if ($status == 'selesai') $badgeClass = 'badge-selesai';
                                        elseif ($status == 'proses') $badgeClass = 'badge-proses';
                                        $isForMe = strtolower($task['nama_karyawan']) == strtolower($namaKaryawan);
                                    ?>
                                        <tr style="<?= $isForMe ? 'border-left: 3px solid #c08b5c;' : '' ?>">
                                            <td><?= date('d M Y', strtotime($task['tanggal'])) ?></td>
                                            <td>
                                                <?= htmlspecialchars($task['nama_karyawan']) ?>
                                                <?php if ($isForMe): ?>
                                                    <span style="color:#c08b5c; font-size:10px;"> (Anda)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($task['nama_tugas'] ?? '-') ?></td>
                                            <td style="text-align:right;">Rp <?= number_format($task['uang_diberikan'] ?? 0, 0, ',', '.') ?></td>
                                            <td><span class="badge-status <?= $badgeClass ?>"><?= ucfirst($task['status'] ?? 'Pending') ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ===== JAM OPERASIONAL ===== -->
                <div class="card" style="margin-top:20px; border-color:#3498db;">
                    <h3>🕐 Jam Operasional</h3>
                    <div class="info-row">
                        <span class="label">🌅 Shift Pagi</span>
                        <span class="value">07:00 - 15:00</span>
                    </div>
                    <div class="info-row">
                        <span class="label">🌇 Shift Siang</span>
                        <span class="value">15:00 - 22:00</span>
                    </div>
                    <div style="margin-top:10px; color:#888; font-size:13px;">
                        ⏰ Istirahat: 12:00 - 13:00 dan 17:00 - 18:00
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>