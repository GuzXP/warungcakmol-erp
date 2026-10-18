<?php
// FILE: admin/produk.php
session_start();
require '../fungsi.php';

if (!isset($_SESSION['login']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    header('Location: ../login.php');
    exit;
}

// ===== PROSES TAMBAH PRODUK =====
if (isset($_POST['tambah_produk'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = intval($_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = intval($_POST['stok'] ?? 0);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $target_dir = "../poto/";
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        
        $check = getimagesize($_FILES['gambar']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = 'poto/' . $file_name;
            }
        }
    }
    
    $stmt = mysqli_prepare($conn, "INSERT INTO produk (nama, harga, kategori, stok, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sissis", $nama, $harga, $kategori, $stok, $deskripsi, $gambar);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('✅ Produk berhasil ditambahkan!'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal menambahkan produk!'); window.location='produk.php';</script>";
    }
    exit;
}

// ===== PROSES EDIT PRODUK =====
if (isset($_POST['edit_produk'])) {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = intval($_POST['harga']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = intval($_POST['stok'] ?? 0);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    
    $gambar = $_POST['gambar_lama'] ?? '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $target_dir = "../poto/";
        $file_name = time() . '_' . basename($_FILES['gambar']['name']);
        $target_file = $target_dir . $file_name;
        
        $check = getimagesize($_FILES['gambar']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                $gambar = 'poto/' . $file_name;
            }
        }
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE produk SET nama = ?, harga = ?, kategori = ?, stok = ?, deskripsi = ?, gambar = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sissisi", $nama, $harga, $kategori, $stok, $deskripsi, $gambar, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('✅ Produk berhasil diupdate!'); window.location='produk.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal mengupdate produk!'); window.location='produk.php';</script>";
    }
    exit;
}

// ===== PROSES HAPUS PRODUK =====
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    
    $produk = query("SELECT gambar FROM produk WHERE id = $id");
    if (!empty($produk) && !empty($produk[0]['gambar'])) {
        $gambar_file = "../" . $produk[0]['gambar'];
        if (file_exists($gambar_file)) {
            unlink($gambar_file);
        }
    }
    
    mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
    echo "<script>alert('🗑️ Produk berhasil dihapus!'); window.location='produk.php';</script>";
    exit;
}

// ===== AMBIL DATA PRODUK =====
$produk = query("SELECT * FROM produk ORDER BY kategori ASC, nama ASC");
$totalProduk = count($produk);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Cafeku</title>
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

        .page-header .badge-count {
            background: #2a2a2a;
            color: #c08b5c;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 13px;
            border: 1px solid #c08b5c;
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
            gap: 20px;
        }

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

        .form-group textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        .form-group .file-input {
            padding: 8px;
            background: #2a2a2a;
            border: 1px solid #333;
            border-radius: 8px;
            color: #ddd;
            width: 100%;
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

        .table-wrapper table tbody td .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #c08b5c;
        }

        .table-wrapper table tbody td .no-image {
            color: #666;
            font-size: 12px;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px 0;
            font-style: italic;
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
            background: rgba(0, 0, 0, 0.7);
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
            max-width: 550px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .modal-box h3 {
            color: #c08b5c;
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }

        .modal-box .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-box .modal-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
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

        .modal-box .current-image {
            color: #888;
            font-size: 13px;
            margin-top: 10px;
            padding: 8px 12px;
            background: #2a2a2a;
            border-radius: 6px;
            border: 1px solid #333;
        }

        .modal-box .current-image span {
            color: #c08b5c;
            font-weight: 600;
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

            .modal-box {
                padding: 20px;
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

            .modal-box {
                padding: 15px;
            }

            .modal-box .modal-actions {
                flex-direction: column;
            }

            .table-wrapper table thead th,
            .table-wrapper table tbody td {
                padding: 6px 8px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>

<div class="admin-container">
    <!-- ===== HEADER ===== -->
    <div class="page-header">
        <div>
            <h1>📦 Kelola Produk</h1>
            <div class="subtitle">Tambah, edit, dan hapus menu produk</div>
        </div>
        <div>
            <span class="badge-count">📋 Total: <?= $totalProduk ?> produk</span>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-primary btn-sm">← Dashboard</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">🚪 Logout</a>
        </div>
    </div>

    <!-- ===== FORM TAMBAH PRODUK ===== -->
    <div class="card">
        <div class="card-header">
            <h3>➕ Tambah Produk Baru</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div>
                    <div class="form-group">
                        <label for="nama">Nama Produk</label>
                        <input type="text" id="nama" name="nama" class="form-control" placeholder="Nama produk..." required>
                    </div>

                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" id="harga" name="harga" class="form-control" placeholder="Harga produk..." required>
                    </div>

                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control" required>
                            <option value="Makanan">🍔 Makanan</option>
                            <option value="Minuman">🥤 Minuman</option>
                            <option value="Snack">🍿 Snack</option>
                            <option value="Pastry">🥐 Pastry</option>
                            <option value="Dessert">🍰 Dessert</option>
                            <option value="Coffee">☕ Coffee</option>
                            <option value="NonCoffee">🧋 Non Coffee</option>
                            <option value="Nonalcohol">🍹 Mocktail</option>
                            <option value="Alcohol">🍸 Alcohol</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="stok">Stok</label>
                        <input type="number" id="stok" name="stok" class="form-control" placeholder="Jumlah stok..." value="0">
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Deskripsi produk..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar</label>
                        <input type="file" id="gambar" name="gambar" class="file-input" accept="image/*">
                    </div>
                </div>
            </div>

            <button type="submit" name="tambah_produk" class="btn btn-success btn-block">
                💾 Tambah Produk
            </button>
        </form>
    </div>

    <!-- ===== TABEL PRODUK ===== -->
    <div class="card">
        <div class="card-header">
            <h3>📋 Daftar Produk</h3>
        </div>

        <div class="table-wrapper">
            <?php if (empty($produk)): ?>
                <div class="empty-state">📭 Belum ada produk. Silakan tambahkan produk baru.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:80px;">Gambar</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th style="text-align:right;">Harga</th>
                            <th style="text-align:center;">Stok</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['gambar'])): ?>
                                        <img src="../<?= $p['gambar'] ?>" alt="<?= htmlspecialchars($p['nama']) ?>" class="product-img">
                                    <?php else: ?>
                                        <span class="no-image">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                                <td><span style="color:#c08b5c;"><?= htmlspecialchars($p['kategori']) ?></span></td>
                                <td style="text-align:right; font-weight:600; color:#fff;">
                                    Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if (($p['stok'] ?? 0) > 0): ?>
                                        <span style="color:#2ecc71;"><?= $p['stok'] ?></span>
                                    <?php else: ?>
                                        <span style="color:#e74c3c;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <button class="btn btn-warning btn-sm" onclick="openEditModal(
                                        <?= $p['id'] ?>,
                                        '<?= addslashes($p['nama']) ?>',
                                        <?= $p['harga'] ?>,
                                        '<?= $p['kategori'] ?>',
                                        <?= $p['stok'] ?? 0 ?>,
                                        '<?= addslashes($p['deskripsi'] ?? '') ?>',
                                        '<?= $p['gambar'] ?>'
                                    )">
                                        ✏️ Edit
                                    </button>
                                    <a href="?hapus=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars($p['nama']) ?>?')">
                                        🗑️ Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== MODAL EDIT PRODUK ===== -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <h3>✏️ Edit Produk</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="gambar_lama" id="edit_gambar_lama">

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="harga" id="edit_harga" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" id="edit_kategori" class="form-control" required>
                    <option value="Makanan">🍔 Makanan</option>
                    <option value="Minuman">🥤 Minuman</option>
                    <option value="Snack">🍿 Snack</option>
                    <option value="Pastry">🥐 Pastry</option>
                    <option value="Dessert">🍰 Dessert</option>
                    <option value="Coffee">☕ Coffee</option>
                    <option value="NonCoffee">🧋 Non Coffee</option>
                    <option value="Nonalcohol">🍹 Mocktail</option>
                    <option value="Alcohol">🍸 Alcohol</option>
                </select>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok" id="edit_stok" class="form-control">
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label>Gambar Baru (kosongkan jika tidak ingin mengganti)</label>
                <input type="file" name="gambar" class="file-input" accept="image/*">
            </div>

            <div class="current-image">
                📷 Gambar saat ini: <span id="edit_gambar_text">Tidak ada gambar</span>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-batal" onclick="closeEditModal()">Batal</button>
                <button type="submit" name="edit_produk" class="btn-simpan">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, nama, harga, kategori, stok, deskripsi, gambar) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_harga').value = harga;
        document.getElementById('edit_kategori').value = kategori;
        document.getElementById('edit_stok').value = stok;
        document.getElementById('edit_deskripsi').value = deskripsi;
        document.getElementById('edit_gambar_lama').value = gambar;
        document.getElementById('edit_gambar_text').textContent = gambar || 'Tidak ada gambar';
        document.getElementById('modalEdit').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('modalEdit').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Klik di luar modal untuk menutup
    document.getElementById('modalEdit').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
        }
    });

    // Tutup dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
        }
    });
</script>

</body>
</html>