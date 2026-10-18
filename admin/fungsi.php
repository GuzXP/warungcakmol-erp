<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: fungsi.php (CORE LOGIC ADMIN)
 * FILE LOGIKA UTAMA: Berisi kumpulan fungsi (Function Library) untuk operasi CRUD, Upload, Validasi, dan Registrasi.
 * DESAIN PATTERN: Menggunakan gaya prosedural dengan fungsi terpisah agar kode modular dan bisa dipanggil berulang kali.
 * DATABASE: Menggunakan 'nasgorcakmoldb' agar data terintegrasi Real-time dengan sisi Client/User.
 */

// -----------------------------------------------------------------------------
// 1. KONEKSI & UTILITY TERPUSAT
// -----------------------------------------------------------------------------
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// -----------------------------------------------------------------------------
// KUMPULAN FUNGSI (HELPER FUNCTIONS)
// -----------------------------------------------------------------------------

/**
 * FUNGSI 1: QUERY (READ DATA - DENGAN PREPARED STATEMENT)
 * Tujuan: Mengambil data dari database secara aman menggunakan parameterisasi.
 * @param string $query - Perintah SQL SELECT lengkap dengan placeholder (?).
 * @param array $params - Nilai parameter untuk query.
 * @param string $types - Tipe data parameter (s = string, i = int, etc).
 * @return array - Array berisi kumpulan data hasil query.
 */
function query($query, $params = [], $types = '') {
    global $koneksi;
    
    if (empty($params)) {
        $result = mysqli_query($koneksi, $query);
        if (!$result) {
            return [];
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    } else {
        $stmt = mysqli_prepare($koneksi, $query);
        if (!$stmt) {
            return [];
        }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            mysqli_stmt_close($stmt);
            return [];
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}

/**
 * FUNGSI 2: TAMBAH (CREATE DATA)
 * Tujuan: Menerima data $_POST dari form tambah, memproses upload gambar, dan insert ke DB secara aman.
 * @param array $data - Array $_POST yang berisi input form.
 * @return int - Jumlah baris yang terpengaruh (1 = sukses, 0/-1 = gagal).
 */
function tambah($data) {
    global $koneksi;

    $nama_menu = htmlspecialchars($data["nama_menu"]);
    $harga = (int)$data["harga_jual"]; // Casting ke int untuk keamanan
    
    // Normalisasi Kategori
    $kategori = trim($data["bahan_utama"]);
    $kategori = html_entity_decode($kategori);
    $kategori = str_replace("\xc2\xa0", " ", $kategori);
    
    $deskripsi = htmlspecialchars($data["deskripsi"]);
    $gambar = upload();
    
    if( !$gambar ) {
        return false; 
    }

    $stmt = mysqli_prepare($koneksi, "INSERT INTO menu (nama_menu, harga, deskripsi, kategori, gambar) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "sisss", $nama_menu, $harga, $deskripsi, $kategori, $gambar);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $affected;
}

/**
 * FUNGSI 3: HAPUS (DELETE DATA)
 * Tujuan: Menghapus data menu dari database DAN menghapus file gambarnya dari folder secara aman.
 * @param int $id - ID menu yang akan dihapus.
 * @return int - Status keberhasilan.
 */
function hapus($id) {
    global $koneksi;

    // LANGKAH 1: Cari Data File Gambar Lama
    $stmt = mysqli_prepare($koneksi, "SELECT gambar FROM menu WHERE id_menu = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $file = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // LANGKAH 2: Hapus File Fisik
    if ($file && !empty($file['gambar'])) {
        $file_path = __DIR__ . '/../img/' . $file['gambar'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // LANGKAH 3: Hapus Record Database
    $stmt = mysqli_prepare($koneksi, "DELETE FROM menu WHERE id_menu = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $affected;
}

/**
 * FUNGSI 4: EDIT (UPDATE DATA)
 * Tujuan: Memperbarui data menu yang sudah ada secara aman, dan menghapus foto lama jika diganti.
 * @param array $data - Array $_POST inputan form edit.
 * @return int - Status keberhasilan.
 */
function edit($data) {
    global $koneksi;
    
    $id_menu = (int)$data["id_produk"]; 
    $nama_menu = htmlspecialchars($data["nama_menu"]);
    $harga = (int)$data["harga_jual"];
    
    $kategori = trim($data["bahan_utama"]); 
    $kategori = html_entity_decode($kategori);
    $kategori = str_replace("\xc2\xa0", " ", $kategori);
    
    $deskripsi = htmlspecialchars($data["deskripsi"]); 
    $foto_lama = htmlspecialchars($data["foto_lama"]);

    // LOGIKA PERGANTIAN GAMBAR
    if( $_FILES['foto_menu']['error'] === 4 ) {
        $gambar = $foto_lama; 
    } else {
        $gambar = upload(); 
        if ($gambar && !empty($foto_lama) && $foto_lama !== $gambar) {
            $old_file_path = __DIR__ . '/../img/' . $foto_lama;
            if (file_exists($old_file_path)) {
                unlink($old_file_path);
            }
        }
    }

    // QUERY UPDATE DENGAN PREPARED STATEMENT
    $stmt = mysqli_prepare($koneksi, "UPDATE menu SET nama_menu = ?, harga = ?, kategori = ?, deskripsi = ?, gambar = ? WHERE id_menu = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "sisssi", $nama_menu, $harga, $kategori, $deskripsi, $gambar, $id_menu);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $affected;
}

/**
 * FUNGSI 5: SEARCH (PENCARIAN)
 * Tujuan: Mencari data menu berdasarkan keyword di kolom Nama, Harga, Kategori, atau Deskripsi secara aman.
 * @param string $keyword - Kata kunci pencarian.
 * @return array - Hasil pencarian.
 */
function search($keyword) {
    $search_keyword = "%" . $keyword . "%";
    $query = "SELECT * FROM menu 
              WHERE nama_menu LIKE ? 
                 OR harga LIKE ? 
                 OR kategori LIKE ? 
                 OR deskripsi LIKE ?";
            
    return query($query, [$search_keyword, $search_keyword, $search_keyword, $search_keyword], "ssss"); 
}

/**
 * FUNGSI 6: UPLOAD GAMBAR
 * Tujuan: Memvalidasi file gambar (ekstensi, ukuran) dan memindahkannya ke folder img/.
 * @return string|bool - Nama file baru jika sukses, FALSE jika gagal.
 */
function upload() {
    $namaFile = $_FILES['foto_menu']['name'];       
    $ukuranFile = $_FILES['foto_menu']['size'];     
    $error = $_FILES['foto_menu']['error'];         
    $tmpName = $_FILES['foto_menu']['tmp_name'];    

    if( $error === 4 ) {
        echo "<script>alert('Pilih gambar terlebih dahulu!');</script>";
        return false;
    }

    $cekEkstensi = ['jpg', 'jpeg', 'png'];
    $ekstensiFoto = explode('.', $namaFile); 
    $ekstensiFoto = strtolower(end($ekstensiFoto)); 
    
    if( !in_array($ekstensiFoto, $cekEkstensi) ) {
        echo "<script>alert('Yang anda upload bukan gambar! (Gunakan jpg/jpeg/png)');</script>";
        return false;
    }

    if( $ukuranFile > 2000000 ) {
        echo "<script>alert('Ukuran gambar terlalu besar! (Maks 2MB)');</script>";
        return false;
    }

    $namaFileBaru = uniqid() . '.' . $ekstensiFoto;
    move_uploaded_file($tmpName, __DIR__ . '/../img/' . $namaFileBaru);

    return $namaFileBaru;
}

/**
 * FUNGSI 7: REGISTRASI USER (ADMIN)
 * Tujuan: Mendaftarkan akun admin baru secara aman menggunakan prepared statement dan password hashing.
 * @param array $data - Data input user.
 * @return int - Status keberhasilan.
 */
function registrasi($data) {
    global $koneksi;

    $namaLengkap = htmlspecialchars($data["fullname"]);
    $username = strtolower(stripslashes($data["username"])); 
    $password = $data["password"];
    $password2 = $data["password2"];

    // Cek Duplikasi Username
    $stmt = mysqli_prepare($koneksi, "SELECT username FROM user WHERE username = ?");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if( $exists ) {
        echo "<script>alert('Username sudah terdaftar! Pilih yang lain.');</script>";
        return false;
    }

    // Cek Konfirmasi Password
    if( $password !== $password2 ) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
        return false;
    }

    // ENKRIPSI PASSWORD (HASHING)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke DB tabel 'user'
    $stmt = mysqli_prepare($koneksi, "INSERT INTO user (nama, username, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, "sss", $namaLengkap, $username, $passwordHash);
    mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    return $affected;
}

?>