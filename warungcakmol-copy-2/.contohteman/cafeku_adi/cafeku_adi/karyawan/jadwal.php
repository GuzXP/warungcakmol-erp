<?php
// FILE: karyawan/jadwal.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: ../login.php');
    exit;
}

// Ambil semua shift karyawan
$shift = query("
    SELECT * FROM shift_karyawan 
    ORDER BY tanggal DESC, shift ASC
");

// ===== PERBAIKAN: Ambil dari tabel karyawan (bukan users) =====
$karyawan = query("
    SELECT nama, jabatan as role FROM karyawan 
    ORDER BY jabatan, nama
");

// Kelompokkan berdasarkan jabatan
$karyawanByRole = [];
foreach ($karyawan as $k) {
    $role = strtolower($k['role'] ?? 'lainnya');
    if (!isset($karyawanByRole[$role])) {
        $karyawanByRole[$role] = [];
    }
    $karyawanByRole[$role][] = $k['nama'];
}

// Ambil daftar libur untuk info
$libur = query("SELECT * FROM jadwal_libur ORDER BY tanggal DESC");
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
    <title>Jadwal Shift - Cafeku</title>
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
        .table-wrapper {
            overflow-x: auto;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #1f1f1f;
            color: #c08b5c;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #c08b5c;
            font-size: 13px;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #333;
            color: #ddd;
            font-size: 13px;
        }
        table tr:hover {
            background: #1f1f1f;
        }
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
            font-size: 13px;
            color: #ddd;
            border: 1px solid #333;
        }
        .karyawan-tag.kasir { border-color: #3498db; color: #3498db; }
        .karyawan-tag.kitchen { border-color: #e74c3c; color: #e74c3c; }
        .karyawan-tag.barista { border-color: #2ecc71; color: #2ecc71; }
        .karyawan-tag.manager { border-color: #9b59b6; color: #9b59b6; }
        .karyawan-tag.waiter { border-color: #f1c40f; color: #f1c40f; }
        .karyawan-tag.lainnya { border-color: #f39c12; color: #f39c12; }
        
        .badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-pagi { background: #f1c40f; color: #1a1a1a; }
        .badge-siang { background: #3498db; color: #fff; }
        .badge-libur { background: #e74c3c; color: #fff; }
        .badge-bertugas { background: #2ecc71; color: #1a1a1a; }
        
        .libur-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #1f1f1f;
            border-radius: 6px;
            border-left: 3px solid #e74c3c;
            margin-bottom: 6px;
        }
        .libur-item .tgl { color: #ddd; font-weight: 600; font-size: 13px; }
        .libur-item .nama { color: #e74c3c; font-weight: 600; font-size: 13px; }
        .libur-item .jumlah { color: #888; font-size: 12px; }
        .libur-empty { color: #888; font-style: italic; padding: 10px 0; text-align: center; }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .dashboard-grid { grid-template-columns: 1fr; }
            .page-header { flex-direction: column; text-align: center; }
            .libur-item { flex-direction: column; align-items: flex-start; gap: 3px; }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <div class="page-header">
            <div>
                <h1>📋 Jadwal Shift Karyawan</h1>
                <p class="page-note">Lihat jadwal shift dan daftar karyawan per role</p>
            </div>
            <div>
                <a href="../logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>

        <div class="admin-grid">
            <div class="navbar">
                <ul style="list-style:none; padding:0; margin:0;">
                    <li><a href="index.php" class="nav-link">📊 Dashboard</a></li>
                    <li><a href="jadwal.php" class="nav-link active">📋 Jadwal Shift</a></li>
                    <li><a href="libur.php" class="nav-link">📅 Jadwal Libur</a></li>
                </ul>
            </div>

            <div class="admin-main" style="flex:1;">

                <!-- ===== DAFTAR KARYAWAN PER ROLE (DARI TABEL KARYAWAN) ===== -->
                <div class="dashboard-grid">
                    <div class="card">
                        <h3>👨‍💼 Manager</h3>
                        <div class="karyawan-list">
                            <?php if (!empty($karyawanByRole['manager'])): ?>
                                <?php foreach ($karyawanByRole['manager'] as $nama): ?>
                                    <span class="karyawan-tag manager"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Belum ada manager</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>👨‍🍳 Kasir</h3>
                        <div class="karyawan-list">
                            <?php if (!empty($karyawanByRole['kasir'])): ?>
                                <?php foreach ($karyawanByRole['kasir'] as $nama): ?>
                                    <span class="karyawan-tag kasir"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Belum ada kasir</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>👨‍🍳 Kitchen</h3>
                        <div class="karyawan-list">
                            <?php if (!empty($karyawanByRole['kitchen'])): ?>
                                <?php foreach ($karyawanByRole['kitchen'] as $nama): ?>
                                    <span class="karyawan-tag kitchen"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Belum ada kitchen</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>☕ Barista</h3>
                        <div class="karyawan-list">
                            <?php 
                            $barista = [];
                            if (!empty($karyawanByRole['barista'])) {
                                foreach ($karyawanByRole['barista'] as $nama) {
                                    $barista[] = $nama;
                                }
                            }
                            ?>
                            <?php if (!empty($barista)): ?>
                                <?php foreach ($barista as $nama): ?>
                                    <span class="karyawan-tag barista"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Belum ada barista</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>🍽️ Waiter</h3>
                        <div class="karyawan-list">
                            <?php if (!empty($karyawanByRole['waiter'])): ?>
                                <?php foreach ($karyawanByRole['waiter'] as $nama): ?>
                                    <span class="karyawan-tag waiter"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Belum ada waiter</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <h3>📋 Lainnya</h3>
                        <div class="karyawan-list">
                            <?php 
                            $lainnya = [];
                            if (!empty($karyawanByRole['lainnya'])) {
                                foreach ($karyawanByRole['lainnya'] as $nama) {
                                    $lainnya[] = $nama;
                                }
                            }
                            ?>
                            <?php if (!empty($lainnya)): ?>
                                <?php foreach ($lainnya as $nama): ?>
                                    <span class="karyawan-tag lainnya"><?= htmlspecialchars($nama) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color:#888;">Tidak ada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ===== JADWAL LIBUR (Ringkasan) ===== -->
                <div class="card" style="margin-top:20px; border-color:#e74c3c;">
                    <h3>📅 Ringkasan Libur</h3>
                    <?php if (empty($liburByDate)): ?>
                        <div class="libur-empty">Belum ada jadwal libur</div>
                    <?php else: ?>
                        <?php 
                        $count = 0;
                        foreach ($liburByDate as $tgl => $namaList): 
                            if ($count >= 5) break;
                            $count++;
                        ?>
                            <div class="libur-item">
                                <span class="tgl">📅 <?= date('d M Y', strtotime($tgl)) ?></span>
                                <span class="nama"><?= implode(' & ', array_map('htmlspecialchars', $namaList)) ?></span>
                                <span class="jumlah">(<?= count($namaList) ?> orang)</span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (count($liburByDate) > 5): ?>
                            <div style="color:#888; font-size:12px; text-align:center; margin-top:5px;">
                                dan <?= count($liburByDate) - 5 ?> hari libur lainnya
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div style="margin-top:10px; text-align:right;">
                        <a href="libur.php" class="btn btn-primary btn-sm">Lihat Semua Libur →</a>
                    </div>
                </div>

                <!-- ===== TABEL SHIFT KERJA ===== -->
                <div class="card" style="margin-top:20px;">
                    <h3>🕐 Daftar Shift Kerja</h3>
                    <div class="table-wrapper">
                        <?php if (empty($shift)): ?>
                            <p style="color:#888; padding:10px 0;">Belum ada shift kerja tersimpan</p>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Karyawan</th>
                                        <th>Shift</th>
                                        <th>Jam Masuk</th>
                                        <th>Jam Pulang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shift as $s): ?>
                                        <tr>
                                            <td><?= date('d M Y', strtotime($s['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($s['nama_karyawan']) ?></td>
                                            <td>
                                                <span class="badge <?= $s['shift'] == 'Pagi' ? 'badge-pagi' : 'badge-siang' ?>">
                                                    <?= $s['shift'] ?>
                                                </span>
                                            </td>
                                            <td><?= date('H:i', strtotime($s['jam_masuk'])) ?></td>
                                            <td><?= date('H:i', strtotime($s['jam_pulang'])) ?></td>
                                            <td>
                                                <span class="badge <?= strtolower($s['status']) == 'libur' ? 'badge-libur' : 'badge-bertugas' ?>">
                                                    <?= $s['status'] ?? 'Bertugas' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>