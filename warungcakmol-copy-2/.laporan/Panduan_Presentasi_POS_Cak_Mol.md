# **PANDUAN PRESENTASI KOMPREHENSIF POS DINE-IN MANDIRI**
## **Warung Nasi Goreng Cak Mol Pulomas (Studi Kasus RPL)**

Dokumen ini disusun khusus sebagai panduan navigasi otak, pemahaman logika kode, dan strategi presentasi Anda di hadapan dosen penguji. Panduan ini dirancang secara berurutan (*step-by-step*) agar Anda dapat menjelaskan seluruh sistem secara tenang, ilmiah, dan meyakinkan tanpa melompat-lompat alur.

---

# **BAGIAN 1: STRATEGI ALUR PRESENTASI (Agar Tidak Lompat-Lompat)**

Saat dosen meminta Anda menjelaskan aplikasi, **jangan langsung mendemokan kode PHP**. Dosen penguji RPL ingin melihat struktur berpikir yang sistematis. Ikuti urutan presentasi berikut:

```
[1. Latar Belakang & Bisnis] ➔ [2. Topologi Jaringan & Folder] ➔ [3. Skema Database] ➔ [4. Flow Order Pelanggan] ➔ [5. Flow Polling Kasir] ➔ [6. CRUD Admin & Security]
```

1. **Latar Belakang Operasional**: Jelaskan masalah nyata Warung Cak Mol (antrean panjang, kertas nota hilang/basah di dapur, kasir salah hitung tagihan pakai kalkulator manual).
2. **Topologi Jaringan Lokal & Setup**: Jelaskan mengapa sistem ini berjalan secara offline di jaringan lokal Wi-Fi warung menggunakan XAMPP (untuk menjaga stabilitas operasional dari gangguan internet ISP luar).
3. **Struktur Database**: Tunjukkan tabel database dan jelaskan relasinya.
4. **Alur Transaksi Utama (Pelanggan)**: Demokan cara pelanggan memesan di meja via smartphone, kustomisasi pedas, hingga muncul struk digital.
5. **Alur Transaksi Utama (Kasir/Monitor Dapur)**: Tunjukkan bagaimana dapur menerima pesanan secara otomatis tanpa kertas lewat sistem AJAX Polling.
6. **Autentikasi & Keamanan Admin**: Tunjukkan panel admin untuk CRUD menu makanan dan bagaimana password dienkripsi Bcrypt di database.

---

# **BAGIAN 2: DEKODIFIKASI SETUP AWAL & ARSITEKTUR KODE**

Dosen sering kali menanyakan hal-hal dasar di root folder. Berikut adalah penjelasan untuk menjawab pertanyaan tersebut dengan gaya bahasa akademis:

### **1. Apa itu Composer (`composer.json` / `composer.lock`) dan folder `vendor`?**
* **Penjelasan untuk Dosen**: 
  > *"Composer adalah dependency manager untuk bahasa pemrograman PHP (serupa dengan npm pada Node.js atau pip pada Python). File `composer.json` mencatat daftar pustaka/library eksternal yang dibutuhkan oleh aplikasi ini, sedangkan `composer.lock` mengunci versi library tersebut agar konsisten di semua lingkungan komputer. Folder `vendor` berisi seluruh file fisik library yang diunduh secara otomatis oleh Composer—seperti library Dompdf/FPDF yang kami gunakan di sisi admin untuk memproses dan mencetak daftar menu ke format PDF."*
* **Mengapa dipisah?**: Memisahkan library eksternal di folder `vendor` menjaga agar codebase inti kita tetap bersih, teratur, dan tidak tercampur dengan kode pihak ketiga.

### **2. Kenapa ada file `style.css` terpisah, tapi ada inline style (tag `style=""`) di dalam PHP?**
* **Penjelasan untuk Dosen**: 
  > *"Kami menerapkan prinsip **Separation of Concerns** (Pemisahan Tugas). Seluruh desain layout global, tipografi, warna dasar, dan responsivitas diletakkan di file CSS eksternal (di dalam folder `css/`) agar kode lebih rapi dan konsisten.*
  > *Namun, kami menggunakan **inline style** di beberapa halaman PHP untuk memfasilitasi **styling dinamis yang nilainya berubah berdasarkan logika program**. Contohnya, status warna dot pulsing di `kasir.php` (hijau jika online, kuning jika mencoba ulang) atau delay staggered animasi pemuatan kartu menu di `order.php` yang nilainya dihitung secara dinamis per baris database menggunakan PHP."*

---

# **BAGIAN 3: BEDAH LOGIKA TABEL DATABASE (`nasgorcakmoldb`)**

Tunjukkan file `nasgorcakmoldb (2).sql` Anda. Ada 5 tabel inti di database Anda. Berikut cara menjelaskannya:

```
    [Meja] 1 ──── 0..* [Pesanan] 1 ──── 1..* [DetailPesanan] 0..* ──── 1 [Menu]
                                                    
                                [User] (Akun Admin)
```

1. **`user`**: Menyimpan kredensial admin (nama, username, password). Password di sini **tidak disimpan dalam bentuk teks polos (plain text)**, melainkan di-hash menggunakan algoritma Bcrypt demi keamanan data pengguna.
2. **`meja`**: Tabel master data statis berisi daftar meja makan (Meja 1, Meja 2) beserta status ketersediaannya (`enum('Kosong', 'Terisi')`).
3. **`menu`**: Tabel master data berisi daftar hidangan makanan/minuman yang aktif (nama menu, harga, deskripsi, kategori, gambar, badge).
4. **`pesanan` (Tabel Header Transaksi)**: Mencatat transaksi utama pelanggan, yaitu nomor meja yang memesan (`id_meja`), total tagihan belanja (`total_bayar`), waktu transaksi (`waktu_pesan`), dan status pesanan (`enum('Pending', 'Selesai')`).
5. **`detail_pesanan` (Tabel Detail Transaksi)**: Mencatat rincian menu apa saja yang dibeli di dalam satu nomor pesanan. Tabel ini berelasi dengan `pesanan` (`id_pesanan`) dan `menu` (`id_menu`), serta mencatat kuantitas (`jumlah`), tingkat kepedasan (`level_pedas`), dan catatan khusus (`catatan`).

---

# **BAGIAN 4: FLOW CODE STEP-BY-STEP (Navigasi Alur Utama)**

Mari kita telusuri alur aplikasi dari depan ke belakang. Navigasikan file-file berikut satu per satu di hadapan dosen penguji:

## **Langkah A: Pelanggan Masuk & Memilih Menu (`user/index.php`)**
* **Alur Penjelasan**: 
  1. Halaman ini adalah landing page pelanggan.
  2. Buka bagian atas kode PHP. Tunjukkan logika **Dynamic Grouping** (Pengelompokan Dinamis):
     ```php
     while($row = mysqli_fetch_assoc($result)) {
         $kategori = trim(html_entity_decode($row['kategori']));
         if(!isset($menuDataGrouped[$kategori])) { ... }
         $menuDataGrouped[$kategori]['items'][] = $menuItem;
     }
     ```
     > **Poin Penjelasan**: *"Di sini, PHP secara cerdas membaca seluruh baris data dari database. PHP tidak menampilkannya secara acak, melainkan mengelompokkannya secara otomatis berdasarkan nama kategorinya ke dalam array bertingkat. Hasilnya, di sisi frontend HTML, menu terbagi secara rapi per tab kategori secara otomatis tanpa perlu hardcode manual."*

---

## **Langkah B: Keranjang & Kustomisasi Rasa (`user/order.php`)**
* **Alur Penjelasan**:
  1. Halaman ini memfasilitasi proses pemesanan mandiri oleh pelanggan.
  2. Buka baris kode JavaScript bagian bawah. Tunjukkan bagaimana keranjang belanja dikelola sepenuhnya di browser menggunakan memori JavaScript melalui array bernama `cart`:
     ```javascript
     let cart = []; // Menyimpan item keranjang belanja
     ```
  3. Tunjukkan logika **Customization Level Pedas Dinamis** di fungsi `openCustomizeModal(item)`:
     ```javascript
     if(item.category.includes('Minuman') || item.category.includes('Pelengkap')) {
         spiceDiv.classList.add('hidden'); // Sembunyikan pedas jika minuman
     } else {
         spiceDiv.classList.remove('hidden');
     }
     ```
     > **Poin Penjelasan**: *"Sistem kami sangat peka terhadap kategori menu. Jika pelanggan memilih makanan, pemilih level pedas (0-4) akan muncul di layar. Namun, jika kategori menu mengandung kata 'Minuman' atau 'Pelengkap', kode JavaScript secara otomatis menyembunyikan pemilih pedas tersebut agar antarmuka tetap masuk akal bagi pengguna."*

---

## **Langkah C: Database Transaction & Keamanan API (`user/process_order.php`)**
*Ini adalah bagian teknis backend yang paling disukai dosen penguji. Jelaskan dengan sangat percaya diri:*

1. **Apa itu Database Transaction (ACID Concept)?**
   Tunjukkan blok kode `mysqli_begin_transaction($koneksi)`, `mysqli_commit($koneksi)`, dan blok `catch` berisi `mysqli_rollback($koneksi)`.
   * **Penjelasan untuk Dosen**: 
     > *"Kami menerapkan prinsip **Database Transaction** untuk menjaga konsistensi data transaksi (konsep ACID). Data pesanan pelanggan harus tersimpan secara utuh pada tabel header (`pesanan`) DAN detail (`detail_pesanan`). Jika query insert header sukses tetapi insert salah satu detail menu gagal (misal karena server mati di tengah jalan), maka sistem akan memicu Exception di blok `catch` dan menjalankan `mysqli_rollback()`. Ini membatalkan seluruh query sebelumnya agar tidak menghasilkan data sampah di database (pesanan tanpa menu)."*

2. **Price Re-calculation (Hitung Ulang Total Harga di Server)**:
   Tunjukkan bagaimana backend menghitung total bayar dengan melakukan looping harga item dari database, bukan langsung percaya angka total bayar yang dikirim dari browser pelanggan.
   * **Penjelasan untuk Dosen**: 
     > *"Kami tidak pernah percaya 100% pada data total harga yang dikirim dari client-side browser karena data tersebut rentan dimanipulasi oleh pengguna nakal lewat fitur Inspect Element di browser. Backend kami selalu melakukan penghitungan ulang total harga secara mandiri di sisi server berdasarkan daftar harga menu asli di database sebelum menyimpan nominal ke tabel pesanan."*

3. **Snapshot Harga (Pencatatan Riwayat Transaksi yang Aman)**:
   Tunjukkan kolom `nama_menu_snapshot` dan `harga_snapshot` pada tabel `detail_pesanan`.
   * **Penjelasan untuk Dosen**: 
     > *"Di tabel `detail_pesanan`, kami tidak hanya menyimpan ID menu, tetapi kami membuat **snapshot** berupa nama menu dan harga menu saat transaksi dilakukan. Tujuannya adalah untuk menjaga keaslian riwayat transaksi keuangan warung. Jika bulan depan admin menaikkan harga nasi goreng gila dari Rp20.000 menjadi Rp25.000, laporan keuangan transaksi bulan lalu tidak ikut berubah/kacau karena harganya telah terkunci aman di kolom snapshot detail pesanan."*

---

## **Langkah D: Monitor Kasir & AJAX Polling (`user/kasir.php` & `user/poll_orders.php`)**
*Ini adalah bagian pengganti Firebase FCM yang wajib Anda jelaskan mekanismenya:*

1. **Bagaimana sistem AJAX Polling bekerja?**
   Tunjukkan script JavaScript di bagian bawah `user/kasir.php` yang menjalankan fungsi `setInterval(pollOrders, 8000)`.
   * **Analogi Sederhana untuk Dosen**: 
     > *"AJAX Polling di kasir bekerja dengan analogi seperti kasir yang setiap 8 detik mengetuk pintu dapur (`poll_orders.php`) dan bertanya secara asinkron (di latar belakang tanpa mengganggu tampilan halaman kasir): **'Apakah ada pesanan pending yang baru masuk?'**. Jika API `poll_orders.php` mengembalikan angka jumlah pesanan pending yang berbeda dengan jumlah pesanan pending yang sedang tampil di layar kasir saat ini, browser kasir akan memicu fungsi `location.reload()` secara otomatis untuk memperbarui antrean pesanan di layar kasir secara instan."*
   * **Mengapa sistem ini jauh lebih unggul daripada Firebase FCM untuk skala UMKM lokal?**
     1. **Bebas API Key Leakage**: Tidak membutuhkan API Key kredensial eksternal di sisi client, sehingga 100% aman dari risiko kebocoran data.
     2. **100% Offline Standalone**: Tidak membutuhkan koneksi internet global ataupun setup SSL/HTTPS lokal yang rumit. Sistem bekerja sempurna hanya dengan localhost XAMPP di jaringan lokal warung.
     3. **Sangat Ringan**: Endpoint `poll_orders.php` dirancang sangat ringan karena hanya melakukan query hitung baris (`SELECT COUNT(*)`) pesanan pending, bukan memuat seluruh data besar, sehingga tidak membebani memori server.

---

## **Langkah E: Autentikasi & Keamanan CRUD Admin (`admin/fungsi.php`)**
*Tunjukkan bagian administrasi sistem untuk memamerkan keandalan proteksi sistem Anda:*

1. **Password Hashing (Enkripsi Satu Arah Bcrypt)**:
   Tunjukkan fungsi `registrasi($data)` dan cari baris penggunaan `password_hash()`:
   ```php
   $password = password_hash($password, PASSWORD_DEFAULT);
   ```
   * **Penjelasan untuk Dosen**: 
     > *"Kami sangat mengutamakan keamanan data pengguna. Sistem POS ini tidak pernah menyimpan kata sandi admin dalam format teks polos di database demi mencegah kebocoran jika database diretas. Kami menggunakan algoritma **Bcrypt** melalui fungsi native PHP `password_hash()` untuk menghasilkan string hash acak satu arah yang tidak dapat didekripsi kembali."*

2. **Session Security (Proteksi Bypass URL)**:
   Tunjukkan baris kode paling atas di file dashboard admin (`admin/index.php`):
   ```php
   session_start();
   if( !isset($_SESSION["login"]) ) {
       header("Location: login.php");
       exit;
   }
   ```
   * **Penjelasan untuk Dosen**: 
     > *"Kami memproteksi halaman dashboard admin secara ketat. Jika ada pengguna ilegal yang mencoba masuk ke panel admin dengan cara mengetik URL dashboard secara langsung di browser (`localhost/warungcakmol-copy/admin/index.php`), kode PHP di baris teratas akan mendeteksi ketiadaan Session Login aktif, lalu membatalkan pemuatan halaman secara paksa (`exit`) dan mengalihkan pengguna kembali ke halaman form login."*

3. **CRUD & Logika Hapus Gambar Fisik (`unlink`)**:
   Tunjukkan fungsi `hapus($id)` di `admin/fungsi.php`:
   ```php
   unlink("../img/" . $file['gambar']);
   ```
   * **Penjelasan untuk Dosen**: 
     > *"Sistem CRUD kami dirancang efisien dalam manajemen penyimpanan (*storage management*). Ketika admin menghapus suatu menu dari database, sistem tidak hanya menghapus datanya dari tabel `menu`, tetapi juga secara proaktif menghapus file fisik foto menu tersebut dari folder penyimpanan server (`../img/`) menggunakan fungsi `unlink()`. Hal ini mencegah menumpuknya file gambar sampah di harddisk server lokal warung."*

---

# **BAGIAN 5: CARA MENDEMOKAN APLIKASI DI DEPAN DOSEN**

Saat demo langsung, ikuti skenario runut berikut untuk meminimalkan kendala teknis:

1. **Buka XAMPP**: Pastikan Apache dan MySQL dalam kondisi *Running*.
2. **Buka Monitor Kasir**: Akses `localhost/warungcakmol-copy/user/kasir.php` di salah satu tab browser PC Anda. Tunjukkan tulisan berdenyut hijau **"Monitor Aktif"**. Tunjukkan bahwa antrean saat ini dalam keadaan kosong (empty state) atau berisi beberapa pesanan pending.
3. **Simulasikan Pesanan Pelanggan**: Buka tab browser baru (atau gunakan mode Responsive Web Design/Inspect Mobile View di browser) dan akses `localhost/warungcakmol-copy/user/index.php`.
4. **Lakukan Pemesanan Dine-In**: 
   * Buka menu pemesanan, tambahkan *Nasi Goreng Gila* ke keranjang.
   * Atur tingkat pedas ke Level 3, tambah catatan *"Kerupuk dibanyakin"*.
   * Tambahkan *Teh Botol* (tunjukkan ke dosen bahwa tingkat pedas otomatis hilang saat memilih minuman).
5. **Kirim Pesanan**: Klik *"Konfirmasi Pesanan"*. Tunjukkan **Animasi Memasak** selama 3 detik, lalu tunjukkan **Struk Digital** yang terbit secara dinamis.
6. **Tunjukkan Efek Polling Real-time**: 
   * Segera beralih ke tab monitor kasir (`kasir.php`) yang Anda buka sebelumnya.
   * **Tunggu maksimal 8 detik**. Tunjukkan kepada dosen bahwa layar monitor kasir otomatis ter-refresh dan memunculkan pesanan baru dari meja pelanggan tersebut di posisi teratas tanpa Anda sentuh sama sekali!
7. **Selesaikan Transaksi**: Klik tombol *"Tandai Selesai"* di layar kasir, klik *"OK"* pada dialog konfirmasi. Tunjukkan kepada dosen bahwa antrean pesanan tersebut langsung hilang dari monitor aktif dan statusnya tersimpan dengan aman sebagai `'Selesai'` di database.

---

### **Navigasi Otak: Kunci Menghadapi Pertanyaan Dosen**
> * **Dosen**: *"Kenapa pakai XAMPP lokal, kenapa gak dihosting saja di awan/cloud?"*
> * **Anda**: *"Untuk skala UMKM warung tenda fisik seperti Nasi Goreng Cak Mol, keandalan koneksi internet di lapangan tidak bisa dijamin 100%. Jika kita menggunakan cloud hosting, saat internet warung terputus, seluruh operasional pemesanan pelanggan dan monitoring dapur kasir akan mati total. Dengan jaringan web lokal via Wi-Fi lokal, warung tetap dapat melayani POS Dine-In dengan stabil dan instan 24 jam tanpa bergantung pada ISP eksternal."*

---

Panduan ini telah tersimpan di direktori kerja Anda dengan nama [Panduan_Presentasi_POS_Cak_Mol.md](file:///c:/xampp/htdocs/all/warungcakmol-copy/Panduan_Presentasi_POS_Cak_Mol.md). Silakan buka file tersebut di VS Code saat Anda menavigasi berkas-berkas kode aplikasi Anda agar logika program ini terpetakan secara sempurna di otak Anda! 

Semoga presentasi tugas rekayasa perangkat lunak Anda berjalan lancar dan sukses mendapatkan nilai A+!
