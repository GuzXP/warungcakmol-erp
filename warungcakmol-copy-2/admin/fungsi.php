<?php
/**
 * Bagus Ramadhan - 2491051003
 * FILE: fungsi.php (CORE LOGIC ADMIN)
 * FILE LOGIKA UTAMA: Berisi kumpulan fungsi (Function Library) untuk operasi CRUD, Upload, Validasi, dan Registrasi.
 * DESAIN PATTERN: Menggunakan gaya prosedural dengan fungsi terpisah agar kode modular dan bisa dipanggil berulang kali.
 * DATABASE: Menggunakan 'nasgorcakmoldb' agar data terintegrasi Real-time dengan sisi Client/User.
 */

// -----------------------------------------------------------------------------
// 1. KONEKSI KE DATABASE UTAMA
// -----------------------------------------------------------------------------
// Menghubungkan script PHP ke MySQL Server.
// Parameter: (Host: localhost, Username: root, Password: [kosong], DB Name: nasgorcakmoldb)
$koneksi = mysqli_connect("localhost", "root", "", "nasgorcakmoldb"); 

// VALIDASI KONEKSI (DEBUGGING)
// Mengecek apakah $koneksi berhasil dibuat (bernilai true object).
// Jika gagal (false), hentikan program (die) dan tampilkan pesan error mysql.
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// -----------------------------------------------------------------------------
// KUMPULAN FUNGSI (HELPER FUNCTIONS)
// -----------------------------------------------------------------------------

/**
 * FUNGSI 1: QUERY (READ DATA)
 * Tujuan: Mengambil data dari database dan mengubahnya menjadi Array Associative murni.
 * @param string $query - Perintah SQL SELECT lengkap.
 * @return array $rows - Array berisi kumpulan data hasil query.
 */
function query($query) {
    global $koneksi; // Mengakses variabel $koneksi yang dideklarasikan di luar fungsi (scope global)
    
    // Eksekusi perintah SQL ke database
    $result = mysqli_query($koneksi, $query);
    
    // Wadah sementara untuk menampung hasil fetch
    $rows = [];
    
    // Proses Fetching (Pengambilan Data) Baris per Baris
    // mysqli_fetch_assoc: Mengambil satu baris data sebagai array asosiatif (Key = Nama Kolom).
    // Loop while akan berjalan selama masih ada baris data yang bisa diambil.
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row; // Menambahkan baris data ($row) ke dalam array wadah ($rows)
    }
    
    // Mengembalikan array lengkap ke pemanggil fungsi
    return $rows;
}

/**
 * FUNGSI 2: TAMBAH (CREATE DATA)
 * Tujuan: Menerima data $_POST dari form tambah, memproses upload gambar, dan insert ke DB.
 * @param array $data - Array $_POST yang berisi input form.
 * @return int - Jumlah baris yang terpengaruh (1 = sukses, 0/-1 = gagal).
 */
function tambah($data) {
    global $koneksi;

    // SANITASI INPUT (SECURITY)
    // htmlspecialchars(): Mengubah karakter khusus (seperti <, >) menjadi entitas HTML.
    // Tujuannya mencegah serangan XSS (Cross Site Scripting) jika data ditampilkan di browser.
    
    $nama_menu = htmlspecialchars($data["nama_menu"]); // Ambil nama menu
    $harga = htmlspecialchars($data["harga_jual"]);    // Ambil harga
    
    // Normalisasi Kategori
    // Masalah umum: spasi ganda atau karakter encoding aneh.
    $kategori = trim($data["bahan_utama"]); // Hapus spasi di awal/akhir
    $kategori = html_entity_decode($kategori); // Decode entitas HTML (misal &amp; jadi &)
    $kategori = str_replace("\xc2\xa0", " ", $kategori); // Bersihkan Non-Breaking Space (UTF-8 issue)
    
    $deskripsi = htmlspecialchars($data["deskripsi"]); // Ambil deskripsi
    
    // PROSES UPLOAD GAMBAR
    // Sebelum simpan text, kita wajib upload gambar fisiknya dulu.
    // Memanggil fungsi upload() yang kita buat di bawah.
    $gambar = upload();
    
    // Validasi Upload: Jika fungsi upload() mengembalikan false (gagal), hentikan proses tambah().
    if( !$gambar ) {
        return false; 
    }

    // QUERY INSERT DATABASE
    // NULL pertama untuk id_menu (AUTO_INCREMENT).
    // NULL terakhir dua kali untuk kolom 'badge' dan 'badge_color' (Default null).
    $query = "INSERT INTO menu 
                VALUES 
              (NULL, '$nama_menu', '$harga', '$deskripsi', '$kategori', '$gambar', NULL, NULL)";

    // Eksekusi Query
    mysqli_query($koneksi, $query);

    // Return status affected rows
    return mysqli_affected_rows($koneksi);
}

/**
 * FUNGSI 3: HAPUS (DELETE DATA)
 * Tujuan: Menghapus data menu dari database DAN menghapus file gambarnya dari folder.
 * @param int $id - ID menu yang akan dihapus.
 * @return int - Status keberhasilan.
 */
function hapus($id) {
    global $koneksi;

    // LANGKAH 1: Cari Data File Gambar Lama
    // Sebelum hapus record DB, kita harus tahu nama file gambarnya untuk dihapus dari folder.
    $result = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu = $id");
    $file = mysqli_fetch_assoc($result);
    
    // LANGKAH 2: Hapus File Fisik
    // unlink(): Fungsi PHP native untuk menghapus file.
    unlink("../img/" . $file['gambar']); // Path relatif: keluar folder admin (../), masuk img/
    
    // LANGKAH 3: Hapus Record Database
    mysqli_query($koneksi, "DELETE FROM menu WHERE id_menu = $id");
    
    return mysqli_affected_rows($koneksi);
}

/**
 * FUNGSI 4: EDIT (UPDATE DATA)
 * Tujuan: Memperbarui data menu yang sudah ada. Mengurus logika ganti gambar vs gambar lama.
 * @param array $data - Array $_POST inputan form edit.
 * @return int - Status keberhasilan.
 */
function edit($data) {
    global $koneksi;
    
    // Ambil ID (hidden input) sebagai kunci WHERE update
    $id_menu = $data["id_produk"]; 
    
    // Ambil data text baru & sanitasi
    $nama_menu = htmlspecialchars($data["nama_menu"]);
    $harga = htmlspecialchars($data["harga_jual"]);
    $kategori = trim($data["bahan_utama"]); 
    $kategori = html_entity_decode($kategori);
    $kategori = str_replace("\xc2\xa0", " ", $kategori);
    $deskripsi = htmlspecialchars($data["deskripsi"]); 
    $foto_lama = htmlspecialchars($data["foto_lama"]); // Nama file lama (jika user tidak ganti gambar)

    // LOGIKA PERGANTIAN GAMBAR
    // Cek error pada $_FILES. Jika error === 4, artinya user TIDAK upload gambar baru.
    if( $_FILES['foto_menu']['error'] === 4 ) {
        $gambar = $foto_lama; // Tetap gunakan nama file lama
    } else {
        $gambar = upload(); // Jalankan upload gambar baru
        // Catatan: Idealnya di sini kita tambahkan unlink($foto_lama) jika gambar berhasil diganti agar hemat storage.
    }

    // QUERY UPDATE
    // Setel ulang semua kolom dengan nilai baru/lama
    $query = "UPDATE menu SET
                nama_menu = '$nama_menu',
                harga = '$harga',
                kategori = '$kategori',
                deskripsi = '$deskripsi',
                gambar = '$gambar'
              WHERE id_menu = $id_menu
            ";

    mysqli_query($koneksi, $query);
    
    return mysqli_affected_rows($koneksi);
}

/**
 * FUNGSI 5: SEARCH (PENCARIAN)
 * Tujuan: Mencari data menu berdasarkan keyword di kolom Nama, Harga, Kategori, atau Deskripsi.
 * @param string $keyword - Kata kunci pencarian.
 * @return array - Hasil pencarian.
 */
function search($keyword) {
    // Menggunakan Wildcard (%) pada operator LIKE
    // %keyword% artinya cari yang MENGANDUNG teks tersebut (di depan/belakang/tengah)
    $query = "SELECT * FROM menu
                WHERE
              nama_menu LIKE '%$keyword%' OR
              harga LIKE '%$keyword%' OR
              kategori LIKE '%$keyword%' OR
              deskripsi LIKE '%$keyword%'
            ";
            
	// Re-use fungsi query() dasar kita
    return query($query); 
}

/**
 * FUNGSI 6: UPLOAD GAMBAR
 * Tujuan: Memvalidasi file gambar (ekstensi, ukuran) dan memindahkannya ke folder img/.
 * @return string|bool - Nama file baru jika sukses, FALSE jika gagal.
 */
function upload() {
    // Ambil metadata file dari variabel superglobal $_FILES
    $namaFile = $_FILES['foto_menu']['name'];       // Nama file asli user
    $ukuranFile = $_FILES['foto_menu']['size'];     // Ukuran dalam bytes
    $error = $_FILES['foto_menu']['error'];         // Kode error
    $tmpName = $_FILES['foto_menu']['tmp_name'];    // Lokasi sementara di server

    // Cek 1: Apakah ada gambar yang diupload? (Error 4 = Empty)
    if( $error === 4 ) {
        echo "<script>alert('Pilih gambar terlebih dahulu!');</script>";
        return false;
    }

    // Cek 2: Validasi Ekstensi File
    // Kita hanya mengizinkan JPG, JPEG, dan PNG untuk keamanan.
    $cekEkstensi = ['jpg', 'jpeg', 'png'];
    $ekstensiFoto = explode('.', $namaFile); // Pecah string berdasarkan titik
    $ekstensiFoto = strtolower(end($ekstensiFoto)); // Ambil elemen terakhir & lowercase
    
    // in_array: Cek apakah ekstensi ada di daftar whitelist kita?
    if( !in_array($ekstensiFoto, $cekEkstensi) ) {
        echo "<script>alert('Yang anda upload bukan gambar! (Gunakan jpg/jpeg/png)');</script>";
        return false;
    }

    // Cek 3: Validasi Ukuran File (Limit 2MB)
    // 2.000.000 Bytes = +- 2MB
    if( $ukuranFile > 2000000 ) {
        echo "<script>alert('Ukuran gambar terlalu besar! (Maks 2MB)');</script>";
        return false;
    }

    // JIKA LOLOS VALIDASI:
    // Generate Nama Baru (Unique ID) agar tidak terjadi overwrite filename yang sama
    // uniqid() membangkitkan string unik berdasarkan timestamp mikro
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiFoto; // Gabung lagi dengan ekstensinya

    // Pindahkan file dari server temp memory ke folder fisik project
    move_uploaded_file($tmpName, '../img/' . $namaFileBaru);

    // Kembalikan nama file baru ke pemanggil (untuk disimpan di DB)
    return $namaFileBaru;
}

/**
 * FUNGSI 7: REGISTRASI USER (ADMIN)
 * Tujuan: Mendaftarkan akun admin baru dengan keamanan Password Hashing.
 * @param array $data - Data input user.
 * @return int - Status keberhasilan.
 */
function registrasi($data) {
    global $koneksi;

    // Sanitasi data dasar
    $namaLengkap = htmlspecialchars($data["fullname"]);
    
    // Username di-lowercase & dihilangkan backslashnya biar bersih
    $username = strtolower(stripslashes($data["username"])); 
    
    // mysqli_real_escape_string: Wajib untuk password sebelum diolah
    // agar karakter ' atau " tidak merusak query SQL.
    $password = mysqli_real_escape_string($koneksi, $data["password"]);
    $password2 = mysqli_real_escape_string($koneksi, $data["password2"]);

    // Cek Duplikasi Username
    // Query apakah username sudah ada di database?
    $result = mysqli_query($koneksi, "SELECT username FROM user WHERE username = '$username'");
    if( mysqli_fetch_assoc($result) ) {
        echo "<script>alert('Username sudah terdaftar! Pilih yang lain.');</script>";
        return false;
    }

    // Cek Konfirmasi Password
    if( $password !== $password2 ) {
        echo "<script>alert('Konfirmasi password tidak sesuai!');</script>";
        return false;
    }

    // ENKRIPSI PASSWORD (HASHING) - FITUR SECURITY TERPENTING
    // Jangan pernah simpan password plain text di database!
    // password_hash() menggunakan algoritma Bcrypt (default PHP) yang sangat kuat.
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke DB tabel 'user'
    mysqli_query($koneksi, "INSERT INTO user VALUES(NULL, '$namaLengkap', '$username', '$password')");

    return mysqli_affected_rows($koneksi);
}

?>