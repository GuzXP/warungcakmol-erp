# PANDUAN IMPLEMENTASI & ROADMAP EKSEKUSI TEKNIS
## Sistem ERP Food Commerce Warung Nasi Goreng Cak Mol

---

## 1. Ikhtisar Rencana Kerja

Perombakan sistem dilakukan secara **bertahap (phased rollout)** dengan prinsip:
1. **Zero Regresi**: Jangan mengubah query atau alur yang sudah berjalan tanpa memperbarui sisi pemanggil dan penerimanya.
2. **Kesesuaian Pola Prosedural**: Tetap menggunakan pola PHP prosedural dan `mysqli` yang sudah ada, tanpa menambah framework berat yang membebani server lokal atau membuat bingung pengembang pemula.
3. **Database Integrity First**: Perbaiki struktur database dan transaksi terlebih dahulu sebelum mempercantik antarmuka.

---

## 2. Tahap 1: Migrasi Skema Database & Transaksi Atomik

### Langkah 1.1: Eksekusi SQL Migration
Jalankan perintah SQL ini pada database lokal (`nasgorcakmoldb`) via phpMyAdmin atau MySQL CLI:

```sql
-- 1. Tambah kolom nominal kasir dan pembatalan pada tabel pesanan
ALTER TABLE `pesanan` 
ADD COLUMN `uang_diterima` INT NOT NULL DEFAULT 0 AFTER `total_bayar`,
ADD COLUMN `kembalian` INT NOT NULL DEFAULT 0 AFTER `uang_diterima`,
ADD COLUMN `alasan_batal` VARCHAR(255) NULL AFTER `status_pesanan`;

-- 2. Perluas status pesanan untuk mendukung pembatalan pesanan (Void)
ALTER TABLE `pesanan` 
MODIFY COLUMN `status_pesanan` ENUM('Pending', 'Selesai', 'Batal') NOT NULL DEFAULT 'Pending';

-- 3. Tambahkan index untuk optimasi query polling kasir dan KDS
CREATE INDEX `idx_pesanan_status` ON `pesanan` (`status_pesanan`, `status_dapur`);
CREATE INDEX `idx_pesanan_waktu` ON `pesanan` (`waktu_pesan`);
```

### Langkah 1.2: Refactor `user/process_order.php`
- Ambil data keranjang dari request body POST.
- Jalankan `mysqli_begin_transaction($koneksi)`.
- Jika pesanan adalah `Dine In`, langsung kunci meja dengan `UPDATE meja SET status_meja = 'Terisi' WHERE id_meja = ?`.
- Hitung total harga riil dari tabel `menu`.
- Simpan record ke `pesanan` dan `detail_pesanan` (dengan snapshot nama dan harga).
- Eksekusi `mysqli_commit($koneksi)`.

### Langkah 1.3: Refactor `user/kasir.php`
- Tangkap nilai POST: `id_pesanan`, `metode_bayar`, `uang_diterima`.
- Hitung kembalian di backend: `$kembalian = max(0, $uang_diterima - $total_bayar)`.
- Bungkus update pesanan dan pembebasan meja (`status_meja = 'Kosong'`) dalam satu blok transaksi database.
- Redirect ke `user/cetak_struk.php?id=...` dengan parameter sukses.

---

## 3. Tahap 2: Harmonisasi Desain UI/UX Kasir Monitor & KDS

### Langkah 2.1: Pembaruan Antarmuka Kasir Monitor (`user/kasir.php`)
- Hapus class border hijau tebal (`border-2 border-green-500`).
- Ganti kartu pesanan menggunakan class:
  `bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow`
- Ganti styling badge status dapur menjadi format pil modern:
  - Siap: `bg-emerald-50 text-emerald-700 border-emerald-200`
  - Dimasak: `bg-blue-50 text-blue-700 border-blue-200`
  - Menunggu: `bg-amber-50 text-amber-700 border-amber-200`
- Perbaiki modal dialog pembayaran:
  - Input teks nominal menggunakan font besar dengan placeholder rapi.
  - Tambahkan tombol quick-preset uang pecahan (`Uang Pas`, `20k`, `50k`, `100k`).
  - Tampilkan nominal kembalian dengan warna hijau tegas.

### Langkah 2.2: Peningkatan Ergonomi KDS (`kitchen/index.php`)
- Perbesar ukuran font nama menu (`text-lg font-bold`).
- Buat penanda khusus untuk kustomisasi racikan:
  - Level pedas: `bg-rose-50 text-rose-700 border border-rose-200 px-2 py-0.5 rounded font-bold`
  - Catatan khusus koki: `bg-amber-50 text-amber-800 border border-amber-200 p-2 rounded text-xs italic font-semibold`
- Tombol aksi masakan sentuh penuh:
  - `Mulai Masak`: tombol biru tinggi 48px (`h-12`).
  - `Siap Saji`: tombol hijau tinggi 48px (`h-12`).
- Pertahankan fungsi audio alert saat ada pesanan baru masuk, dengan tombol "Aktifkan Audio Bell" di sudut atas.

---

## 4. Tahap 3: Pembenahan Admin Dashboard & Laporan Keuangan

### Langkah 3.1: Redesain Sidebar & Layout Admin (`admin/index.php`)
- Ganti background sidebar menjadi `bg-slate-900`.
- Terapkan state navigasi aktif yang rapi (border kiri oranye tipis `border-l-2 border-amber-500` dengan latar `bg-slate-800`).
- Rapikan header pengguna: "Admin Aktif: [nama]" dan tombol Logout berwarna merah lembut.

### Langkah 3.2: Filter & Paginasi Manajemen Keuangan (`admin/index.php?page=keuangan`)
- Tambahkan filter rentang tanggal di bagian atas:
  ```html
  <form method="GET" class="flex items-center gap-2 mb-4">
    <input type="hidden" name="page" value="keuangan">
    <select name="periode" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
      <option value="hari_ini">Hari Ini</option>
      <option value="7_hari">7 Hari Terakhir</option>
      <option value="bulan_ini">Bulan Ini</option>
      <option value="semua">Semua Waktu</option>
    </select>
    <button type="submit" class="bg-slate-900 text-white rounded-lg px-4 py-2 text-sm font-medium">Filter</button>
  </form>
  ```
- Batasi query riwayat pemasukan dan pengeluaran menggunakan `LIMIT ? OFFSET ?`.
- Tambahkan navigasi halaman sederhana (Sebelumnya / Halaman X / Selanjutnya).

### Langkah 3.3: Visualisasi Grafik Omzet Mingguan
- Tambahkan query agregasi omzet 7 hari terakhir:
  ```sql
  SELECT DATE(waktu_selesai) as tgl, COALESCE(SUM(total_bayar), 0) as omzet
  FROM pesanan
  WHERE status_pesanan = 'Selesai' 
    AND waktu_selesai >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY DATE(waktu_selesai)
  ORDER BY tgl ASC;
  ```
- Render grafik menggunakan Chart.js via CDN atau bar chart CSS murni yang ringan dan bersih.

---

## 5. Skenario Pengujian Menyeluruh (Testing & Verification Checklist)

Sebelum merilis sistem ke operasional nyata, lakukan pengujian pada skenario berikut:

| No | Skenario Uji | Tindakan | Hasil yang Diharapkan |
|---|---|---|---|
| 1 | **Dine-In Happy Path** | Pesan via QR Meja 1 ➔ KDS Mulai Masak ➔ KDS Siap Saji ➔ Kasir Bayar Tunai 100k ➔ Cetak Struk | Meja langsung terisi saat order dibuat, koki menerima pesanan, kasir menghitung kembalian, meja kembali kosong setelah bayar, omzet admin bertambah. |
| 2 | **Takeaway Flow** | Buka `takeaway.php`, isi nama "Budi", pesan 2 menu ➔ Bayar QRIS | Pesanan tanpa nomor meja, nama Budi muncul di KDS dan Kasir, tidak mempengaruhi status meja fisik. |
| 3 | **Kustomisasi Racikan** | Pesan Nasi Goreng Biasa Level 4 catatan "tanpa kecap" | Level pedas dan catatan tampil jelas di KDS dengan warna kontras, tercetak di struk kasir. |
| 4 | **Validasi Uang Tunai Kurang** | Total Rp 40.000, kasir input uang diterima Rp 30.000 | Sistem menolak proses bayar, tombol submit di-disable, muncul teks peringatan uang kurang. |
| 5 | **Pembatalan Pesanan (Void)** | Pesanan salah di Kasir dibatalkan dengan alasan "Pelanggan batal tunggu" | Status menjadi 'Batal', meja kembali kosong, total tidak dihitung ke omzet penjualan admin. |
| 6 | **Audit Laci Kasir Harian** | Buka rekap kasir di akhir sesi shift | Total penjualan tunai tercatat akurat sesuai jumlah uang fisik di laci kasir. |
