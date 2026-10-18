-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 25 Bulan Mei 2026 pada 14.34
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nasgorcakmoldb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(11) NOT NULL,
  `id_pesanan` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `nama_menu_snapshot` varchar(100) NOT NULL,
  `harga_snapshot` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `level_pedas` varchar(50) DEFAULT 'Tidak Pedas',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_menu`, `nama_menu_snapshot`, `harga_snapshot`, `jumlah`, `level_pedas`, `catatan`) VALUES
(1, 1, 16, 'Capcay Kuah', 27000, 1, 'Level 1', ''),
(2, 2, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(3, 3, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(4, 4, 4, 'Nasi Goreng Hongkong', 20000, 1, 'Tidak Pedas', NULL),
(5, 5, 3, 'Nasi Goreng Biasa', 20000, 1, 'Tidak Pedas', NULL),
(6, 6, 11, 'Bihun Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(7, 7, 15, 'Capcay Goreng', 27000, 1, 'Tidak Pedas', NULL),
(8, 8, 6, 'Mie Goreng Jawa', 20000, 2, 'Tidak Pedas', NULL),
(9, 8, 6, 'Mie Goreng Jawa', 20000, 1, 'Tidak Pedas', NULL),
(10, 9, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(11, 10, 5, 'Nasi Goreng Ruwet', 20000, 1, 'Tidak Pedas', NULL),
(12, 11, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(13, 12, 2, 'Nasi Goreng Gila', 20000, 2, 'Tidak Pedas', NULL),
(14, 12, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(15, 13, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(16, 14, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(17, 15, 2, 'Nasi Goreng Gila', 20000, 2, 'Tidak Pedas', NULL),
(18, 15, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(19, 16, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(20, 17, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(21, 18, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(22, 19, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(23, 20, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(24, 21, 6, 'Mie Goreng Jawa', 20000, 1, 'Tidak Pedas', NULL),
(25, 22, 7, 'Mie Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(26, 23, 7, 'Mie Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(27, 24, 10, 'Bihun Rebus', 20000, 1, 'Tidak Pedas', NULL),
(28, 25, 7, 'Mie Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(29, 26, 18, 'Teh Botol', 5000, 1, 'Tidak Pedas', NULL),
(30, 27, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(31, 28, 3, 'Nasi Goreng Biasa', 20000, 1, 'Tidak Pedas', NULL),
(32, 29, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(33, 30, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(34, 31, 6, 'Mie Goreng Jawa', 20000, 1, 'Tidak Pedas', NULL),
(35, 31, 3, 'Nasi Goreng Biasa', 20000, 1, 'Tidak Pedas', NULL),
(36, 31, 3, 'Nasi Goreng Biasa', 20000, 1, 'Tidak Pedas', NULL),
(37, 32, 2, 'Nasi Goreng Gila', 20000, 2, 'Tidak Pedas', NULL),
(38, 32, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(39, 32, 18, 'Teh Botol', 5000, 1, 'Tidak Pedas', NULL),
(40, 33, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(41, 34, 1, 'Nasi Goreng Merah Surabaya', 20000, 1, 'Tidak Pedas', NULL),
(42, 35, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(43, 36, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(44, 36, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(45, 36, 18, 'Teh Botol', 5000, 1, 'Tidak Pedas', NULL),
(46, 36, 6, 'Mie Goreng Jawa', 20000, 1, 'Tidak Pedas', NULL),
(47, 37, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(48, 37, 2, 'Nasi Goreng Gila', 20000, 1, 'Tidak Pedas', NULL),
(49, 37, 18, 'Teh Botol', 5000, 1, 'Tidak Pedas', NULL),
(50, 37, 6, 'Mie Goreng Jawa', 20000, 1, 'Tidak Pedas', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `meja`
--

CREATE TABLE `meja` (
  `id_meja` int(11) NOT NULL,
  `nama_meja` varchar(50) NOT NULL,
  `status_meja` enum('Kosong','Terisi') DEFAULT 'Kosong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `meja`
--

INSERT INTO `meja` (`id_meja`, `nama_meja`, `status_meja`) VALUES
(1, 'Meja 1', 'Kosong'),
(2, 'Meja 2', 'Kosong');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(50) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `badge_color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`, `deskripsi`, `kategori`, `gambar`, `badge`, `badge_color`) VALUES
(1, 'Nasi Goreng Merah Surabaya', 20000, 'Authentic Suroboyo! Saos merah tomat asli manis gurih, aroma smokey, lengkap dengan bakso dan telur.', 'Nasi Goreng Series', 'nasigoreng-merahsurabaya.png', 'NEW!', 'badge-red'),
(2, 'Nasi Goreng Gila', 20000, 'Favorit! Nasi goreng disiram tumisan topping Gila (Sosis, Bakso, Telur Orak-arik) bumbu medok.', 'Nasi Goreng Series', 'nasigoreng-gila.png', 'BEST SELLER', 'badge-yellow'),
(3, 'Nasi Goreng Biasa', 20000, 'Legenda sejak 98. Nasi goreng kampung bumbu kecap manis gurih, pakai ayam suwir dan acar.', 'Nasi Goreng Series', 'nasigoreng-biasa.png', NULL, NULL),
(4, 'Nasi Goreng Hongkong', 20000, 'Gurih Asin (Tanpa Kecap). Dimasak putih dengan jagung manis dan wortel. Simpel tapi nendang.', 'Nasi Goreng Series', 'nasigoreng-hongkong.png', NULL, NULL),
(5, 'Nasi Goreng Ruwet', 20000, 'Ambyar! Perpaduan Nasi dan Mie Goreng dimasak mawut jadi satu. Porsi kenyang maksimal.', 'Nasi Goreng Series', 'nasigoreng-ruwet.png', NULL, NULL),
(6, 'Mie Goreng Jawa', 20000, 'Mie kuning kenyal dimasak Nyemek (sedikit basah) dengan bumbu ulek kemiri dan bawang wangi.', 'Mie & Noodle Series', 'mie-goreng.png', NULL, NULL),
(7, 'Mie Goreng Gila', 20000, 'Mie goreng polos disiram topping Gila (Telur, Sosis, Bakso) yang melimpah ruah.', 'Mie & Noodle Series', 'mie-goreng-gila.png', NULL, NULL),
(8, 'Mie Rebus', 20000, 'Kuah kaldu panas yang ngaldu banget dengan telur kocok kental. Cocok buat penghangat badan.', 'Mie & Noodle Series', 'mie-rebus.png', NULL, NULL),
(9, 'Bihun Goreng', 20000, 'Bihun jagung tekstur kenyal, dimasak manis gurih kecap. Bumbu meresap sampai ke dalam.', 'Mie & Noodle Series', 'bihun-goreng.png', NULL, NULL),
(10, 'Bihun Rebus', 20000, 'Kuah kaldu bening dengan aroma bawang putih kuat, disajikan panas dengan telur dan sayuran segar.', 'Mie & Noodle Series', 'bihun-rebus.png', NULL, NULL),
(11, 'Bihun Goreng Gila', 20000, 'Bihun goreng lembut yang diamuk dengan topping Gila (Sosis, Bakso, Telur) dan bumbu pedas nendang.', 'Mie & Noodle Series', 'bihun-goreng-gila.png', NULL, NULL),
(12, 'Kwetiau Goreng', 20000, 'Kwetiau lembut dengan aroma Wok Hei (asap wajan), dimasak dengan tauge dan telur.', 'Mie & Noodle Series', 'kwetiau-goreng.png', NULL, NULL),
(13, 'Kwetiau Rebus', 20000, 'Kwetiau licin disiram kuah gurih kental (nyemek) dengan telur yang dikocok dalam kuah. Comfort food!', 'Mie & Noodle Series', 'kwetiau-rebus.png', NULL, NULL),
(14, 'Kwetiau Goreng Gila', 20000, 'Tekstur kwetiau yang lebar berpadu sempurna dengan tumisan topping Gila yang melimpah ruah.', 'Mie & Noodle Series', 'kwetiau-goreng-gila.png', NULL, NULL),
(15, 'Capcay Goreng', 27000, 'Sayuran segar (Wortel, Sawi, Kol) dimasak nyemek dengan kekian ayam dan bakso. Sehat!', 'Capcay (Special)', 'capcay-goreng.png', NULL, NULL),
(16, 'Capcay Kuah', 27000, 'Kuah kental gurih dengan isian sayur mayur lengkap. Segar dan bergizi.', 'Capcay (Special)', 'capcay.png', NULL, NULL),
(17, 'Nasi Putih', 5000, 'Nasi putih pulen porsi kenyang.', 'Minuman & Pelengkap', 'nasiputih.png', NULL, NULL),
(18, 'Teh Botol', 5000, 'Teh botol sosro dingin menyegarkan.', 'Minuman & Pelengkap', 'tehbotol.png', NULL, NULL),
(19, 'Aqua Botol 600ml', 5000, 'Air mineral dingin 600ml.', 'Minuman & Pelengkap', '69673f4d0fc12.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(11) NOT NULL,
  `id_meja` int(11) NOT NULL,
  `total_bayar` int(11) NOT NULL,
  `status_pesanan` enum('Pending','Selesai') DEFAULT 'Pending',
  `waktu_pesan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_meja`, `total_bayar`, `status_pesanan`, `waktu_pesan`) VALUES
(1, 1, 27000, 'Selesai', '2026-01-13 20:10:44'),
(2, 1, 20000, 'Selesai', '2026-01-13 21:30:53'),
(3, 1, 20000, 'Selesai', '2026-01-13 21:31:48'),
(4, 1, 20000, 'Selesai', '2026-01-13 21:32:13'),
(5, 1, 20000, 'Selesai', '2026-01-13 22:08:03'),
(6, 1, 20000, 'Selesai', '2026-01-13 23:19:00'),
(7, 1, 27000, 'Selesai', '2026-01-14 08:49:15'),
(8, 1, 60000, 'Selesai', '2026-01-14 11:55:05'),
(9, 1, 20000, 'Selesai', '2026-01-14 13:50:48'),
(10, 1, 20000, 'Selesai', '2026-01-14 13:51:19'),
(11, 1, 20000, 'Selesai', '2026-01-14 16:31:35'),
(12, 1, 60000, 'Selesai', '2026-01-14 16:37:13'),
(13, 1, 20000, 'Selesai', '2026-01-14 16:37:41'),
(14, 1, 20000, 'Selesai', '2026-01-14 16:41:34'),
(15, 1, 60000, 'Selesai', '2026-01-24 13:31:56'),
(16, 1, 20000, 'Selesai', '2026-01-24 13:32:28'),
(17, 1, 20000, 'Selesai', '2026-01-28 14:15:28'),
(18, 1, 20000, 'Selesai', '2026-01-28 14:29:10'),
(19, 1, 20000, 'Selesai', '2026-01-28 14:29:37'),
(20, 1, 20000, 'Selesai', '2026-01-28 14:30:56'),
(21, 1, 20000, 'Selesai', '2026-01-28 14:31:53'),
(22, 1, 20000, 'Selesai', '2026-01-28 14:32:21'),
(23, 1, 20000, 'Selesai', '2026-01-28 14:32:58'),
(24, 1, 20000, 'Selesai', '2026-01-28 14:33:49'),
(25, 1, 20000, 'Selesai', '2026-01-28 14:37:33'),
(26, 1, 5000, 'Selesai', '2026-01-28 14:37:50'),
(27, 1, 20000, 'Selesai', '2026-01-28 14:39:53'),
(28, 1, 20000, 'Selesai', '2026-01-28 14:40:32'),
(29, 1, 20000, 'Selesai', '2026-01-28 14:40:49'),
(30, 1, 20000, 'Selesai', '2026-01-28 14:41:06'),
(31, 1, 60000, 'Selesai', '2026-02-24 12:32:33'),
(32, 1, 65000, 'Selesai', '2026-04-23 21:19:52'),
(33, 1, 20000, 'Selesai', '2026-04-27 21:19:03'),
(34, 1, 20000, 'Pending', '2026-05-18 08:38:20'),
(35, 1, 20000, 'Pending', '2026-05-18 08:55:16'),
(36, 1, 65000, 'Pending', '2026-05-18 17:03:08'),
(37, 1, 65000, 'Pending', '2026-05-18 17:03:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `nama`, `username`, `password`) VALUES
(1, 'Cak Mol', 'admin', '$2y$10$wJ1c.g/e.e/a/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u'),
(2, 'gus', 'gus', '$2y$10$.DugyDB0VbDzcEfCD5edaeQDIfQcytqkG9C6yoCof9ZtE8WUlWanG'),
(3, 'bagus', 'bagus', '$2y$10$6lbzhS03e8SyLhgIsHQgeeTy7gaEewpuNRB/HErUCKVLvHUTSQk5i');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indeks untuk tabel `meja`
--
ALTER TABLE `meja`
  ADD PRIMARY KEY (`id_meja`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_meja` (`id_meja`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `meja`
--
ALTER TABLE `meja`
  MODIFY `id_meja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`),
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_meja`) REFERENCES `meja` (`id_meja`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
