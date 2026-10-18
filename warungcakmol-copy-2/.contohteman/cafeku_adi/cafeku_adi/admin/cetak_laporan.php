<?php
include '../fungsi.php';

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

<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan</title>

    <style>

        body{
            font-family:Arial;
            padding:20px;
        }

        h1{
            text-align:center;
        }

        .tanggal{
            text-align:center;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        table th,
        table td{
            border:1px solid black;
            padding:10px;
            text-align:center;
        }

        table th{
            background:#ddd;
        }

        .total{
            margin-top:20px;
            font-size:22px;
            font-weight:bold;
            text-align:right;
        }

    </style>

</head>
<body onload="window.print()">

    <h1>LAPORAN PENJUALAN CAFE</h1>

    <div class="tanggal">

        <?php if($dari != '' && $sampai != ''){ ?>

            Periode :
            <?= date('d-m-Y', strtotime($dari)) ?>
            s/d
            <?= date('d-m-Y', strtotime($sampai)) ?>

        <?php } else { ?>

            Semua Data Transaksi

        <?php } ?>

    </div>

    <table>

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
                Rp <?= number_format($d['total']) ?>
            </td>

            <td>
                Rp <?= number_format($d['bayar']) ?>
            </td>

            <td>
                Rp <?= number_format($d['kembalian']) ?>
            </td>

            <td><?= $d['metode_pembayaran'] ?></td>

            <td><?= $d['kasir'] ?></td>

        </tr>

        <?php } ?>

    </table>

    <div class="total">
        Total Penjualan :
        Rp <?= number_format($total['grand_total'] ?? 0) ?>
    </div>

</body>
</html>