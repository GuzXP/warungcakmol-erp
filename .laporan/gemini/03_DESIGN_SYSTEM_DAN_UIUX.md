# SPESIFIKASI DESIGN SYSTEM & PEDOMAN UI/UX (SHADCN-STYLE)
## Sistem ERP Food Commerce Warung Nasi Goreng Cak Mol

---

## 1. Filosofi Desain: Melawan "AI Slop" & Menjaga Konsistensi

Salah satu keluhan terbesar dalam pengembangan sebelumnya adalah **desain yang belang-belang**:
- Halaman User memiliki cita rasa modern, bersih, minimalis, dan terstruktur.
- Sementara halaman Kasir dan Admin tampak seperti template hasil coba-coba AI dengan garis tebal warna-warni, kontras warna yang menusuk mata, dan tombol yang tidak proporsional.

Dokumen ini mendefinisikan **Design System Tunggal** berbasis pola **shadcn/ui** yang diterapkan seragam di semua modul.

---

## 2. Fondasi Visual (Design Tokens)

### 2.1 Palet Warna (Color Palette)

Gunakan token warna Tailwind CSS yang terstandarisasi berikut:

| Token | Nilai Hex | Penggunaan Utama |
|---|---|---|
| **Background Dasar** | `#ffffff` / `#f8fafc` (`bg-slate-50`) | Latar belakang seluruh halaman aplikasi. |
| **Surface / Card** | `#ffffff` (`bg-white`) | Kartu menu, kartu pesanan kasir, modal dialog. |
| **Border Halus** | `#e2e8f0` (`border-slate-200`) | Garis tepi kartu, tabel, dan pemisah konten. |
| **Teks Primer** | `#0f172a` (`text-slate-900`) | Judul, nama menu, angka total harga. |
| **Teks Sekunder** | `#64748b` (`text-slate-500`) | Deskripsi, jam order, label form. |
| **Brand Primary** | `#0f172a` / `#18181b` (`zinc-900`) | Tombol aksi utama (Tambah, Selesaikan, Simpan). |
| **Brand Accent (Warm)** | `#f59e0b` (`amber-500`) | Aksen logo Cak Mol & badge Best Seller. |
| **Status Menunggu** | `bg-amber-50 text-amber-700 border-amber-200` | Badge pesanan belum dimasak. |
| **Status Dimasak** | `bg-blue-50 text-blue-700 border-blue-200` | Badge pesanan sedang dimasak di dapur. |
| **Status Siap / Selesai** | `bg-emerald-50 text-emerald-700 border-emerald-200` | Badge makanan siap saji / pembayaran selesai. |
| **Status Bahaya / Hapus** | `bg-rose-50 text-rose-700 border-rose-200` | Tombol hapus, pesan error, uang kurang. |

> **Aturan Tegas Anti-Slop**:
> Jangan pernah menggunakan border berwarna solid tebal seperti `border-2 border-green-500` di seluruh kartu antrean. Status pesanan cukup ditunjukkan oleh **badge pil kecil yang elegan di pojok kartu**, bukan dengan mewarnai seluruh tepi kartu.

---

### 2.2 Tipografi & Skala Font
Font dasar: `Plus Jakarta Sans`, `Inter`, atau sans-serif modern.

- **Display / Hero**: `text-3xl font-extrabold tracking-tight text-slate-900`
- **Heading 1 (Judul Halaman)**: `text-2xl font-bold tracking-tight text-slate-900`
- **Heading 2 (Judul Kartu/Modal)**: `text-lg font-semibold text-slate-900`
- **Body Regular (Teks Biasa)**: `text-sm font-normal text-slate-600`
- **Body Medium (Label & Nama Menu)**: `text-sm font-medium text-slate-900`
- **Caption / Meta**: `text-xs font-normal text-slate-400`
- **Badge Text**: `text-[11px] font-semibold tracking-wide uppercase`

---

## 3. Komponen Standar (Shadcn-Inspired UI Components)

### 3.1 Kartu Antrean Kasir (`Kasir Order Card`)

**Kondisi Lama (Bermasalah)**:
Border hijau terang 2px tebal (`border-green-500`), teks level pedas merah muda mencolok, tidak ada hirarki visual.

**Desain Standar Baru**:
```html
<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
  <!-- Card Header -->
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <div class="flex items-center gap-2">
      <span class="text-base font-bold text-slate-900">MEJA 1</span>
      <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium">Dine In</span>
    </div>
    <div class="flex items-center gap-2">
      <span class="text-xs text-slate-400">19:00</span>
      <!-- Subtle Status Badge -->
      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
        Dapur: Siap
      </span>
    </div>
  </div>

  <!-- Items List -->
  <div class="py-4 space-y-3">
    <div class="flex items-start justify-between text-sm">
      <div class="flex-1 pr-2">
        <span class="font-medium text-slate-900">1x Nasi Goreng Gila</span>
        <span class="ml-2 inline-flex items-center px-1.5 py-0.2 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Level 4</span>
        <p class="text-xs text-slate-500 italic mt-0.5">"tanpa acar, banyakin kerupuk"</p>
      </div>
      <span class="font-semibold text-slate-900">Rp 20.000</span>
    </div>
  </div>

  <!-- Card Footer -->
  <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
    <div>
      <span class="text-xs text-slate-500 block">Total Tagihan</span>
      <span class="text-lg font-bold text-slate-900">Rp 20.000</span>
    </div>
    <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition-colors shadow-sm">
      Proses Pembayaran
    </button>
  </div>
</div>
```

---

### 3.2 Modal Dialog Pembayaran Kasir (`Payment Modal`)

Pola dialog bersih dengan backdrop blur lembut:
- **Header Modal**: Judul "Pembayaran - Meja 1" dengan tombol close (X) di sudut kanan atas.
- **Total Tagihan Highlight**: Angka besar `text-3xl font-extrabold text-slate-900` di dalam box abu-abu lembut (`bg-slate-50 rounded-xl p-4`).
- **Tab Pilihan Metode**:
  - `Cash / Tunai` vs `QRIS Nontunai`.
  - Tombol tab aktif: Border gelap atau border hijau halus dengan background lembut, bukan tombol mencolok yang bikin sakit mata.
- **Preset Nominal Uang Tunai**:
  - `[ Uang Pas ]`, `[ 20k ]`, `[ 50k ]`, `[ 100k ]`.
  - Gaya tombol: `border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg px-3 py-1.5 text-xs font-semibold`.
- **Indikator Kembalian**:
  - Jika uang cukup: Teks hijau tua `text-emerald-600 font-bold text-lg`.
  - Jika uang kurang: Teks peringatan merah `text-rose-600 font-medium text-sm` dan tombol konfirmasi di-disable.

---

### 3.3 Kartu Tampilan KDS (Dapur)

Layar dapur memiliki kebutuhan ergonomi visual khusus:
1. **Keterbacaan Jarak Jauh**: Ukuran font nama menu minimal `text-base` atau `text-lg`.
2. **Sorotan Instruksi Racikan**:
   - Catatan seperti *"pedas gila level 4"* atau *"tanpa kecap manis"* diberi penanda blok kuning muda bergaris tepi:
     `<div class="mt-1 bg-amber-50 text-amber-900 border border-amber-200 rounded px-2 py-1 text-xs font-semibold">Catatan: mecin dikit tanpa acar</div>`
3. **Tombol Eksekusi Sentuh Cepat**:
   - Tombol lebar dengan tinggi minimal `h-12` agar mudah ditekan koki meski menggunakan sarung tangan atau jari berminyak:
     - Tombol Mulai: `bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold text-sm w-full py-3`
     - Tombol Siap: `bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-sm w-full py-3`

---

### 3.4 Desain Admin Panel & Navigasi Sidebar

**Transformasi Sidebar**:
- Ganti warna navy pekat dengan **Slate/Zinc-900** (`#0f172a`).
- Brand Header: Icon Chef Hat warna oranye hangat lembut + teks putih tebal "CAK MOL POS".
- Link Navigasi:
  - Default: `text-slate-400 hover:text-white hover:bg-slate-800/60 rounded-lg px-3 py-2 text-sm font-medium flex items-center gap-3`.
  - Aktif: `bg-slate-800 text-white shadow-sm border-l-2 border-amber-500`.
- Hindari penggunaan blok oranye terang yang menutupi seluruh tombol navigasi aktif.

**Transformasi Metrik Dashboard**:
- 4 Kartu Metrik Ringkasan:
  - Mengganti strip warna-warni atas (oranye, hijau, merah, biru) menjadi kartu putih minimalis dengan icon halus di pojok kanan atas.
  - Hirarki:
    1. Label kecil: `text-xs font-semibold uppercase tracking-wider text-slate-500`
    2. Angka utama: `text-2xl font-bold text-slate-900 mt-1`
    3. Indikator tambahan: `text-xs text-emerald-600 mt-1` (misal: "↑ 12% dari minggu lalu")
