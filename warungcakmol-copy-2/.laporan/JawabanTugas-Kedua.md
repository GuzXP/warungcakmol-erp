**TUGAS REKAYASA PERANGKAT LUNAK**

**Sistem Informasi Apartemen IBN**

Disusun Oleh:

**Bagus Ramadhan**

2491051003

**Program Studi Rekayasa Perangkat Lunak**

Institut Bisnis Nusantara (IBN)

2024 / 2025

# **1\. Analisis Sistem Berjalan**

Saat ini IBN sedang mengembangkan bisnis apartemen yang berlokasi di belakang kampus. Proses pengelolaan apartemen yang berjalan saat ini masih dilakukan secara manual dan konvensional. Calon penginap yang ingin memesan ruangan harus datang langsung ke lokasi atau menghubungi pihak pengelola melalui telepon. Pencatatan data pemesanan, data penginap, dan transaksi pembayaran dilakukan secara tertulis atau menggunakan dokumen fisik yang rentan terhadap kesalahan, kehilangan data, dan ketidakefisienan waktu. Pihak manajemen (Rektor IBN) juga tidak dapat memantau data penginap, keuangan, maupun ketersediaan ruangan secara real-time karena belum ada sistem terpusat yang mengelola seluruh informasi tersebut.

# **2\. Analisis Sistem Usulan**

Rektor IBN menginginkan sistem informasi berbasis komputer yang dapat mengelola seluruh proses bisnis apartemen secara digital dan terintegrasi. Sistem usulan yang diajukan adalah Sistem Informasi Apartemen IBN dengan alur sebagai berikut:

**a. Pemesanan Ruangan**

\- Calon penginap dapat melihat daftar ruangan apartemen yang tersedia secara online

\- Calon penginap dapat melakukan pemesanan ruangan secara online

**b. Pembayaran**

\- Sistem dapat menerima dan memproses bukti pembayaran transfer dari calon penginap

**c. Pengelolaan Data Penginap**

\- Sistem dapat menampilkan dan menyimpan data calon penginap

\- Data tersebut dapat digunakan untuk keperluan promosi oleh pihak manajemen

**d. Laporan & Monitoring (Manajemen)**

\- Sistem dapat menampilkan data penginap secara lengkap

\- Sistem dapat menampilkan data keuangan apartemen

# **3\. Analisa Kebutuhan**

## **A. Kebutuhan Aktor**

| **No** | **Aktor**                  | **Deskripsi**                                                                                                       |
| ------ | -------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| 1      | **Calon Penginap**         | Pengguna yang ingin memesan ruangan apartemen, melihat ketersediaan ruangan, dan melakukan pembayaran secara online |
| 2      | **Admin**                  | Pengelola sistem yang bertugas memverifikasi bukti pembayaran, mengelola data ruangan, dan mengelola data penginap  |
| 3      | **Manajemen (Rektor IBN)** | Pihak pimpinan yang memantau data penginap, data keuangan, dan menggunakan data untuk keperluan promosi             |

## **B. Kebutuhan Fungsional**

| **Kode**                     | **Kebutuhan Fungsional**                                                                      |
| ---------------------------- | --------------------------------------------------------------------------------------------- |
| **Modul Ruangan**            |                                                                                               |
| **F-01**                     | Sistem menampilkan daftar ruangan apartemen yang tersedia beserta detail dan foto             |
| **F-02**                     | Sistem memfilter ruangan berdasarkan ketersediaan, tipe, dan harga                            |
| **F-03**                     | Sistem memperbarui status ketersediaan ruangan secara otomatis setelah pemesanan dikonfirmasi |
| **Modul Pemesanan**          |                                                                                               |
| **F-04**                     | Sistem menyediakan form pemesanan ruangan secara online bagi calon penginap                   |
| **F-05**                     | Sistem menyimpan data pemesanan beserta informasi calon penginap                              |
| **F-06**                     | Sistem menampilkan konfirmasi pemesanan kepada calon penginap setelah proses selesai          |
| **Modul Pembayaran**         |                                                                                               |
| **F-07**                     | Calon penginap dapat mengunggah bukti pembayaran transfer melalui sistem                      |
| **F-08**                     | Admin dapat melihat dan memverifikasi bukti pembayaran yang masuk                             |
| **F-09**                     | Sistem memperbarui status pemesanan menjadi lunas setelah pembayaran diverifikasi             |
| **Modul Data Penginap**      |                                                                                               |
| **F-10**                     | Sistem menyimpan dan menampilkan data lengkap calon penginap                                  |
| **F-11**                     | Sistem menyediakan fitur pencarian dan filter data penginap                                   |
| **F-12**                     | Data penginap dapat diekspor untuk keperluan promosi oleh manajemen                           |
| **Modul Keuangan & Laporan** |                                                                                               |
| **F-13**                     | Sistem menampilkan data keuangan apartemen (pemasukan per periode)                            |
| **F-14**                     | Sistem menghasilkan laporan data penginap secara periodik                                     |
| **F-15**                     | Sistem menghasilkan laporan ketersediaan dan penggunaan ruangan                               |

## **C. Kebutuhan Non-Fungsional**

| **Kode**  | **Kategori**       | **Kebutuhan Non-Fungsional**                                                                                          |
| --------- | ------------------ | --------------------------------------------------------------------------------------------------------------------- |
| **NF-01** | **Keamanan**       | Setiap aktor memiliki akun login dengan hak akses berbeda sesuai perannya (role-based access)                         |
| **NF-02** | **Keamanan**       | Data pribadi calon penginap dan data keuangan dienkripsi dan hanya dapat diakses oleh aktor yang berwenang            |
| **NF-03** | **Ketersediaan**   | Sistem dapat diakses 24 jam penuh untuk mendukung pemesanan online calon penginap                                     |
| **NF-04** | **Performa**       | Sistem merespons setiap permintaan pengguna dalam waktu kurang dari 3 detik pada kondisi normal                       |
| **NF-05** | **Kegunaan**       | Antarmuka sistem dirancang intuitif dan mudah digunakan oleh calon penginap yang tidak memiliki latar belakang teknis |
| **NF-06** | **Keandalan**      | Sistem memiliki uptime minimal 99% untuk menjamin ketersediaan layanan pemesanan online                               |
| **NF-07** | **Skalabilitas**   | Sistem mampu menangani penambahan jumlah penginap, ruangan, dan transaksi tanpa penurunan performa yang signifikan    |
| **NF-08** | **Pemeliharaan**   | Sistem mudah diperbarui dan dipelihara oleh tim IT IBN tanpa mengganggu operasional yang sedang berjalan              |
| **NF-09** | **Kompatibilitas** | Sistem dapat diakses melalui berbagai browser modern dan perangkat mobile (responsive design)                         |
| **NF-10** | **Legalitas**      | Pengelolaan data penginap mengacu pada regulasi privasi data yang berlaku di Indonesia                                |

# **4\. Perancangan Sistem**

## **A. Use Case Diagram**

(Use Case Diagram terlampir - dibuat menggunakan PlantUML)

## **B. Deskripsi Use Case**

| **No**    | **Nama Use Case**           | **Aktor**      | **Deskripsi**                                                                                                                   | **Relasi**                             |
| --------- | --------------------------- | -------------- | ------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------- |
| **UC-01** | **Login Sistem**            | Semua Aktor    | Seluruh pengguna sistem melakukan autentikasi dengan memasukkan username dan password untuk mengakses fitur sesuai hak aksesnya | \-                                     |
| **UC-02** | **Lihat Ruangan Tersedia**  | Calon Penginap | Calon penginap melihat daftar ruangan apartemen yang tersedia beserta detail, tipe, dan harga                                   | «include» UC-03 Filter Ruangan         |
| **UC-03** | **Filter Ruangan**          | Calon Penginap | Calon penginap memfilter daftar ruangan berdasarkan ketersediaan, tipe, dan harga                                               | \-                                     |
| **UC-04** | **Pesan Ruangan**           | Calon Penginap | Calon penginap melakukan pemesanan ruangan secara online melalui form yang tersedia di sistem                                   | «include» UC-05 Simpan Data Pemesanan  |
| **UC-05** | **Simpan Data Pemesanan**   | Sistem         | Sistem menyimpan data pemesanan beserta informasi calon penginap dan mengirimkan konfirmasi                                     | \-                                     |
| **UC-06** | **Upload Bukti Pembayaran** | Calon Penginap | Calon penginap mengunggah bukti pembayaran transfer melalui sistem sebagai syarat konfirmasi pemesanan                          | «include» UC-04 Pesan Ruangan          |
| **UC-07** | **Verifikasi Pembayaran**   | Admin          | Admin melihat dan memverifikasi bukti pembayaran yang diunggah calon penginap                                                   | «extend» UC-08 Tolak Pembayaran        |
| **UC-08** | **Tolak Pembayaran**        | Admin          | Admin menolak bukti pembayaran yang tidak valid dan memberikan notifikasi kepada calon penginap                                 | \-                                     |
| **UC-09** | **Kelola Data Ruangan**     | Admin          | Admin mengelola data ruangan apartemen meliputi penambahan, pengeditan, dan penghapusan data ruangan                            | \-                                     |
| **UC-10** | **Kelola Data Penginap**    | Admin          | Admin mengelola data penginap yang telah terdaftar meliputi pencarian, pengeditan, dan penghapusan data                         | \-                                     |
| **UC-11** | **Lihat Data Penginap**     | Manajemen      | Manajemen melihat data lengkap seluruh penginap yang terdaftar di sistem untuk keperluan monitoring dan promosi                 | «extend» UC-12 Ekspor Data Penginap    |
| **UC-12** | **Ekspor Data Penginap**    | Manajemen      | Manajemen mengekspor data penginap dalam format tertentu untuk keperluan promosi dan pelaporan                                  | \-                                     |
| **UC-13** | **Lihat Data Keuangan**     | Manajemen      | Manajemen melihat data keuangan apartemen berupa pemasukan berdasarkan periode yang dipilih                                     | «extend» UC-14 Ekspor Laporan Keuangan |
| **UC-14** | **Ekspor Laporan Keuangan** | Manajemen      | Manajemen mengekspor laporan keuangan dalam format PDF atau Excel untuk keperluan evaluasi                                      | \-                                     |
| **UC-15** | **Lihat Laporan Ruangan**   | Manajemen      | Manajemen melihat laporan ketersediaan dan tingkat hunian ruangan apartemen secara periodik                                     | \-                                     |

## **C. Skenario Use Case**

**Nama Use Case : Login Sistem (UC-01)**

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

**Nama Use Case : Lihat Ruangan Tersedia (UC-02)**

Skenario:

| **Aksi Aktor**                           | **Reaksi Sistem**                                                                      |
| ---------------------------------------- | -------------------------------------------------------------------------------------- |
| **Skenario Normal**                      |                                                                                        |
|                                          | 1\. Memeriksa status login calon penginap                                              |
| 2\. Memilih menu lihat ruangan tersedia  |                                                                                        |
|                                          | 3\. Menampilkan daftar ruangan apartemen yang tersedia beserta detail, tipe, dan harga |
| 4\. Memilih ruangan untuk melihat detail |                                                                                        |
|                                          | 5\. Menampilkan detail lengkap ruangan yang dipilih                                    |
| **Skenario Alternatif**                  |                                                                                        |
|                                          | 1\. Memeriksa status login calon penginap                                              |
| 2\. Memilih menu lihat ruangan tersedia  |                                                                                        |
|                                          | 3\. Menampilkan pesan bahwa tidak ada ruangan yang tersedia saat ini                   |

**Nama Use Case : Filter Ruangan (UC-03)**

Skenario:

| **Aksi Aktor**                                              | **Reaksi Sistem**                                                         |
| ----------------------------------------------------------- | ------------------------------------------------------------------------- |
| **Skenario Normal**                                         |                                                                           |
|                                                             | 1\. Memeriksa status login calon penginap                                 |
| 2\. Memilih menu lihat ruangan tersedia                     |                                                                           |
|                                                             | 3\. Menampilkan daftar ruangan dan form filter                            |
| 4\. Mengisi kriteria filter (tipe, harga, ketersediaan)     |                                                                           |
| 5\. Mengklik tombol Filter                                  |                                                                           |
|                                                             | 6\. Memproses kriteria filter yang dimasukkan                             |
|                                                             | 7\. Menampilkan daftar ruangan sesuai kriteria filter                     |
| **Skenario Alternatif**                                     |                                                                           |
|                                                             | 1\. Memeriksa status login calon penginap                                 |
| 2\. Memilih menu lihat ruangan tersedia                     |                                                                           |
|                                                             | 3\. Menampilkan daftar ruangan dan form filter                            |
| 4\. Mengisi kriteria filter yang tidak sesuai data yang ada |                                                                           |
| 5\. Mengklik tombol Filter                                  |                                                                           |
|                                                             | 6\. Memproses kriteria filter yang dimasukkan                             |
|                                                             | 7\. Menampilkan pesan bahwa tidak ada ruangan yang sesuai kriteria filter |
| 8\. Mengubah kriteria filter                                |                                                                           |
|                                                             | 9\. Memproses kriteria filter yang baru                                   |
|                                                             | 10\. Menampilkan daftar ruangan sesuai kriteria filter yang baru          |

**Nama Use Case : Pesan Ruangan (UC-04)**

Skenario:

| **Aksi Aktor**                                                                    | **Reaksi Sistem**                                                       |
| --------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| **Skenario Normal**                                                               |                                                                         |
|                                                                                   | 1\. Memeriksa status login calon penginap                               |
| 2\. Memilih ruangan yang akan dipesan                                             |                                                                         |
|                                                                                   | 3\. Menampilkan form pemesanan ruangan                                  |
| 4\. Mengisi data pemesanan (nama, identitas, tanggal check-in, tanggal check-out) |                                                                         |
| 5\. Mengklik tombol Pesan                                                         |                                                                         |
|                                                                                   | 6\. Memeriksa kelengkapan dan validitas data masukan                    |
|                                                                                   | 7\. Memeriksa ketersediaan ruangan pada tanggal yang dipilih            |
|                                                                                   | 8\. Menyimpan data pemesanan ke basis data                              |
|                                                                                   | 9\. Menampilkan konfirmasi pemesanan berhasil beserta detail pemesanan  |
| **Skenario Alternatif**                                                           |                                                                         |
|                                                                                   | 1\. Memeriksa status login calon penginap                               |
| 2\. Memilih ruangan yang akan dipesan                                             |                                                                         |
|                                                                                   | 3\. Menampilkan form pemesanan ruangan                                  |
| 4\. Mengisi data pemesanan tidak lengkap                                          |                                                                         |
| 5\. Mengklik tombol Pesan                                                         |                                                                         |
|                                                                                   | 6\. Memeriksa kelengkapan dan validitas data masukan                    |
|                                                                                   | 7\. Menampilkan pesan bahwa data masukan tidak lengkap                  |
| 8\. Melengkapi data pemesanan yang kosong                                         |                                                                         |
|                                                                                   | 9\. Memeriksa kelengkapan dan validitas data masukan                    |
|                                                                                   | 10\. Memeriksa ketersediaan ruangan pada tanggal yang dipilih           |
|                                                                                   | 11\. Menyimpan data pemesanan ke basis data                             |
|                                                                                   | 12\. Menampilkan konfirmasi pemesanan berhasil beserta detail pemesanan |

**Nama Use Case : Simpan Data Pemesanan (UC-05)**

Skenario:

| **Aksi Aktor**                | **Reaksi Sistem**                                                       |
| ----------------------------- | ----------------------------------------------------------------------- |
| **Skenario Normal**           |                                                                         |
|                               | 1\. Menerima data pemesanan yang telah divalidasi dari proses pemesanan |
|                               | 2\. Menyimpan data pemesanan ke basis data                              |
|                               | 3\. Memperbarui status ketersediaan ruangan menjadi dipesan             |
|                               | 4\. Mengirimkan notifikasi konfirmasi pemesanan kepada calon penginap   |
| **Skenario Alternatif**       |                                                                         |
|                               | 1\. Menerima data pemesanan yang telah divalidasi dari proses pemesanan |
|                               | 2\. Mendeteksi kegagalan koneksi ke basis data                          |
|                               | 3\. Menampilkan pesan bahwa pemesanan gagal disimpan                    |
| 4\. Mengklik tombol Coba Lagi |                                                                         |
|                               | 5\. Menyimpan data pemesanan ke basis data                              |
|                               | 6\. Memperbarui status ketersediaan ruangan menjadi dipesan             |
|                               | 7\. Mengirimkan notifikasi konfirmasi pemesanan kepada calon penginap   |

**Nama Use Case : Upload Bukti Pembayaran (UC-06)**

Skenario:

| **Aksi Aktor**                                     | **Reaksi Sistem**                                                   |
| -------------------------------------------------- | ------------------------------------------------------------------- |
| **Skenario Normal**                                |                                                                     |
|                                                    | 1\. Memeriksa status login calon penginap                           |
| 2\. Memilih menu upload bukti pembayaran           |                                                                     |
|                                                    | 3\. Menampilkan form upload bukti pembayaran beserta detail tagihan |
| 4\. Memilih file bukti pembayaran dari perangkat   |                                                                     |
| 5\. Mengklik tombol Upload                         |                                                                     |
|                                                    | 6\. Memeriksa format dan ukuran file yang diunggah                  |
|                                                    | 7\. Menyimpan file bukti pembayaran ke sistem                       |
|                                                    | 8\. Memperbarui status pemesanan menjadi menunggu verifikasi        |
|                                                    | 9\. Mengirimkan notifikasi ke admin bahwa ada bukti pembayaran baru |
|                                                    | 10\. Menampilkan pesan bahwa bukti pembayaran berhasil diunggah     |
| **Skenario Alternatif**                            |                                                                     |
|                                                    | 1\. Memeriksa status login calon penginap                           |
| 2\. Memilih menu upload bukti pembayaran           |                                                                     |
|                                                    | 3\. Menampilkan form upload bukti pembayaran beserta detail tagihan |
| 4\. Memilih file dengan format yang tidak didukung |                                                                     |
| 5\. Mengklik tombol Upload                         |                                                                     |
|                                                    | 6\. Memeriksa format dan ukuran file yang diunggah                  |
|                                                    | 7\. Menampilkan pesan bahwa format file tidak didukung              |
| 8\. Memilih file dengan format yang sesuai         |                                                                     |
|                                                    | 9\. Memeriksa format dan ukuran file yang diunggah                  |
|                                                    | 10\. Menyimpan file bukti pembayaran ke sistem                      |
|                                                    | 11\. Memperbarui status pemesanan menjadi menunggu verifikasi       |
|                                                    | 12\. Menampilkan pesan bahwa bukti pembayaran berhasil diunggah     |

**Nama Use Case : Verifikasi Pembayaran (UC-07)**

Skenario:

| **Aksi Aktor**                                           | **Reaksi Sistem**                                                          |
| -------------------------------------------------------- | -------------------------------------------------------------------------- |
| **Skenario Normal**                                      |                                                                            |
|                                                          | 1\. Memeriksa status login admin                                           |
| 2\. Membuka menu verifikasi pembayaran                   |                                                                            |
|                                                          | 3\. Menampilkan daftar bukti pembayaran yang menunggu verifikasi           |
| 4\. Memilih bukti pembayaran yang akan diverifikasi      |                                                                            |
|                                                          | 5\. Menampilkan detail bukti pembayaran dan data pemesanan terkait         |
| 6\. Memeriksa kesesuaian nominal dan data transfer       |                                                                            |
| 7\. Mengklik tombol Konfirmasi Pembayaran                |                                                                            |
|                                                          | 8\. Memperbarui status pemesanan menjadi lunas                             |
|                                                          | 9\. Mengirimkan notifikasi konfirmasi pembayaran kepada calon penginap     |
|                                                          | 10\. Menampilkan pesan verifikasi pembayaran berhasil                      |
| **Skenario Alternatif**                                  |                                                                            |
|                                                          | 1\. Memeriksa status login admin                                           |
| 2\. Membuka menu verifikasi pembayaran                   |                                                                            |
|                                                          | 3\. Menampilkan daftar bukti pembayaran yang menunggu verifikasi           |
| 4\. Memilih bukti pembayaran yang akan diverifikasi      |                                                                            |
|                                                          | 5\. Menampilkan detail bukti pembayaran dan data pemesanan terkait         |
| 6\. Menemukan ketidaksesuaian nominal atau data transfer |                                                                            |
| 7\. Mengklik tombol Tolak Pembayaran                     |                                                                            |
|                                                          | 8\. Menampilkan form alasan penolakan pembayaran                           |
| 9\. Mengisi alasan penolakan dan mengklik Kirim          |                                                                            |
|                                                          | 10\. Memperbarui status pemesanan menjadi pembayaran ditolak               |
|                                                          | 11\. Mengirimkan notifikasi penolakan beserta alasan kepada calon penginap |
|                                                          | 12\. Menampilkan pesan penolakan pembayaran berhasil diproses              |

**Nama Use Case : Tolak Pembayaran (UC-08)**

Skenario:

| **Aksi Aktor**                                             | **Reaksi Sistem**                                                         |
| ---------------------------------------------------------- | ------------------------------------------------------------------------- |
| **Skenario Normal**                                        |                                                                           |
|                                                            | 1\. Memeriksa status login admin                                          |
| 2\. Memilih pemesanan dengan status pembayaran tidak valid |                                                                           |
|                                                            | 3\. Menampilkan detail pemesanan dan bukti pembayaran                     |
| 4\. Mengklik tombol Tolak Pembayaran                       |                                                                           |
|                                                            | 5\. Menampilkan form alasan penolakan                                     |
| 6\. Mengisi alasan penolakan dan mengklik Konfirmasi       |                                                                           |
|                                                            | 7\. Memperbarui status pemesanan menjadi pembayaran ditolak               |
|                                                            | 8\. Mengirimkan notifikasi penolakan beserta alasan kepada calon penginap |
|                                                            | 9\. Menampilkan pesan penolakan berhasil diproses                         |
| **Skenario Alternatif**                                    |                                                                           |
|                                                            | 1\. Memeriksa status login admin                                          |
| 2\. Memilih pemesanan dengan status pembayaran tidak valid |                                                                           |
|                                                            | 3\. Menampilkan detail pemesanan dan bukti pembayaran                     |
| 4\. Mengklik tombol Tolak Pembayaran                       |                                                                           |
|                                                            | 5\. Menampilkan form alasan penolakan                                     |
| 6\. Mengklik tombol Batal                                  |                                                                           |
|                                                            | 7\. Menutup form penolakan dan kembali ke halaman detail pemesanan        |

**Nama Use Case : Kelola Data Ruangan (UC-09)**

Skenario:

| **Aksi Aktor**                                                 | **Reaksi Sistem**                                          |
| -------------------------------------------------------------- | ---------------------------------------------------------- |
| **Skenario Normal**                                            |                                                            |
|                                                                | 1\. Memeriksa status login admin                           |
| 2\. Membuka menu kelola data ruangan                           |                                                            |
|                                                                | 3\. Menampilkan daftar seluruh ruangan apartemen           |
| 4\. Mengklik tombol Tambah Ruangan                             |                                                            |
|                                                                | 5\. Menampilkan form input data ruangan baru               |
| 6\. Mengisi data ruangan (nomor, tipe, harga, fasilitas, foto) |                                                            |
| 7\. Mengklik tombol Simpan                                     |                                                            |
|                                                                | 8\. Memeriksa valid tidaknya data masukan                  |
|                                                                | 9\. Menyimpan data ruangan baru ke basis data              |
|                                                                | 10\. Menampilkan pesan data ruangan berhasil disimpan      |
| **Skenario Alternatif**                                        |                                                            |
|                                                                | 1\. Memeriksa status login admin                           |
| 2\. Membuka menu kelola data ruangan                           |                                                            |
|                                                                | 3\. Menampilkan daftar seluruh ruangan apartemen           |
| 4\. Memilih ruangan yang akan diubah dan mengklik tombol Edit  |                                                            |
|                                                                | 5\. Menampilkan form edit dengan data ruangan yang dipilih |
| 6\. Mengubah data ruangan yang diperlukan                      |                                                            |
| 7\. Mengklik tombol Simpan                                     |                                                            |
|                                                                | 8\. Memeriksa valid tidaknya data masukan                  |
|                                                                | 9\. Menyimpan perubahan data ruangan ke basis data         |
|                                                                | 10\. Menampilkan pesan data ruangan berhasil diperbarui    |

**Nama Use Case : Kelola Data Penginap (UC-10)**

Skenario:

| **Aksi Aktor**                                                 | **Reaksi Sistem**                                         |
| -------------------------------------------------------------- | --------------------------------------------------------- |
| **Skenario Normal**                                            |                                                           |
|                                                                | 1\. Memeriksa status login admin                          |
| 2\. Membuka menu kelola data penginap                          |                                                           |
|                                                                | 3\. Menampilkan daftar seluruh penginap yang terdaftar    |
| 4\. Memasukkan kata kunci pencarian penginap                   |                                                           |
|                                                                | 5\. Mencari data penginap berdasarkan kata kunci          |
|                                                                | 6\. Menampilkan hasil pencarian data penginap             |
| 7\. Memilih penginap yang akan dikelola                        |                                                           |
|                                                                | 8\. Menampilkan detail data penginap yang dipilih         |
| 9\. Mengubah data penginap yang diperlukan dan mengklik Simpan |                                                           |
|                                                                | 10\. Memeriksa valid tidaknya data masukan                |
|                                                                | 11\. Menyimpan perubahan data penginap ke basis data      |
|                                                                | 12\. Menampilkan pesan data penginap berhasil diperbarui  |
| **Skenario Alternatif**                                        |                                                           |
|                                                                | 1\. Memeriksa status login admin                          |
| 2\. Membuka menu kelola data penginap                          |                                                           |
|                                                                | 3\. Menampilkan daftar seluruh penginap yang terdaftar    |
| 4\. Memasukkan kata kunci pencarian yang tidak ditemukan       |                                                           |
|                                                                | 5\. Mencari data penginap berdasarkan kata kunci          |
|                                                                | 6\. Menampilkan pesan bahwa data penginap tidak ditemukan |
| 7\. Mengubah kata kunci pencarian                              |                                                           |
|                                                                | 8\. Mencari data penginap berdasarkan kata kunci baru     |
|                                                                | 9\. Menampilkan hasil pencarian data penginap             |

**Nama Use Case : Lihat Data Penginap (UC-11)**

Skenario:

| **Aksi Aktor**                                      | **Reaksi Sistem**                                                             |
| --------------------------------------------------- | ----------------------------------------------------------------------------- |
| **Skenario Normal**                                 |                                                                               |
|                                                     | 1\. Memeriksa status login manajemen                                          |
| 2\. Membuka menu data penginap                      |                                                                               |
|                                                     | 3\. Menampilkan daftar lengkap seluruh penginap beserta informasi detail      |
| 4\. Memilih filter periode atau status penginap     |                                                                               |
|                                                     | 5\. Memproses filter yang dipilih                                             |
|                                                     | 6\. Menampilkan data penginap sesuai filter yang dipilih                      |
| **Skenario Alternatif**                             |                                                                               |
|                                                     | 1\. Memeriksa status login manajemen                                          |
| 2\. Membuka menu data penginap                      |                                                                               |
|                                                     | 3\. Menampilkan daftar lengkap seluruh penginap                               |
| 4\. Memilih filter periode yang tidak memiliki data |                                                                               |
|                                                     | 5\. Memproses filter yang dipilih                                             |
|                                                     | 6\. Menampilkan pesan bahwa tidak ada data penginap pada periode yang dipilih |
| 7\. Mengubah filter periode                         |                                                                               |
|                                                     | 8\. Memproses filter yang baru                                                |
|                                                     | 9\. Menampilkan data penginap sesuai filter yang baru                         |

**Nama Use Case : Ekspor Data Penginap (UC-12)**

Skenario:

| **Aksi Aktor**                            | **Reaksi Sistem**                                       |
| ----------------------------------------- | ------------------------------------------------------- |
| **Skenario Normal**                       |                                                         |
|                                           | 1\. Memeriksa status login manajemen                    |
| 2\. Membuka menu data penginap            |                                                         |
|                                           | 3\. Menampilkan daftar data penginap                    |
| 4\. Mengklik tombol Ekspor Data           |                                                         |
|                                           | 5\. Menampilkan pilihan format ekspor (PDF atau Excel)  |
| 6\. Memilih format ekspor yang diinginkan |                                                         |
|                                           | 7\. Memproses dan meng-generate file ekspor             |
|                                           | 8\. Menampilkan pesan bahwa file berhasil diunduh       |
| **Skenario Alternatif**                   |                                                         |
|                                           | 1\. Memeriksa status login manajemen                    |
| 2\. Membuka menu data penginap            |                                                         |
|                                           | 3\. Menampilkan daftar data penginap                    |
| 4\. Mengklik tombol Ekspor Data           |                                                         |
|                                           | 5\. Menampilkan pilihan format ekspor                   |
| 6\. Memilih format ekspor yang diinginkan |                                                         |
|                                           | 7\. Mendeteksi kegagalan dalam proses generate file     |
|                                           | 8\. Menampilkan pesan bahwa ekspor data gagal dilakukan |
| 9\. Mengklik tombol Coba Lagi             |                                                         |
|                                           | 10\. Memproses dan meng-generate file ekspor            |
|                                           | 11\. Menampilkan pesan bahwa file berhasil diunduh      |

**Nama Use Case : Lihat Data Keuangan (UC-13)**

Skenario:

| **Aksi Aktor**                                         | **Reaksi Sistem**                                                             |
| ------------------------------------------------------ | ----------------------------------------------------------------------------- |
| **Skenario Normal**                                    |                                                                               |
|                                                        | 1\. Memeriksa status login manajemen                                          |
| 2\. Membuka menu data keuangan                         |                                                                               |
|                                                        | 3\. Menampilkan pilihan filter periode (harian, bulanan, tahunan)             |
| 4\. Memilih periode laporan keuangan                   |                                                                               |
|                                                        | 5\. Memproses dan mengagregasi data transaksi sesuai periode                  |
|                                                        | 6\. Menampilkan data keuangan beserta grafik pemasukan                        |
| **Skenario Alternatif**                                |                                                                               |
|                                                        | 1\. Memeriksa status login manajemen                                          |
| 2\. Membuka menu data keuangan                         |                                                                               |
|                                                        | 3\. Menampilkan pilihan filter periode                                        |
| 4\. Memilih periode yang tidak memiliki data transaksi |                                                                               |
|                                                        | 5\. Memproses data sesuai periode yang dipilih                                |
|                                                        | 6\. Menampilkan pesan bahwa tidak ada data keuangan pada periode yang dipilih |
| 7\. Mengubah filter periode                            |                                                                               |
|                                                        | 8\. Memproses dan mengagregasi data transaksi sesuai periode baru             |
|                                                        | 9\. Menampilkan data keuangan beserta grafik pemasukan                        |

**Nama Use Case : Ekspor Laporan Keuangan (UC-14)**

Skenario:

| **Aksi Aktor**                            | **Reaksi Sistem**                                          |
| ----------------------------------------- | ---------------------------------------------------------- |
| **Skenario Normal**                       |                                                            |
|                                           | 1\. Memeriksa status login manajemen                       |
| 2\. Membuka menu data keuangan            |                                                            |
|                                           | 3\. Menampilkan data keuangan sesuai periode yang dipilih  |
| 4\. Mengklik tombol Ekspor Laporan        |                                                            |
|                                           | 5\. Menampilkan pilihan format ekspor (PDF atau Excel)     |
| 6\. Memilih format ekspor yang diinginkan |                                                            |
|                                           | 7\. Memproses dan meng-generate file laporan keuangan      |
|                                           | 8\. Menampilkan pesan bahwa laporan berhasil diunduh       |
| **Skenario Alternatif**                   |                                                            |
|                                           | 1\. Memeriksa status login manajemen                       |
| 2\. Membuka menu data keuangan            |                                                            |
|                                           | 3\. Menampilkan data keuangan sesuai periode yang dipilih  |
| 4\. Mengklik tombol Ekspor Laporan        |                                                            |
|                                           | 5\. Menampilkan pilihan format ekspor                      |
| 6\. Memilih format ekspor yang diinginkan |                                                            |
|                                           | 7\. Mendeteksi kegagalan dalam proses generate file        |
|                                           | 8\. Menampilkan pesan bahwa ekspor laporan gagal dilakukan |
| 9\. Mengklik tombol Coba Lagi             |                                                            |
|                                           | 10\. Memproses dan meng-generate file laporan keuangan     |
|                                           | 11\. Menampilkan pesan bahwa laporan berhasil diunduh      |

**Nama Use Case : Lihat Laporan Ruangan (UC-15)**

Skenario:

| **Aksi Aktor**                               | **Reaksi Sistem**                                                                 |
| -------------------------------------------- | --------------------------------------------------------------------------------- |
| **Skenario Normal**                          |                                                                                   |
|                                              | 1\. Memeriksa status login manajemen                                              |
| 2\. Membuka menu laporan ruangan             |                                                                                   |
|                                              | 3\. Menampilkan pilihan filter periode laporan                                    |
| 4\. Memilih periode laporan ruangan          |                                                                                   |
|                                              | 5\. Memproses dan mengagregasi data ketersediaan dan hunian ruangan               |
|                                              | 6\. Menampilkan laporan tingkat hunian dan ketersediaan ruangan beserta statistik |
| **Skenario Alternatif**                      |                                                                                   |
|                                              | 1\. Memeriksa status login manajemen                                              |
| 2\. Membuka menu laporan ruangan             |                                                                                   |
|                                              | 3\. Menampilkan pilihan filter periode laporan                                    |
| 4\. Memilih periode yang tidak memiliki data |                                                                                   |
|                                              | 5\. Memproses data sesuai periode yang dipilih                                    |
|                                              | 6\. Menampilkan pesan bahwa tidak ada data ruangan pada periode yang dipilih      |
| 7\. Mengubah filter periode                  |                                                                                   |
|                                              | 8\. Memproses dan mengagregasi data ketersediaan dan hunian ruangan               |
|                                              | 9\. Menampilkan laporan tingkat hunian dan ketersediaan ruangan beserta statistik |