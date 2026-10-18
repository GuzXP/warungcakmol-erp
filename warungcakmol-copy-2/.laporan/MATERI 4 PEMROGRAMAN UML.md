|     | **MODUL PERKULIAHAN**                   |
| --- | --------------------------------------- |
|     |                                         |
|     | **Pratikum**<br><br>**Pemrograman UML** |
|     |                                         |
|     |                                         |
|     |                                         |

|     | **Fakultas**  | **Program Studi**  | **Tatap Muka** | **Kode MK** | **Disusun Oleh**                   |     |
| --- | ------------- | ------------------ | -------------- | ----------- | ---------------------------------- | --- |
|     | Ilmu Komputer | Teknik Informatika | **04**         |             | Essy Malays Sari Sakti, Skom.MMSI. |     |

| **Abstract**     | **Kompetensi**                                            |
| ---------------- | --------------------------------------------------------- |
| Scenario Diagram | Mahasiswa memahami cara merancang dengan Scenario diagram |
|                  |                                                           |

# Pengertian Scenario Diagram

Setiap use case diagram dilengkapi dengan skenario, skenario use case / use case skenario adalah alur jalannya proses use case dari sisi aktor dan system. Berikut adalah format tabel skenario use case diagram:

:

| **Aksi Aktor**      | **Reaksi Sistem** |
| ------------------- | ----------------- |
| Skenario Normal     |                   |
|                     |                   |
|                     |                   |
|                     |                   |
|                     |                   |
| Skenario Alternatif |                   |
|                     |                   |
|                     |                   |
|                     |                   |

Skenario use case dibuat per use case terkecil, misalkan untuk generalisasi maka scenario yang dibuat adalah use case yang lebih khusus. Skenario normal adalah scenario bila system berjalan normal tanpa terjadi kesalahan atau error. Sedangkan skenario alternatif adalah scenario bila system tidak berjalan normal atau mengalami error. Skenario normal dan skenario alternatif dapat berjumlah lebih dari satu. Alur skenario inilah yang nantinya menjadi landasan pembuatan sequence diagram / diagram sekuen.

Dari studi kasus di materi sebelumnya maka Berikut adalah hasil pendefinisian beberapa use case skenaio (tidak kami sertakan semua) dari masing-masing use case yang telah didefinisikan sebelumnya:

Setiap usecase harus dibuat semua scenarionnya  
<br/>

Nama Use Case : Login

Skenario:

| **Aksi Aktor**                                  | **Reaksi Sistem**                                                           |
| ----------------------------------------------- | --------------------------------------------------------------------------- |
| Skenario Normal                                 |                                                                             |
| 1\. Memasukka username dan password             |                                                                             |
|                                                 | 2\. Memeriksa valid tidaknya data masukan dengan memeriksa ke tabel petugas |
|                                                 | 3\. Masuk ke aplikasi pengelolaan data perpustakaan                         |
| Skenario Alternatif                             |                                                                             |
| 1\. Memasukkan username dan password            |                                                                             |
|                                                 | 2\. Memeriksa valid tidaknya data masukan dengan memeriksa ke tabel petugas |
|                                                 | 3\. Menampilkan pesan login tidak valid                                     |
| 4\. Memasukkan username dan password yang valid |                                                                             |
|                                                 | 5\. Memeriksa valid tidaknya data masukan dengan memriksa ke tabel petugas  |
|                                                 | 6\. Masuk ke aplikasi pengelolaan data perpustakaan                         |

Nama Use Case : Memasukkan Pustaka  
Skenario:

| **Aksi Aktor**                                    | **Reaksi Sistem**                                     |
| ------------------------------------------------- | ----------------------------------------------------- |
| Skenario Normal                                   |                                                       |
|                                                   | 1\. Memeriksa status login                            |
| 2\. Memasukkan data pustaka sesuai kolom yang ada |                                                       |
|                                                   | 3\. Memeriksa valid tidaknya data masukan             |
|                                                   | 4\. Menyimpan data pustaka ke basis data              |
|                                                   | 5\. Menampilkan pesan sukses disimpan                 |
| Skenario Alternatif                               |                                                       |
|                                                   | 1\. Memeriksa status login                            |
| 2\. Memasukkan data pustaka sesuai kolom yang ada |                                                       |
|                                                   | 3\. Memeriksa valid tidaknya data masukan             |
|                                                   | 4\. Mengeluarkan pesan bahwa data masukan tidak valid |
| 5\. Memperbaiki data masukan yang tidak valid     |                                                       |
|                                                   | 6\. Memeriksa valid tidaknya data masukan             |
|                                                   | 7\. Menyimpan data pustaka ke basis data              |
|                                                   | 8\. Menampilkan pesan sukses disimpan                 |

Nama Use Case : Melihat Pustaka  
Skenario:

| **Aksi Aktor**                  | **Reaksi Sistem**                                                                                                |
| ------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Skenario Normal                 |                                                                                                                  |
|                                 | 1\. Memeriksa status login                                                                                       |
|                                 | 2\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 3\. Memilih pustaka yang dicari |                                                                                                                  |
|                                 | 4\. Menampilkan data pustaka (detail sebuah data pustaka) dari pustaka yang dipilih                              |

Nama Use Case : Mengubah Pustaka  
Skenario:

| **Aksi Aktor**                                            | **Reaksi Sistem**                                                                                                |
| --------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Skenario Normal                                           |                                                                                                                  |
|                                                           | 1\. Memeriksa status login                                                                                       |
| 2\. Memasukkan kata kunci dan kategori pencarian          |                                                                                                                  |
|                                                           | 3\. Mencari data pustaka yang akan diubah                                                                        |
|                                                           | 4\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 5\. Memilih data pustaka yang akan diubah                 |                                                                                                                  |
|                                                           | 6\. Menampilkan data pustaka (detail sebuah data pustaka) dari pustaka yang akan diubah                          |
| 7\. Mengubah data pustaka                                 |                                                                                                                  |
|                                                           | 8\. Memeriksa valid tidaknya data masukan                                                                        |
|                                                           | 9\. Menyimpan data yang telah diubah ke basis data                                                               |
|                                                           | 10\. Menampilkan pesan bahwa data sukses disimpan                                                                |
| Skenario Alternatif                                       |                                                                                                                  |
|                                                           | 1\. Memeriksa status login                                                                                       |
| 2\. Memasukkan kata kunci dan kategori pencarian          |                                                                                                                  |
|                                                           | 3\. Mencari data pustaka yang akan diubah                                                                        |
|                                                           | 4\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 5\. Memilih data pustaka yang akan diubah                 |                                                                                                                  |
|                                                           | 6\. Menampilkan data pustaka (detail sebuah data pustaka) dari pustaka yang akan diubah                          |
| 7\. Mengubah data pustaka                                 |                                                                                                                  |
|                                                           | 8\. Memeriksa valid tidaknya data masukan                                                                        |
|                                                           | 9\. Menampilkan pesan bahwa data masukan tidak valid                                                             |
| 10\. Memperbaiki data masukan yang diubah dan tidak valid |                                                                                                                  |
|                                                           | 11\. Memeriksa valid tidaknya data masukan                                                                       |
|                                                           | 12\. Menyimpan data yang telah diubah ke basis data                                                              |
|                                                           | 13\. Menampilkan pesan bahwa data sukses disimpan                                                                |

Nama Use Case : Menghapus Pustaka  
Skenario:

| **Aksi Aktor**                                   | **Reaksi Sistem**                                                                                                |
| ------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| Skenario Normal                                  |                                                                                                                  |
|                                                  | 1\. Memeriksa status login                                                                                       |
| 2\. Memasukkan kata kunci dan kategori pencarian |                                                                                                                  |
|                                                  | 3\. Mencari data pustaka yang akan dihapus                                                                       |
|                                                  | 4\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 5\. Memilih data pustaka yang akan dihapus       |                                                                                                                  |
|                                                  | 6\. Menampilkan pesan konfirmasi apakah data akan benar-benar dihapus                                            |
| 7\. Mengklik pilihan setuju data dihapus         |                                                                                                                  |
|                                                  | 8\. Menghapus data pustaka dari basis data                                                                       |
|                                                  | 9\. Menampilkan pesan bahwa data sukses dihapus                                                                  |
| Skenario Alternatif                              |                                                                                                                  |
|                                                  | 1\. Memeriksa status login                                                                                       |
| 2\. Memasukkan kata kunci dan kategori pencarian |                                                                                                                  |
|                                                  | 3\. Mencari data pustaka yang akan dihapus                                                                       |
|                                                  | 4\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 5\. Memilih data pustaka yang akan dihapus       |                                                                                                                  |
|                                                  | 6\. Menampilkan pesan konfirmasi apakah data akan benar-benar dihapus                                            |
| 7\. Mengklik pilihan tidak setuju data dihapus   |                                                                                                                  |
|                                                  | 8\. Kembali ke form pencarian pustaka                                                                            |

Nama Use Case : Mencari Pustaka  
Skenario:

| **Aksi Aktor**                                   | **Reaksi Sistem**                                                                                                |
| ------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------- |
| Skenario Normal                                  |                                                                                                                  |
| 1\. Memasukkan kata kunci dan kategori pencarian |                                                                                                                  |
|                                                  | 2\. Mencari data pustaka yang dicari                                                                             |
|                                                  | 3\. Menampilkan data pustaka yang dicari (belum detail, dan tampil bentuk list)                                  |
| 4\. Memilih data pustaka yang akan dicari        |                                                                                                                  |
|                                                  | 5\. Menampilkan data pustaka (detail sebuah data pustaka) dari pustaka yang dipilih                              |
| Skenario Alternatif                              |                                                                                                                  |
| 1\. Memasukkan kata kunci dan kategori pencarian |                                                                                                                  |
|                                                  | 2\. Mencari data pustaka yang dicari                                                                             |
|                                                  | 3\. Menampilkan pesan data pustaka tidak ada                                                                     |
| 4\. Memasukkan kata kunci dan kategori pencarian |                                                                                                                  |
|                                                  | 5\. Mencari data pustaka yang dicari                                                                             |
|                                                  | 6\. Menampilkan data pustaka yang dicari (belum detail, missal hanya judulnya saja dan tampil dalam bentuk list) |
| 7\. Memilih data pustaka yang akan dicari        |                                                                                                                  |
|                                                  | 8\. Menampilkan data pustaka (detail sebuah data pustaka) dari pustaka yang dipilih                              |

# Tugas

Lengkapi Scenario dari Sistem Informasi Manajemen Perpustakaan:

Dikumpul

Daftar Pustaka

- GUMANTI, M., GUMANTI, M., & lengkapku, L. (2018). diagram uml. Retrieved from <http://mukti362.blogspot.com/2012/09/diagram-uml.html>
- Ritonga, P., & Ritonga, P. (2018). Pengertian Unified Modeling Language ( UML ) dan Modelnya Menurut Pakar dan Ahli. Retrieved from <http://www.bangpahmi.com/2015/04/pengertian-unified-modelling-language-uml-dan-modelnya-menurut-pakar.html>
- John W. Satzinger. (2012). Introduction to systems analysis and design : an agile, iterative approach. 06. Course Technology. Canada. ISBN: 9781111972264.
- Rosa A.S. - M. Shalahuddin, tahun 2018 , Rekayasa Perangkat Lunak, cet I, Medula, Bandung
- Hass, A. M. tahun 2002, Configuration Management Principles and Pratice. Addison Wesley
- Bernd Bruegge, Stephen Krusche, Andreas Seitz, Jan Knobloch. Software Engineering Essentials. Free Online Course from Technical University of Munich trough edX (<https://courses.edx.org/courses/course-v1:TUMx+EASEx+2T2017/course/>)
- Rajendra Prasad Mahapatra dan Govind Verma. 2016. Software Engineering. Khanna Publishing
- Bernd Bruegge dan Allen H. Dutoit. 2013. Object-Oriented Software Engineering Using UML, Patterns, and Java (International Edition). Pearson.