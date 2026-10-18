<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'kasir') {
    header('Location: ../login.php');
    exit;
}

include '../fungsi.php';

// Tambah produk ke keranjang
if (isset($_GET['tambah'])) {
    $id_produk = intval($_GET['tambah']);
    $cek = mysqli_query($conn, "SELECT * FROM keranjang WHERE produk_id = $id_produk");

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE produk_id = $id_produk");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (produk_id, qty) VALUES ($id_produk, 1)");
    }

    header('Location: kasir.php');
    exit;
}

// Ubah qty item
if (isset($_GET['id'], $_GET['qty'])) {
    $id = intval($_GET['id']);
    $qty = $_GET['qty'];

    if ($qty === 'tambah') {
        mysqli_query($conn, "UPDATE keranjang SET qty = qty + 1 WHERE id = $id");
    } elseif ($qty === 'kurang') {
        $res = mysqli_query($conn, "SELECT qty FROM keranjang WHERE id = $id");
        $data = mysqli_fetch_assoc($res);

        if ($data && $data['qty'] > 1) {
            mysqli_query($conn, "UPDATE keranjang SET qty = qty - 1 WHERE id = $id");
        } else {
            mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id");
        }
    }

    header('Location: kasir.php');
    exit;
}

// Hapus item 
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM keranjang WHERE id = $id");
    header('Location: kasir.php');
    exit;
}

// Reset keranjang
if (isset($_GET['reset'])) {
    mysqli_query($conn, "DELETE FROM keranjang");
    header('Location: kasir.php');
    exit;
}

$notice = '';
$noticeClass = '';

if (isset($_POST['bayar'])) {
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $ppn = floatval($_POST['ppn'] ?? 0);
    $grand_total = floatval($_POST['grand_total'] ?? 0);
    $metode = $_POST['metode'] ?? '';
    $bayar_uang = floatval($_POST['bayar_uang'] ?? 0);

    if ($metode === '') {
        $notice = 'Pilih metode pembayaran terlebih dahulu.';
        $noticeClass = 'notice-error';
    } elseif ($metode === 'Cash' && $bayar_uang < $grand_total) {
        $notice = 'Uang bayar tidak cukup.';
        $noticeClass = 'notice-error';
    } else {
        if ($metode === 'Cash') {
            $kembali = $bayar_uang - $grand_total;
            $notice = 'Pembayaran berhasil. Kembalian: Rp ' . number_format($kembali, 0, ',', '.');
        } else {
            $notice = 'Pembayaran QRIS berhasil. Cetak struk setelah konfirmasi.';
        }
        $noticeClass = 'notice-success';
        mysqli_query($conn, "DELETE FROM keranjang");
        $_SESSION['flash_notice'] = $notice;
        $_SESSION['flash_class'] = $noticeClass;
        header('Location: kasir.php');
        exit;
    }
}

if (isset($_SESSION['flash_notice'])) {
    $notice = $_SESSION['flash_notice'];
    $noticeClass = $_SESSION['flash_class'] ?? 'notice-success';
    unset($_SESSION['flash_notice'], $_SESSION['flash_class']);
}

$produk = query('SELECT * FROM produk ORDER BY id DESC');
$cart = query('SELECT k.id, p.nama, p.harga, k.qty FROM keranjang k JOIN produk p ON k.produk_id = p.id');
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['harga'] * $item['qty'];
}
$ppn = round($subtotal * 0.10);
$grand_total = $subtotal + $ppn;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Cafe - Admin</title>
    <link rel="stylesheet" href="../css/style-admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="page-header page-header-split">
            <a href="index.php" class="btn">← Dashboard</a>
            <h1>Kasir Cafe</h1>
            <a href="../logout.php" class="btn btn-danger">Logout</a>
        </div>

        <?php if ($notice) : ?>
            <div class="notice <?= htmlspecialchars($noticeClass) ?>">
                <?= htmlspecialchars($notice) ?>
            </div>
        <?php endif; ?>

        <div class="kasir-wrapper">
            <div class="menu-panel">
                <h2>Menu</h2>
                <div class="menu-list">
                    <?php foreach ($produk as $p) : ?>
                        <div class="menu-item" onclick="window.location.href='?tambah=<?= $p['id'] ?>'">
                            <img src="../<?= htmlspecialchars($p['gambar'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($p['nama'], ENT_QUOTES) ?>">
                            <div class="menu-item-info">
                                <div class="nama"><?= htmlspecialchars($p['nama'], ENT_QUOTES) ?></div>
                                <div class="harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                                <div class="deskripsi"><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></div>
                                <a href="?tambah=<?= $p['id'] ?>" class="btn btn-small">Tambah</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="struk-panel">
                <h2>🧾 Pesanan</h2>

                <?php if (empty($cart)) : ?>
                    <p class="empty-cart">Belum ada item di keranjang.</p>
                <?php else : ?>
                    <table>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Hapus</th>
                        </tr>
                        <?php foreach ($cart as $c) : ?>
                            <tr>
                                <td><?= htmlspecialchars($c['nama'], ENT_QUOTES) ?></td>
                                <td>
                                    <div class="qty-box">
                                        <a href="?id=<?= $c['id'] ?>&qty=kurang" class="qty-btn">-</a>
                                        <?= $c['qty'] ?>
                                        <a href="?id=<?= $c['id'] ?>&qty=tambah" class="qty-btn">+</a>
                                    </div>
                                </td>
                                <td class="order-price">Rp <?= number_format($c['harga'] * $c['qty'], 0, ',', '.') ?></td>
                                <td><a href="?hapus=<?= $c['id'] ?>" class="link-danger">X</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div class="total-box">
                        <div class="total-row"><span>Subtotal</span><span>Rp <?= number_format($subtotal, 0, ',', '.') ?></span></div>
                        <div class="total-row"><span>PPN 10%</span><span>Rp <?= number_format($ppn, 0, ',', '.') ?></span></div>
                        <div class="total-row grand"><span>Total</span><span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span></div>
                    </div>

                    <form method="POST" class="payment-form">
                        <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
                        <input type="hidden" name="ppn" value="<?= $ppn ?>">
                        <input type="hidden" name="grand_total" value="<?= $grand_total ?>">
                        <input type="hidden" name="metode" id="metode" value="">

                        <div class="metode-bayar">
                            <button type="button" class="metode-btn cash" onclick="pilihMetode('Cash')">💵 Cash</button>
                            <button type="button" class="metode-btn qris" onclick="pilihMetode('QRIS')">📱 QRIS</button>
                        </div>

                        <div id="cash-area" class="payment-area" style="display: none;">
                            <input type="number" name="bayar_uang" id="bayar" placeholder="Masukkan uang pembayaran" oninput="hitungKembalian()">
                            <div class="kembalian-box" id="kembalian-box" style="display:none;">Kembalian : <span id="kembalian-text">Rp 0</span></div>
                        </div>

                        <div class="qris-box" id="qris-box" style="display: none;">
                            <img src="../poto/qris.jpeg" alt="QRIS">
                            <p>Scan QRIS untuk pembayaran</p>
                        </div>

                        <button type="submit" name="bayar" class="btn btn-success btn-struk">🖨️ Bayar & Cetak</button>
                    </form>
                <?php endif; ?>

                <a href="?reset=1" class="btn btn-danger btn-struk">Reset Pesanan</a>
            </div>
        </div>
    </div>

    <script>
        let metode = '';

        function pilihMetode(m) {
            metode = m;
            document.getElementById('metode').value = m;

            const cashArea = document.getElementById('cash-area');
            const qrisBox = document.getElementById('qris-box');

            if (m === 'Cash') {
                cashArea.style.display = 'block';
                qrisBox.style.display = 'none';
            } else {
                cashArea.style.display = 'none';
                qrisBox.style.display = 'block';
            }

            hitungKembalian();
        }

        function hitungKembalian() {
            const total = <?= $grand_total ?>;
            const bayarValue = parseInt(document.getElementById('bayar').value || 0, 10);
            const kembali = bayarValue - total;
            const box = document.getElementById('kembalian-box');

            if (metode !== 'Cash' || bayarValue <= 0) {
                box.style.display = 'none';
                document.getElementById('kembalian-text').innerText = 'Rp 0';
                return;
            }

            box.style.display = 'block';
            if (kembali >= 0) {
                document.getElementById('kembalian-text').innerText = 'Rp ' + kembali.toLocaleString('id-ID');
            } else {
                document.getElementById('kembalian-text').innerText = 'Kurang Rp ' + Math.abs(kembali).toLocaleString('id-ID');
            }
        }
    </script>
</body>

</html>


<div class="metode">

    <button
        type="button"
        class="cash"
        onclick="pilihMetode('Cash')">
        💵 Cash
    </button>

    <button
        type="button"
        class="qris"
        onclick="pilihMetode('QRIS')">
        📱 QRIS
    </button>

</div>

<div id="cash-area">

    <input
        type="number"
        name="bayar_uang"
        id="bayar"
        placeholder="Masukkan uang pembayaran"
        oninput="hitungKembalian()">

    <div class="kembalian" id="kembalian-box">
        Kembalian :
        <span id="kembalian-text">
            Rp 0
        </span>
    </div>

</div>

<div class="qris-box" id="qris-box">

    <img src="../poto/qris.jpeg">

    <p style="margin-top:10px;">
        Scan QRIS untuk pembayaran
    </p>

</div>

<button
    type="submit"
    name="bayar"
    class="btn"
    style="width:100%; margin-top:15px;">
    🖨️ Bayar & Cetak
</button>

</form>

<a
    href="?reset=1"
    class="btn reset">
    Reset Pesanan
</a>

</div>

</div>

<script>
    let metode = '';

    function pilihMetode(m) {

        metode = m;

        document.getElementById('metode').value = m;

        if (m == 'Cash') {

            document.getElementById('cash-area').style.display = 'block';

            document.getElementById('qris-box').style.display = 'none';

        } else {

            document.getElementById('cash-area').style.display = 'none';

            document.getElementById('qris-box').style.display = 'block';
        }
    }

    function hitungKembalian() {

        let total =
            <?= $grand_total ?>;

        let bayar =
            document.getElementById('bayar').value;

        let kembali = bayar - total;

        let box =
            document.getElementById('kembalian-box');

        if (bayar != '') {

            box.style.display = 'block';

            if (kembali >= 0) {

                document.getElementById('kembalian-text')
                    .innerHTML =
                    'Rp ' + kembali.toLocaleString('id-ID');

            } else {

                document.getElementById('kembalian-text')
                    .innerHTML =
                    'Kurang Rp ' +
                    Math.abs(kembali).toLocaleString('id-ID');
            }
        }
    }
</script>

</body>

</html>