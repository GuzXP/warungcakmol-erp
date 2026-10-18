-- Note: 
-- 1. 4 menu yang kurang udah ditambahkan di syntax sql ini
-- 2. meja hanya 2 udah di ganti juga di syntax sql ini


-- =========================================================
-- SQL SETUP DATABASE WARUNG NASI GORENG CAK MOL
-- Database: nasgorcakmoldb
-- =========================================================

-- 1. BUAT DATABASE BARU
DROP DATABASE IF EXISTS nasgorcakmoldb;
CREATE DATABASE nasgorcakmoldb;
USE nasgorcakmoldb;

-- ---------------------------------------------------------

-- 2. TABEL USER (Untuk Login Admin)
-- Digunakan oleh file: login.php, logout.php, admin/*
CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Insert Akun Admin Default
-- Username: admin
-- Password: admin123 (Hash Bcrypt)
INSERT INTO user (nama, username, password) VALUES 
('Cak Mol', 'admin', '$2y$10$wJ1c.g/e.e/a/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u/u');

-- ---------------------------------------------------------

-- 3. TABEL MEJA (Hanya 2 Meja)
-- Digunakan oleh: order.php (User), kasir.php (Admin)
CREATE TABLE meja (
    id_meja INT AUTO_INCREMENT PRIMARY KEY,
    nama_meja VARCHAR(50) NOT NULL,
    status_meja ENUM('Kosong', 'Terisi') DEFAULT 'Kosong'
);

INSERT INTO meja (nama_meja) VALUES 
('Meja 1'), 
('Meja 2');

-- ---------------------------------------------------------

-- 4. TABEL MENU (Katalog Makanan)
-- Digunakan oleh: index.php (User), order.php (User), admin/index.php
CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    nama_menu VARCHAR(100) NOT NULL,
    harga INT NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50) NOT NULL, 
    gambar VARCHAR(255) DEFAULT NULL,
    -- Kolom Khusus Tampilan User (Antigravity Style)
    badge VARCHAR(50) DEFAULT NULL,       -- Contoh: 'NEW!', 'BEST SELLER'
    badge_color VARCHAR(50) DEFAULT NULL  -- Contoh: 'badge-red', 'badge-yellow'
);

-- INSERT DATA MENU (LENGKAP + 4 MENU BARU)
INSERT INTO menu (nama_menu, harga, deskripsi, kategori, gambar, badge, badge_color) VALUES
-- A. NASI GORENG SERIES
('Nasi Goreng Merah Surabaya', 20000, 'Authentic Suroboyo! Saos merah tomat asli manis gurih, aroma smokey, lengkap dengan bakso dan telur.', 'Nasi Goreng Series', 'nasigoreng-merahsurabaya.png', 'NEW!', 'badge-red'),
('Nasi Goreng Gila', 20000, 'Favorit! Nasi goreng disiram tumisan topping Gila (Sosis, Bakso, Telur Orak-arik) bumbu medok.', 'Nasi Goreng Series', 'nasigoreng-gila.png', 'BEST SELLER', 'badge-yellow'),
('Nasi Goreng Biasa', 20000, 'Legenda sejak 98. Nasi goreng kampung bumbu kecap manis gurih, pakai ayam suwir dan acar.', 'Nasi Goreng Series', 'nasigoreng-biasa.png', NULL, NULL),
('Nasi Goreng Hongkong', 20000, 'Gurih Asin (Tanpa Kecap). Dimasak putih dengan jagung manis dan wortel. Simpel tapi nendang.', 'Nasi Goreng Series', 'nasigoreng-hongkong.png', NULL, NULL),
('Nasi Goreng Ruwet', 20000, 'Ambyar! Perpaduan Nasi dan Mie Goreng dimasak mawut jadi satu. Porsi kenyang maksimal.', 'Nasi Goreng Series', 'nasigoreng-ruwet.png', NULL, NULL),

-- B. MIE SERIES
('Mie Goreng Jawa', 20000, 'Mie kuning kenyal dimasak Nyemek (sedikit basah) dengan bumbu ulek kemiri dan bawang wangi.', 'Mie & Noodle Series', 'mie-goreng.png', NULL, NULL),
('Mie Goreng Gila', 20000, 'Mie goreng polos disiram topping Gila (Telur, Sosis, Bakso) yang melimpah ruah.', 'Mie & Noodle Series', 'mie-goreng-gila.png', NULL, NULL),
('Mie Rebus', 20000, 'Kuah kaldu panas yang ngaldu banget dengan telur kocok kental. Cocok buat penghangat badan.', 'Mie & Noodle Series', 'mie-rebus.png', NULL, NULL),

-- C. BIHUN SERIES (Dengan Menu Baru)
('Bihun Goreng', 20000, 'Bihun jagung tekstur kenyal, dimasak manis gurih kecap. Bumbu meresap sampai ke dalam.', 'Mie & Noodle Series', 'bihun-goreng.png', NULL, NULL),
('Bihun Rebus', 20000, 'Kuah kaldu bening dengan aroma bawang putih kuat, disajikan panas dengan telur dan sayuran segar.', 'Mie & Noodle Series', 'bihun-rebus.png', NULL, NULL),
('Bihun Goreng Gila', 20000, 'Bihun goreng lembut yang diamuk dengan topping Gila (Sosis, Bakso, Telur) dan bumbu pedas nendang.', 'Mie & Noodle Series', 'bihun-goreng-gila.png', NULL, NULL),

-- D. KWETIAU SERIES (Dengan Menu Baru)
('Kwetiau Goreng', 20000, 'Kwetiau lembut dengan aroma Wok Hei (asap wajan), dimasak dengan tauge dan telur.', 'Mie & Noodle Series', 'kwetiau-goreng.png', NULL, NULL),
('Kwetiau Rebus', 20000, 'Kwetiau licin disiram kuah gurih kental (nyemek) dengan telur yang dikocok dalam kuah. Comfort food!', 'Mie & Noodle Series', 'kwetiau-rebus.png', NULL, NULL),
('Kwetiau Goreng Gila', 20000, 'Tekstur kwetiau yang lebar berpadu sempurna dengan tumisan topping Gila yang melimpah ruah.', 'Mie & Noodle Series', 'kwetiau-goreng-gila.png', NULL, NULL),

-- E. CAPCAY (Harga Beda 27k)
('Capcay Goreng', 27000, 'Sayuran segar (Wortel, Sawi, Kol) dimasak nyemek dengan kekian ayam dan bakso. Sehat!', 'Capcay (Special)', 'capcay-goreng.png', NULL, NULL),
('Capcay Kuah', 27000, 'Kuah kental gurih dengan isian sayur mayur lengkap. Segar dan bergizi.', 'Capcay (Special)', 'capcay.png', NULL, NULL),

-- F. MINUMAN & PELENGKAP
('Nasi Putih', 5000, 'Nasi putih pulen porsi kenyang.', 'Minuman & Pelengkap', 'nasiputih.png', NULL, NULL),
('Teh Botol', 5000, 'Teh botol sosro dingin menyegarkan.', 'Minuman & Pelengkap', 'tehbotol.png', NULL, NULL),
('Aqua Botol', 5000, 'Air mineral dingin 600ml.', 'Minuman & Pelengkap', 'aqua.png', NULL, NULL);

-- ---------------------------------------------------------

-- 5. TABEL KASIR DEVICE (FCM Token)
-- Digunakan oleh: save_token.php, fungsi.php (kirimNotif)
CREATE TABLE kasir_device (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_fcm TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE(token_fcm(255))
);

-- ---------------------------------------------------------

-- 6. TABEL PESANAN (Header Transaksi)
-- Digunakan oleh: process_order.php (Insert), kasir.php (Select)
CREATE TABLE pesanan (
    id_pesanan INT AUTO_INCREMENT PRIMARY KEY,
    id_meja INT NOT NULL,
    total_bayar INT NOT NULL,
    status_pesanan ENUM('Pending', 'Selesai') DEFAULT 'Pending',
    waktu_pesan DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_meja) REFERENCES meja(id_meja)
);

-- ---------------------------------------------------------

-- 7. TABEL DETAIL PESANAN (Rincian Item)
-- Digunakan oleh: process_order.php (Insert), admin/cetakPDF.php (Select)
CREATE TABLE detail_pesanan (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_pesanan INT NOT NULL,
    id_menu INT NOT NULL,
    nama_menu_snapshot VARCHAR(100) NOT NULL,
    harga_snapshot INT NOT NULL,
    jumlah INT NOT NULL,
    -- Fitur Penting dari User Design:
    level_pedas VARCHAR(50) DEFAULT 'Tidak Pedas', 
    catatan TEXT,
    
    FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan),
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
);