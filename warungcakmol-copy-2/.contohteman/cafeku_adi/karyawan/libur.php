<?php
// FILE: karyawan/libur.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: ../login.php');
    exit;
}

// Ambil jadwal libur (2 orang per hari)
$libur = query("
    SELECT * FROM jadwal_libur 
    ORDER BY tanggal DESC
");

// Kelompokkan berdasarkan tanggal (maksimal 2 orang per hari)
$liburByDate = [];
foreach ($libur as $l) {
    $tgl = $l['tanggal'];
    if (!isset($liburByDate[$tgl])) {
        $liburByDate[$tgl] = [];
    }
    if (count($liburByDate[$tgl]) < 2) {
        $liburByDate[$tgl][] = $l['nama_karyawan'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal Libur - Cafeku</title>
    <link rel="stylesheet" href="../css/style-admin.css">
    <style>
        .card {
            background: #1f1f1f;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #333;
            margin-bottom: 20px;
        }
        .card h3 {
            color: #c08b5c;
            margin-top: 0;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .card .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
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
        .libur-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #2a2a2a;
            border-radius: 8px;
            border-left: 4px solid #e74c3c;
            margin-bottom: 10px;
        }
        .libur-item .tgl {
            color: #ddd;
            font-weight: 600;
        }
        .libur-item .nama {
            color: #e74c3c;
            font-weight: 600;
        }
        .libur-item .jumlah {
            color: #888;
            font-size: 13px;
        }
        .libur-empty {
            color: #888;
            font-style: italic;
            padding: 20px 0;
            text-align: center;
        }
        @media (max-width: 600px) {
            .libur-item { flex-direction: column; align-items: flex-start; gap: 5px; }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <a href="../logout.php" class="btn btn-danger btn-logout">Logout</a>
        <div style="clear:both;"></div>
        <h1 style="color:#c08b5c;">📅 Jadwal Libur</h1>

        <div class="admin-grid">
            <div class="navbar">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li><a href="index.php" class="nav-link">📊 Dashboard</a></li>
                    <li><a href="jadwal.php" class="nav-link">📋 Jadwal Karyawan</a></li>
                    <li><a href="libur.php" class="nav-link active">📅 Jadwal Libur</a></li>
                </ul>
            </div>

            <div class="admin-main">
                <!-- ===== INFO LIBUR ===== -->
                <div class="card" style="border:1px solid #e74c3c;">
                    <h3>📌 Informasi Libur</h3>
                    <div class="info-row">
                        <span class="label">📅 Total Hari Libur</span>
                        <span class="value"><?= count($liburByDate) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">👥 Maksimal Libur per Hari</span>
                        <span class="value">2 orang</span>
                    </div>
                </div>

                <!-- ===== DAFTAR LIBUR ===== -->
                <div class="card">
                    <h3>📋 Daftar Jadwal Libur</h3>
                    <?php if (empty($liburByDate)): ?>
                        <div class="libur-empty">Belum ada jadwal libur</div>
                    <?php else: ?>
                        <?php foreach ($liburByDate as $tgl => $namaList): ?>
                            <div class="libur-item">
                                <div>
                                    <span class="tgl">📅 <?= date('d M Y', strtotime($tgl)) ?></span>
                                </div>
                                <div>
                                    <span class="nama"><?= implode(' & ', array_map('htmlspecialchars', $namaList)) ?></span>
                                    <span class="jumlah">(<?= count($namaList) ?> orang)</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- ===== RINGKASAN ===== -->
                <div class="card">
                    <h3>📊 Ringkasan Libur per Karyawan</h3>
                    <?php 
                    $liburCount = [];
                    foreach ($libur as $l) {
                        $nama = $l['nama_karyawan'];
                        if (!isset($liburCount[$nama])) {
                            $liburCount[$nama] = 0;
                        }
                        $liburCount[$nama]++;
                    }
                    ?>
                    <?php if (empty($liburCount)): ?>
                        <div class="libur-empty">Belum ada data libur</div>
                    <?php else: ?>
                        <?php foreach ($liburCount as $nama => $total): ?>
                            <div class="info-row">
                                <span class="label"><?= htmlspecialchars($nama) ?></span>
                                <span class="value"><?= $total ?> hari</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>