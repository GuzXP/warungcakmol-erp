# Panduan Development

## Prinsip perubahan

1. Baca instruksi di [`.github/copilot-instructions.md`](../.github/copilot-instructions.md), lalu buka dokumen arsitektur/alur/model data yang relevan dengan tugas.
2. Telusuri halaman pemanggil, endpoint yang menerima request, query database, dan halaman yang menampilkan hasil. Jangan mengubah satu sisi kontrak saja.
3. Buat perubahan sekecil mungkin sesuai pola PHP prosedural dan `mysqli` yang sudah digunakan. Hindari framework, dependency, abstraksi, atau konfigurasi baru tanpa kebutuhan yang jelas.
4. Jika perubahan mengubah alur bisnis, enum, schema, endpoint, atau setup, perbarui dokumen yang terkait.
5. Jangan mengedit `vendor/` atau `warungcakmol-copy-2/` untuk tugas aplikasi ini kecuali diminta eksplisit.

## Database dan input

- Gunakan prepared statement (`mysqli_prepare`, bind parameter) untuk nilai yang berasal dari request atau input pengguna.
- Nama tabel/kolom atau bagian SQL yang tidak dapat diparameterkan harus dipilih dari daftar nilai tetap yang diizinkan; jangan menerima fragmen SQL mentah dari request.
- Validasi method HTTP, bentuk/tipe input, ID, kuantitas, batas nilai, dan enum domain di server. Validasi JavaScript/HTML membantu UX, tetapi bukan batas keamanan.
- Ambil harga dan atribut katalog authoritative dari database. Jangan percaya `price`, `total_bayar`, nama menu, atau data lain yang dikirim browser.
- Gunakan transaksi DB untuk operasi multi-row yang harus berhasil/gagal sebagai satu kesatuan, seperti pembuatan header pesanan bersama seluruh detailnya. Pastikan rollback pada kegagalan.
- Gunakan hasil snapshot untuk membaca transaksi historis. Jangan membangun ulang histori dari nilai katalog yang bisa berubah.
- Perubahan schema harus mempertimbangkan data yang sudah ada, foreign key, dump SQL, semua query terkait, dan rencana penerapan lokal. Saat ini belum ada migration runner.

## Output, autentikasi, dan keamanan web

- Escape teks saat dikeluarkan ke konteks HTML. Helper `sanitize()` memakai `htmlspecialchars()` dan sesuai untuk teks HTML biasa; untuk konteks atribut, URL, JavaScript, atau CSS, gunakan encoding/validasi khusus konteks tersebut.
- Jangan merakit JavaScript/HTML dari input tidak tepercaya tanpa encoding yang sesuai. Hindari memasukkan nilai PHP mentah ke dalam event handler inline.
- Halaman admin/kasir yang memerlukan akses harus tetap menggunakan pemeriksaan sesi (`confirm_admin_login()` / `confirm_kasir_login()` sesuai modul).
- Aksi perubahan melalui form harus memeriksa token CSRF sebelum menulis data. Endpoint JSON yang mengubah data juga harus mempertimbangkan perlindungan CSRF bila memakai autentikasi berbasis cookie/session.
- Jangan menyimpan password plaintext; ikuti `password_hash()` dan `password_verify()`.
- Validasi upload di server: error upload, ukuran, tipe konten/file dan ekstensi, nama file yang dihasilkan aplikasi, lokasi simpan yang tepat, serta kegagalan pemindahan. Jangan menggunakan nama file kiriman pengguna sebagai path.
- Jangan menambahkan kredensial atau kunci rahasia ke source, log, dokumentasi, maupun respons error untuk pengguna.
- Tampilkan error yang aman kepada pengguna dan catat detail teknis secara internal bila diperlukan; jangan diam-diam mengubah kegagalan menjadi respons sukses.

## Kontrak halaman dan endpoint

- Sebelum mengubah endpoint, cari seluruh pemanggil `fetch()`/form. Pertahankan method HTTP, content type, nama field, bentuk JSON, kode status HTTP, dan penanganan error pemanggil atau perbarui keduanya dalam perubahan yang sama.
- Endpoint polling harus mempertahankan struktur data yang dipakai renderer UI serta menghindari cache respons yang sudah basi.
- Untuk alur pesanan, periksa checkout dine-in dan takeaway, API checkout, KDS, kasir, pembayaran, status meja, dan struk.
- Jangan mengasumsikan UI browser adalah sumber state durable. Simpan status bisnis di database dan gunakan browser hanya sebagai state sementara/UX.

## Perubahan frontend

- Gunakan stylesheet terkait di `css/`; halaman KDS masih memakai style dan JavaScript inline.
- Pertahankan tampilan responsif dan interaksi yang sudah ada. Untuk markup yang dirender dinamis, encode nilai data sebelum memasukkannya ke DOM.
- Ikuti pemuatan library/CDN yang sudah digunakan halaman; jangan menambah dependency frontend/build pipeline kecuali diminta dan kebutuhan telah dibuktikan.

## Pemeriksaan dan pengujian

Belum ditemukan test otomatis atau konfigurasi build/lint proyek. Untuk perubahan PHP:

1. Jalankan pemeriksaan sintaks pada file PHP yang berubah, jika PHP CLI tersedia:

   ```powershell
   php -l user\process_order.php
   ```

2. Uji halaman/API terkait pada Apache dengan database lokal yang sesuai. Jangan melakukan perubahan destruktif pada data lokal.
3. Untuk perubahan checkout/status, uji happy path dan validasi gagal pada dine-in dan takeaway, polling KDS, transisi status, pembayaran, status meja, serta cetak struk yang relevan.
4. Untuk perubahan admin/keuangan, uji sesi tidak login, token CSRF invalid, input valid, validasi gagal, dan hasil laporan.
5. Untuk perubahan schema, uji pada database pengembangan yang dapat dipulihkan; jangan menganggap impor ulang dump aman bagi database aktif.
6. Laporkan pemeriksaan yang dijalankan dan keterbatasan yang belum dapat diuji. Jangan menyatakan test lulus jika hanya pemeriksaan sintaks dilakukan.

## Checklist PR/perubahan

- [ ] Semua pemanggil dan penerima endpoint yang terdampak telah ditinjau.
- [ ] Input divalidasi server-side; SQL menggunakan parameter untuk nilai input.
- [ ] Perubahan sesi, CSRF, escaping output, dan upload mengikuti kebutuhan fitur.
- [ ] Status, tipe, relasi meja, dan kalkulasi transaksi tetap konsisten.
- [ ] Schema/dump dan dokumentasi diperbarui bila perubahan domain/schema memang dilakukan.
- [ ] Pemeriksaan sintaks dan uji alur yang relevan dijalankan atau keterbatasannya dijelaskan.
