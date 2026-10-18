# AUDIT MENDALAM LOGIKA BISNIS, DATABASE, & KEAMANAN
## Sistem ERP Food Commerce Warung Nasi Goreng Cak Mol

---

## 1. Analisis Skema Database Aktual (`nasgorcakmoldb`)

### 1.1 Struktur Tabel Inti & Masalah yang Ditemukan

#### Tabel `pesanan` (Header Transaksi)
Kolom saat ini:
- `id_pesanan` (INT, PK, AUTO_INCREMENT)
- `id_meja` (INT, FK nullable ke `meja.id_meja`)
- `total_bayar` (INT)
- `status_pesanan` (ENUM: `'Pending'`, `'Selesai'`)
- `waktu_pesan` (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- `nama_pelanggan` (VARCHAR(100), NULL untuk Dine In, Wajib untuk Takeaway)
- `tipe_pesanan` (ENUM: `'Dine In'`, `'Takeaway'`)
- `metode_bayar` (ENUM: `'Cash'`, `'QRIS'`, NULL sebelum bayar)
- `waktu_selesai` (DATETIME, NULL)
- `status_dapur` (ENUM: `'Menunggu'`, `'Dimasak'`, `'Siap'`, DEFAULT `'Menunggu'`)

**Temuan Kritis & Cacat Struktur:**
1. **Tidak Ada Kolom Fisik Keuangan Kasir (`uang_diterima`, `kembalian`)**:
   - Di UI Kasir (Screenshot 5), terdapat input nominal tunai yang diserahkan pelanggan dan sistem menghitung kembalian.
   - Karena kedua angka ini tidak disimpan di database, sistem tidak dapat mencatat berapa uang riil yang masuk ke laci kasir (*cash drawer*).
   - **Solusi**:
     ```sql
     ALTER TABLE `pesanan` 
     ADD COLUMN `uang_diterima` INT NOT NULL DEFAULT 0 AFTER `total_bayar`,
     ADD COLUMN `kembalian` INT NOT NULL DEFAULT 0 AFTER `uang_diterima`;
     ```
2. **Ketiadaan Status Pembatalan (`Batal` / `Void`)**:
   - Kolom `status_pesanan` hanya memiliki opsi `'Pending'` dan `'Selesai'`.
   - Jika pelanggan membatalkan pesanan (misal karena menunggu terlalu lama atau salah pesan), saat ini pilihannya hanya menghapus record (merusak nomor urut transaksi) atau membiarkannya menggantung di antrean selamanya.
   - **Solusi**:
     ```sql
     ALTER TABLE `pesanan` 
     MODIFY COLUMN `status_pesanan` ENUM('Pending', 'Selesai', 'Batal') NOT NULL DEFAULT 'Pending',
     ADD COLUMN `alasan_batal` VARCHAR(255) NULL AFTER `status_pesanan`;
     ```

---

#### Tabel `detail_pesanan` (Item Pesanan)
Kolom saat ini:
- `id_detail` (INT, PK, AUTO_INCREMENT)
- `id_pesanan` (INT, FK ke `pesanan.id_pesanan`)
- `id_menu` (INT, FK ke `menu.id_menu`)
- `nama_menu_snapshot` (VARCHAR(100))
- `harga_snapshot` (INT)
- `jumlah` (INT)
- `level_pedas` (VARCHAR(50), misal: `'Level 1'`, `'Level 4'`)
- `catatan` (TEXT, misal: `"mecin dikit tanpa acar"`)

**Evaluasi:**
- Tabel ini **sudah sangat baik** karena menyimpan `nama_menu_snapshot` dan `harga_snapshot`. Hal ini menjamin bahwa jika suatu saat harga menu Nasi Goreng Gila naik dari Rp 20.000 menjadi Rp 22.000, laporan historis pesanan bulan lalu tidak akan berubah nilainya.
- **Rekomendasi Keamanan**: Pastikan FK `detail_pesanan.id_pesanan` memiliki constraint `ON DELETE CASCADE` agar jika pesanan dihapus oleh admin, baris detail tidak menjadi *orphaned rows*.

---

#### Tabel `meja` (Master Meja Dine-In)
Kolom saat ini:
- `id_meja` (INT, PK)
- `nama_meja` (VARCHAR(50), contoh: `'Meja 1'`, `'Meja 2'`)
- `status_meja` (ENUM: `'Kosong'`, `'Terisi'`)
- `kode_qr` (VARCHAR(100))

**Temuan Kritis Logika Alur Bisnis:**
- Saat ini sistem mengubah `status_meja` menjadi `'Terisi'` saat KDS menandai pesanan `'Siap'`, bukan saat pesanan dibuat.
- Ini memicu kondisi bahaya (*race condition*):
  - Pelanggan A duduk di Meja 1 pada pukul 19:00 dan mengirim pesanan.
  - Masakan butuh waktu 15 menit.
  - Pada pukul 19:05, pelanggan B yang baru datang scan QR Meja 1 dan sistem masih menganggap Meja 1 `'Kosong'`.
- **Aturan Tegas**:
  - `status_meja` = `'Terisi'` **seketika saat pesanan Dine-In berhasil dibuat di `process_order.php`**.
  - `status_meja` = `'Kosong'` **hanya ketika kasir menyelesaikan pembayaran di `user/kasir.php`**.

---

## 2. Audit Integritas Transaksi Database (ACID Compliance)

### 2.1 Pembuatan Pesanan (`user/process_order.php`)
Diperlukan transaksi database penuh untuk menjamin header dan detail tersimpan utuh:

```php
// CONTOH POLA TRANSAKSI YANG WAJIB DITERAPKAN DI process_order.php
mysqli_begin_transaction($koneksi);

try {
    // 1. Validasi Meja jika Dine In
    if ($tipe_pesanan === 'Dine In') {
        $stmt_meja = mysqli_prepare($koneksi, "SELECT status_meja FROM meja WHERE id_meja = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_meja, "i", $id_meja);
        mysqli_stmt_execute($stmt_meja);
        $res_meja = mysqli_stmt_get_result($stmt_meja);
        $meja = mysqli_fetch_assoc($res_meja);
        
        if (!$meja || $meja['status_meja'] === 'Terisi') {
            throw new Exception("Meja sedang digunakan atau tidak valid.");
        }
    }

    // 2. Hitung total authoritative dari database tabel menu (JANGAN percaya harga dari frontend)
    $total_bayar = 0;
    foreach ($cart_items as $item) {
        $stmt_menu = mysqli_prepare($koneksi, "SELECT nama_menu, harga FROM menu WHERE id_menu = ?");
        mysqli_stmt_bind_param($stmt_menu, "i", $item['id_menu']);
        mysqli_stmt_execute($stmt_menu);
        $res_menu = mysqli_stmt_get_result($stmt_menu);
        $menu_data = mysqli_fetch_assoc($res_menu);
        
        if (!$menu_data) {
            throw new Exception("Menu ID " . $item['id_menu'] . " tidak ditemukan.");
        }
        $subtotal = $menu_data['harga'] * (int)$item['jumlah'];
        $total_bayar += $subtotal;
    }

    // 3. Insert Header Pesanan
    $stmt_order = mysqli_prepare($koneksi, 
        "INSERT INTO pesanan (id_meja, total_bayar, status_pesanan, nama_pelanggan, tipe_pesanan, status_dapur) 
         VALUES (?, ?, 'Pending', ?, ?, 'Menunggu')"
    );
    mysqli_stmt_bind_param($stmt_order, "iisss", $id_meja, $total_bayar, $nama_pelanggan, $tipe_pesanan);
    mysqli_stmt_execute($stmt_order);
    $order_id = mysqli_insert_id($koneksi);

    // 4. Insert Detail Pesanan (Snapshot)
    foreach ($cart_items as $item) {
        // ... ambil snapshot nama & harga, lalu insert ke detail_pesanan ...
    }

    // 5. Update Status Meja Menjadi Terisi (Jika Dine In)
    if ($tipe_pesanan === 'Dine In') {
        $stmt_up_meja = mysqli_prepare($koneksi, "UPDATE meja SET status_meja = 'Terisi' WHERE id_meja = ?");
        mysqli_stmt_bind_param($stmt_up_meja, "i", $id_meja);
        mysqli_stmt_execute($stmt_up_meja);
    }

    mysqli_commit($koneksi);
    echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
```

---

### 2.2 Penyelesaian Pembayaran di Kasir (`user/kasir.php`)
Saat ini proses pembayaran dan pengosongan meja dieksekusi tanpa transaksi, yang berpotensi menyebabkan status meja tidak sinkron jika server mati sesaat.

**Pola Transaksi Pembayaran yang Benar:**
```php
mysqli_begin_transaction($koneksi);

try {
    // 1. Ambil data pesanan
    $stmt = mysqli_prepare($koneksi, "SELECT id_meja, total_bayar, tipe_pesanan FROM pesanan WHERE id_pesanan = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $id_pesanan);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$order) {
        throw new Exception("Pesanan tidak ditemukan.");
    }

    // Validasi uang diterima
    if ($metode_bayar === 'Cash' && $uang_diterima < $order['total_bayar']) {
        throw new Exception("Nominal uang yang diterima kurang dari total tagihan.");
    }

    $kembalian = ($metode_bayar === 'Cash') ? ($uang_diterima - $order['total_bayar']) : 0;
    $waktu_selesai = date('Y-m-d H:i:s');

    // 2. Update Header Pesanan
    $stmt_pay = mysqli_prepare($koneksi, 
        "UPDATE pesanan 
         SET status_pesanan = 'Selesai', 
             metode_bayar = ?, 
             uang_diterima = ?, 
             kembalian = ?, 
             waktu_selesai = ? 
         WHERE id_pesanan = ?"
    );
    mysqli_stmt_bind_param($stmt_pay, "siisi", $metode_bayar, $uang_diterima, $kembalian, $waktu_selesai, $id_pesanan);
    mysqli_stmt_execute($stmt_pay);

    // 3. Bebaskan Meja jika Dine In
    if ($order['tipe_pesanan'] === 'Dine In' && !empty($order['id_meja'])) {
        $stmt_meja = mysqli_prepare($koneksi, "UPDATE meja SET status_meja = 'Kosong' WHERE id_meja = ?");
        mysqli_stmt_bind_param($stmt_meja, "i", $order['id_meja']);
        mysqli_stmt_execute($stmt_meja);
    }

    mysqli_commit($koneksi);
    // Redirect ke cetak struk
    header("Location: cetak_struk.php?id=" . $id_pesanan);
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $error_message = $e->getMessage();
}
```

---

## 3. Audit Keamanan Aplikasi (Security Hardening)

### 3.1 Pencegahan SQL Injection
- Semua endpoint pemanggil query (`process_order.php`, `kasir.php`, `poll_orders.php`, `update_status.php`, `admin/add.php`, dll.) **harus 100% menggunakan Prepared Statements** (`mysqli_prepare` + `mysqli_stmt_bind_param`).
- Jangan ada string concatenation seperti `$sql = "SELECT * FROM pesanan WHERE id = " . $_GET['id'];`.

### 3.2 Proteksi Cross-Site Scripting (XSS)
- Helper `sanitize($data)` di `includes/helpers.php` memakai `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
- Pastikan saat mencetak nama menu, nama pemesan, dan catatan kustom di HTML (baik di KDS, kasir, admin, struk), helper ini selalu dipanggil:
  ```php
  <p class="text-sm font-medium"><?= sanitize($item['nama_menu_snapshot']); ?></p>
  <span class="italic text-xs"><?= sanitize($item['catatan']); ?></span>
  ```

### 3.3 Proteksi Cross-Site Request Forgery (CSRF)
- Setiap form POST di Admin (Tambah Menu, Edit Menu, Catat Pengeluaran, Simpan Pengaturan) dan Kasir (Proses Bayar) harus memiliki input token:
  ```html
  <input type="hidden" name="csrf_token" value="<?= csrf_token(); ?>">
  ```
- Backend harus memverifikasi:
  ```php
  if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
      die("Invalid CSRF token.");
  }
  ```

### 3.4 Validasi Upload Bukti Pengeluaran & Foto Menu
- Folder upload: `img/` dan `img/receipts/`.
- Aturan validasi ketat:
  1. Cek ukuran file (maksimal 2MB).
  2. Cek MIME type riil menggunakan `finfo_file` (hanya `image/jpeg`, `image/png`, `image/webp`).
  3. Cek ekstensi file yang diizinkan (`jpg`, `jpeg`, `png`, `webp`).
  4. Generate nama file acak baru (`uniqid('menu_', true) . '.' . $ext`). **Jangan gunakan nama asli kiriman pengguna**.
  5. Simpan di luar root eksekusi script atau cegah eksekusi script `.php` di folder upload melalui `.htaccess`:
     ```apache
     <FilesMatch "\.(php|php5|phtml)$">
         Deny from all
     </FilesMatch>
     ```
