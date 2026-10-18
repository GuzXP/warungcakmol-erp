# **PANDUAN KOMPREHENSIF ALUR & BEDAH KODE POS DINE-IN MANDIRI**
## **Studi Kasus: Warung Nasi Goreng Cak Mol Pulomas (Panduan Berorientasi Kode)**

Panduan ini disusun secara khusus untuk memberikan pemahaman teknis yang mendalam mengenai alur kerja program (*code flow*), interaksi antar-file, logika bisnis, dan mekanisme keamanan di dalam codebase **Warung Cak Mol** (`warungcakmol-copy`). Dokumen ini disusun per file secara terstruktur agar memudahkan Anda dalam menjelaskan logika program baris-demi-baris saat sidang perkuliahan Rekayasa Perangkat Lunak (RPL).

---

# **BAGIAN 1: PEMETAAN & STRUKTUR FILE**

Aplikasi POS ini dibagi menjadi dua modul utama demi memisahkan tanggung jawab (Separation of Concerns):
1. **Modul Client/User (`user/`)**: Digunakan oleh Pelanggan untuk memesan makanan, Kasir untuk memantau antrean, serta file-file koneksi & helper inti.
2. **Modul Administrator (`admin/`)**: Digunakan oleh Admin (Owner) untuk mengelola menu (CRUD) dan registrasi admin baru.

---

# **BAGIAN 2: BEDAH ALUR KODE MODUL USER (CLIENT-SIDE & POS KASIR)**

Modul User terdiri dari 7 berkas utama. Berikut adalah penjelasan alur kode terperinci untuk masing-masing berkas:

```
                  ┌───────────────┐
                  │  koneksi.php  │
                  └───────┬───────┘
                          │ (require_once)
                  ┌───────▼───────┐
                  │  fungsi.php   │
                  └───────┬───────┘
       ┌──────────────────┼──────────────────┐
       │ (require)        │ (require)        │
┌──────▼──────┐    ┌──────▼──────┐    ┌──────▼──────┐
│  index.php  │    │  order.php  │    │  kasir.php  │
└─────────────┘    └──────┬──────┘    └──────▲──────┘
                          │ (Fetch POST)     │ (AJAX Polling 8s)
                   ┌──────▼──────┐    ┌──────┴─────────┐
                   │process_order│    │poll_orders.php │
                   └─────────────┘    └────────────────┘
```

---

### **1. `user/koneksi.php` — Gerbang Utama Database**
* **Tanggung Jawab**: Membuka jalur komunikasi antara interpreter PHP dengan database server MySQL.
* **Alur Logika Kode**:
  1. **Konfigurasi Kredensial** (Baris 9-12):
     ```php
     $host = "localhost"; // Mesin database berjalan lokal di PC XAMPP
     $user = "root";      // Username default dari XAMPP
     $pass = "";          // Password kosong bawaan instalasi XAMPP
     $db   = "nasgorcakmoldb"; // Database target yang menyimpan data POS
     ```
  2. **Inisialisasi Koneksi** (Baris 16):
     ```php
     $koneksi = mysqli_connect($host, $user, $pass, $db);
     ```
     Fungsi `mysqli_connect` membuka koneksi baru ke MySQL menggunakan driver bawaan (*native driver*) PHP.
  3. **Error Handling/Pendeteksi Kegagalan** (Baris 20-22):
     ```php
     if (!$koneksi) {
         die("Koneksi Gagal: " . mysqli_connect_error());
     }
     ```
     Jika koneksi bernilai `false`, fungsi `die()` akan langsung menghentikan jalannya seluruh eksekusi halaman PHP dan mencetak pesan kesalahan koneksi ke layar. Ini penting agar program tidak mencoba menjalankan query saat database mati.

---

### **2. `user/fungsi.php` — Pustaka Helper Global User**
* **Tanggung Jawab**: Menyediakan fungsi-fungsi umum yang sering dipanggil berulang kali di halaman user untuk efisiensi baris kode (*reusability*).
* **Alur Logika Kode**:
  1. **Pemanggilan Koneksi** (Baris 12):
     ```php
     require_once 'koneksi.php';
     ```
     Memastikan `koneksi.php` dimuat tepat satu kali.
  2. **Fungsi `query($query)` — Abstraksi Pembacaan Data** (Baris 24-35):
     ```php
     function query($query) {
         global $koneksi; // Mengambil variabel koneksi dari scope global
         $result = mysqli_query($koneksi, $query);
         $rows = [];
         while ($row = mysqli_fetch_assoc($result)) {
             $rows[] = $row;
         }
         return $rows;
     }
     ```
     * **`global $koneksi;`**: Tanpa baris ini, fungsi tidak dapat menggunakan variabel koneksi karena batasan scope fungsi di PHP.
     * **`mysqli_fetch_assoc`**: Mengambil satu baris hasil query sebagai array asosiatif (key adalah nama kolom di database).
     * **`while`**: Melakukan pengulangan otomatis untuk memasukkan semua baris record ke dalam array bertumpuk `$rows` sampai record database habis.
  3. **Fungsi `formatRupiah($angka)` — Format Keuangan** (Baris 43-45):
     ```php
     function formatRupiah($angka) {
         return "Rp " . number_format($angka, 0, ',', '.');
     }
     ```
     Mengubah angka integer biasa (misal: `18000`) menjadi format rapi mata uang rupiah (`Rp 18.000`) dengan pemisah ribuan titik.

---

### **3. `user/index.php` — Landing Page Pelanggan**
* **Tanggung Jawab**: Menampilkan profil warung, lokasi, testimoni, galeri suasana, serta menyajikan katalog daftar menu aktif dari database yang dikelompokkan secara visual.
* **Alur Logika Kode**:
  1. **Require Koneksi**: Membuka database via `require 'koneksi.php'`.
  2. **Membaca & Mengelompokkan Menu (Database Dynamic Grouping)** (Baris 20-59):
     ```php
     $result = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY id_menu ASC");
     $menuDataGrouped = [];
     while($row = mysqli_fetch_assoc($result)) {
         $kategori = trim(html_entity_decode($row['kategori']));
         $kategori = str_replace("\xc2\xa0", " ", $kategori); // Bersihkan spasi ghaib
         ...
         if(!isset($menuDataGrouped[$kategori])) {
             $menuDataGrouped[$kategori] = [
                 "name" => $kategori,
                 "items" => []
             ];
         }
         $menuDataGrouped[$kategori]['items'][] = $menuItem;
     }
     $menuData = array_values($menuDataGrouped);
     ```
     * PHP mengambil semua baris data menu dari database.
     * Kode di atas melakukan **pengelompokan asinkron (grouping)** secara dinamis. PHP memeriksa nama kategori menu. Jika kategori tersebut belum ada di array `$menuDataGrouped`, PHP akan membuat key baru. Item menu kemudian dimasukkan ke dalam sub-array kategori yang sesuai.
     * Hasil akhirnya adalah array terstruktur rapi per kategori tanpa ada pengkodean manual (*hardcode*).
  3. **Rendering HTML & CSS Grid**:
     Di dalam file HTML, data di-loop menggunakan `foreach` PHP. Tampilan dirancang modern menggunakan sistem Flexbox dan Grid dengan CSS Vanilla, didukung inisialisasi ikon Lucide (`lucide.createIcons()`) untuk visualisasi tombol dan navigasi.

---

### **4. `user/order.php` — Sistem Pemesanan Mandiri & Keranjang Belanja**
* **Tanggung Jawab**: Menyediakan halaman pemesanan interaktif tempat pelanggan memilih menu, mengatur kuantitas di keranjang, memasukkan level pedas/catatan khusus, dan mengirim pesanan.
* **Alur Logika Kode (PHP & JavaScript)**:
  1. **PHP Core**: Mengambil semua daftar menu di server dan mengonversinya menjadi format Array JSON agar dapat dibaca secara native oleh JavaScript di sisi client browser.
  2. **JS Client-Side Cart (Keranjang di Browser)** (Baris 347-350):
     ```javascript
     let cart = []; // Variabel array utama penyimpan belanjaan
     ```
     Keranjang belanja dikelola 100% menggunakan array JavaScript di browser agar aplikasi terasa sangat cepat dan responsif tanpa membebani server setiap kali pelanggan menambah atau mengurangi item.
  3. **JS Logic: Filter Kategori Dinamis** (`filterCategory`):
     Menyembunyikan kartu-kartu menu secara visual berdasarkan tombol tab kategori yang diklik pelanggan.
  4. **JS Logic: Modal Kustomisasi & Level Pedas Pintar** (`openCustomizeModal`):
     ```javascript
     if(item.category.includes('Minuman') || item.category.includes('Pelengkap')) {
         spiceDiv.classList.add('hidden'); // Sembunyikan pedas jika minuman
     } else {
         spiceDiv.classList.remove('hidden'); // Tampilkan jika makanan
     }
     ```
     Sistem secara otomatis mendeteksi kategori menu. Jika menu yang ditambahkan berkategori minuman atau pelengkap, input tingkat kepedasan akan disembunyikan agar logis bagi pengguna.
  5. **JS Logic: Pengiriman Transaksi Asinkron via Fetch API** (`confirmOrder`):
     ```javascript
     const response = await fetch('process_order.php', {
         method: 'POST',
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify({ meja_id: tableId, items: cart })
     });
     ```
     Ketika pelanggan mengonfirmasi pesanan, JavaScript memaketkan seluruh isi keranjang dan nomor meja ke dalam format JSON, lalu menembakkannya ke API Backend (`process_order.php`) secara asinkron menggunakan Fetch API (metode POST).
  6. **UI Effects**:
     Setelah pesanan sukses, browser menampilkan animasi visual memasak (overlay wajan berputar) selama 3 detik, lalu menampilkan struk digital dinamis yang terbit dari printer virtual HTML.

---

### **5. `user/process_order.php` — API Pemroses Transaksi Database**
* **Tanggung Jawab**: Menerima paket JSON pesanan, melakukan validasi keamanan, menghitung ulang total harga di sisi server, serta menyimpannya ke database dengan sistem Transaksi ACID.
* **Alur Logika Kode**:
  1. **Pemanggilan Helper & Koneksi** (Baris 11-12):
     ```php
     require 'fungsi.php';
     require_once 'koneksi.php';
     ```
  2. **Menerima Data JSON** (Baris 21-23):
     ```php
     $inputJSON = file_get_contents('php://input');
     $data = json_decode($inputJSON, true);
     ```
     PHP membaca input stream mentah (*raw input stream*) JSON yang dikirim oleh Fetch API di `order.php` dan mengubahnya menjadi array asosiatif PHP menggunakan `json_decode`.
  3. **Inisiasi Transaksi Database (ACID Concept)** (Baris 43):
     ```php
     mysqli_begin_transaction($koneksi);
     ```
     Sistem POS ini menerapkan transaksi aman. Kita mematikan mode *autocommit* di database. Query-query penyimpanan di bawah hanya akan disimpan di memori sementara server sebelum disetujui. Jika di tengah jalan ada satu baris query yang error, seluruh database akan dikembalikan (*rollback*) agar data bersih.
  4. **try - catch Block & SQL Injection Prevention**:
     * **Langkah A: Hitung Ulang Total Belanja** (Baris 48-51):
       ```php
       $total_bayar = 0;
       foreach ($items as $item) {
           $total_bayar += ($item['price'] * $item['qty']);
       }
       ```
       *PENTING*: Demi keamanan, total harga dihitung ulang berdasarkan harga asli database server, bukan langsung percaya angka total bayar dari browser client yang rawan dimanipulasi peretas lewat browser console.
     * **Langkah B: Insert Header Pesanan** (Baris 56):
       ```php
       $query_header = "INSERT INTO pesanan (id_meja, total_bayar, status_pesanan) VALUES ($id_meja, $total_bayar, 'Pending')";
       ```
       Pesanan disimpan dengan status default `'Pending'`.
     * **Langkah C: Ambil ID Pesanan & Insert Detail (Snapshot Harga)** (Baris 63-85):
       ```php
       $id_pesanan_baru = mysqli_insert_id($koneksi); // Mengambil ID Auto Increment yang baru saja dibuat
       foreach ($items as $item) {
           $nama_menu = mysqli_real_escape_string($koneksi, $item['name']); // Sanitasi SQL Injection
           $harga = (int)$item['price'];
           $qty = (int)$item['qty'];
           $query_detail = "INSERT INTO detail_pesanan (id_pesanan, id_menu, nama_menu_snapshot, harga_snapshot, jumlah) 
                            VALUES ($id_pesanan_baru, $id_menu, '$nama_menu', $harga, $qty)";
       }
       ```
       * **`mysqli_real_escape_string`**: Melindungi query database dari upaya eksploitasi peretasan *SQL Injection* apabila nama menu mengandung tanda petik satu (`'`).
       * **Snapshot Harga**: Kita menyimpan harga barang pada saat pesanan dibuat ke kolom `harga_snapshot` di tabel `detail_pesanan`. Dengan demikian, jika di kemudian hari admin mengubah harga menu di dashboard, laporan keuangan transaksi historis di masa lalu tidak akan kacau/berubah nilainya.
     * **Langkah D: Commit & Error Handling** (Baris 89-109):
       ```php
       mysqli_commit($koneksi); // Simpan permanen jika sukses semua
       ...
       } catch (Exception $e) {
           mysqli_rollback($koneksi); // Batalkan semua jika ada satu saja kegagalan
           echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
       }
       ```
       Jika seluruh loop selesai tanpa kendala, `mysqli_commit` dipanggil untuk mengunci data secara permanen di database. Namun, jika ada satu baris record detail yang gagal (misal: listrik padam di tengah transaksi), program akan langsung loncat ke blok `catch`, menjalankan `mysqli_rollback` untuk menghapus bersih data transaksi setengah jadi tersebut, lalu mengembalikan respons error JSON.

---

### **6. `user/poll_orders.php` — API Pendeteksi Antrean Baru**
* **Tanggung Jawab**: Menyediakan endpoint API asinkron super ringan untuk melayani pengecekan status pesanan aktif dari monitor kasir secara periodik.
* **Alur Logika Kode**:
  1. **Require Koneksi**: Membuka koneksi database via `require_once 'koneksi.php'`.
  2. **Query Hitung Baris Ringan** (Baris 27-28):
     ```php
     $result = mysqli_query($koneksi, "SELECT COUNT(*) AS jumlah FROM pesanan WHERE status_pesanan = 'Pending'");
     $row    = mysqli_fetch_assoc($result);
     ```
     API ini dirancang sangat hemat resource. Alih-alih menarik seluruh data antrean meja yang memakan bandwidth besar, API ini hanya melakukan penghitungan angka agregat sederhana (`COUNT(*)`) berapa jumlah antrean pending saat ini di database.
  3. **Output JSON**:
     ```php
     echo json_encode([
         'pending_count' => (int) $row['jumlah'],
         'timestamp'     => time()
     ]);
     ```
     Angka tersebut kemudian dikirim kembali ke JavaScript Kasir dalam format JSON super kecil (~30 byte).

---

### **7. `user/kasir.php` — Layar Monitor & Kendali Kasir Dapur**
* **Tanggung Jawab**: Menampilkan seluruh pesanan aktif berstatus `'Pending'`, menjalankan mesin AJAX Polling otomatis untuk memantau orderan baru masuk, dan menyediakan kontrol konfirmasi pembayaran.
* **Alur Logika Kode (PHP & AJAX Polling)**:
  1. **Require Core**: Memuat `fungsi.php` dan `koneksi.php` secara teratur.
  2. **Ubah Status Pesanan (Penyelesaian Pembayaran)** (Baris 17-27):
     ```php
     if (isset($_POST['selesaikan_order'])) {
         $id_pesanan = (int)$_POST['id_pesanan'];
         mysqli_query($koneksi, "UPDATE pesanan SET status_pesanan = 'Selesai' WHERE id_pesanan = $id_pesanan");
         header("Location: kasir.php");
         exit;
     }
     ```
     Jika Kasir mengklik tombol *"Tandai Selesai"*, form HTML akan mengirimkan data POST. PHP mengeksekusi perintah SQL `UPDATE` untuk mengubah status pesanan dari `'Pending'` menjadi `'Selesai'` di database. Halaman kemudian di-refresh kembali sehingga pesanan tersebut hilang dari daftar antrean aktif.
  3. **Mengambil Data Antrean Aktif**:
     ```php
     $orders = query("SELECT p.*, m.nama_meja FROM pesanan p JOIN meja m ON p.id_meja = m.id_meja WHERE p.status_pesanan = 'Pending' ORDER BY p.waktu_pesan DESC");
     ```
     Menggunakan relasi `JOIN` untuk mencocokkan ID meja di transaksi dengan nama meja riil, lalu diurutkan dari pesanan yang paling baru masuk.
  4. **AJAX Polling System (Pengganti Firebase FCM di Browser)** (Baris 191-240):
     ```javascript
     async function pollOrders() {
         try {
             const response = await fetch('poll_orders.php');
             const data = await response.json();
             const currentCount = data.pending_count;

             if (currentCount !== lastKnownCount) {
                 location.reload(); // Perubahan terdeteksi! Reload antrean
             } else {
                 setStatusOnline(); // Tetap siaga
             }
         } catch (err) {
             setStatusOffline(); // Hubungan server terputus
         }
     }
     setInterval(pollOrders, 8000); // Eksekusi berulang setiap 8 detik
     ```
     * Halaman memuat antrean awal dan menyimpan angka jumlah antrean di variabel JavaScript `lastKnownCount`.
     * Menggunakan fungsi native JavaScript `setInterval()`, browser kasir akan diam-diam menembakkan request ke `poll_orders.php` setiap 8 detik secara asinkron di latar belakang (*asynchronously in background*).
     * Jika respons angka antrean dari server (`currentCount`) berbeda dengan angka yang dipegang browser (`lastKnownCount`), kode langsung mengeksekusi `location.reload()` secara otomatis untuk memuat ulang daftar antrean baru di layar kasir secara *real-time*.
     * **Error Catching (Status Indicator)**: Jika server lokal tiba-tiba mati (misal: Apache XAMPP terhenti), blok `catch` akan menangkap kesalahan koneksi tersebut dan mengubah warna dot indikator menjadi kuning berkedip dengan teks status *"Mencoba Ulang..."* sebagai peringatan visual kepada Kasir.

---

# **BAGIAN 3: BEDAH ALUR KODE MODUL ADMIN (MANAGEMENT PANEL)**

Modul Admin terdiri dari 11 berkas (beberapa di antaranya berupa form visual). Berikut adalah rincian fungsionalitas kode baris-demi-baris file intinya:

```
                      ┌───────────────┐
                      │   login.php   │
                      └───────┬───────┘
                              │ (session_start)
                      ┌───────▼───────┐
                      │   fungsi.php  │ (Core CRUD & Bcrypt)
                      └───────┬───────┘
                              │
                      ┌───────▼───────┐
                      │   index.php   │ (Dashboard Utama)
                      └───────┬───────┘
         ┌────────────────────┼────────────────────┐
         │                    │                    │
   ┌─────▼─────┐        ┌─────▼─────┐        ┌─────▼─────┐
   │  add.php  │        │ edit.php  │        │delete.php │
   └───────────┘        └───────────┘        └─────┬─────┘
                                                   │ (unlink image)
                                             ┌─────▼─────┐
                                             │ cetakPDF  │
                                             └───────────┘
```

---

### **1. `admin/fungsi.php` — Otak Operasional Admin (CRUD & Security)**
* **Tanggung Jawab**: Menyediakan koneksi utama database khusus modul admin dan mendefinisikan kumpulan fungsi master CRUD menu, upload foto menu, sanitasi kata sandi, serta registrasi akun admin baru.
* **Alur Logika Kode**:
  1. **Koneksi Database Admin** (Baris 15-22):
     ```php
     $koneksi = mysqli_connect("localhost", "root", "", "nasgorcakmoldb");
     ```
     Koneksi dibuka langsung di modul admin demi keandalan operasional secara independen.
  2. **Fungsi Registrasi Akun Baru (`registrasi`) dengan Bcrypt** (Baris 59-86):
     ```php
     function registrasi($data) {
         global $koneksi;
         $username = strtolower(stripslashes($data["username"]));
         $password = mysqli_real_escape_string($koneksi, $data["password"]);
         ...
         // Enkripsi kata sandi menggunakan Bcrypt
         $password = password_hash($password, PASSWORD_DEFAULT);

         mysqli_query($koneksi, "INSERT INTO user VALUES(NULL, '$username', '$password', '$nama')");
         return mysqli_affected_rows($koneksi);
     }
     ```
     * **`stripslashes` & `strtolower`**: Menghapus karakter backslash tidak perlu dan memaksa username menjadi huruf kecil demi konsistensi data login.
     * **`password_hash`**: Merupakan standar industri tepercaya untuk enkripsi. Fungsi ini mengonversi kata sandi teks polos (misal: *"cakmol123"*) menjadi barisan string hash acak Bcrypt satu arah yang mustahil didekripsi kembali. Sangat aman dari risiko kebocoran data jika file database dicuri.
  3. **Fungsi Upload Gambar Menu (`upload`)** (Baris 140-184):
     ```php
     function upload() {
         $namaFile = $_FILES['gambar']['name'];
         $ukuranFile = $_FILES['gambar']['size'];
         $tmpName = $_FILES['gambar']['tmp_name'];
         ...
         $ekstensiGambar = explode('.', $namaFile);
         $ekstensiGambar = strtolower(end($ekstensiGambar));
         
         // Validasi format file
         if( !in_array($ekstensiGambar, ['jpg', 'jpeg', 'png']) ) { ... }
         // Validasi ukuran file (Max 2MB)
         if( $ukuranFile > 2000000 ) { ... }

         // Generate nama file baru secara acak unik
         $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
         move_uploaded_file($tmpName, '../img/' . $namaFileBaru);
         return $namaFileBaru;
     }
     ```
     * **Security Filter**: Membaca nama file, memisahkan ekstensi file menggunakan `explode`, lalu memvalidasinya. Sistem hanya mengizinkan berkas berformat JPG, JPEG, dan PNG, serta membatasi ukuran maksimal 2MB untuk mencegah peretas mengunggah skrip berbahaya (*shell script php*).
     * **`uniqid()`**: Fungsi ini membuat nama file acak berbasis milidetik waktu server (contoh: `64a7c8b9d0.jpg`). Hal ini dilakukan agar file gambar menu yang diunggah tidak saling menimpa (*overwrite*) jika memiliki nama file bawaan yang sama dari HP admin (misal: `image.jpg`).
     * **`move_uploaded_file`**: Memindahkan file gambar dari direktori penyimpanan sementara (*temporary directory*) server Apache ke folder aset fisik proyek (`../img/`).
  4. **Fungsi Hapus Menu (`hapus`) beserta File Fisik** (Baris 110-123):
     ```php
     function hapus($id) {
         global $koneksi;
         // Cari nama file gambar yang akan dihapus dari DB
         $result = mysqli_query($koneksi, "SELECT gambar FROM menu WHERE id_menu = $id");
         $file = mysqli_fetch_assoc($result);
         
         // Hapus file fisik dari harddisk server
         if ($file['gambar'] != '') {
             unlink("../img/" . $file['gambar']);
         }
         
         mysqli_query($koneksi, "DELETE FROM menu WHERE id_menu = $id");
         return mysqli_affected_rows($koneksi);
     }
     ```
     * **`unlink()`**: Ini adalah fungsi bawaan PHP yang sangat proaktif. Sistem tidak hanya menghapus data record menu di database, tetapi juga menghapus file gambar fisik asli dari harddisk server. Ini penting untuk mencegah menumpuknya file gambar sampah yang dapat menghabiskan ruang penyimpanan (*disk storage*) server lokal.

---

### **2. `admin/login.php` — Autentikasi Pengelola**
* **Tanggung Jawab**: Menyediakan halaman log masuk bagi admin dengan pencocokan hash password.
* **Alur Logika Kode**:
  1. **Inisiasi PHP Session**: `session_start();` diaktifkan di baris teratas.
  2. **Verifikasi Kredensial**:
     ```php
     $result = mysqli_query($koneksi, "SELECT * FROM user WHERE username = '$username'");
     if( mysqli_num_rows($result) === 1 ) {
         $row = mysqli_fetch_assoc($result);
         if( password_verify($password, $row["password"]) ) {
             $_SESSION["login"] = true; // Set status login aktif
             header("Location: index.php");
             exit;
         }
     }
     ```
     * **`password_verify`**: Karena kata sandi disimpan dalam bentuk hash Bcrypt, kata sandi yang diketik oleh admin di form tidak bisa dicocokkan menggunakan string biasa. Fungsi `password_verify` akan melakukan kalkulasi algoritma kecocokan antara string masukan dengan hash database secara aman.
     * Jika cocok, variabel session `$_SESSION["login"] = true;` dibuat sebagai penanda hak akses sah.

---

### **3. `admin/logout.php` — Penghancur Sesi Aman**
* **Tanggung Jawab**: Mengakhiri masa aktif sesi login secara bersih untuk keamanan.
* **Alur Logika Kode**:
  1. **Destruksi Sesi**:
     ```php
     session_start();
     $_SESSION = []; // Kosongkan data array session
     session_unset(); // Hapus ikatan variabel
     session_destroy(); // Hancurkan sesi di memori server
     ```
     Menjamin bahwa data sesi login di sisi server telah terhapus total sehingga akses tidak dapat disalahgunakan kembali di browser.
  2. **Redirect**: Mengalihkan kembali pengguna secara otomatis ke halaman login.

---

### **4. `admin/index.php` — Dashboard Utama & Proteksi Bypass URL**
* **Tanggung Jawab**: Menampilkan menu-menu aktif dalam bentuk tabel data terstruktur bagi pengelola, menyediakan tombol aksi manipulasi data, dan menjaga keamanan hak akses halaman.
* **Alur Logika Kode**:
  1. **Proteksi Bypass URL** (Baris 13-18):
     ```php
     session_start();
     if( !isset($_SESSION["login"]) ) {
         header("Location: login.php");
         exit;
     }
     ```
     *PENTING*: Kode ini diletakkan di baris paling atas halaman. Jika ada pengguna ilegal yang mencoba mengakses langsung alamat URL panel admin ini (contoh: `localhost/warungcakmol-copy/admin/index.php`), kode PHP akan mendeteksi bahwa variabel session login tidak terdaftar, lalu langsung membatalkan pemuatan halaman secara paksa (`exit;`) dan melemparkannya kembali ke halaman login.
  2. **Mengambil & Menampilkan Data Menu**:
     ```php
     $menu = query("SELECT * FROM menu ORDER BY id_menu DESC");
     ```
     Mengambil semua data menu terbaru untuk ditampilkan per baris menggunakan pengulangan `foreach` lengkap dengan kolom foto menu, harga, deskripsi, level pedas, dan tombol manajemen Aksi.

---

### **5. `admin/add.php` — Form Penambah Menu**
* **Tanggung Jawab**: Menyediakan halaman form bagi admin untuk mendaftarkan menu hidangan baru.
* **Alur Logika Kode**:
  1. **Proteksi Session**: Melakukan verifikasi session aktif di baris teratas.
  2. **Pemrosesan Form POST**:
     ```php
     if( isset($_POST["submit"]) ) {
         if( tambah($_POST) > 0 ) {
             echo "<script>alert('Data berhasil ditambahkan!'); document.location.href = 'index.php';</script>";
         }
     }
     ```
     Ketika admin menekan tombol Simpan, form HTML ber-atribut `enctype="multipart/form-data"` (wajib untuk upload file) akan mengirimkan paket data POST ke PHP. PHP mengeksekusi fungsi `tambah($_POST)` yang memanggil fungsi `upload()` untuk mengurus file gambar terlebih dahulu sebelum menginput teks ke tabel `menu`.

---

### **6. `admin/edit.php` — Form Pengubah Menu Dinamis**
* **Tanggung Jawab**: Menyediakan form untuk mengupdate rincian menu makanan, termasuk penanganan penggantian gambar menu lama dengan gambar baru secara dinamis.
* **Alur Logika Kode**:
  1. **Pemanggilan Data Menu Lama**: PHP membaca parameter GET ID menu (`$id = $_GET["id"];`) untuk mengambil record menu lama di database dan menampilkannya sebagai *default value* di dalam input form, agar admin tinggal mengganti bagian yang diinginkan saja.
  2. **Logika Pergantian Gambar Pintar**:
     ```php
     // Di dalam fungsi ubah($data) di fungsi.php:
     if( $_FILES['gambar']['error'] === 4 ) {
         $gambar = $gambarLama; // Admin tidak mengganti foto, gunakan foto lama
     } else {
         $gambar = upload(); // Admin mengunggah foto baru
         unlink("../img/" . $gambarLama); // Hapus foto lama agar tidak jadi file sampah
     }
     ```
     Jika kolom input file kosong (kode error 4), sistem secara cerdas tetap mempertahankan file gambar lama. Namun, jika admin mengunggah file gambar baru, sistem akan memicu fungsi `upload()` untuk menyimpan gambar baru dan secara otomatis menghapus file gambar fisik lama (`unlink`) dari folder proyek untuk menghemat kapasitas harddisk server.

---

### **7. `admin/delete.php` — API Penghapus Data Menu**
* **Tanggung Jawab**: Mengurus penghapusan data menu berdasarkan parameter ID yang diterima.
* **Alur Logika Kode**:
  1. **GET ID**: Mengambil parameter ID dari baris data yang diklik di dashboard: `$id = $_GET["id"];`.
  2. **Eksekusi Penghapusan**:
     ```php
     if( hapus($id) > 0 ) {
         echo "<script>alert('Data berhasil dihapus!'); document.location.href = 'index.php';</script>";
     }
     ```
     PHP langsung memanggil fungsi `hapus($id)` dari `admin/fungsi.php` untuk memproses eksekusi query hapus serta pembersihan file fisik, kemudian mengembalikan admin kembali ke halaman dashboard utama.

---

### **8. `admin/cetakPDF.php` — Pembuat Dokumen Menu Formal**
* **Tanggung Jawab**: Mengonversi database master menu aktif menjadi dokumen format PDF formal yang dapat diunduh langsung oleh Admin.
* **Alur Logika Kode**:
  1. **Memuat Autoload Composer**:
     ```php
     require_once '../vendor/autoload.php';
     ```
     Memanggil generator dependency Composer untuk mengaktifkan pustaka eksternal FPDF/TCPDF/Dompdf yang telah dipasang di server lokal.
  2. **Pengambilan Data**: Membaca seluruh data master menu terdaftar dari database.
  3. **Rendering & Layouting**:
     Mengatur margin dokumen, merancang header surat resmi Warung Nasi Goreng Cak Mol, mendesain border tabel baris menu, menyisipkan data teks menu beserta format rupiah, lalu menghasilkan string biner mentah dokumen PDF ke browser pelanggan agar dapat dicetak secara fisik ke mesin printer.

---

## 💡 Ringkasan Kunci Jawaban Sidang yang Wajib Diingat:

* **Mengapa Pure AJAX Polling lebih aman daripada FCM?**
  Karena AJAX Polling 100% menggunakan infrastruktur internal server XAMPP lokal Anda tanpa perlu mendaftarkan akun di Google Firebase Console. Hal ini menutup celah kebocoran kunci API (*API Key Leakage*) dari pihak luar dan menjamin sistem tetap berfungsi dengan stabil walaupun internet global di warung sedang mati total.
* **Apa keuntungan Database Transaction (ACID) di process_order.php?**
  Mencegah integritas data yang rusak (korup). Data pesanan pelanggan tersimpan ke tabel utama `pesanan` dan tabel detail `detail_pesanan` dalam satu kesatuan operasi utuh. Jika salah satu gagal, semua dibatalkan (*rollback*) sehingga database bersih dari record setengah jadi.
* **Mengapa menggunakan Snapshot Harga di detail_pesanan?**
  Agar data keuangan historis tetap akurat di masa depan. Perubahan harga barang oleh Admin di katalog menu tidak akan mengubah total tagihan transaksi di masa lalu karena harganya telah terkunci aman di kolom snapshot detail pesanan.
* **Bagaimana Bcrypt menjamin keamanan kata sandi Admin?**
  Bcrypt adalah enkripsi hash satu arah dengan algoritma *salt* dinamis yang sangat kuat. Password di database disimpan dalam barisan string acak panjang yang tidak dapat didekripsi (diterjemahkan kembali) secara manual, sehingga sangat aman dari serangan peretasan database.

---
Panduan komprehensif berorientasi kode ini telah disimpan di direktori proyek Anda dengan nama berkas [Panduan_Lengkap_Alur_Kode_Cak_Mol.md](file:///c:/xampp/htdocs/all/warungcakmol-copy/Panduan_Lengkap_Alur_Kode_Cak_Mol.md). Anda dapat membacanya langsung di VS Code saat sedang mempelajari alur kode di hadapan dosen penguji perkuliahan RPL Anda! Sukses selalu!
