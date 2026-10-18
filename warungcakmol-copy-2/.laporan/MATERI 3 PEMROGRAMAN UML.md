|     | **MODUL PERKULIAHAN**                   |
| --- | --------------------------------------- |
|     |                                         |
|     | **Pratikum**<br><br>**Pemrograman UML** |
|     |                                         |
|     |                                         |
|     |                                         |

|     | **Fakultas**  | **Program Studi**  | **Tatap Muka** | **Kode MK** | **Disusun Oleh**                   |     |
| --- | ------------- | ------------------ | -------------- | ----------- | ---------------------------------- | --- |
|     | Ilmu Komputer | Teknik Informatika | **03**         |             | Essy Malays Sari Sakti, Skom.MMSI. |     |

| **Abstract**                | **Kompetensi**                                   |
| --------------------------- | ------------------------------------------------ |
| Studi kasus usecase diagram | Mahasiswa mempu merancang dengan usecase diagram |
|                             |                                                  |

# Studi Kasus

Anda di minta untuk merancang use case diagram Sistem informasi manajemen perpustakaan. Dimana Sistem informasi manajeman perpustakaa adalah adalah sebuah sistem informasi untuk mengelola informasi yang diperlukan dalam sebuah perpustakaan yang meliputi pengelolaan pustaka, pengelolaan anggota, pengelolaan petugas dan pengelolaan peminjaman pustaka.

Adapun kebutuhan yang harus di penuh pada sistem informasi manajemen perpustakaan yang akan dimodelkan adalah sebagai berikut:

- Pustaka dapat memiliki lebih dari satu pengarang
- Anggota dapat meminjam lebih dari satu buku (pustaka) dalam satu waktu (waktu yang bersamaan)
- Anggota dapat memiliki lebih dari satu nomor telepon
- Anggota dapat mengembalikan pustaka yang dipinjam tidak dalam waktu yang bersamaan, meskipun pustaka-pustaka tersebut dipinjam pada waktu yang bersamaan.
- Pengunjung yang bukan anggota diperbolehkan mencari data pustaka yang ingin dibacanya.
- Pengunjung yang bukan anggota tidak diperbolehkan meminjam pustaka.
- Proses pendaftaran pustaka, anggota, dan peminjaman dilakukan oleh petugas perpustakaan.
- Anggota dan pengunjung dapat melakukan pencarian pustaka.
- Satu pustaka akan disimpan sebagai satu data dengan id yang unik

Sistem Informasi Manajemen Perpustakaan yang akan dimodelkan memiliki fungsi-fungsi sebagai berikut:

- Validasi Petugas
- Login
- Mengelola data Pustaka
- Memasukkan data pustaka baru
- Mengubah data pustaka
- Menghapus data pustaka
- Mencari data pustaka
- Melihat data pustaka
- Mengelola data petugas
- Memasukkan data petugas baru
- Mengubah data petugas
- Menghapus data petugas
- Mencari data petugas
- Melihat data petugas
- Mengelola data Anggota
- Memasukkan data anggota baru
- Mengubah data anggota
- Menghapus data anggota
- Mencari data anggota
- Melihat data anggota
- Mengelola data Peminjaman
- Memasukkan data peminjaman
- Mengubah data peminjaman
- Mencari data peminjaman
- Melihat data peminjaman

# Jawaban Studi kasus

Untuk menyelesaikan studi kasus diatas menjadi sebuah use case diagram, umumnya terdapat 4 tahapan yang harus dilalui yaitu :

- Pendefinisian Aktor
- Pendefinisian Use Case
- Pembuatan Use Case Skenario
- Menggambarkan Use Case Diagram
- **Pendefinisian Aktor**

Berikut adalah hasil pendefinisian aktor pada Sistem Informasi Manajemen Perpustakaan:

| **No** | **Aktor**                         | **Deskripsi**                                                                                                                                                                                                             |
| ------ | --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1      | Petugas Perpustakaan              | Petugas perpustakaan adalah orang yang bertugas dan memiliki hak akses untuk melakukan operasi pengelolaan data pustaka, anggota, dan proses peminjaman pustaka                                                           |
| 2      | Anggota / Pengunjung Perpustakaan | Anggota adalah orang yang diperbolehkan meminjam pustaka sesuai dengan hak aksesnya, sedangkan pengunjung hanya memiliki hak akses melihat pustaka dan membaca di perpustakaan tanpa memiliki hak untuk meminjam pustaka. |

- **Pendefinisian Use Case**

Berikut adalah hasil pendefinisian use case pada Sistem Informasi Manajemen Perpustakaan:

| **No** | **Use Case**          | **Deskripsi**                                                                                                                                                                                |
| ------ | --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1      | Login                 | Merupakan proses untuk melakukan login petugas perpustakaan                                                                                                                                  |
| 2      | Mengelola Pustaka     | Mengelola Pustaka merupakan proses pengelolaan data pustaka yang meliputi memasukkan pustaka, melihat pustaka, mengubah pustaka, menghapus pustaka dan mencari pustaka.                      |
| 3      | Memasukkan Pustaka    | Merupakan proses memasukkan data pustaka ke dalam basis data                                                                                                                                 |
| 4      | Melihat Pustaka       | Merupakan proses menampilkan data pustaka yang ada di dalam basis data                                                                                                                       |
| 5      | Mengubah Pustaka      | Merupakan proses mengubah data pustaka yang ada di dalam basis data                                                                                                                          |
| 6      | Menghapus Pustaka     | Merupakan proses menghapus data pustaka yang ada di dalam basis data                                                                                                                         |
| 7      | Mencari Pustaka       | Merupakan proses mencari data pustaka yang ada di dalam basis data                                                                                                                           |
| 8      | Mengelola Anggota     | Mengelola Anggota merupakan proses pengelolaan data anggota yang meliputi memasukkan anggota, melihat anggota, mengubah anggota, menghapus anggota dan mencari anggota.                      |
| 9      | Memasukkan Anggota    | Merupakan proses memasukkan data anggota ke dalam basis data                                                                                                                                 |
| 10     | Melihat Anggota       | Merupakan proses menampilkan data anggota yang ada di dalam basis data                                                                                                                       |
| 11     | Mengubah Anggota      | Merupakan proses mengubah data anggota yang ada di dalam basis data                                                                                                                          |
| 12     | Menghapus Anggota     | Merupakan proses menghapus data anggota yang ada di dalam basis data                                                                                                                         |
| 13     | Mencari Anggota       | Merupakan proses mencari data anggota yang ada di dalam basis data                                                                                                                           |
| 14     | Mengelola Peminjaman  | Mengelola Peminjaman merupakan proses pengelolaan data peminjaman yang meliputi memasukkan peminjaman, melihat peminjaman, mengubah peminjaman, menghapus peminjaman dan mencari peminjaman. |
| 15     | Memasukkan Peminjaman | Merupakan proses memasukkan data peminjaman ketika ada anggota yang meminjam pustaka                                                                                                         |
| 16     | Melihat Peminjaman    | Merupakan proses menampilkan / melihat data peminjaman yang ada di dalam basis data                                                                                                          |
| 17     | Mengubah Peminjaman   | Merupakan proses mengubah data peminjaman yang dapat dilakukan untuk mengubah status peminjaman begitu pustaka dikembalikan                                                                  |
| 18     | Menghapus Peminjaman  | Merupakan proses menghapus data peminjaman jika ternyata peminjaman tidak jadi dilakukan atau data sudah terlalu banyak dan data sudah di backup terlebih dahulu                             |
| 19     | Mencari Peminjaman    | Merupakan proses mencari data peminjaman yang ada di dalam basis data                                                                                                                        |
| 20     | Mengelola Petugas     | Mengelola Petugas merupakan proses pengelolaan data pemtugas yang meliputi memasukkan petugas, melihat petugas, mengubah petugas, menghapus petugas dan mencari petugas.                     |
| 21     | Memasukkan Petugas    | Merupakan proses memasukkan data petugas ke dalam basis data                                                                                                                                 |
| 22     | Melihat Petugas       | Merupakan proses menampilkan data petugas yang ada di dalam basis data                                                                                                                       |
| 23     | Mengubah Petugas      | Merupakan proses mengubah data petugas yang ada di dalam basis data                                                                                                                          |
| 24     | Menghapus Petugas     | Merupakan proses menghapus data petugas yang ada di dalam basis data                                                                                                                         |
| 25     | Mencari Petugas       | Merupakan proses mencari data petugas yang ada di basis data                                                                                                                                 |
| 26     | Transaksi peminjaman  | Proses peminjaman buku pustas                                                                                                                                                                |

Mengelola Pustaka merupakan proses pengelolaan data pustaka yang meliputi memasukkan pustaka, melihat pustaka, mengubah pustaka, menghapus pustaka dan mencari pustaka. Dari pernyataan tersebut bahwa memasukkan pustaka, melihat pustaka, mengubah pustaka, menghapus pustaka dan mencari pustaka merupakan sub dari mengelola pustaka sehingga membutuhkan relasi generalisasi.

Mengelola Anggota merupakan proses pengelolaan data anggota yang meliputi memasukkan anggota, melihat anggota, mengubah anggota, menghapus anggota dan mencari anggota. Dari pernyataan tersebut bahwa memasukkan anggota, melihat anggota, mengubah anggota, menghapus anggota dan mencari anggota merupakan sub dari mengelola pustaka sehingga membutuhkan relasi generalisasi.

Mengelola Peminjaman merupakan proses pengelolaan data peminjaman yang meliputi memasukkan peminjaman, melihat peminjaman, mengubah peminjaman, menghapus peminjaman dan mencari peminjaman. Dari pernyataan tersebut bahwa memasukkan peminjaman, melihat peminjaman, mengubah peminjaman, menghapus peminjaman dan mencari peminjaman merupakan sub dari mengelola pustaka sehingga membutuhkan relasi generalisasi.

Mengelola Petugas merupakan proses pengelolaan data pemtugas yang meliputi memasukkan petugas, melihat petugas, mengubah petugas, menghapus petugas dan mencari petugas. Dari pernyataan tersebut bahwa memasukkan petugas, melihat petugas, mengubah petugas, menghapus petugas dan mencari petugas merupakan sub dari mengelola pustaka sehingga membutuhkan relasi generalisasi.

![](data:image/jpeg;base64,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)![](data:image/jpeg;base64,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)

Anggota

Daru use diagram diatas dapat buat secara sederhana

Daftar Pustaka

- GUMANTI, M., GUMANTI, M., & lengkapku, L. (2018). diagram uml. Retrieved from <http://mukti362.blogspot.com/2012/09/diagram-uml.html>
- Ritonga, P., & Ritonga, P. (2018). Pengertian Unified Modeling Language ( UML ) dan Modelnya Menurut Pakar dan Ahli. Retrieved from <http://www.bangpahmi.com/2015/04/pengertian-unified-modelling-language-uml-dan-modelnya-menurut-pakar.html>
- John W. Satzinger. (2012). Introduction to systems analysis and design : an agile, iterative approach. 06. Course Technology. Canada. ISBN: 9781111972264.
- Rosa A.S. - M. Shalahuddin, tahun 2018 , Rekayasa Perangkat Lunak, cet I, Medula, Bandung
- Hass, A. M. tahun 2002, Configuration Management Principles and Pratice. Addison Wesley
- Bernd Bruegge, Stephen Krusche, Andreas Seitz, Jan Knobloch. Software Engineering Essentials. Free Online Course from Technical University of Munich trough edX (<https://courses.edx.org/courses/course-v1:TUMx+EASEx+2T2017/course/>)
- Rajendra Prasad Mahapatra dan Govind Verma. 2016. Software Engineering. Khanna Publishing
- Bernd Bruegge dan Allen H. Dutoit. 2013. Object-Oriented Software Engineering Using UML, Patterns, and Java (International Edition). Pearson.