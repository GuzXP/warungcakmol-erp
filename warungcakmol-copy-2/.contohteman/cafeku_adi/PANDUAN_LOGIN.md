# Panduan Login User Biasa (Customer)

## Flow Login yang Benar:

### Langkah 1: Registrasi Akun Baru
1. Buka: `http://localhost/Galinfilephp/cafeku_adi/login.php`
2. Klik tombol **"Buat Akun Baru"**
3. Isi form registrasi:
   - Nama Lengkap: (nama anda)
   - Username: (pilih username baru, belum pernah ada)
   - Password: (minimal 5 karakter)
   - Konfirmasi Password: (sama dengan password)
4. Klik **"DAFTAR"**
5. Jika berhasil, akan muncul pesan sukses dan auto-redirect ke login page

### Langkah 2: Login dengan Akun Baru
1. Di halaman login, masukkan:
   - Username: (sesuai yang didaftar)
   - Password: (sesuai yang didaftar)
2. Centang "Remember Me" (opsional)
3. Klik **"LOGIN"**
4. Jika berhasil, akan redirect ke halaman katalog menu user

## Struktur Flow:
```
registrasi.php
    ↓ (daftar berhasil)
login.php
    ↓ (login berhasil, role=user)
index.php (auto-redirect)
    ↓
user/index.php (halaman katalog menu)
```

## Database:
- Tabel: `users`
- Kolom: `id`, `nama`, `username`, `password`
- Default role: `'user'` (di set di login.php baris 47)

## Catatan Penting:
- Jangan gunakan admin di registrasi user page
- Admin hanya bisa login langsung, tidak pakai registrasi dari sini
- Password disimpan terenkripsi menggunakan PASSWORD_DEFAULT (bcrypt)
