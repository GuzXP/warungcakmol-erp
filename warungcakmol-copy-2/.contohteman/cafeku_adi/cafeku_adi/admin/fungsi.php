<?php
// FILE: fungsi.php (ROOT)
// Lokasi: cafeku/fungsi.php

// 1. KONEKSI DATABASE
$conn = mysqli_connect("localhost", "root", "", "cafeku_adi");

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// 2. FUNGSI QUERY (Read Data)
function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// 3. FUNGSI TAMBAH (Create)
function tambah($data)
{
    global $conn;

    // Ambil data dari form (HTML Special Chars biar aman)
    $nama = htmlspecialchars($data["nama"]);
    $harga = htmlspecialchars($data["harga"]);
    $kategori = htmlspecialchars($data["kategori"]);
    $deskripsi = htmlspecialchars($data["deskripsi"]);

    // Upload Gambar
    $gambar = upload();
    if (!$gambar) {
        return false;
    }

    // Insert ke DB
    // Format path gambar di DB: 'poto/namafile.jpg'
    $query = "INSERT INTO produk 
                (nama, harga, kategori, deskripsi, gambar)
              VALUES
                ('$nama', '$harga', '$kategori', '$deskripsi', '$gambar')
            ";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

// 4. FUNGSI UPLOAD GAMBAR
function upload()
{
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];

    // Cek ada gambar yg diupload
    if ($error === 4) {
        echo "<script>alert('Pilih gambar terlebih dahulu!');</script>";
        return false;
    }

    // Cek ekstensi
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
    $ekstensi = explode('.', $namaFile);
    $ekstensi = strtolower(end($ekstensi));

    if (!in_array($ekstensi, $ekstensiValid)) {
        echo "<script>alert('File bukan gambar!');</script>";
        return false;
    }

    // Generate nama unik
    $namaFileBaru = uniqid() . '.' . $ekstensi;

    // Pindah file ke folder 'poto/'
    // Karena fungsi.php dipanggil dari 'admin/add.php', maka path keluarnya adalah '../poto/'
    move_uploaded_file($tmpName, '../poto/' . $namaFileBaru);

    // Kembalikan string path untuk disimpan di DB
    return 'poto/' . $namaFileBaru;
}

// 5. FUNGSI HAPUS (Delete)
function hapus($id)
{
    global $conn;

    // Hapus file gambar fisik dulu
    $result = mysqli_query($conn, "SELECT gambar FROM produk WHERE id = $id");
    $data = mysqli_fetch_assoc($result);

    // Path fisik dari folder admin adalah '../poto/namafile'
    $pathFisik = "../" . $data['gambar'];

    if (file_exists($pathFisik)) {
        unlink($pathFisik);
    }

    // Hapus data di DB
    mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
    return mysqli_affected_rows($conn);
}

// 6. FUNGSI UBAH (Update)
function ubah($data)
{
    global $conn;

    $id = $data["id"];
    $nama = htmlspecialchars($data["nama"]);
    $harga = htmlspecialchars($data["harga"]);
    $kategori = htmlspecialchars($data["kategori"]);
    $deskripsi = htmlspecialchars($data["deskripsi"]);
    $gambarLama = htmlspecialchars($data["gambarLama"]);

    // Cek apakah user pilih gambar baru
    if ($_FILES['gambar']['error'] === 4) {
        $gambar = $gambarLama;
    } else {
        $gambar = upload();
        if (!$gambar) return false;
    }

    $query = "UPDATE produk SET
                nama = '$nama',
                harga = '$harga',
                kategori = '$kategori',
                deskripsi = '$deskripsi',
                gambar = '$gambar'
              WHERE id = $id
            ";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

// 7. FUNGSI CARI (Search)
function cari($keyword)
{
    $query = "SELECT * FROM produk
                WHERE
              nama LIKE '%$keyword%' OR
              kategori LIKE '%$keyword%'
            ";
    return query($query);
}


// 8. FUNGSI REGISTRASI USER (ADMIN BARU)
function registrasi($data)
{
    global $conn;

    // Ambil data dari form
    $nama = htmlspecialchars($data["nama"]);
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);
    $role = mysqli_real_escape_string($conn, strtolower(trim($data["role"] ?? 'customer')));
    $allowedRoles = ['customer', 'kasir', 'kitchen', 'admin', 'owner', 'karyawan'];

    if (!in_array($role, $allowedRoles, true)) {
        $role = 'customer';
    }

    // Cek Username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
        return false;
    }

    // Cek Konfirmasi Password
    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
        return false;
    }

    // Enkripsi Password (Hashing)
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan user baru ke database
    mysqli_query($conn, "INSERT INTO users (nama, username, password, role) VALUES('$nama', '$username', '$password', '$role')");

    return mysqli_affected_rows($conn);
}
