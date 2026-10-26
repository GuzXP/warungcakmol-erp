# Diagram Perancangan Sistem Cak Mol POS
## Diagram 7 — Class Diagram dan Diagram 8 — Entity Relationship Diagram (ERD)

**Codebase acuan:** aplikasi aktif di root `warungcakmol-copy`

**Sumber model data:** `nasgorcakmoldb (2).sql`

**Cakupan:** diagram konseptual dan struktur database yang benar-benar digunakan codebase terbaru.
**Batas analisis:** folder `warungcakmol-copy-2/` tidak termasuk.

> **Catatan representasi:** Source aplikasi menggunakan PHP prosedural, bukan class PHP. Karena itu Class Diagram di bawah memodelkan entitas database sebagai kelas domain dan menggambarkan fungsi/endpoint prosedural sebagai modul operasional dengan operasi representatif. Operasi tersebut menunjukkan tanggung jawab yang dijalankan oleh fungsi atau file terkait; diagram ini **tidak** menyatakan bahwa source sudah mendefinisikan kelas PHP atau metode OOP tersebut. ERD, sebaliknya, mengikuti tabel dan foreign key pada dump SQL.

---

# 7. Class Diagram

## 7.1 Ringkasan model

- Kelas domain `Menu`, `Kategori`, `Meja`, `Pesanan`, `DetailPesanan`, `User`, `UangMasuk`, `UangKeluar`, dan `Pengaturan` merepresentasikan tabel pada dump database.
- Atribut kelas mengikuti nama dan tipe kolom database. Primary key serta foreign key ditandai pada atribut.
- Operasi domain tidak dipaksakan sebagai metode entitas. Tanggung jawab aktual ditempatkan pada kelas berstereotip `<<modul prosedural>>` atau `<<endpoint>>`, lalu dipetakan ke fungsi/file source yang menjalankannya.
- Hubungan `Kategori`–`Menu` **tidak** digambar sebagai relasi database: `menu.kategori` menyimpan teks nama kategori dan tidak memiliki foreign key ke `kategori`.
- Status meja/pesanan, tipe pesanan, dan metode pembayaran menggunakan nilai enum yang sama dengan schema.

## 7.2 Diagram PlantUML

```plantuml
@startuml
title Diagram 7 — Class Diagram Cak Mol POS (Model Konseptual dari Codebase Prosedural)
left to right direction
skinparam classAttributeIconSize 0
hide empty members

package "Model domain / tabel database" {
  class Menu <<entity>> {
    +id_menu: int {PK}
    +nama_menu: varchar(100)
    +harga: int
    +deskripsi: text [nullable]
    +kategori: varchar(50)
    +gambar: varchar(255) [nullable]
    +badge: varchar(50) [nullable]
    +badge_color: varchar(50) [nullable]
  }

  class Kategori <<entity>> {
    +id_kategori: int {PK}
    +nama_kategori: varchar(100)
    +icon_name: varchar(50) [nullable]
    +urutan: int [nullable]
  }

  class Meja <<entity>> {
    +id_meja: int {PK}
    +nama_meja: varchar(50)
    +status_meja: enum(Kosong, Terisi) [nullable, default Kosong]
    +kode_qr: varchar(100) [nullable]
  }

  class Pesanan <<entity>> {
    +id_pesanan: int {PK}
    +id_meja: int {FK, nullable}
    +total_bayar: int
    +status_pesanan: enum(Pending, Selesai) [nullable, default Pending]
    +waktu_pesan: datetime [nullable, default current_timestamp]
    +nama_pelanggan: varchar(100) [nullable]
    +tipe_pesanan: enum(Dine In, Takeaway) [nullable, default Dine In]
    +metode_bayar: enum(Cash, QRIS) [nullable]
    +waktu_selesai: datetime [nullable]
    +status_dapur: enum(Menunggu, Dimasak, Siap) [nullable, default Menunggu]
  }

  class DetailPesanan <<entity>> {
    +id_detail: int {PK}
    +id_pesanan: int {FK}
    +id_menu: int {FK}
    +nama_menu_snapshot: varchar(100)
    +harga_snapshot: int
    +jumlah: int
    +level_pedas: varchar(50) [nullable]
    +catatan: text [nullable]
  }

  class User <<entity>> {
    +id: int {PK}
    +nama: varchar(100)
    +username: varchar(50)
    +password: varchar(255)
  }

  class UangMasuk <<entity>> {
    +id: int {PK}
    +sumber: varchar(200)
    +jumlah: int
    +tanggal: date
    +keterangan: text [nullable]
    +created_at: datetime [nullable]
  }

  class UangKeluar <<entity>> {
    +id: int {PK}
    +jenis: enum(Belanja Bahan, Operasional, Lainnya)
    +jumlah: int
    +tanggal: date
    +keterangan: text [nullable]
    +foto_struk: varchar(255) [nullable]
    +created_at: datetime [nullable]
  }

  class Pengaturan <<entity>> {
    +kunci: varchar(100) {PK}
    +nilai: text
  }
}

package "Modul aplikasi PHP prosedural" {
  class OrderEndpoint <<endpoint>> {
    +processOrder(): JSON response
    --
    file: user/process_order.php
  }

  class KitchenPollingEndpoint <<endpoint>> {
    +getActiveOrders(): JSON response
    --
    file: kitchen/poll_kitchen.php
  }

  class KitchenStatusEndpoint <<endpoint>> {
    +updateKitchenStatus(): JSON response
    --
    file: kitchen/update_status.php
  }

  class CashierModule <<modul prosedural>> {
    +confirmPayment(): redirect
    +pollOrderChanges(): JSON response
    --
    files: user/kasir.php, user/poll_orders.php
  }

  class ReceiptPage <<halaman PHP>> {
    +renderAndPrintReceipt(): HTML
    --
    file: user/cetak_struk.php
  }

  class AdminMenuFunctions <<modul prosedural>> {
    +query(sql, params, types): array
    +tambah(data): int|false
    +edit(data): int|false
    +hapus(id): int|false
    +search(keyword): array
    +upload(): string|false
    +registrasi(data): int|false
    --
    file: admin/fungsi.php
  }

  class AdminFinanceModule <<modul prosedural>> {
    +recordIncome(): database operation
    +recordExpense(): database and file operation
    +buildFinancialReport(): PDF
    --
    files: admin/index.php, admin/cetakLaporan.php
  }

  class AdminSettingsModule <<modul prosedural>> {
    +saveSettings(): database operation
    --
    file: admin/index.php
  }
}

Meja "0..1" -- "0..*" Pesanan : FK id_meja\nnullable untuk takeaway
Pesanan "1" -- "0..*" DetailPesanan : FK id_pesanan
Menu "1" -- "0..*" DetailPesanan : FK id_menu

OrderEndpoint ..> Menu : membaca nama/harga resmi
OrderEndpoint ..> Pesanan : membuat header
OrderEndpoint ..> DetailPesanan : membuat snapshot item

KitchenPollingEndpoint ..> Pesanan : membaca antrean Pending
KitchenPollingEndpoint ..> DetailPesanan : membaca detail
KitchenStatusEndpoint ..> Pesanan : memperbarui status dapur
KitchenStatusEndpoint ..> Meja : memperbarui status meja

CashierModule ..> Pesanan : membaca dan menyelesaikan order
CashierModule ..> Meja : mengosongkan meja setelah pembayaran
ReceiptPage ..> Pesanan : membaca header struk
ReceiptPage ..> DetailPesanan : membaca item snapshot

AdminMenuFunctions ..> Menu : CRUD dan pencarian
AdminMenuFunctions ..> Kategori : membaca pilihan kategori
AdminMenuFunctions ..> User : registrasi akun
AdminFinanceModule ..> Pesanan : pemasukan order Selesai
AdminFinanceModule ..> UangMasuk : membaca dan mencatat pemasukan manual
AdminFinanceModule ..> UangKeluar : membaca dan mencatat pengeluaran
AdminSettingsModule ..> Pengaturan : membaca dan menyimpan key/value

note bottom of Kategori
  Tidak ada relasi FK ke Menu.
  menu.kategori menyimpan teks
  nama kategori.
end note

note bottom of User
  Schema tidak menyimpan role.
  Admin dan kasir memakai
  mekanisme sesi yang sama.
end note

note bottom of DetailPesanan
  nama_menu_snapshot dan harga_snapshot
  menyimpan nilai saat order dibuat;
  bukan atribut yang dibaca ulang
  dari katalog untuk histori.
end note

note bottom of OrderEndpoint
  Nama operasi adalah representasi
  tanggung jawab script PHP,
  bukan metode class PHP aktual.
end note
@enduml
```

## 7.3 Pemetaan operasi ke source

| Operasi representatif | Implementasi yang dipetakan |
|---|---|
| `OrderEndpoint.processOrder()` | Skrip `user/process_order.php`: memvalidasi payload, membaca harga dari `menu`, lalu menyimpan `pesanan` dan `detail_pesanan` dalam transaksi. |
| `KitchenPollingEndpoint.getActiveOrders()` | `kitchen/poll_kitchen.php`: membaca order Pending berstatus dapur Menunggu/Dimasak beserta detail. |
| `KitchenStatusEndpoint.updateKitchenStatus()` | `kitchen/update_status.php`: menerima POST JSON dan mengubah status dapur; saat status Siap, juga mencoba mengubah status meja dine-in. |
| `CashierModule.confirmPayment()` | Handler POST `selesaikan_order` pada `user/kasir.php`: mengubah status pembayaran, metode, waktu selesai, dan status meja. |
| `CashierModule.pollOrderChanges()` | `user/poll_orders.php`: mengirim jumlah order Pending dan hash status untuk polling kasir. |
| `ReceiptPage.renderAndPrintReceipt()` | `user/cetak_struk.php`: mengambil header/detail dan menampilkan struk yang memicu dialog print browser. |
| `AdminMenuFunctions.query/tambah/edit/hapus/search/upload/registrasi` | Fungsi prosedural bernama sama di `admin/fungsi.php`. Signature/return ditampilkan secara ringkas; perilaku tetap milik fungsi PHP yang ada. |
| `AdminFinanceModule.recordIncome()` | Handler POST `tambah_uang_masuk` di `admin/index.php`. |
| `AdminFinanceModule.recordExpense()` | Handler POST `tambah_uang_keluar` di `admin/index.php`, termasuk file bukti opsional. |
| `AdminFinanceModule.buildFinancialReport()` | Perhitungan dan tampilan keuangan di `admin/index.php`, serta PDF di `admin/cetakLaporan.php`. |
| `AdminSettingsModule.saveSettings()` | Handler POST `simpan_pengaturan` di `admin/index.php`, yang melakukan upsert key/value. |

---

# 8. Entity Relationship Diagram (ERD)

## 8.1 Aturan pembacaan

- `PK` menandai primary key; `FK` menandai foreign key yang benar-benar didefinisikan pada dump.
- Atribut bertanda `[nullable]` mengizinkan nilai `NULL` pada definisi kolom. `NOT NULL` tidak diberi tanda nullable.
- Kardinalitas relasi diturunkan dari foreign key database, bukan dari asumsi UI.
- Pesanan dapat memiliki `id_meja = NULL` untuk takeaway; jika terhubung ke meja, satu order merujuk paling banyak satu meja. Satu meja dapat direferensikan banyak order sepanjang histori.
- Setiap baris `detail_pesanan` harus merujuk tepat satu `pesanan` dan satu `menu`; order/menu dapat memiliki nol atau banyak baris detail menurut struktur FK.
- Tidak ada relasi FK antara `menu.kategori` dan `kategori.nama_kategori`, meskipun aplikasi mencocokkan keduanya sebagai teks.
- Tabel `user`, `uang_masuk`, `uang_keluar`, dan `pengaturan` tidak memiliki foreign key dalam dump.

## 8.2 Diagram PlantUML

```plantuml
@startuml
title Diagram 8 — ERD Database Cak Mol POS (nasgorcakmoldb)
hide circle
skinparam linetype ortho
skinparam entity {
  BackgroundColor #F8FAFC
  BorderColor #334155
}

entity "meja" as meja {
  * id_meja : int <<PK>>
  --
  nama_meja : varchar(50)
  status_meja : enum(Kosong, Terisi) [NULL, default Kosong]
  kode_qr : varchar(100) [NULL]
}

entity "pesanan" as pesanan {
  * id_pesanan : int <<PK>>
  --
  id_meja : int <<FK, NULL>>
  total_bayar : int
  status_pesanan : enum(Pending, Selesai) [NULL, default Pending]
  waktu_pesan : datetime [NULL, default current_timestamp]
  nama_pelanggan : varchar(100) [NULL]
  tipe_pesanan : enum(Dine In, Takeaway) [NULL, default Dine In]
  metode_bayar : enum(Cash, QRIS) [NULL]
  waktu_selesai : datetime [NULL]
  status_dapur : enum(Menunggu, Dimasak, Siap) [NULL, default Menunggu]
}

entity "detail_pesanan" as detail_pesanan {
  * id_detail : int <<PK>>
  --
  id_pesanan : int <<FK>>
  id_menu : int <<FK>>
  nama_menu_snapshot : varchar(100)
  harga_snapshot : int
  jumlah : int
  level_pedas : varchar(50) [NULL, default Tidak Pedas]
  catatan : text [NULL]
}

entity "menu" as menu {
  * id_menu : int <<PK>>
  --
  nama_menu : varchar(100)
  harga : int
  deskripsi : text [NULL]
  kategori : varchar(50)
  gambar : varchar(255) [NULL]
  badge : varchar(50) [NULL]
  badge_color : varchar(50) [NULL]
}

entity "kategori" as kategori {
  * id_kategori : int <<PK>>
  --
  nama_kategori : varchar(100)
  icon_name : varchar(50) [NULL, default utensils]
  urutan : int [NULL, default 0]
}

entity "user" as user {
  * id : int <<PK>>
  --
  nama : varchar(100)
  username : varchar(50)
  password : varchar(255)
}

entity "uang_masuk" as uang_masuk {
  * id : int <<PK>>
  --
  sumber : varchar(200)
  jumlah : int
  tanggal : date
  keterangan : text [NULL]
  created_at : datetime [NULL, default current_timestamp]
}

entity "uang_keluar" as uang_keluar {
  * id : int <<PK>>
  --
  jenis : enum(Belanja Bahan, Operasional, Lainnya)
  jumlah : int
  tanggal : date
  keterangan : text [NULL]
  foto_struk : varchar(255) [NULL]
  created_at : datetime [NULL, default current_timestamp]
}

entity "pengaturan" as pengaturan {
  * kunci : varchar(100) <<PK>>
  --
  nilai : text
}

meja "0..1" -- "0..*" pesanan : FK id_meja\nON DELETE SET NULL
pesanan "1" -- "0..*" detail_pesanan : FK id_pesanan
menu "1" -- "0..*" detail_pesanan : FK id_menu

note bottom of kategori
  Tidak ada FK ke menu.
  menu.kategori berisi teks
  yang dicocokkan dengan
  kategori.nama_kategori.
end note

note bottom of user
  Tidak ada atribut role
  pada schema.
end note

note bottom of detail_pesanan
  FK id_pesanan dan id_menu
  tidak didefinisikan dengan
  ON DELETE CASCADE.
end note
@enduml
```

## 8.3 Ringkasan kardinalitas dan constraint

| Relasi | Kardinalitas | Bukti/aturan database |
|---|---|---|
| `meja` — `pesanan` | Satu meja dapat direferensikan `0..*` pesanan; satu pesanan mereferensikan `0..1` meja. | FK `pesanan.id_meja` nullable ke `meja.id_meja`; `ON DELETE SET NULL`, `ON UPDATE CASCADE`. NULL dipakai untuk takeaway. |
| `pesanan` — `detail_pesanan` | Satu pesanan mempunyai `0..*` detail; setiap detail merujuk tepat `1` pesanan. | FK `detail_pesanan.id_pesanan` NOT NULL ke `pesanan.id_pesanan`; tidak ada `ON DELETE CASCADE`. Batas minimal satu detail berasal dari validasi alur checkout, bukan constraint relasional. |
| `menu` — `detail_pesanan` | Satu menu dapat direferensikan `0..*` detail; setiap detail merujuk tepat `1` menu. | FK `detail_pesanan.id_menu` NOT NULL ke `menu.id_menu`; tidak ada `ON DELETE CASCADE`. |
| `kategori` — `menu` | Tidak dimodelkan sebagai relasi FK. | `menu.kategori` menyimpan `varchar(50)` dan pencocokan ke `kategori.nama_kategori` dilakukan pada aplikasi. |
| `user`, `uang_masuk`, `uang_keluar`, `pengaturan` | Tidak mempunyai relasi FK pada dump. | Masing-masing berdiri sebagai tabel independen; tidak dibuat hubungan yang tidak ada di schema. |

## 8.4 Pemeriksaan diagram

Diagram ditulis sebagai kode PlantUML dan struktur kelas/tabel, atribut, penanda PK/FK serta kardinalitas diperiksa agar selaras dengan definisi pada dump SQL. CLI PlantUML tidak tersedia pada environment saat dokumen dibuat, sehingga diagram belum diklaim berhasil dirender. Untuk validasi visual akhir, salin setiap blok PlantUML ke renderer lokal atau ekstensi PlantUML editor.
