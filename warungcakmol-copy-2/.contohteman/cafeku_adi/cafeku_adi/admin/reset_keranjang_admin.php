<?php
require '../fungsi.php';
mysqli_query($conn, "DELETE FROM keranjang");
header("Location: pembayaran.php");