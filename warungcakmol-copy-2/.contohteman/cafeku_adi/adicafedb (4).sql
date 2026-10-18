-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Jun 2026 pada 11.02
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adicafedb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_belanja`
--

CREATE TABLE `detail_belanja` (
  `id` int(11) NOT NULL,
  `tugas_id` int(11) DEFAULT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_belanja`
--

INSERT INTO `detail_belanja` (`id`, `tugas_id`, `nama_barang`, `qty`, `harga`, `subtotal`) VALUES
(1, 2, 'soklin', 1, 5000, 5000),
(2, 3, 'matcha', 2, 50000, 100000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `produk_id`, `qty`, `harga`, `subtotal`) VALUES
(1, 1, 19, 1, 200000, 200000),
(2, 1, 5, 1, 28000, 28000),
(3, 2, 5, 1, 28000, 28000),
(4, 2, 16, 1, 955000, 955000),
(5, 5, 17, 1, 90000, 90000),
(6, 5, 12, 1, 24000, 24000),
(7, 5, 15, 1, 21000, 21000),
(8, 6, 16, 1, 955000, 955000),
(9, 6, 3, 3, 28000, 84000),
(10, 6, 12, 1, 24000, 24000),
(11, 6, 6, 1, 30000, 30000),
(12, 7, 18, 1, 1750000, 1750000),
(13, 7, 4, 1, 33000, 33000),
(14, 7, 19, 1, 200000, 200000),
(15, 8, 13, 1, 30000, 30000),
(16, 8, 12, 1, 24000, 24000),
(17, 8, 4, 1, 33000, 33000),
(18, 8, 5, 1, 28000, 28000),
(19, 8, 1, 1, 32000, 32000),
(20, 8, 19, 1, 200000, 200000),
(21, 8, 8, 1, 26000, 26000),
(22, 9, 18, 1, 1750000, 1750000),
(23, 9, 17, 1, 90000, 90000),
(24, 9, 12, 1, 24000, 24000),
(25, 9, 11, 1, 28000, 28000),
(26, 10, 5, 1, 28000, 28000),
(27, 10, 16, 1, 955000, 955000),
(28, 10, 15, 1, 21000, 21000),
(29, 11, 33, 2, 25000, 50000),
(30, 11, 18, 1, 3750000, 3750000),
(31, 12, 30, 1, 45000, 45000),
(32, 12, 20, 1, 10000, 10000),
(33, 13, 16, 1, 955000, 955000),
(34, 14, 19, 1, 200000, 200000),
(35, 14, 34, 1, 28000, 28000),
(36, 14, 30, 1, 45000, 45000),
(37, 14, 9, 1, 22000, 22000),
(38, 15, 30, 1, 45000, 45000),
(39, 15, 31, 1, 50000, 50000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `gaji_karyawan`
--

CREATE TABLE `gaji_karyawan` (
  `id` int(11) NOT NULL,
  `karyawan_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `gaji_karyawan`
--

INSERT INTO `gaji_karyawan` (`id`, `karyawan_id`, `tanggal`, `jumlah`, `status`) VALUES
(3, 14, '2026-05-31', 1500000, 'Lunas'),
(4, 9, '2026-05-31', 2500000, 'Lunas'),
(5, 12, '2026-05-31', 2500000, 'Lunas'),
(6, 13, '2026-05-31', 1500000, 'Lunas'),
(7, 11, '2026-05-31', 5000000, 'Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `gaji` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `karyawan`
--

INSERT INTO `karyawan` (`id`, `nama`, `jabatan`, `no_hp`, `alamat`, `gaji`) VALUES
(9, 'bagus', 'Kitchen', '083322221233', 'jalan pulomas', 2500000),
(11, 'widya', 'Manager', '0895806341711', 'jalan sukapura\r\n', 5000000),
(12, 'galang', 'Barista', '45679987545', 'pulogadung', 2500000),
(13, 'mpit', 'Waiter', '223444433', 'sukapura', 1500000),
(14, 'ade', 'Kasir', '12345667', 'duren sawit\r\n', 1500000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembelian`
--

CREATE TABLE `pembelian` (
  `id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `nama_barang` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `karyawan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembelian`
--

INSERT INTO `pembelian` (`id`, `tanggal`, `nama_barang`, `qty`, `harga`, `total`, `supplier`, `karyawan`) VALUES
(1, '2026-06-02 09:19:43', 'bubuk ', 2, 500000, 1000000, 'indomaret', 'ade');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `nama`, `harga`, `kategori`, `deskripsi`, `gambar`) VALUES
(1, 'Caramel Latte', 32000, 'coffee', 'Latte creamy caramel premium.', 'poto/download (26).jpeg'),
(2, 'Americano', 20000, 'coffee', 'Kopi hitam ringan tanpa gula.', 'poto/download (25).jpeg'),
(3, 'Cappuccino', 28000, 'coffee', 'Espresso dengan foam creamy.', 'poto/download (24).jpeg'),
(4, 'Hazelnut Latte', 33000, 'coffee', 'Latte dengan rasa hazelnut manis.', 'poto/download (23).jpeg'),
(5, 'Iced Chocolate', 28000, 'noncoffee', 'Minuman cokelat dingin yang kaya rasa.', 'poto/download (22).jpeg'),
(6, 'Matcha Latte', 30000, 'noncoffee', 'Minuman matcha Jepang + susu.', 'poto/download (21).jpeg'),
(7, 'Strawberry Milk', 25000, 'noncoffee', 'Susu stroberi manis-asam.', 'poto/download (20).jpeg'),
(8, 'Taro Latte', 26000, 'noncoffee', 'Rasa talas yang unik dan creamy.', 'poto/download (19).jpeg'),
(9, 'Croissant Butter', 22000, 'pastry', 'Croissant mentega klasik renyah.', 'poto/6a1e1cfb1670c.jpg'),
(10, 'Choco Danish', 25000, 'pastry', 'Danish isi cokelat lumer.', 'poto/6a1e1d26ae7cf.jpg'),
(11, 'Almond Croissant', 28000, 'pastry', 'Croissant dengan topping almond.', 'poto/6a14733b26fca.jpg'),
(12, 'Cinnamon Roll', 24000, 'pastry', 'Roti gulung kayu manis lembut.', 'poto/download (9).jpeg'),
(13, 'Virgin Mojito', 30000, 'nonalcohol', 'Segar nipis dan mint tanpa alkohol.', 'poto/download (8).jpeg'),
(15, 'Orange Juice', 21000, 'nonalcohol', 'Jus jeruk murni.', 'poto/download (6).jpeg'),
(16, 'Red Wine', 955000, 'alcohol', 'Red wine dari fermentasi anggur merah.', 'poto/download (1).jpeg'),
(17, 'Beer Gold', 90000, 'alcohol', 'Bir kadar alkohol sedang.', 'poto/images.jpeg'),
(18, 'Whiskey Premium', 3750000, 'alcohol', 'Wiski premium rasa kompleks.', 'poto/download (14).jpeg'),
(19, 'Vodka Crystal', 200000, 'alcohol', 'Vodka crystal berkualitas.', 'poto/download (15).jpeg'),
(20, 'Dancow', 10000, 'noncoffee', 'Susu Dancow terkenal dengan rasanya yang krim, gurih, dan manis pas. Merek dari Nestlé ini menawarkan berbagai pilihan varian rasa yang lezat dan disukai anak-anak hingga dewasa', 'poto/6a1e1c48925d3.jpg'),
(21, 'Danish Pastry', 20000, 'pastry', ' adalah roti berlapis (pastry) asal Denmark yang memiliki tekstur renyah di luar namun sangat lembut di dalam, dengan cita rasa manis, gurih, dan buttery (kaya mentega). Adonannya menggunakan ragi dan telur, memberikan kelembutan yang khas', 'poto/6a1e1de254443.jpg'),
(22, 'Blue Hawaii', 25000, 'nonalcohol', 'Minuman ini punya ciri khas yang sangat kelihatan\r\n Disajikan dingin dengan es batu yang melimpah.', 'poto/6a1e1dc8cc63c.jpg'),
(23, 'Ice Lemon Tea', 6000, 'noncoffee', 'Minuman penyelamat di kala gerah ini harganya merakyat banget, tapi bisa bervariasi tergantung tempat nongkrongmu:', 'poto/6a1e1e82ce6e5.jpeg'),
(24, 'Es Kopi Susu Gula Aren', 15000, 'coffee', 'Layering tiga warna yang jelas sebelum diaduk (gula aren cair di dasar, susu segar di tengah, dan espresso shot di bagian atas).', 'poto/6a1e1ef101040.jpg'),
(25, 'Jus Alpukat', 18000, 'noncoffee', 'Teksturnya kental (creamy), berwarna hijau muda, disajikan dingin dengan es batu, dan biasanya ditambahkan kental manis (cokelat/putih) di pinggiran gelasnya untuk penambah rasa.', 'poto/6a1e1f47a013a.jpg'),
(26, 'Phyllo Wrapped Cheesecake', 55000, 'pastry', 'Perpaduan tekstur lembut dari adonan cheesecake di bagian dalam, dibungkus dengan kulit phyllo pastry yang tipis dan super renyah di bagian luar, serta diberi siraman madu/sirup dan taburan kacang walnut hancur di atasnya.', 'poto/6a1e1f8bef7cc.jpg'),
(27, 'Savory Cheese Tart.', 45000, 'pastry', 'Berbeda dengan tart manis, ini adalah savory pastry (pastry gurih). Menggunakan adonan kulit tart yang renyah (shortcrust pastry), diisi dengan karamelisasi bawang bombay merah (caramelized red onion), lalu diberi potongan besar keju kambing (goat cheese) di atasnya dan dipanggang sampai kecokelatan.', 'poto/6a1e20206b22f.jpg'),
(28, 'Chocolate Frappe', 40000, 'noncoffee', 'Minuman es cokelat kental dengan hiasan saus cokelat di dinding gelas, lalu diberi topping whipped cream gunung dan taburan bubuk cokelat/serutan cokelat di atasnya.', 'poto/6a1e21504393d.jpeg'),
(29, 'Tropical Red Sangria', 40000, 'nonalcohol', 'Minuman segar berbasis buah berwarna merah cerah, diisi dengan potongan jeruk, stroberi, belimbing, dan buah-buahan tropis lainnya di dalam stoples kaca (jar).', 'poto/6a1e2160cc08a.jpg'),
(30, 'Choco Lava Donut', 45000, 'pastry', ': Donat dengan glaze cokelat tebal di luar, dan ketika dipotong mengeluarkan isian krim cokelat lumer (melted) yang melimpah', 'poto/6a1e217e087be.jpeg'),
(31, 'Croissant with Speculoos Spread', 50000, 'pastry', 'Roti croissant renyah klasik yang dibelah dan diolesi dengan krim olesan tebal (seperti rasa kue speculoos/mentega kacang). Disajikan bersama stoples selai dan secangkir kopi hangat.', 'poto/6a1e21a8acbd7.jpeg'),
(32, 'ced Black Coffee', 35000, 'coffee', 'Ekstraksi espresso murni yang dilarutkan dengan air dingin dan es batu tanpa tambahan gula atau susu. Disajikan estetik di atas nampan kayu kecil bersama wadah sirup terpisah.', 'poto/6a1e22cadf3d4.jpeg'),
(33, 'Ice Chocolatte', 25000, 'noncoffee', 'Minuman es cokelat pekat di bagian bawah yang dipadukan dengan layer susu cair/krim gurih di atasnya, disajikan dingin dengan es batu', 'poto/6a1e2302413db.jpg'),
(34, 'Iced Lychee Tea', 28000, 'noncoffee', 'Minuman teh manis segar beraroma leci yang disajikan dingin, lengkap dengan topping beberapa buah leci utuh di dalam gelasnya.', 'poto/6a1e23675f7f8.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total` int(11) NOT NULL,
  `bayar` int(11) NOT NULL,
  `kembalian` int(11) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `kasir` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `tanggal`, `total`, `bayar`, `kembalian`, `metode_pembayaran`, `kasir`) VALUES
(1, 'TRX1779210637', '2026-05-20 00:10:37', 250800, 350000, 99200, 'Cash', 'Admin'),
(2, 'TRX1779721641', '2026-05-25 22:07:21', 1081300, 1081300, 0, 'Cash', 'Admin'),
(3, 'TRX1779721645', '2026-05-25 22:07:25', 0, 0, 0, '', 'Admin'),
(4, 'TRX1779721652', '2026-05-25 22:07:32', 0, 0, 0, 'QRIS', 'Admin'),
(5, 'TRX1779758389', '2026-05-26 08:19:49', 148500, 148500, 0, '', 'Admin'),
(6, 'TRX1779760117', '2026-05-26 08:48:37', 1202300, 1202300, 0, '', 'Admin'),
(7, 'TRX1780018476', '2026-05-29 08:34:36', 2181300, 2181300, 0, 'QRIS', 'Admin'),
(8, 'TRX1780225383', '2026-05-31 18:03:03', 410300, 410300, 0, '', 'Admin'),
(9, 'TRX1780225713', '2026-05-31 18:08:33', 2081200, 2081200, 0, 'QRIS', 'Admin'),
(10, 'TRX1780286136', '2026-06-01 10:55:36', 1104400, 1104400, 0, 'Cash', 'Admin'),
(11, 'TRX1780365364', '2026-06-02 08:56:04', 4180000, 4180000, 0, 'Cash', 'Admin'),
(12, 'TRX1780365990', '2026-06-02 09:06:30', 60500, 60500, 0, '', 'Admin'),
(13, 'TRX1780560011', '2026-06-04 15:00:11', 1050500, 1050500, 0, 'QRIS', 'Admin'),
(14, 'TRX1780900851', '2026-06-08 13:40:51', 324500, 324500, 0, 'QRIS', 'Admin'),
(15, 'TRX1781100718', '2026-06-10 21:11:58', 104500, 104500, 0, '', 'Admin');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas_belanja`
--

CREATE TABLE `tugas_belanja` (
  `id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `nama_karyawan` varchar(100) DEFAULT NULL,
  `uang_diberikan` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas_belanja`
--

INSERT INTO `tugas_belanja` (`id`, `tanggal`, `nama_karyawan`, `uang_diberikan`, `status`) VALUES
(2, '2026-05-29 08:22:38', 'widya', 10000, 'Selesai'),
(3, '2026-06-01 10:56:43', 'bagus', 100000, 'Selesai'),
(4, '2026-06-08 13:42:34', 'ade', 250000, 'Belum Selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `uang_keluar`
--

CREATE TABLE `uang_keluar` (
  `id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `keperluan` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uang_keluar`
--

INSERT INTO `uang_keluar` (`id`, `tanggal`, `keperluan`, `jumlah`, `keterangan`) VALUES
(6, '2026-05-31 18:05:23', 'Gaji Karyawan', 1500000, 'Pembayaran gaji karyawan'),
(7, '2026-05-31 18:06:09', 'Gaji Karyawan', 2500000, 'Pembayaran gaji karyawan'),
(8, '2026-05-31 18:06:22', 'Gaji Karyawan', 2500000, 'Pembayaran gaji karyawan'),
(9, '2026-05-31 18:06:31', 'Gaji Karyawan', 1500000, 'Pembayaran gaji karyawan'),
(10, '2026-05-31 18:06:43', 'Gaji Karyawan', 5000000, 'Pembayaran gaji karyawan'),
(11, '2026-06-01 10:56:44', 'Uang Belanja Karyawan', 100000, 'Uang belanja untuk bagus'),
(12, '2026-06-02 09:17:57', 'Gaji Karyawan', 1600000, 'Pembayaran gaji karyawan'),
(13, '2026-06-02 09:19:43', 'Pembelian Barang', 1000000, 'bubuk '),
(15, '2026-06-08 13:42:04', 'Gaji Karyawan', 1500000, 'Pembayaran gaji karyawan'),
(16, '2026-06-08 13:42:34', 'Uang Belanja Karyawan', 250000, 'Uang belanja untuk ade');

-- --------------------------------------------------------

--
-- Struktur dari tabel `uang_masuk`
--

CREATE TABLE `uang_masuk` (
  `id` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `sumber` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uang_masuk`
--

INSERT INTO `uang_masuk` (`id`, `tanggal`, `sumber`, `jumlah`, `keterangan`) VALUES
(5, '2026-05-26 08:19:49', 'Penjualan Cafe', 148500, 'Transaksi TRX1779758389'),
(6, '2026-05-26 08:48:37', 'Penjualan Cafe', 1202300, 'Transaksi TRX1779760117'),
(7, '2026-05-29 08:34:36', 'Penjualan Cafe', 2181300, 'Transaksi TRX1780018476'),
(8, '2026-05-31 18:03:03', 'Penjualan Cafe', 410300, 'Transaksi TRX1780225383'),
(9, '2026-05-31 18:08:33', 'Penjualan Cafe', 2081200, 'Transaksi TRX1780225713'),
(10, '2026-06-01 10:55:37', 'Penjualan Cafe', 1104400, 'Transaksi TRX1780286136'),
(11, '2026-06-02 08:56:04', 'Penjualan Cafe', 4180000, 'Transaksi TRX1780365364'),
(12, '2026-06-02 09:06:30', 'Penjualan Cafe', 60500, 'Transaksi TRX1780365990'),
(13, '2026-06-04 15:00:11', 'Penjualan Cafe', 1050500, 'Transaksi TRX1780560011'),
(14, '2026-06-08 13:40:51', 'Penjualan Cafe', 324500, 'Transaksi TRX1780900851'),
(15, '2026-06-10 21:11:58', 'Penjualan Cafe', 104500, 'Transaksi TRX1781100718');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','kasir','kitchen','admin','owner','karyawan') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Galin Widya', 'admin', '$2a$12$Al3LGNkZf6OL1iLXFx33bue8DnU4WFwIyIYz7zl42P3fVCscAJN9a', 'admin'),
(4, 'adi', 'adi', '$2y$10$USOySsyVI901K2h5JEHK0exqZzYjxG0or4b29jFfjh9MUk28R84iS', 'user'),
(5, 'essy', 'essy123', '$2y$10$L.uD9nqc/p7.gPttHTlDYedEfTRu3SNFRZ3oF8ucImJ6s2OpSPfta', 'user'),
(6, 'jafo', 'foo', '$2y$10$MFktFLe0BfywsteXSbFNlOIT2RQT4JwJ9o9czGKQ9bDtZMOxNAa6q', 'user'),
(7, 'ade', 'ade123', '$2y$10$Ls8Brj2eiQKedaZyNLfwLONSH/IEAqTimSZ0SQCd/0t8vNhS0rO2K', 'user'),
(9, 'kitchen', 'kitchen', '$2y$10$/1rC6MEpdufG3GJKADGrD.AGyXmAmmVQ0c7RErciWrpjF9PELauUm', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_belanja`
--
ALTER TABLE `detail_belanja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tugas_id` (`tugas_id`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`);

--
-- Indeks untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tugas_belanja`
--
ALTER TABLE `tugas_belanja`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `uang_keluar`
--
ALTER TABLE `uang_keluar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `uang_masuk`
--
ALTER TABLE `uang_masuk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_belanja`
--
ALTER TABLE `detail_belanja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT untuk tabel `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `pembelian`
--
ALTER TABLE `pembelian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `tugas_belanja`
--
ALTER TABLE `tugas_belanja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `uang_keluar`
--
ALTER TABLE `uang_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `uang_masuk`
--
ALTER TABLE `uang_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_belanja`
--
ALTER TABLE `detail_belanja`
  ADD CONSTRAINT `detail_belanja_ibfk_1` FOREIGN KEY (`tugas_id`) REFERENCES `tugas_belanja` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `gaji_karyawan`
--
ALTER TABLE `gaji_karyawan`
  ADD CONSTRAINT `gaji_karyawan_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
