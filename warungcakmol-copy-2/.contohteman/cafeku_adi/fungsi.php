<?php
// FILE: fungsi.php
// Lokasi: cafeku/fungsi.php

// 1. KONEKSI DATABASE
$conn = mysqli_connect("localhost", "root", "", "cafeku_adi");

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}

// 2. FUNGSI QUERY
function query($query)
{
    global $conn;
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query error: " . mysqli_error($conn));
    }
    
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

// 3. FUNGSI TAMBAH PRODUK
function tambah($data)
{
    global $conn;

    $nama = htmlspecialchars($data["nama"]);
    $harga = intval($data["harga"]);
    $kategori = htmlspecialchars($data["kategori"]);
    $stok = intval($data["stok"] ?? 0);
    $deskripsi = htmlspecialchars($data["deskripsi"] ?? '');

    $gambar = upload();
    if (!$gambar) {
        return false;
    }

    $query = "INSERT INTO produk 
                (nama, harga, kategori, stok, deskripsi, gambar)
              VALUES
                ('$nama', '$harga', '$kategori', '$stok', '$deskripsi', '$gambar')
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

    if ($error === 4) {
        echo "<script>alert('Pilih gambar terlebih dahulu!');</script>";
        return false;
    }

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $ekstensi = explode('.', $namaFile);
    $ekstensi = strtolower(end($ekstensi));

    if (!in_array($ekstensi, $ekstensiValid)) {
        echo "<script>alert('File bukan gambar!');</script>";
        return false;
    }

    if ($ukuranFile > 5000000) {
        echo "<script>alert('Ukuran gambar terlalu besar! (Max 5MB)');</script>";
        return false;
    }

    $namaFileBaru = time() . '_' . uniqid() . '.' . $ekstensi;

    if (!is_dir('../poto/')) {
        mkdir('../poto/', 0777, true);
    }
    
    move_uploaded_file($tmpName, '../poto/' . $namaFileBaru);

    return 'poto/' . $namaFileBaru;
}

// 5. FUNGSI HAPUS PRODUK
function hapus($id)
{
    global $conn;

    $result = mysqli_query($conn, "SELECT gambar FROM produk WHERE id = $id");
    $data = mysqli_fetch_assoc($result);

    if ($data && !empty($data['gambar'])) {
        $pathFisik = "../" . $data['gambar'];
        if (file_exists($pathFisik)) {
            unlink($pathFisik);
        }
    }

    mysqli_query($conn, "DELETE FROM produk WHERE id = $id");
    return mysqli_affected_rows($conn);
}

// 6. FUNGSI UBAH PRODUK
function ubah($data)
{
    global $conn;

    $id = intval($data["id"]);
    $nama = htmlspecialchars($data["nama"]);
    $harga = intval($data["harga"]);
    $kategori = htmlspecialchars($data["kategori"]);
    $stok = intval($data["stok"] ?? 0);
    $deskripsi = htmlspecialchars($data["deskripsi"] ?? '');
    $gambarLama = htmlspecialchars($data["gambarLama"] ?? '');

    if ($_FILES['gambar']['error'] === 4) {
        $gambar = $gambarLama;
    } else {
        if (!empty($gambarLama)) {
            $pathFisik = "../" . $gambarLama;
            if (file_exists($pathFisik)) {
                unlink($pathFisik);
            }
        }
        $gambar = upload();
        if (!$gambar) return false;
    }

    $query = "UPDATE produk SET
                nama = '$nama',
                harga = '$harga',
                kategori = '$kategori',
                stok = '$stok',
                deskripsi = '$deskripsi',
                gambar = '$gambar'
              WHERE id = $id
            ";

    mysqli_query($conn, $query);
    return mysqli_affected_rows($conn);
}

// 7. FUNGSI CARI
function cari($keyword)
{
    $keyword = mysqli_real_escape_string($GLOBALS['conn'], $keyword);
    $query = "SELECT * FROM produk
                WHERE
              nama LIKE '%$keyword%' OR
              kategori LIKE '%$keyword%' OR
              deskripsi LIKE '%$keyword%'
            ";
    return query($query);
}

// 8. FUNGSI REGISTRASI USER
function registrasi($data)
{
    global $conn;

    $nama = htmlspecialchars($data["nama"]);
    $username = strtolower(stripslashes($data["username"]));
    $password = mysqli_real_escape_string($conn, $data["password"]);
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);
    $role = strtolower(trim($data["role"] ?? 'customer'));

    $allowedRoles = ['customer', 'kasir', 'kitchen', 'karyawan', 'admin', 'owner', 'manager', 'waiter', 'barista'];
    if (!in_array($role, $allowedRoles)) {
        $role = 'customer';
    }

    $result = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
    if (mysqli_fetch_assoc($result)) {
        echo "<script>alert('Username sudah terdaftar!');</script>";
        return false;
    }

    if ($password !== $password2) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
        return false;
    }

    if (strlen($password) < 5) {
        echo "<script>alert('Password minimal 5 karakter!');</script>";
        return false;
    }

    $password = password_hash($password, PASSWORD_DEFAULT);

    mysqli_query($conn, "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password', '$role')");

    return mysqli_affected_rows($conn);
}

// 9. FUNGSI NORMALISASI ROLE
function normalizeRole($role)
{
    $role = strtolower(trim($role));
    
    $roleMap = [
        'chef' => 'kitchen',
        'dapur' => 'kitchen',
        'kitchen' => 'kitchen',
        'admin' => 'admin',
        'owner' => 'admin',
        'kasir' => 'kasir',
        'cashier' => 'kasir',
        'karyawan' => 'karyawan',
        'employee' => 'karyawan',
        'user' => 'user',
        'customer' => 'user',
        'manager' => 'manager',
        'waiter' => 'waiter',
        'barista' => 'barista'
    ];
    
    return $roleMap[$role] ?? $role;
}

// 10. FUNGSI REDIRECT BY ROLE
function redirectByRole($role)
{
    $role = normalizeRole($role);

    switch ($role) {
        case 'admin':
        case 'owner':
            header("Location: admin/index.php");
            break;
        case 'manager':
            header("Location: admin/manager.php");
            break;
        case 'kasir':
            header("Location: kasir/index.php");
            break;
        case 'kitchen':
            header("Location: kitchen/dashboard.php");
            break;
        case 'karyawan':
            header("Location: karyawan/index.php");
            break;
        case 'waiter':
            header("Location: karyawan/index.php");
            break;
        case 'barista':
            header("Location: karyawan/index.php");
            break;
        case 'user':
        case 'customer':
            header("Location: user/index.php");
            break;
        default:
            header("Location: user/index.php");
            break;
    }
    exit;
}

// 11. FUNGSI FORMAT RUPIAH
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>