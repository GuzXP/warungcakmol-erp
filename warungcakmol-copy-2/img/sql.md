-- 1. BERSIHKAN DATABASE LAMA (RESET TOTAL)
DROP DATABASE IF EXISTS nasgor_cakmoldb;

-- 2. BUAT DATABASE BARU
CREATE DATABASE nasgor_cakmoldb;
USE nasgor_cakmoldb;

-- ==========================================
-- A. TABEL MENU (PRODUK)
-- ==========================================
CREATE TABLE menu_cakmol (
ID_Produk INT PRIMARY KEY AUTO_INCREMENT,
Nama_Menu VARCHAR(100) NOT NULL,
Harga_Jual INT NOT NULL,
Bahan_Utama VARCHAR(255) NOT NULL,
Deskripsi TEXT NOT NULL,
Foto_Menu VARCHAR(255) NOT NULL
);

-- ==========================================
-- B. ISI DATA MENU (19 ITEM)
-- ==========================================

INSERT INTO menu_cakmol (Nama_Menu, Harga_Jual, Bahan_Utama, Deskripsi, Foto_Menu) VALUES
-- 1. NASI GORENG SERIES
('Nasi Goreng Biasa', 20000, 'Nasi, Telur, Ayam Suwir, Sawi', 'Nasi goreng original dengan bumbu kecap manis gurih, disajikan dengan kerupuk dan acar.', 'nasigoreng-biasa.png'),

('Nasi Goreng Gila', 20000, 'Nasi, Topping Telur Orak-arik Tomat & Bumbu Rahasia', 'Spesial! Topping telur orak-arik dimasak terpisah dengan tomat dan bumbu rahasia, lalu ditaruh di atas nasi.', 'nasigoreng-gila.png'),

('Nasi Goreng Hongkong', 20000, 'Nasi, Jagung, Wortel, Telur (Tanpa Kecap)', 'Nasi goreng putih (tanpa kecap) dengan isian sayuran mix (jagung/wortel) dan telur, rasa gurih asin.', 'nasigoreng-hongkong.png'),

('Nasi Goreng Ruwet', 20000, 'Nasi, Mie Kuning, Sayur, Ayam', 'Perpaduan nasi dan mie goreng yang dimasak mawut jadi satu, porsi kenyang maksimal.', 'nasigoreng-ruwet.png'),

('Nasi Goreng Merah Surabaya', 20000, 'Nasi, Saos Merah, Bakso, Telur', 'Khas Surabaya/Makassar dengan saos merah yang menggoda, rasanya sedikit manis gurih.', 'nasigoreng-merahsurabaya.png'),

-- 2. MIE SERIES
('Mie Goreng', 20000, 'Mie Kuning, Sawi, Kubis, Ayam', 'Mie goreng jawa dimasak nyemek/kering sesuai selera dengan bumbu rempah yang kuat.', 'mie-goreng.png'),

('Mie Goreng Gila', 20000, 'Mie, Topping Telur Orak-arik Tomat & Bumbu Rahasia', 'Mie goreng polos disiram topping gila (telur, tomat, bumbu rahasia) di atasnya. Mantap!', 'mie-goreng-gila.png'),

('Mie Rebus', 20000, 'Mie, Kuah Kaldu, Telur, Sayur', 'Mie kuah dengan kaldu panas yang segar, cocok dimakan malam hari.', 'mie-rebus.png'),

-- 3. BIHUN SERIES
('Bihun Goreng', 20000, 'Bihun Jagung, Kecap, Sayuran', 'Bihun goreng manis gurih, tekstur kenyal dan bumbu meresap.', 'bihun-goreng.png'),

('Bihun Goreng Gila', 20000, 'Bihun, Topping Telur Orak-arik Tomat & Bumbu Rahasia', 'Bihun goreng disajikan dengan topping gila (telur orak-arik tomat) yang melimpah di atasnya.', 'bihun-goreng-gila.png'),

('Bihun Rebus', 20000, 'Bihun, Kuah Bawang, Seledri', 'Bihun kuah bening dengan aroma bawang putih yang sedap.', 'bihun-rebus.png'),

-- 4. KWETIAU SERIES
('Kwetiau Goreng', 20000, 'Kwetiau, Tauge, Telur, Bakso', 'Kwetiau goreng dengan tekstur lembut, dimasak dengan api besar (wok hei).', 'kwetiau-goreng.png'),

('Kwetiau Goreng Gila', 20000, 'Kwetiau, Topping Telur Orak-arik Tomat & Bumbu Rahasia', 'Kwetiau goreng spesial dengan tambahan topping gila terpisah di atasnya.', 'kwetiau-goreng-gila.png'),

('Kwetiau Rebus', 20000, 'Kwetiau, Kuah Kental, Sawi', 'Kwetiau siram/kuah dengan telur yang dikocok dalam kuah panas.', 'kwetiau-rebus.png'),

-- 5. CAPCAY (HARGA KHUSUS 25K)
('Capcay Kuah', 25000, 'Wortel, Sawi, Kembang Kol, Kekian, Ayam', 'Tumis sayuran lengkap dengan kuah kental yang gurih dan sehat.', 'capcay.png'),

('Capcay Goreng', 25000, 'Wortel, Sawi, Kol, Ayam, Bakso', 'Capcay dimasak kering/nyemek dengan sedikit saos kecap.', 'capcay-goreng.png'),

-- 6. PELENGKAP & MINUMAN (HARGA 5K)
('Nasi Putih', 5000, 'Beras Pilihan', 'Nasi putih pulen, tambahan untuk lauk capcay.', 'nasiputih.png'),

('Teh Botol', 5000, 'Minuman Kemasan', 'Teh botol sosro dingin menyegarkan.', 'tehbotol.png'),

('Aqua', 5000, 'Air Mineral 600ml', 'Air mineral dingin.', 'aqua.png');

-- ==========================================
-- C. TABEL USER (ADMIN)
-- ==========================================
CREATE TABLE user (
id INT PRIMARY KEY AUTO_INCREMENT,
nama_lengkap VARCHAR(100) NOT NULL,
username VARCHAR(50) NOT NULL,
password VARCHAR(255) NOT NULL
);

-- ==========================================
-- D. AKUN ADMIN DEFAULT
-- ==========================================
-- Username: admin
-- Password: admin123
INSERT INTO user VALUES
(NULL, 'Administrator Utama', 'admin', '$2y$10$bfS6Ww7.q3.B.X7K.tS7UO.vW/1j.Y1.p1.k1.u1.m1.o1');

CREATE TABLE `user` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`nama` varchar(50) NOT NULL,
`username` varchar(50) NOT NULL,
`password` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
