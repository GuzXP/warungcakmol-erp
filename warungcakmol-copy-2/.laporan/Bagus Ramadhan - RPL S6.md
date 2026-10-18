**TUGAS REKAYASA PERANGKAT LUNAK**

**Sistem Informasi Klinik IBN**

Disusun Oleh:

Bagus Ramadhan

2491051003

**Program Studi Rekayasa Perangkat Lunak**

Institut Bisnis Nusantara (IBN)

2024 / 2025

- **Studi Kasus: Sistem Informasi Klinik IBN**

IBN akan membuat Klinik Kesehatan untuk Keluarga Mahasiswa, Dosen, dan Karyawannya. Sebagai akademisi, Rektor IBN menginginkan pendaftaran pasien, Rekam Medis, obat-obatan, dan Transaksi Pembayaran dibuatkan secara komputerisasi.

**Alur Sistem Usulan**

**a. Pendaftaran**

\- Pasien daftar secara online

\- Admin mencatat pasien baru

**b. Pemeriksaan**

\- Dokter menginput Rekam Medis

**c. Apotek**

\- Petugas apotek melihat resep dari dokter dan menyiapkan obat

**d. Kasir**

\- Pembayaran berdasarkan Tindakan dan obat

\- Pencetakan kuitansi

**e. Dewan Kesehatan (Pimpinan/Manajemen)**

\- Laporan keuangan

\- Laporan obat-obatan

\- Laporan pasien

- **Analisa Kebutuhan**

**A. Aktor yang Terlibat**

| **No** | **Aktor**           | **Peran dalam Sistem**                                   |
| ------ | ------------------- | -------------------------------------------------------- |
| 1      | **Pasien**          | Melakukan pendaftaran kunjungan klinik secara online     |
| 2      | **Admin**           | Mencatat data pasien baru dan mengelola data pendaftaran |
| 3      | **Dokter**          | Menginput rekam medis dan membuat resep obat             |
| 4      | **Petugas Apotek**  | Melihat resep dokter dan menyiapkan obat                 |
| 5      | **Kasir**           | Memproses pembayaran dan mencetak kuitansi               |
| 6      | **Dewan Kesehatan** | Mengakses laporan keuangan, obat, dan pasien             |

**B. Kebutuhan Fungsional**

| **Kode**                            | **Kebutuhan Fungsional**                                             |
| ----------------------------------- | -------------------------------------------------------------------- |
| **Modul Pendaftaran**               |                                                                      |
| **F-01**                            | Sistem menyediakan form pendaftaran pasien secara online             |
| **F-02**                            | Sistem memvalidasi data pasien (NIK, nama, status keanggotaan IBN)   |
| **F-03**                            | Admin dapat mencatat dan mengelola data pasien baru                  |
| **F-04**                            | Sistem menghasilkan nomor antrian atau nomor rekam medis otomatis    |
| **Modul Pemeriksaan**               |                                                                      |
| **F-05**                            | Dokter dapat menginput rekam medis pasien (diagnosis, tindakan)      |
| **F-06**                            | Dokter dapat membuat resep obat untuk pasien                         |
| **F-07**                            | Sistem menyimpan histori rekam medis per pasien                      |
| **Modul Apotek**                    |                                                                      |
| **F-08**                            | Petugas apotek dapat melihat resep dari dokter secara digital        |
| **F-09**                            | Petugas apotek dapat mengupdate status ketersediaan obat             |
| **F-10**                            | Sistem mengelola stok obat (tambah, kurang, cek stok)                |
| **Modul Kasir / Pembayaran**        |                                                                      |
| **F-11**                            | Kasir dapat memproses pembayaran berdasarkan tindakan medis dan obat |
| **F-12**                            | Sistem menghitung total biaya secara otomatis                        |
| **F-13**                            | Sistem mencetak kuitansi pembayaran                                  |
| **Modul Laporan (Dewan Kesehatan)** |                                                                      |
| **F-14**                            | Sistem menghasilkan laporan keuangan (pemasukan per periode)         |
| **F-15**                            | Sistem menghasilkan laporan obat-obatan (penggunaan & stok)          |
| **F-16**                            | Sistem menghasilkan laporan data pasien (kunjungan, diagnosis)       |

**C. Kebutuhan Non-Fungsional**

| **Kode**  | **Kategori**       | **Kebutuhan Non-Fungsional**                                                                    |
| --------- | ------------------ | ----------------------------------------------------------------------------------------------- |
| **NF-01** | **Keamanan**       | Setiap aktor memiliki akun login dengan hak akses berbeda (role-based access)                   |
| **NF-02** | **Keamanan**       | Data rekam medis pasien dienkripsi dan hanya dapat diakses oleh aktor yang berwenang            |
| **NF-03** | **Ketersediaan**   | Sistem dapat diakses 24 jam untuk pendaftaran online pasien                                     |
| **NF-04** | **Performa**       | Sistem merespons permintaan pengguna dalam waktu kurang dari 3 detik                            |
| **NF-05** | **Kegunaan**       | Antarmuka sistem mudah digunakan oleh pengguna non-teknis (dokter, kasir, apotek)               |
| **NF-06** | **Keandalan**      | Sistem memiliki uptime minimal 99% selama jam operasional klinik                                |
| **NF-07** | **Skalabilitas**   | Sistem mampu menangani penambahan jumlah pasien, dokter, dan transaksi tanpa penurunan performa |
| **NF-08** | **Pemeliharaan**   | Sistem mudah diperbarui oleh tim IT IBN tanpa mengganggu operasional                            |
| **NF-09** | **Kompatibilitas** | Sistem dapat diakses melalui browser modern dan perangkat mobile                                |
| **NF-10** | **Legalitas**      | Pengelolaan data pasien mengacu pada regulasi privasi data yang berlaku di Indonesia            |

**3\. Perancangan Sistem**

**A. Use Case Diagram**

(Use Case Diagram terlampir)

**B. Deskripsi Use Case**

| **No**    | **Nama Use Case**             | **Aktor**       | **Deskripsi**                                                                                                                   | **Relasi**                                                         |
| --------- | ----------------------------- | --------------- | ------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------ |
| **UC-01** | **Daftar Pasien Online**      | Pasien          | Pasien melakukan pendaftaran kunjungan klinik secara online dengan mengisi form yang tersedia di sistem                         | «include» UC-05 Generate Nomor Antrian                             |
| **UC-02** | **Login Sistem**              | Semua Aktor     | Seluruh pengguna sistem melakukan autentikasi dengan memasukkan username dan password untuk mengakses fitur sesuai hak aksesnya | \-                                                                 |
| **UC-03** | **Catat Pasien Baru**         | Admin           | Admin mencatat dan menyimpan data pasien baru yang mendaftar langsung ke klinik secara offline                                  | «extend» UC-04 Kelola Data Pasien                                  |
| **UC-04** | **Kelola Data Pasien**        | Admin           | Admin melakukan pengelolaan data pasien yang sudah terdaftar meliputi edit dan hapus data                                       | \-                                                                 |
| **UC-05** | **Generate Nomor Antrian**    | Sistem          | Sistem secara otomatis membuat dan menerbitkan nomor antrian setelah pendaftaran pasien berhasil tersimpan                      | \-                                                                 |
| **UC-06** | **Lihat Data Pasien**         | Dokter          | Dokter melihat informasi lengkap pasien yang akan diperiksa berdasarkan daftar antrian hari ini                                 | «extend» UC-09 Lihat Histori Rekam Medis                           |
| **UC-07** | **Input Rekam Medis**         | Dokter          | Dokter menginput hasil pemeriksaan berupa diagnosis dan tindakan medis ke dalam sistem rekam medis pasien                       | «include» UC-08 Buat Resep Obat                                    |
| **UC-08** | **Buat Resep Obat**           | Dokter          | Dokter membuat resep obat yang berisi nama obat, dosis, dan aturan pakai sebagai bagian dari proses pemeriksaan                 | \-                                                                 |
| **UC-09** | **Lihat Histori Rekam Medis** | Dokter          | Dokter melihat riwayat rekam medis pasien dari kunjungan-kunjungan sebelumnya sebagai referensi pemeriksaan                     | \-                                                                 |
| **UC-10** | **Lihat Resep Dokter**        | Petugas Apotek  | Petugas apotek melihat resep obat yang dikirimkan dokter secara digital untuk segera diproses                                   | \-                                                                 |
| **UC-11** | **Siapkan Obat**              | Petugas Apotek  | Petugas apotek menyiapkan obat sesuai resep dokter dan memperbarui status resep menjadi siap diserahkan                         | «include» UC-10 Lihat Resep Dokter «extend» UC-12 Kelola Stok Obat |
| **UC-12** | **Kelola Stok Obat**          | Petugas Apotek  | Petugas apotek mengelola data stok obat meliputi penambahan stok, pengurangan, dan pengecekan ketersediaan                      | \-                                                                 |
| **UC-13** | **Hitung Total Biaya**        | Sistem          | Sistem menghitung secara otomatis total biaya yang harus dibayar pasien berdasarkan tindakan medis dan obat yang diberikan      | \-                                                                 |
| **UC-14** | **Proses Pembayaran**         | Kasir           | Kasir memproses transaksi pembayaran pasien berdasarkan rincian biaya yang telah dihitung sistem                                | «include» UC-13 Hitung Total Biaya «include» UC-15 Cetak Kuitansi  |
| **UC-15** | **Cetak Kuitansi**            | Kasir           | Kasir mencetak kuitansi sebagai bukti pembayaran yang sah untuk diserahkan kepada pasien                                        | \-                                                                 |
| **UC-16** | **Lihat Laporan Keuangan**    | Dewan Kesehatan | Dewan Kesehatan melihat laporan pemasukan klinik berdasarkan periode yang dipilih untuk keperluan evaluasi manajemen            | \-                                                                 |
| **UC-17** | **Lihat Laporan Obat**        | Dewan Kesehatan | Dewan Kesehatan melihat laporan penggunaan dan ketersediaan stok obat klinik secara periodik                                    | \-                                                                 |
| **UC-18** | **Lihat Laporan Pasien**      | Dewan Kesehatan | Dewan Kesehatan melihat laporan statistik kunjungan dan data pasien klinik secara periodik                                      | \-                                                                 |

**Keterangan Relasi**

- **«include» - Relasi Wajib**

| **Use Case Utama**      | **Include ke**               | **Alasan**                                              |
| ----------------------- | ---------------------------- | ------------------------------------------------------- |
| UC-01 Daftar Online     | UC-05 Generate Nomor Antrian | Setiap pendaftaran **pasti** menghasilkan nomor antrian |
| UC-07 Input Rekam Medis | UC-08 Buat Resep Obat        | Setiap input rekam medis **pasti** disertai resep       |
| UC-14 Proses Pembayaran | UC-13 Hitung Total Biaya     | Pembayaran **pasti** membutuhkan kalkulasi biaya        |
| UC-14 Proses Pembayaran | UC-15 Cetak Kuitansi         | Setelah bayar **pasti** dicetak kuitansi                |
| UC-11 Siapkan Obat      | UC-10 Lihat Resep            | Menyiapkan obat **pasti** harus lihat resep dulu        |

- **«extend» - Relasi Opsional**

| **Use Case Utama**      | **Extend ke**                   | **Alasan**                                              |
| ----------------------- | ------------------------------- | ------------------------------------------------------- |
| UC-03 Catat Pasien Baru | UC-04 Kelola Data Pasien        | Admin **bisa** mengedit/hapus data setelah mencatat     |
| UC-06 Lihat Data Pasien | UC-09 Lihat Histori Rekam Medis | Dokter **bisa** melihat histori, tidak selalu wajib     |
| UC-11 Siapkan Obat      | UC-12 Kelola Stok Obat          | Update stok dilakukan **jika** obat diambil dari gudang |

**Ringkasan Use Case per Aktor**

| **Aktor**       | **Jumlah UC** | **Use Case**                      |
| --------------- | ------------- | --------------------------------- |
| Pasien          | 2             | UC-01, UC-02                      |
| Admin           | 3             | UC-02, UC-03, UC-04               |
| Dokter          | 5             | UC-02, UC-06, UC-07, UC-08, UC-09 |
| Petugas Apotek  | 4             | UC-02, UC-10, UC-11, UC-12        |
| Kasir           | 4             | UC-02, UC-13, UC-14, UC-15        |
| Dewan Kesehatan | 4             | UC-02, UC-16, UC-17, UC-18        |

**C. Skenario Use Case**

**Nama Use Case : Login Sistem (UC-02)**

Skenario:

| **Aksi Aktor**                                  | **Reaksi Sistem**                                                            |
| ----------------------------------------------- | ---------------------------------------------------------------------------- |
| **Skenario Normal**                             |                                                                              |
| 1\. Memasukkan username dan password            |                                                                              |
|                                                 | 2\. Memeriksa valid tidaknya data masukan dengan memeriksa ke tabel pengguna |
|                                                 | 3\. Menampilkan dashboard sesuai role pengguna                               |
| **Skenario Alternatif**                         |                                                                              |
| 1\. Memasukkan username dan password            |                                                                              |
|                                                 | 2\. Memeriksa valid tidaknya data masukan dengan memeriksa ke tabel pengguna |
|                                                 | 3\. Menampilkan pesan login tidak valid                                      |
| 4\. Memasukkan username dan password yang benar |                                                                              |
|                                                 | 5\. Memeriksa valid tidaknya data masukan dengan memeriksa ke tabel pengguna |
|                                                 | 6\. Menampilkan dashboard sesuai role pengguna                               |

**Nama Use Case : Daftar Pasien Online (UC-01)**

Skenario:

| **Aksi Aktor**                                            | **Reaksi Sistem**                                                      |
| --------------------------------------------------------- | ---------------------------------------------------------------------- |
| **Skenario Normal**                                       |                                                                        |
|                                                           | 1\. Memeriksa status login pasien                                      |
| 2\. Memilih menu pendaftaran online                       |                                                                        |
|                                                           | 3\. Menampilkan form pendaftaran                                       |
| 4\. Mengisi keluhan, memilih dokter dan tanggal kunjungan |                                                                        |
| 5\. Mengklik tombol Daftar                                |                                                                        |
|                                                           | 6\. Memeriksa kelengkapan data masukan                                 |
|                                                           | 7\. Menyimpan data pendaftaran ke basis data                           |
|                                                           | 8\. Meng-generate nomor antrian secara otomatis                        |
|                                                           | 9\. Menampilkan konfirmasi pendaftaran berhasil beserta nomor antrian  |
| **Skenario Alternatif**                                   |                                                                        |
|                                                           | 1\. Memeriksa status login pasien                                      |
| 2\. Memilih menu pendaftaran online                       |                                                                        |
|                                                           | 3\. Menampilkan form pendaftaran                                       |
| 4\. Mengisi data pendaftaran tidak lengkap                |                                                                        |
| 5\. Mengklik tombol Daftar                                |                                                                        |
|                                                           | 6\. Memeriksa kelengkapan data masukan                                 |
|                                                           | 7\. Menampilkan pesan bahwa data masukan tidak lengkap                 |
| 8\. Melengkapi data pendaftaran yang kosong               |                                                                        |
|                                                           | 9\. Memeriksa kelengkapan data masukan                                 |
|                                                           | 10\. Menyimpan data pendaftaran ke basis data                          |
|                                                           | 11\. Menampilkan konfirmasi pendaftaran berhasil beserta nomor antrian |

**Nama Use Case : Catat Pasien Baru (UC-03)**

Skenario:

| **Aksi Aktor**                                          | **Reaksi Sistem**                                         |
| ------------------------------------------------------- | --------------------------------------------------------- |
| **Skenario Normal**                                     |                                                           |
|                                                         | 1\. Memeriksa status login admin                          |
| 2\. Memilih menu tambah pasien baru                     |                                                           |
|                                                         | 3\. Menampilkan form input data pasien                    |
| 4\. Mengisi data pasien (nama, NIK, status IBN, kontak) |                                                           |
| 5\. Mengklik tombol Simpan                              |                                                           |
|                                                         | 6\. Memeriksa valid tidaknya data masukan                 |
|                                                         | 7\. Menyimpan data pasien baru ke basis data              |
|                                                         | 8\. Meng-generate nomor rekam medis secara otomatis       |
|                                                         | 9\. Menampilkan pesan data pasien berhasil disimpan       |
| **Skenario Alternatif**                                 |                                                           |
|                                                         | 1\. Memeriksa status login admin                          |
| 2\. Memilih menu tambah pasien baru                     |                                                           |
|                                                         | 3\. Menampilkan form input data pasien                    |
| 4\. Mengisi data pasien dengan NIK yang sudah terdaftar |                                                           |
| 5\. Mengklik tombol Simpan                              |                                                           |
|                                                         | 6\. Memeriksa valid tidaknya data masukan                 |
|                                                         | 7\. Menampilkan pesan bahwa NIK sudah terdaftar di sistem |
| 8\. Memperbaiki data NIK yang dimasukkan                |                                                           |
|                                                         | 9\. Memeriksa valid tidaknya data masukan                 |
|                                                         | 10\. Menyimpan data pasien baru ke basis data             |
|                                                         | 11\. Menampilkan pesan data pasien berhasil disimpan      |

**Nama Use Case : Kelola Data Pasien (UC-04)**

Skenario:

| **Aksi Aktor**                                 | **Reaksi Sistem**                                                     |
| ---------------------------------------------- | --------------------------------------------------------------------- |
| **Skenario Normal**                            |                                                                       |
|                                                | 1\. Memeriksa status login admin                                      |
| 2\. Membuka menu manajemen pasien              |                                                                       |
|                                                | 3\. Menampilkan daftar seluruh pasien terdaftar                       |
| 4\. Memilih pasien yang akan diubah datanya    |                                                                       |
|                                                | 5\. Menampilkan form edit dengan data pasien yang dipilih             |
| 6\. Mengubah data pasien yang diperlukan       |                                                                       |
| 7\. Mengklik tombol Simpan                     |                                                                       |
|                                                | 8\. Memeriksa valid tidaknya data masukan                             |
|                                                | 9\. Menyimpan perubahan data pasien ke basis data                     |
|                                                | 10\. Menampilkan pesan data berhasil diperbarui                       |
| **Skenario Alternatif**                        |                                                                       |
|                                                | 1\. Memeriksa status login admin                                      |
| 2\. Membuka menu manajemen pasien              |                                                                       |
|                                                | 3\. Menampilkan daftar seluruh pasien terdaftar                       |
| 4\. Memilih pasien yang akan dihapus           |                                                                       |
|                                                | 5\. Menampilkan pesan konfirmasi apakah data akan benar-benar dihapus |
| 6\. Mengklik pilihan tidak setuju data dihapus |                                                                       |
|                                                | 7\. Kembali ke halaman daftar pasien                                  |

**Nama Use Case : Input Rekam Medis (UC-07)**

Skenario:

| **Aksi Aktor**                                           | **Reaksi Sistem**                                              |
| -------------------------------------------------------- | -------------------------------------------------------------- |
| **Skenario Normal**                                      |                                                                |
|                                                          | 1\. Memeriksa status login dokter                              |
| 2\. Membuka menu daftar antrian pasien                   |                                                                |
|                                                          | 3\. Menampilkan daftar antrian pasien hari ini                 |
| 4\. Memilih pasien yang akan diperiksa                   |                                                                |
|                                                          | 5\. Menampilkan data pasien dan form rekam medis               |
| 6\. Mengisi diagnosis dan tindakan medis                 |                                                                |
| 7\. Mengisi resep obat (nama obat, dosis, aturan pakai)  |                                                                |
| 8\. Mengklik tombol Simpan Rekam Medis                   |                                                                |
|                                                          | 9\. Memeriksa kelengkapan data rekam medis                     |
|                                                          | 10\. Menyimpan rekam medis ke basis data                       |
|                                                          | 11\. Menyimpan resep obat ke basis data                        |
|                                                          | 12\. Mengirimkan notifikasi resep baru ke modul apotek         |
|                                                          | 13\. Menampilkan pesan rekam medis berhasil disimpan           |
| **Skenario Alternatif**                                  |                                                                |
|                                                          | 1\. Memeriksa status login dokter                              |
| 2\. Membuka menu daftar antrian pasien                   |                                                                |
|                                                          | 3\. Menampilkan daftar antrian pasien hari ini                 |
| 4\. Memilih pasien yang akan diperiksa                   |                                                                |
|                                                          | 5\. Menampilkan data pasien dan form rekam medis               |
| 6\. Mengisi diagnosis dan tindakan medis                 |                                                                |
| 7\. Mengisi resep obat yang menyebabkan alergi pasien    |                                                                |
|                                                          | 8\. Memeriksa histori alergi pasien di basis data              |
|                                                          | 9\. Menampilkan pesan peringatan alergi terhadap obat tersebut |
| 10\. Mengganti obat dengan yang tidak menyebabkan alergi |                                                                |
| 11\. Mengklik tombol Simpan Rekam Medis                  |                                                                |
|                                                          | 12\. Menyimpan rekam medis dan resep ke basis data             |
|                                                          | 13\. Mengirimkan notifikasi resep baru ke modul apotek         |
|                                                          | 14\. Menampilkan pesan rekam medis berhasil disimpan           |

**Nama Use Case : Lihat Histori Rekam Medis (UC-09)**

Skenario:

| **Aksi Aktor**                                         | **Reaksi Sistem**                                                        |
| ------------------------------------------------------ | ------------------------------------------------------------------------ |
| **Skenario Normal**                                    |                                                                          |
|                                                        | 1\. Memeriksa status login dokter                                        |
| 2\. Membuka detail data pasien                         |                                                                          |
|                                                        | 3\. Menampilkan data lengkap pasien                                      |
| 4\. Memilih menu histori rekam medis                   |                                                                          |
|                                                        | 5\. Menampilkan daftar kunjungan pasien sebelumnya                       |
| 6\. Memilih kunjungan tertentu untuk dilihat detailnya |                                                                          |
|                                                        | 7\. Menampilkan detail rekam medis kunjungan yang dipilih                |
| **Skenario Alternatif**                                |                                                                          |
|                                                        | 1\. Memeriksa status login dokter                                        |
| 2\. Membuka detail data pasien                         |                                                                          |
|                                                        | 3\. Menampilkan data lengkap pasien                                      |
| 4\. Memilih menu histori rekam medis                   |                                                                          |
|                                                        | 5\. Menampilkan pesan bahwa pasien belum memiliki rekam medis sebelumnya |

**Nama Use Case : Lihat Resep & Siapkan Obat (UC-10 & UC-11)**

Skenario:

| **Aksi Aktor**                                        | **Reaksi Sistem**                                                               |
| ----------------------------------------------------- | ------------------------------------------------------------------------------- |
| **Skenario Normal**                                   |                                                                                 |
|                                                       | 1\. Memeriksa status login petugas apotek                                       |
| 2\. Membuka menu daftar resep masuk                   |                                                                                 |
|                                                       | 3\. Menampilkan daftar resep yang belum diproses                                |
| 4\. Memilih resep yang akan diproses                  |                                                                                 |
|                                                       | 5\. Menampilkan detail resep beserta nama pasien, obat, dosis, dan aturan pakai |
|                                                       | 6\. Memeriksa ketersediaan stok obat di basis data                              |
|                                                       | 7\. Menampilkan konfirmasi stok obat mencukupi                                  |
| 8\. Mengklik tombol Tandai Obat Siap                  |                                                                                 |
|                                                       | 9\. Mengubah status resep menjadi Siap Diserahkan                               |
|                                                       | 10\. Mengurangi stok obat secara otomatis di basis data                         |
|                                                       | 11\. Mengirimkan notifikasi ke kasir bahwa pasien siap melakukan pembayaran     |
|                                                       | 12\. Menampilkan pesan obat berhasil disiapkan                                  |
| **Skenario Alternatif**                               |                                                                                 |
|                                                       | 1\. Memeriksa status login petugas apotek                                       |
| 2\. Membuka menu daftar resep masuk                   |                                                                                 |
|                                                       | 3\. Menampilkan daftar resep yang belum diproses                                |
| 4\. Memilih resep yang akan diproses                  |                                                                                 |
|                                                       | 5\. Menampilkan detail resep                                                    |
|                                                       | 6\. Memeriksa ketersediaan stok obat di basis data                              |
|                                                       | 7\. Menampilkan pesan peringatan stok obat tidak mencukupi                      |
| 8\. Menghubungi dokter untuk meminta penggantian obat |                                                                                 |

**Nama Use Case : Kelola Stok Obat (UC-12)**

Skenario:

| **Aksi Aktor**                                      | **Reaksi Sistem**                                                                  |
| --------------------------------------------------- | ---------------------------------------------------------------------------------- |
| **Skenario Normal**                                 |                                                                                    |
|                                                     | 1\. Memeriksa status login petugas apotek                                          |
| 2\. Membuka menu manajemen stok obat                |                                                                                    |
|                                                     | 3\. Menampilkan daftar obat beserta jumlah stok saat ini                           |
| 4\. Memilih obat yang akan diperbarui stoknya       |                                                                                    |
|                                                     | 5\. Menampilkan form edit stok obat                                                |
| 6\. Mengubah jumlah stok obat                       |                                                                                    |
| 7\. Mengklik tombol Simpan                          |                                                                                    |
|                                                     | 8\. Memeriksa valid tidaknya data masukan                                          |
|                                                     | 9\. Menyimpan perubahan stok ke basis data                                         |
|                                                     | 10\. Menampilkan pesan stok obat berhasil diperbarui                               |
| **Skenario Alternatif**                             |                                                                                    |
|                                                     | 1\. Memeriksa status login petugas apotek                                          |
| 2\. Membuka menu manajemen stok obat                |                                                                                    |
|                                                     | 3\. Menampilkan daftar obat beserta jumlah stok saat ini                           |
|                                                     | 4\. Menampilkan peringatan otomatis pada obat yang stoknya mendekati batas minimum |
| 5\. Memilih obat dengan stok menipis untuk ditambah |                                                                                    |
|                                                     | 6\. Menampilkan form edit stok obat                                                |
| 7\. Mengisi jumlah penambahan stok                  |                                                                                    |
| 8\. Mengklik tombol Simpan                          |                                                                                    |
|                                                     | 9\. Menyimpan perubahan stok ke basis data                                         |
|                                                     | 10\. Menampilkan pesan stok obat berhasil diperbarui                               |

**Nama Use Case : Proses Pembayaran & Cetak Kuitansi (UC-14 & UC-15)**

Skenario:

| **Aksi Aktor**                                                         | **Reaksi Sistem**                                                 |
| ---------------------------------------------------------------------- | ----------------------------------------------------------------- |
| **Skenario Normal**                                                    |                                                                   |
|                                                                        | 1\. Memeriksa status login kasir                                  |
| 2\. Membuka menu transaksi pembayaran                                  |                                                                   |
|                                                                        | 3\. Menampilkan daftar pasien yang siap melakukan pembayaran      |
| 4\. Memilih data pasien yang akan diproses                             |                                                                   |
|                                                                        | 5\. Mengambil data tindakan medis dan obat pasien dari basis data |
|                                                                        | 6\. Menghitung total biaya tindakan dan obat secara otomatis      |
|                                                                        | 7\. Menampilkan rincian biaya dan total yang harus dibayar        |
| 8\. Memilih metode pembayaran dan mengklik Proses Pembayaran           |                                                                   |
|                                                                        | 9\. Mencatat transaksi pembayaran ke basis data                   |
|                                                                        | 10\. Memperbarui status pasien menjadi Lunas                      |
|                                                                        | 11\. Menampilkan konfirmasi pembayaran berhasil                   |
| 12\. Mengklik tombol Cetak Kuitansi                                    |                                                                   |
|                                                                        | 13\. Mengambil data transaksi dari basis data                     |
|                                                                        | 14\. Mengirimkan perintah cetak ke printer                        |
|                                                                        | 15\. Menampilkan pesan kuitansi berhasil dicetak                  |
| **Skenario Alternatif**                                                |                                                                   |
|                                                                        | 1\. Memeriksa status login kasir                                  |
| 2\. Membuka menu transaksi pembayaran                                  |                                                                   |
|                                                                        | 3\. Menampilkan daftar pasien yang siap melakukan pembayaran      |
| 4\. Memilih data pasien yang akan diproses                             |                                                                   |
|                                                                        | 5\. Menghitung total biaya dan menampilkan rincian                |
| 6\. Memilih metode pembayaran non-tunai dan mengklik Proses Pembayaran |                                                                   |
|                                                                        | 7\. Memproses pembayaran non-tunai                                |
|                                                                        | 8\. Menampilkan pesan pembayaran gagal                            |
| 9\. Beralih ke metode pembayaran tunai                                 |                                                                   |
|                                                                        | 10\. Mencatat transaksi pembayaran ke basis data                  |
|                                                                        | 11\. Menampilkan konfirmasi pembayaran berhasil                   |
| 12\. Mengklik tombol Cetak Kuitansi                                    |                                                                   |
|                                                                        | 13\. Mengirimkan perintah cetak ke printer                        |
|                                                                        | 14\. Menampilkan pesan printer tidak terdeteksi                   |
| 15\. Memeriksa dan memperbaiki koneksi printer                         |                                                                   |
| 16\. Mengklik tombol Cetak Kuitansi kembali                            |                                                                   |
|                                                                        | 17\. Mengirimkan perintah cetak ke printer                        |
|                                                                        | 18\. Menampilkan pesan kuitansi berhasil dicetak                  |

**Nama Use Case : Lihat Laporan (UC-16, UC-17, UC-18)**

Skenario:

| **Aksi Aktor**                               | **Reaksi Sistem**                                                     |
| -------------------------------------------- | --------------------------------------------------------------------- |
| **Skenario Normal**                          |                                                                       |
|                                              | 1\. Memeriksa status login Dewan Kesehatan                            |
| 2\. Membuka menu laporan                     |                                                                       |
|                                              | 3\. Menampilkan pilihan jenis laporan (keuangan, obat, pasien)        |
| 4\. Memilih jenis laporan dan filter periode |                                                                       |
|                                              | 5\. Memproses dan mengagregasi data sesuai jenis laporan yang dipilih |
|                                              | 6\. Menampilkan laporan beserta grafik dan statistik                  |
| 7\. Mengklik tombol Unduh Laporan            |                                                                       |
|                                              | 8\. Meng-generate file laporan dalam format PDF atau Excel            |
|                                              | 9\. Menampilkan pesan laporan berhasil diunduh                        |
| **Skenario Alternatif**                      |                                                                       |
|                                              | 1\. Memeriksa status login Dewan Kesehatan                            |
| 2\. Membuka menu laporan                     |                                                                       |
|                                              | 3\. Menampilkan pilihan jenis laporan                                 |
| 4\. Memilih jenis laporan dan filter periode |                                                                       |
|                                              | 5\. Memproses data sesuai periode yang dipilih                        |
|                                              | 6\. Menampilkan pesan bahwa tidak ada data pada periode yang dipilih  |
| 7\. Mengubah filter periode yang dipilih     |                                                                       |
|                                              | 8\. Memproses data sesuai periode baru                                |
|                                              | 9\. Menampilkan laporan beserta grafik dan statistik                  |