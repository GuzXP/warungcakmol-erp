<?php
include '../fungsi.php';

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Penjualan.xls");

$dari = $_GET['dari'] ?? '';
$sampai = $_GET['sampai'] ?? '';

$where = "";

if($dari != '' && $sampai != ''){

    $where = "
        WHERE DATE(tanggal)
        BETWEEN '$dari'
        AND '$sampai'
    ";
}

$data = mysqli_query($conn, "
    SELECT * FROM transaksi
    $where
    ORDER BY id DESC
");

$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) as grand_total
    FROM transaksi
    $where
"));
?>

<table border="1">

    <tr>
        <th colspan="8">
            LAPORAN PENJUALAN CAFE
        </th>
    </tr>

    <tr>
        <th>No</th>
        <th>Kode</th>
        <th>Tanggal</th>
        <th>Total</th>
        <th>Bayar</th>
        <th>Kembalian</th>
        <th>Pembayaran</th>
        <th>Kasir</th>
    </tr>

    <?php

    $no = 1;

    while($d = mysqli_fetch_assoc($data)){
    ?>

    <tr>

        <td><?= $no++ ?></td>

        <td><?= $d['kode_transaksi'] ?></td>

        <td>
            <?= date('d-m-Y H:i', strtotime($d['tanggal'])) ?>
        </td>

        <td>
            <?= $d['total'] ?>
        </td>

        <td>
            <?= $d['bayar'] ?>
        </td>

        <td>
            <?= $d['kembalian'] ?>
        </td>

        <td><?= $d['metode_pembayaran'] ?></td>

        <td><?= $d['kasir'] ?></td>

    </tr>

    <?php } ?>

    <tr>

        <th colspan="3">
            TOTAL PENJUALAN
        </th>

        <th colspan="5">
            Rp <?= number_format($total['grand_total'] ?? 0) ?>
        </th>

    </tr>

</table>