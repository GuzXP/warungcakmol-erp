# Cak Mol POS

Sistem Point of Sale Warung Nasi Goreng Cak Mol untuk pemesanan mandiri dine-in dan takeaway, antrean dapur (KDS), kasir, serta administrasi menu dan keuangan. Aplikasi ini berjalan sebagai PHP prosedural dengan MySQL/MariaDB dan ditujukan untuk penggunaan lokal melalui Apache/XAMPP.

## Mulai cepat (XAMPP)

1. Letakkan folder proyek di bawah document root Apache. Contoh lokasi workspace:
   `C:\xampp\htdocs\all\warungcakmol-copy`
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buat database `nasgorcakmoldb` di MySQL/MariaDB dan impor [`nasgorcakmoldb (2).sql`](./nasgorcakmoldb%20%282%29.sql) menggunakan phpMyAdmin atau klien SQL. File ini adalah dump yang berisi schema dan data contoh; cadangkan database tujuan sebelum mengimpornya.
4. Periksa konfigurasi koneksi di [`includes/koneksi.php`](./includes/koneksi.php). Nilai bawaan proyek menggunakan host `localhost`, user `root`, password kosong, dan database `nasgorcakmoldb`. Sesuaikan hanya untuk lingkungan lokal yang digunakan.
5. Pastikan dependency Composer proyek tersedia. Dari root proyek, jalankan `composer install` bila `vendor/autoload.php` belum tersedia.
6. Buka:
   - Halaman pelanggan: `http://localhost/all/warungcakmol-copy/user/`
   - Login admin/kasir: `http://localhost/all/warungcakmol-copy/admin/login.php`
   - KDS: `http://localhost/all/warungcakmol-copy/kitchen/`

Path URL mengikuti lokasi folder proyek di bawah `htdocs`; sesuaikan bila folder dipindah. File koneksi mengakhiri request jika koneksi database gagal.

## Peta dokumentasi

- [Arsitektur aplikasi](./docs/ARCHITECTURE.md) — struktur, batas modul, dan integrasi antarbagian.
- [Alur bisnis](./docs/WORKFLOWS.md) — checkout, pemrosesan dapur, pembayaran, dan administrasi.
- [Model data](./docs/DATA_MODEL.md) — tabel, relasi, status, dan aturan data.
- [Panduan development](./docs/DEVELOPMENT.md) — konvensi perubahan, keamanan, dan verifikasi.
- [Instruksi agent](./.github/copilot-instructions.md) — ringkasan aturan yang dibaca agent saat bekerja di workspace.

## Batas cakupan

- `warungcakmol-copy-2/` adalah salinan lain dan bukan source aplikasi aktif yang didokumentasikan di sini.
- `vendor/` adalah dependency Composer; jangan mengedit atau membaca implementasinya untuk perubahan aplikasi biasa.
- Aplikasi saat ini mencakup POS dan pencatatan keuangan. Jangan menganggap modul inventori/stok tersedia.
- Detail fitur yang tertulis di dokumen ini menggambarkan implementasi yang ditemukan; bila source berubah, source aktif dan schema yang benar menjadi acuan dan dokumentasi perlu diperbarui.

## Teknologi dan batasan saat ini

- PHP prosedural, HTML, CSS, dan JavaScript; halaman server dan sebagian JavaScript berada dalam file PHP.
- MySQL/MariaDB melalui ekstensi `mysqli`.
- Dependency Composer root mencakup mPDF dan Kreait Firebase PHP. Ekspor PDF menggunakan mPDF. Telusuri pemakaian aktual sebelum mengubah integrasi dependency lainnya.
- Tidak ditemukan konfigurasi test otomatis, pipeline build frontend, atau sistem migrasi database. Lihat [Panduan development](./docs/DEVELOPMENT.md) untuk pemeriksaan yang tersedia dan checklist uji manual.
