# ANALISIS SISTEM BISNIS WARUNG NASI GORENG CAK MOL

**Proyek Pemrograman UML** | **Penulis:** Bagus Ramadhan (2491051003) | **Tanggal:** 30 Maret 2025

---

## 📊 DAFTAR ISI

1. [Analisis Sistem Berjalan](#-analisis-sistem-berjalan)
2. [Analisis Sistem Usulan](#-analisis-sistem-usulan)
3. [Kebutuhan Fungsional](#-kebutuhan-fungsional)
4. [Kebutuhan Non-Fungsional](#-kebutuhan-non-fungsional)
5. [Use Case Diagram](#-use-case-diagram)

---

## 🔍 ANALISIS SISTEM BERJALAN

### 1.1 Ringkasan Sistem Existing

Warung Nasi Goreng Cak Mol adalah usaha kuliner yang telah beroperasi sejak **1998 di Pulomas**. Saat ini, sistem operasional warung masih **manual dan semi-digital**, dengan banyak proses yang dilakukan secara tradisional.

### 1.2 Proses Bisnis Berjalan Saat Ini

| #   | Proses               | Deskripsi                             | Media               |
| --- | -------------------- | ------------------------------------- | ------------------- |
| 1   | **Manajemen Menu**   | Menu ditulis manual atau spreadsheet  | Papan tulis / Excel |
| 2   | **Pemesanan**        | Pelanggan langsung memesan ke kasir   | Percakapan langsung |
| 3   | **Pencatatan**       | Kasir catat di kertas atau buku besar | Kertas & Pen        |
| 4   | **Komunikasi Dapur** | Pesanan disampaikan lisan atau kertas | Verbal / Teks       |
| 5   | **Tracking Pesanan** | Tidak ada                             | Manual checking     |
| 6   | **Pembayaran**       | Tunai langsung di tempat              | Cash handler        |
| 7   | **Laporan**          | Rekapitulasi manual end-of-day        | Kertas / Kalkulator |

### 1.3 Kelemahan Sistem Berjalan

```
┌─────────────────────────────────────────────────────────────┐
│           🚫 KELEMAHAN SISTEM EXISTING                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ❌ Tidak ada inventory menu terstruktur                    │
│  ❌ Sulit tracking pesanan real-time                        │
│  ❌ Verifikasi pembayaran manual & rawan error              │
│  ❌ Laporan penjualan tidak akurat                          │
│  ❌ Tidak ada komunikasi real-time kasir ↔ dapur           │
│  ❌ Data penjualan historis hilang & tidak terorganisir     │
│  ❌ Tidak ada sistem peringatan stok habis                  │
│  ❌ Pelanggan menunggu lama tanpa info status               │
│  ❌ Analisis performa penjualan tidak possible              │
│  ❌ Rentan human error dalam mencatat                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 1.4 Stakeholder Sistem Berjalan

| Stakeholder       | Peran       | Tanggung Jawab                               |
| ----------------- | ----------- | -------------------------------------------- |
| 👤 **Pemilik**    | Owner       | Manajemen bisnis, keputusan strategis        |
| 👥 **Kasir**      | Operasional | Layani pelanggan, catat pesanan, terima uang |
| 👨‍🍳 **Koki/Dapur** | Produksi    | Terima pesanan lisan, masak, siapkan         |
| 👨‍💼 **Pelanggan**  | End User    | Pesan menu, bayar, makan                     |

---

## ⚙️ ANALISIS SISTEM USULAN

### 2.1 Visi & Misi Sistem

```
┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
┃          🎯 VISI SISTEM DIGITAL CAK MOL                  ┃
┣━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┫
┃                                                          ┃
┃  Mengimplementasikan sistem informasi berbasis web      ┃
┃  untuk manajemen menu, pemesanan, dan operasional       ┃
┃  warung secara terintegrasi, real-time, dan terukur.   ┃
┃                                                          ┃
┃  📈 Target: Efisiensi +70% | Akurasi +99%              ┃
┃                                                          ┃
┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
```

### 2.2 Tujuan Sistem Usulan

1. ✅ Digitalisasi penuh manajemen menu dengan inventory
2. ✅ Otomasi proses pemesanan dari pelanggan hingga dapur
3. ✅ Komunikasi real-time kasir ↔ dapur via notifikasi
4. ✅ Generate laporan penjualan otomatis & akurat
5. ✅ Tingkatkan kepuasan pelanggan dengan tracking status
6. ✅ Analisis performa penjualan & tren menu
7. ✅ Kurangi human error dalam operasional

### 2.3 Ruang Lingkup (Scope)

```
┌─────────────────────────────────────────────────────────────┐
│           📦 MODUL-MODUL SISTEM USULAN                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  🔐 MODUL A: ADMIN - Dashboard & Management                │
│   ├─ Login & Autentikasi                                  │
│   ├─ CRUD Menu (Create, Read, Update, Delete)             │
│   ├─ Upload Gambar & Deskripsi                            │
│   ├─ Manajemen Kategori Menu                              │
│   ├─ Sistem Badge (Best Seller, New, dll)                 │
│   └─ Generate Laporan PDF Penjualan                        │
│                                                             │
│  🏠 MODUL B: HOMEPAGE - Public Interface                  │
│   ├─ Profil Warung & Sejarah                              │
│   ├─ Gallery Menu                                         │
│   ├─ Browse Menu by Kategori (Tabs)                       │
│   ├─ Search & Filter Menu                                 │
│   └─ Location & Contact Info                              │
│                                                             │
│  🛒 MODUL C: ORDER SYSTEM - Shopping & Checkout            │
│   ├─ Shopping Cart (Client-side)                          │
│   ├─ Input Nomor Meja                                     │
│   ├─ Qty Selection per Item                               │
│   ├─ Real-time Total Calculation                          │
│   ├─ Submit Pesanan (AJAX)                                │
│   └─ Order Confirmation & Tracking                        │
│                                                             │
│  💰 MODUL D: KASIR / POS SYSTEM                            │
│   ├─ Receive FCM Notifications                            │
│   ├─ View Pending Orders                                  │
│   ├─ Mark Order Ready                                     │
│   ├─ Process Payment                                      │
│   ├─ Generate Receipt/Struk Digital                       │
│   └─ Order History                                        │
│                                                             │
│  🔔 MODUL E: NOTIFIKASI - Real-time Alert                │
│   ├─ Firebase Cloud Messaging (FCM)                       │
│   ├─ Push Notif ke Kasir saat Order Baru                  │
│   ├─ Alert Pesanan Siap Disajikan                         │
│   └─ Status Update ke Pelanggan                           │
│                                                             │
│  🗄️  MODUL F: DATABASE & BACKEND                           │
│   ├─ MySQL Database (nasgorcakmoldb)                      │
│   ├─ PHP 7.4+ Server-side Logic                           │
│   ├─ RESTful API Endpoints                                │
│   ├─ Session & Authentication                            │
│   └─ Data Validation & Sanitasi                           │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### 2.4 Technology Stack

| Layer              | Teknologi                        | Deskripsi                  |
| ------------------ | -------------------------------- | -------------------------- |
| **Frontend**       | HTML5, CSS3, JavaScript          | Modern, Responsive         |
| **Icons**          | Lucide Icons                     | SVG-based, lightweight     |
| **Backend**        | PHP 7.4+                         | Server-side processing     |
| **Database**       | MySQL 5.7+                       | Relational database        |
| **Libraries**      | mPDF, Kreait, Firebase Admin SDK | PDF, JWT, FCM              |
| **Design Pattern** | Shadcn UI Inspired               | Modern, minimal aesthetics |

### 2.5 Alur Proses Bisnis Usulan

#### A. Manajemen Menu (Admin)

```
[Admin Login]
    ↓
[Dashboard Admin]
    ↓
[Menu Management] → View | Search | Add | Edit | Delete | Export PDF
    ↓
[Database MySQL] ← Store/Retrieve data
    ↓
[Success/Error Response] → Display to Admin
```

#### B. Pemesanan Pelanggan (User)

```
[Homepage]
    ↓
[Browse Menu by Category] ← Load dari Database
    ↓
[Select Item + Qty] → Add to Cart
    ↓
[Review Cart] → Confirm
    ↓
[Checkout] → Input Table Number
    ↓
[Submit Order] -AJAX→ Backend
    ↓
[Validate & Store DB] ← Server-side check
    ↓
[Trigger FCM Notification] → Firebase
    ↓
[Order Confirmation Page] → Show to Customer
    ↓
[Kasir Notification] ← FCM Push
```

#### C. Kasir/POS (Staff)

```
[FC M Notification Received]
    ↓
[Open Kasir App]
    ↓
[View Pending Orders]
    ↓
[Mark Ready] | [Process Payment] | [Generate Receipt]
    ↓
[Update Order Status] → Database
    ↓
[Customer Receives Update]
```

---

## 📋 KEBUTUHAN FUNGSIONAL

### 3.1 Functional Requirements Summary

| ID         | Kategori                 | Jumlah |
| ---------- | ------------------------ | ------ |
| **AUTH**   | Authentication & Login   | 3      |
| **MENU**   | Manajemen Menu (CRUD)    | 6      |
| **PUBLIC** | Homepage & Browse        | 3      |
| **ORDER**  | Order System & Cart      | 5      |
| **KASIR**  | POS & Kasir Module       | 6      |
| **NOTIF**  | Notification System      | 3      |
| **REPORT** | Reporting & Analytics    | 3      |
| **TOTAL**  | **Kebutuhan Fungsional** | **29** |

### 3.2 Tabel Kebutuhan Fungsional Terperinci

| ID        | FR Title             | Actor   | Deskripsi                                               |
| --------- | -------------------- | ------- | ------------------------------------------------------- |
| **FR-01** | Admin Login          | Admin   | Auth dengan username & password, support "Remember Me"  |
| **FR-02** | Admin Logout         | Admin   | Logout dengan confirmation dialog, destroy session      |
| **FR-03** | Registrasi Admin     | Admin   | Registrasi admin baru, hash password Argon2/Bcrypt      |
| **FR-04** | View Menu List       | Admin   | Display tabel semua menu, sorting, responsive design    |
| **FR-05** | Search Menu          | Admin   | Search by nama, kategori, harga dengan real-time filter |
| **FR-06** | Add Menu             | Admin   | Form tambah menu, upload gambar, validate input         |
| **FR-07** | Edit Menu            | Admin   | Form edit dengan pre-filled data, opsi ganti gambar     |
| **FR-08** | Delete Menu          | Admin   | Delete record DB + file gambar dengan confirmation      |
| **FR-09** | Export to PDF        | Admin   | Generate PDF menu list dengan format profesional        |
| **FR-10** | Display Homepage     | User    | Show profil warung, sejarah, location, contact          |
| **FR-11** | Browse by Category   | User    | Menu grouped by kategori dengan tabs/cards              |
| **FR-12** | Search Menu Homepage | User    | Global search real-time dengan highlight hasil          |
| **FR-13** | Input Table Number   | User    | Prompt nomor meja (1-20), validate range                |
| **FR-14** | Select & Add to Cart | User    | Select item, input qty, add to cart, visual feedback    |
| **FR-15** | View Shopping Cart   | User    | Display drawer cart dengan qty, price, subtotal         |
| **FR-16** | Checkout Order       | User    | Submit order via AJAX dengan validation                 |
| **FR-17** | Order Confirmation   | User    | Show success page dengan order ID, tracking status      |
| **FR-18** | Receive Notification | Kasir   | FCM push notif saat order baru dengan action button     |
| **FR-19** | View Pending Orders  | Kasir   | List pesanan pending/sedang masak, real-time update     |
| **FR-20** | Mark Ready           | Kasir   | Button tandai pesanan ready, update status DB           |
| **FR-21** | Process Payment      | Kasir   | Input payment amount, auto-calculate kembalian          |
| **FR-22** | Generate Receipt     | Kasir   | Format struk: header, items, total, payment, change     |
| **FR-23** | Dashboard Analytics  | Admin   | Key metrics, charts (trend, top menu, revenue)          |
| **FR-24** | Generate Report      | Admin   | Export PDF sales report by date range                   |
| **FR-25** | Order History        | Admin   | View historis pesanan, filter by date/status            |
| **FR-26** | Firebase Setup       | System  | Initialize Firebase Admin SDK dengan service account    |
| **FR-27** | Send Notification    | System  | Trigger FCM ke kasir dengan title, body, payload        |
| **FR-28** | Notif Lifecycle      | System  | Track delivered, mark received, log untuk audit         |
| **FR-29** | API Endpoints        | Backend | RESTful API untuk create/read/update orders             |

---

## 🔒 KEBUTUHAN NON-FUNGSIONAL

### 4.1 Performance Requirements

| NFR        | Requirement          | Target      | Keterangan                |
| ---------- | -------------------- | ----------- | ------------------------- |
| **NFR-P1** | Server Response Time | ≤ 2 detik   | DB query max 1 detik      |
| **NFR-P2** | Page Load Time       | ≤ 3 detik   | Critical pages frontend   |
| **NFR-P3** | AJAX Request         | ≤ 1.5 detik | Per request ke server     |
| **NFR-P4** | Concurrent Users     | ≥ 100 users | Simultaneous active usage |
| **NFR-P5** | Daily Transactions   | ≥ 10,000    | Peak capacity             |
| **NFR-P6** | Uptime               | ≥ 99%       | Availability target       |

### 4.2 Security Requirements

| NFR        | Requirement              | Implementasi                                  |
| ---------- | ------------------------ | --------------------------------------------- |
| **NFR-S1** | Password Hashing         | Argon2 / Bcrypt algorithm                     |
| **NFR-S2** | SQL Injection Prevention | Prepared statements / Parameterized queries   |
| **NFR-S3** | XSS Prevention           | htmlspecialchars(), sanitize output           |
| **NFR-S4** | CSRF Protection          | CSRF token di form submission                 |
| **NFR-S5** | Session Security         | Timeout 30 min, secure cookies, HttpOnly flag |
| **NFR-S6** | Data Encryption          | HTTPS/SSL in production, encrypted PII        |
| **NFR-S7** | Firebase Key Protection  | Service account JSON restricted access        |
| **NFR-S8** | Rate Limiting            | Limit login attempts, API throttling          |

### 4.3 Usability Requirements

| NFR        | Requirement          | Deskripsi                                 |
| ---------- | -------------------- | ----------------------------------------- |
| **NFR-U1** | Mobile Responsive    | Mobile-first, tablet, desktop breakpoints |
| **NFR-U2** | Modern UI Design     | Shadcn-inspired, consistent styling       |
| **NFR-U3** | Accessibility        | WCAG 2.1 AA standard compliance           |
| **NFR-U4** | Touch Friendly       | Min button size 44x44px pada mobile       |
| **NFR-U5** | Intuitive Navigation | Clear CTA, logical flow, minimal clicks   |
| **NFR-U6** | Loading Feedback     | Progress indicator, loading animation     |
| **NFR-U7** | Error Messages       | User-friendly, non-technical language     |

### 4.4 Compatibility Requirements

| NFR        | Browser/Device      | Support Level            |
| ---------- | ------------------- | ------------------------ |
| **NFR-C1** | Chrome              | ≥ 90 ✅                  |
| **NFR-C2** | Firefox             | ≥ 88 ✅                  |
| **NFR-C3** | Safari              | ≥ 14 ✅                  |
| **NFR-C4** | Edge                | ≥ 90 ✅                  |
| **NFR-C5** | Mobile Safari (iOS) | ≥ 14 ✅                  |
| **NFR-C6** | Chrome Android      | ≥ 90 ✅                  |
| **NFR-C7** | Desktop OS          | Windows, macOS, Linux ✅ |
| **NFR-C8** | Tablet              | iPad, Android tablets ✅ |

---

## 🎭 USE CASE DIAGRAM

### 5.1 Actors & Stakeholders

```
┏━━━━━━━━━━━━━━━━┓
┃  👤 ADMIN      ┃  Owner / Manager warung
┃  (Aktor 1)     ┃  - Kelola menu
┃                ┃  - Monitor pesanan
┃                ┃  - Generate laporan
┗━━━━━━━━━━━━━━━━┛

┏━━━━━━━━━━━━━━━━┓
┃  👨‍💼 PELANGGAN  ┃  Pengunjung warung / End-user
┃  (Aktor 2)     ┃  - Lihat menu
┃                ┃  - Pesan makanan
┃                ┃  - Track status
┗━━━━━━━━━━━━━━━━┛

┏━━━━━━━━━━━━━━━━┓
┃  💳 KASIR       ┃  Staff operasional
┃  (Aktor 3)     ┃  - Terima notifikasi
┃                ┃  - Manage orders
┃                ┃  - Proses pembayaran
┗━━━━━━━━━━━━━━━━┛

┏━━━━━━━━━━━━━━━━┓
┃  🔥 FIREBASE   ┃  External system
┃  (Aktor 4)     ┃  - Send notifications
┃                ┃  - Cloud messaging
┗━━━━━━━━━━━━━━━━┛
```

### 5.2 Use Case List

#### 👤 ADMIN USE CASES (11 UC)

```
┌──────────────────────────────────┐
│      ADMIN USE CASES             │
│                                  │
│  UC-01: □ Admin Login            │
│  UC-02: □ Admin Logout           │
│  UC-03: □ View Menu List         │
│  UC-04: □ Search Menu            │
│  UC-05: □ Add New Menu           │
│  UC-06: □ Edit Menu              │
│  UC-07: □ Delete Menu            │
│  UC-08: □ Export PDF             │
│  UC-09: □ View Dashboard         │
│  UC-10: □ Generate Report        │
│  UC-11: □ View Order History     │
│                                  │
└──────────────────────────────────┘
```

#### 👨‍💼 PELANGGAN USE CASES (12 UC)

```
┌──────────────────────────────────┐
│    PELANGGAN USE CASES           │
│                                  │
│  UC-12: □ View Homepage          │
│  UC-13: □ Browse by Category     │
│  UC-14: □ Search Menu            │
│  UC-15: □ View Menu Detail       │
│  UC-16: □ Input Table Number     │
│  UC-17: □ Select & Add to Cart   │
│  UC-18: □ View Shopping Cart     │
│  UC-19: □ Update Cart Qty        │
│  UC-20: □ Remove from Cart       │
│  UC-21: □ Checkout Order         │
│  UC-22: □ View Order Status      │
│  UC-23: □ Receive Confirmation   │
│                                  │
└──────────────────────────────────┘
```

#### 💳 KASIR USE CASES (6 UC)

```
┌──────────────────────────────────┐
│      KASIR USE CASES             │
│                                  │
│  UC-24: □ Receive Notification   │
│  UC-25: □ View Pending Orders    │
│  UC-26: □ Mark Ready             │
│  UC-27: □ Process Payment        │
│  UC-28: □ Generate Receipt       │
│  UC-29: □ Confirm Completion     │
│                                  │
└──────────────────────────────────┘
```

### 5.3 Diagram UML - Use Case Komprehensif

```
                        ╔════════════════════════════════════════════╗
                        ║   WARUNG NASI GORENG CAK MOL              ║
                        ║   ORDER MANAGEMENT SYSTEM                  ║
                        ╚════════════════════════════════════════════╝


     ┏━━━━━━━━━━┓                                              ┏━━━━━━━━━━┓
     ┃  ADMIN   ┃                                              ┃PELANGGAN ┃
     ┃          ┃                                              ┃ (USER)   ┃
     ┗━━━━━━━━━━┛                                              ┗━━━━━━━━━━┛
          │                                                        │
          │                 SYSTEM BOUNDARY                       │
          │         ┌─────────────────────────────┐               │
          │         │  ADMIN USECASES             │               │
          │         │  ┌──────────────────────┐   │               │
     ┌────┼─────────┼─→│ UC-01: Admin Login   │   │               │
     │    │         │  │ UC-02: Logout       │   │               │
     │    │         │  │ UC-03: View List    │   │               │
     │    │         │  │ UC-04: Search       │   │               │
     │    │         │  │ UC-05: Add Menu     │   │               │
     │    │         │  │ UC-06: Edit Menu    │   │               │
     │    │         │  │ UC-07: Delete       │   │               │
     │    │         │  │ UC-08: Export PDF   │   │               │
     │    │         │  │ UC-09: Dashboard    │   │               │
     │    │         │  │ UC-10: Laporan      │   │               │
     │    │         │  │ UC-11: Riwayat      │   │               │
     │    │         │  └──────────────────────┘   │               │
     │    │         │                              │               │
     │    │         │  USER USECASES               │               │
     │    │         │  ┌──────────────────────┐   │   ┌───────────┴────────┐
     │    │         │  │ UC-12: Homepage      │───┼───→                   │
     │    │         │  │ UC-13: Browse Menu   │───┼───→                   │
     │    │         │  │ UC-14: Search        │───┼───→                   │
     │    │         │  │ UC-15: Detail        │───┼───→                   │
     │    │         │  │ UC-16: Table #       │───┼───→ Pelanggan         │
     │    │         │  │ UC-17: Add to Cart   │───┼───→ Interactions      │
     │    │         │  │ UC-18: View Cart     │───┼───┐                   │
     │    │         │  │ UC-19: Update Qty    │───┼───┤                   │
     │    │         │  │ UC-20: Remove Item   │───┼───┤                   │
     │    │         │  │ UC-21: Checkout      │───┼───┤                   │
     │    │         │  │ UC-22: Track Status  │───┼───┤                   │
     │    │         │  │ UC-23: Confirmation  │───┼───→                   │
     │    │         │  └──────────────────────┘   │   └───────────────────┘
     │    │         │                              │
     │    │         │  KASIR USECASES              │
     │    │         │  ┌──────────────────────┐   │
     │    │         │  │ UC-24: Notify        │   │
     │    ├────────→┼──│ UC-25: Pending Ord   │   │    ┏━━━━━━━━━━┓
     │    │         │  │ UC-26: Mark Ready    │───┼───→┃  KASIR   ┃
     │    │         │  │ UC-27: Payment       │   │    ┃ (STAFF)  ┃
     │    │         │  │ UC-28: Receipt       │   │    ┗━━━━━━━━━━┛
     │    │         │  │ UC-29: Complete      │   │
     │    │         │  └──────────────────────┘   │
     │    │         └─────────────────────────────┘
     │    │                      ▲
     │    │                      │ <<uses>>
     │    │                      │
     │    │         ┌──────────────────────┐
     │    │         │  FIREBASE FCM        │
     │    └────────→│  Cloud Messaging     │
     │              │  (Send Notif)        │
     │              └──────────────────────┘
     │                      │
     └──────────────────────┤
                            │
                    ╔═══════▼═══════╗
                    ║  DATABASE     ║
                    ║  MySQL        ║
                    ║ (nasgorcakmol) ║
                    ╚═══════════════╝
```

### 5.4 Activity Diagram - Proses Order Pelanggan

```
PROSES ORDERING DARI PERSPEKTIF PELANGGAN

      ┌─────────────┐
      │    START    │
      └──────┬──────┘
             │
      ┌──────▼──────────┐
      │ View Homepage   │
      └──────┬──────────┘
             │
      ┌──────▼────────────────┐
      │ Browse Menu Category  │
      └──────┬────────────────┘
             │
      ┌──────▼──────────────┐
      │ Select Menu Items   │
      └──────┬──────────────┘
             │
      ┌──────▼──────────────────┐      ┌─────────┐
      │ Add to Cart + Qty        │─No─→ │ Continue│
      └──────┬──────────────────┘      │ Shopping│
             │ Yes                      └─────────┘
             │                                ▲
      ┌──────▼──────────────┐               │
      │ Review Cart         │───────────────┘
      └──────┬──────────────┘
             │
      ┌──────▼──────────────┐
      │ Input Table #       │
      └──────┬──────────────┘
             │
      ┌──────▼──────────────┐
      │ Review Final Order  │
      │ (Items + Total)     │
      └──────┬──────────────┘
             │
      ┌──────▼──────────────┐
      │ Click "Checkout"    │
      └──────┬──────────────┘
             │
      ┌──────▼──────────────┐
      │ SUBMIT ORDER[AJAX]  │
      └──────┬──────────────┘
             │
  ┌──────────┴──────────┐
  │                     │
  ▼ Server Validate     ▼ Failed
┌───────────────────────────┐
│ Validate:                 │
│ • Cart not empty?         ├─→ ❌ Error Message
│ • Qty > 0?                │   └─→ Return to Cart
│ • Table valid?            │
└───────────────────────────┘
  │ Success
  ▼
┌──────────────────────────────┐
│ Generate Order ID            │
│ Insert to DB (orders table)  │
└──────────────────┬───────────┘
                   │
              ┌────▼──────────────┐
              │ Trigger Firebase   │
              │ FCM to Kasir       │
              └────┬───────────────┘
                   │
           ┌───────▼────────┐
           │ Show Confirm   │
           │ Page with:     │
           │ • Order ID     │
           │ • Table #      │
           │ • Items        │
           │ • Total $      │
           │ • Animation    │
           └────┬───────────┘
                │
           ┌────▼────────────┐
           │ Poll Order      │
           │ Status (5 sec)  │
           └────┬────────────┘
                │
           ┌────▼──────────────────┐
           │ Display Status:        │
           │ • Preparing...         │
           │ • Ready for Pickup!    │
           │ • Completed           │
           └────┬──────────────────┘
                │
           ┌────▼────────┐
           │    END      │
           └─────────────┘
```

### 5.5 Sequence Diagram - Kasir Menerima Pesanan

```
KASIR <- FIREBASE FCM <- ORDER SYSTEM

╔════════╗         ╔═════════╗         ╔════════════╗        ╔════════════╗
║  User  ║         ║ Backend ║         ║ Firebase   ║        ║   Kasir    ║
║        ║         ║  (PHP)  ║         ║   (FCM)    ║        ║    App     ║
╚════════╝         ╚═════════╝         ╚════════════╝        ╚════════════╝
    │                  │                    │                    │
    │ 1. Submit Order  │                    │                    │
    ├─────────────────→│                    │                    │
    │                  │                    │                    │
    │                  │ 2. Validate        │                    │
    │                  │    + Insert DB     │                    │
    │                  │                    │                    │
    │                  │ 3. Generate FCM    │                    │
    │                  │    Message         │                    │
    │                  ├───────────────────→│                    │
    │                  │                    │                    │
    │                  │                    │ 4. Send Push       │
    │                  │                    │    Notification    │
    │                  │                    ├───────────────────→│
    │                  │                    │                    │
    │                  │                    │    5. Display Alert│
    │                  │                    │    [Sound/Vibrate] │
    │                  │                    │                    │
    │                  │                    │    6. Kasir Click  │
    │                  │                    │    "View Order"    │
    │                  │                    │←───────────────────┤
    │                  │                    │                    │
    │                  │    7. Request      │    8. Display      │
    │                  │    Order Details   │    Order List      │
    │  9. Ack Success  │←───────────────────┤                    │
    │←─────────────────┤                    │                    │
    │                  │                    │                    │
```

### 5.6 Use Case Narrative - UC-05: Add New Menu

#### 📝 Use Case Details

| Attribute         | Nilai                                                 |
| ----------------- | ----------------------------------------------------- |
| **ID**            | UC-05                                                 |
| **Judul**         | Add New Menu (Create Menu)                            |
| **Actor**         | Admin                                                 |
| **Precondition**  | Admin sudah login, berada di halaman "Manajemen Menu" |
| **Postcondition** | Menu baru tersimpan di database, tampil di listing    |
| **Frequency**     | Regular (sesuai kebutuhan penambahan menu baru)       |

#### 🔄 Main Flow

```
STEP 1: Admin Navigate to Add Menu Page
  Admin klik button "+ Tambah Menu" di halaman menu list
  ↓
STEP 2: System Display Form
  Menampilkan form dengan fields:
  • Nama Menu (text input, required)
  • Harga Jual (number input, required)
  • Kategori (dropdown select, required)
  • Deskripsi (textarea, optional)
  • Gambar Menu (file input, required, max 2MB)
  • Button: [Simpan] [Batal]
  ↓
STEP 3: Admin Fill Form
  Admin isi semua field yang required
  • Nama: "Nasi Goreng Telur"
  • Harga: 15000
  • Kategori: "Nasi Goreng Series"
  • Deskripsi: "Nasi goreng dengan telur matang di atas..."
  • Upload gambar: "nasigoring_telur.jpg"
  ↓
STEP 4: Admin Submit Form
  Klik button "Simpan"
  ↓
STEP 5: Validation Check (Server-side)
  ✓ Nama menu: not empty
  ✓ Harga: number, >= 0
  ✓ Kategori: exists in database
  ✓ File format: jpg/png only
  ✓ File size: <= 2MB
  ↓
  [VALIDATION SUCCESS]
  ↓
STEP 6: Sanitasi Input
  • htmlspecialchars() untuk mencegah XSS
  • trim() whitespace
  • Decode HTML entities
  ↓
STEP 7: Upload & Store Image
  • Generate unique filename: "menu_1711819200_abc123.jpg"
  • Move file ke: /img/ folder
  • Validate move success
  ↓
STEP 8: Insert to Database
  Query: INSERT INTO menu (NULL, 'Nasi Goreng Telur', 15000,
         'Nasi goreng...', 'Nasi Goreng Series',
         'menu_1711819200_abc123.jpg', NULL, NULL)
  ↓
STEP 9: Response Success
  System display: "✅ Menu berhasil ditambahkan!"
  ↓
STEP 10: Redirect to List
  Auto-redirect ke halaman menu list
  Tampil menu terbaru di atas (sorting DESC)
```

#### ⚠️ Alternative Flows

**ALT-A: File Gambar Terlalu Besar**

```
Step 5a: File size > 2MB
  FAIL validation
  ↓
Step 6a: Display error message
  "❌ File terlalu besar! Maksimal 2MB"
  ↓
Step 7a: Form tetap terbuka
  Data yang udah diisi masih tersimpan
  (Browser cache input)
  ↓
Step 8a: Admin reduce file size
  Compress/resize gambar
  Re-upload
```

**ALT-B: File Format Invalid**

```
Step 5b: File format = .gif, .webp (not jpg/png)
  FAIL validation
  ↓
Step 6b: Display error
  "❌ Format file harus JPG atau PNG"
  ↓
Step 7b: Form tetap terbuka
  Admin upload file yang benar
```

**ALT-C: Database Error**

```
Step 8c: INSERT query fails
  (misal disk full, permission error)
  ↓
Step 9c: Catch exception
  Display: "❌ Gagal menyimpan. Hubungi admin."
  ↓
Step 10c: Rollback file upload
  Delete file yang udah terupload dari /img/
  Form tetap terbuka
```

---

### 5.7 Use Case Narrative - UC-21: Checkout & Process Order

#### 📝 Use Case Details

| Attribute         | Nilai                                                           |
| ----------------- | --------------------------------------------------------------- |
| **ID**            | UC-21                                                           |
| **Judul**         | Checkout & Submit Order                                         |
| **Actor**         | Pelanggan (User)                                                |
| **Precondition**  | User sudah memilih items, cart tidak kosong, nomor meja diinput |
| **Postcondition** | Order tersimpan di DB, kasir menerima notifikasi FCM            |
| **Frequency**     | Per transaction (~100+ per hari)                                |

#### 🔄 Main Flow

```
STEP 1: User Review Cart
  Buka shopping cart drawer
  Lihat items, qty, prices
  Total amount: Rp 45.000
  ↓
STEP 2: Confirm Cart
  Pastikan semua item benar
  Klik button "Checkout"
  ↓
STEP 3: Validation - Client Side
  • Cart not empty? ✓ (2 items)
  • Qty > 0 for all items? ✓ (1, 1)
  • Table number set? ✓ (Meja 5)
  ↓
STEP 4: Loading Animation
  Show spinning animation + text
  "Pesanan sedang diproses..."
  ↓
STEP 5: AJAX Submit to Server
  Fetch POST to: /user/process_order.php
  Payload:
  {
    numeja: 5,
    items: [{id: 1, qty: 1}, {id: 3, qty: 1}],
    total: 45000,
    timestamp: 2025-03-30 14:35:22
  }
  ↓
STEP 6: Server Receive & Validate
  Verify:
  • Table 1-20? ✓ 5
  • Items exists in DB? ✓
  • Price calculation correct? ✓ 45000
  • Qty positive integers? ✓
  ↓
  [VALIDATION PASS]
  ↓
STEP 7: Generate Order ID
  Format: ORD-YYYYMMDD-XXXXX
  Example: ORD-20250330-00127
  ↓
STEP 8: Insert Order to Database
  INSERT orders (
    id_order: auto,
    order_id: 'ORD-20250330-00127',
    no_meja: 5,
    items_json: [...],
    total: 45000,
    status: 'pending',
    created_at: NOW(),
    payment_status: 'unpaid'
  )
  ↓
STEP 9: Insert Order Detail
  INSERT order_items:
  • Row 1: order_id, item_id=1, qty=1, price=20000
  • Row 2: order_id, item_id=3, qty=1, price=25000
  ↓
STEP 10: Trigger Firebase FCM
  Build message:
  {
    title: "🔔 Pesanan Baru!",
    body: "Meja 5 • 2 item • Rp 45.000",
    data: {
      order_id: "ORD-20250330-00127",
      meja: "5",
      items: "Nasi Goreng Telur, Mie Goreng Seafood",
      total: "45000",
      waktu: "14:35"
    }
  }
  ↓
  Send to all kasir_device tokens
  ↓
STEP 11: Return Response to Client
  {
    status: "success",
    order_id: "ORD-20250330-00127",
    message: "Pesanan berhasil diterima!"
  }
  ↓
STEP 12: Display Confirmation Page
  Stop loading animation
  Show success screen:

  ✅ PESANAN DITERIMA!
  ├─ Order ID: ORD-20250330-00127
  ├─ Meja: 5
  ├─ Items:
  │  • Nasi Goreng Telur x1 = Rp 20.000
  │  • Mie Goreng Seafood x1 = Rp 25.000
  ├─ Total: Rp 45.000
  └─ Status: Sedang dipersiapkan... 🍳

  ↓
STEP 13: Start Status Polling
  Auto-refresh status setiap 5 detik
  GET /user/order_status.php?order_id=ORD-20250330-00127
  ↓
STEP 14: Display Status Updates (Real-time)
  • "Sedang dipersiapkan..."
  • "Pesanan siap disajikan!"
  • "Selamat menikmati! 🍽️"
  ↓
STEP 15: Completed
  User tungu makanannya tiba
```

#### ⚠️ Alternative Flows

**ALT-A: Cart Empty**

```
Step 3a: Cart items count = 0
  ❌ Validation FAILS
  ↓
Error message: "Keranjang kosong! Silakan tambah menu dulu."
Button: [Lanjutkan Belanja]
  ↓
Redirect to order page
```

**ALT-B: Table Number Not Set**

```
Step 3b: Table number undefined
  ❌ Validation FAILS
  ↓
Prompt: "Masukkan nomor meja (1-20)"
  ↓
User input table, then retry
```

**ALT-C: Network Error During Submit**

```
Step 5c: AJAX request timeout
  Network unavailable
  ↓
Catch error → Display:
  "❌ Gagal menghubungi server. Cek koneksi & coba lagi."
Button: [Retry Checkout]
  ↓
User can retry
```

**ALT-D: Server Processing Error**

```
Step 8d: Database insert fails
  (constraint error, disk full, etc)
  ↓
Return error response:
  {status: "error", message: "Gagal menyimpan pesanan"}
  ↓
Display: "❌ Terjadi kesalahan. Coba lagi nanti."
```

**ALT-E: FCM Notification Failed**

```
Step 10e: Firebase return error
  Token invalid / service down
  ↓
System log error untuk audit
  ↓
But order still SAVED in DB
  ↓
Display warning:
  "⚠️ Order tersimpan tapi notifikasi kasir terlambat.
   Kasir bisa lihat di list order."
```

---

## 📊 Database Schema Overview

```
TABLE: menu
┌─────────────────────────────────────┐
│ id_menu (PK) : INT                  │
│ nama_menu : VARCHAR(100)            │
│ harga : INT                         │
│ deskripsi : TEXT                    │
│ kategori : VARCHAR(50)              │
│ gambar : VARCHAR(255)               │
│ badge : VARCHAR(50) [nullable]      │
│ badge_color : VARCHAR(50) [nullable]│
└─────────────────────────────────────┘

TABLE: orders
┌─────────────────────────────────────┐
│ id_order (PK) : INT                 │
│ order_id : VARCHAR(50) [UNIQUE]     │
│ no_meja : INT                       │
│ total : INT                         │
│ status : ENUM('pending','ready'...) │
│ created_at : DATETIME               │
│ payment_status : VARCHAR(20)        │
└─────────────────────────────────────┘

TABLE: order_items
┌─────────────────────────────────────┐
│ id : INT (PK)                       │
│ order_id : VARCHAR(50) (FK)         │
│ item_id : INT (FK → menu)           │
│ qty : INT                           │
│ price : INT                         │
└─────────────────────────────────────┘

TABLE: user (Admin)
┌─────────────────────────────────────┐
│ id : INT (PK)                       │
│ username : VARCHAR(50) [UNIQUE]     │
│ password : VARCHAR(255) [HASHED]    │
│ created_at : DATETIME               │
└─────────────────────────────────────┘

TABLE: kasir_device
┌─────────────────────────────────────┐
│ id : INT (PK)                       │
│ token_fcm : VARCHAR(255) [UNIQUE]   │
│ created_at : DATETIME               │
│ last_active : DATETIME              │
└─────────────────────────────────────┘
```

---

## 🎯 Key Metrics & Success Criteria

| Metrik                    | Target                       | Target Date |
| ------------------------- | ---------------------------- | ----------- |
| **Efisiensi Operasional** | +70% pengurangan waktu order | Q2 2025     |
| **Akurasi Data**          | 99% keakuratan pencatatan    | Q1 2025     |
| **Customer Satisfaction** | ≥ 4.5/5 score                | Q3 2025     |
| **System Uptime**         | 99% availability             | Ongoing     |
| **Order Processing Time** | ≤ 2 menit                    | Real-time   |
| **Mobile Users**          | ≥ 60% dari total users       | Q2 2025     |

---

## 📝 Kesimpulan

Sistem Warung Nasi Goreng Cak Mol dirancang untuk mentransformasi operasional warung dari **manual menjadi digital**, meningkatkan **efisiensi, akurasi, dan kepuasan pelanggan**.

Dengan implementasi modul-modul terstruktur, teknologi real-time (Firebase FCM), dan interface yang user-friendly, sistem ini siap mendukung pertumbuhan bisnis warung untuk dekade mendatang.

---

**Dokumen ini berisi:**

- ✅ Analisis Sistem Berjalan & Usulan
- ✅ 29 Kebutuhan Fungsional Terperinci
- ✅ 15+ Kebutuhan Non-Fungsional
- ✅ 6 Use Case Diagram & Narratives
- ✅ ASCII Art Diagrams & Tables
- ✅ Database Schema Overview

**Dibuat oleh:** Bagus Ramadhan (2491051003)  
**Tanggal:** 30 Maret 2025  
**Project:** Warung Nasi Goreng Cak Mol - Order Management System
