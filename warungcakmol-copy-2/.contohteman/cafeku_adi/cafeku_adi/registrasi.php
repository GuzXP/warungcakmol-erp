<?php
// FILE: registrasi.php (ROOT)
// Halaman Registrasi untuk User Biasa (Customer)

session_start();
require 'fungsi.php';

// ===== CEK DAN TAMBAHKAN NILAI ROLE KE ENUM =====
$cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($cekKolom) > 0) {
    $colInfo = mysqli_fetch_assoc($cekKolom);
    $colType = $colInfo['Type'] ?? '';
    
    // Jika ENUM, tambahkan nilai yang mungkin hilang
    if (strpos($colType, 'enum') !== false) {
        preg_match("/^enum\((.*)\)$/", $colType, $matches);
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
            $enumValues = array_map(function($val) {
                return trim($val, "'");
            }, $enumValues);
            
            $valuesToAdd = ['customer', 'kitchen', 'kasir', 'karyawan', 'admin', 'owner'];
            $added = false;
            
            foreach ($valuesToAdd as $val) {
                if (!in_array($val, $enumValues)) {
                    $enumValues[] = $val;
                    $added = true;
                }
            }
            
            if ($added) {
                $newEnum = "enum('" . implode("','", $enumValues) . "')";
                $alterQuery = "ALTER TABLE users MODIFY COLUMN role $newEnum DEFAULT 'customer'";
                mysqli_query($conn, $alterQuery);
            }
        }
    }
}

// Jika sudah login, admin/owner tetap boleh memakai halaman ini untuk membuat akun baru
if (isset($_SESSION['login']) && !in_array($_SESSION['role'], ['admin', 'owner'], true)) {
    if ($_SESSION['role'] === 'kitchen') {
        header("Location: kitchen/dashboard.php");
    } elseif ($_SESSION['role'] === 'kasir') {
        header("Location: kasir/index.php");
    } elseif ($_SESSION['role'] === 'karyawan') {
        header("Location: karyawan/index.php");
    } else {
        header("Location: user/index.php");
    }
    exit;
}

$error = false;
$success = false;

$allowedRoles = ['customer', 'kasir', 'kitchen', 'admin', 'owner', 'karyawan'];
$role = 'customer';

if (isset($_POST["register"])) {
    $nama = htmlspecialchars($_POST["nama"] ?? "");
    $username = strtolower(stripslashes($_POST["username"] ?? ""));
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $selectedRole = strtolower(trim($_POST['role'] ?? 'customer'));

    // Validasi role yang diizinkan
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'owner'], true)) {
        if (in_array($selectedRole, $allowedRoles, true)) {
            $role = $selectedRole;
        }
    }

    // Validasi input kosong
    if (empty($nama) || empty($username) || empty($password) || empty($password2)) {
        $error = "Semua field harus diisi!";
    }
    // Validasi panjang password
    elseif (strlen($password) < 5) {
        $error = "Password minimal 5 karakter!";
    }
    // Validasi konfirmasi password
    elseif ($password !== $password2) {
        $error = "Konfirmasi password tidak sesuai!";
    } else {
        // Cek username sudah ada atau belum
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = "Username sudah terdaftar!";
        } else {
            // Insert user baru
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $password_hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Cafeku</title>
    <link rel="stylesheet" href="css/style-admin.css">
    <style>
        .reg-box {
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background: #1f1f1f;
            border-radius: 15px;
            border: 1px solid #c08b5c;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }
        .reg-box h2 {
            color: #c08b5c;
            text-align: center;
        }
        .reg-box label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        .reg-box input[type="text"],
        .reg-box input[type="password"],
        .reg-box select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 15px;
            margin-bottom: 18px;
            box-sizing: border-box;
            transition: all 0.3s;
        }
        .reg-box input:focus,
        .reg-box select:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }
        .reg-box select option {
            background: #2a2a2a;
            color: #ddd;
        }
        .reg-box .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #c08b5c, #a67b5b);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .reg-box .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(192, 139, 92, 0.3);
        }
        .alert-error {
            color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        .alert-success {
            color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }
        .login-link {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #333;
        }
        .login-link p {
            color: #888;
            margin-bottom: 10px;
        }
        .login-link a {
            color: #c08b5c;
            text-decoration: none;
            font-size: 14px;
            background: rgba(192, 139, 92, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: all 0.3s;
        }
        .login-link a:hover {
            background: rgba(192, 139, 92, 0.2);
        }
    </style>
</head>

<body>

    <div class="reg-box">
        <h2>☕ Registrasi Akun</h2>
        <p style="text-align:center; color:#888; margin-bottom:30px; font-size:14px;">
            Buat akun untuk memesan menu di Cafeku
        </p>

        <?php if ($error) : ?>
            <div class="alert-error">❌ <?= $error ?></div>
        <?php endif; ?>

        <?php if ($success) : ?>
            <div class="alert-success">
                ✅ Berhasil! Akun Anda telah dibuat. Silakan login.
            </div>
            <script>
                setTimeout(() => {
                    document.location.href = 'login.php';
                }, 2000);
            </script>
        <?php endif; ?>

        <?php if (!$success) : ?>
            <form action="" method="post">
                <label for="nama">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" required placeholder="Nama Anda..." autocomplete="off">

                <label for="username">Username</label>
                <input type="text" name="username" id="username" required placeholder="Username untuk login" autocomplete="off">

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Minimal 5 karakter" autocomplete="off">

                <label for="password2">Konfirmasi Password</label>
                <input type="password" name="password2" id="password2" required placeholder="Ulangi password" autocomplete="off">

                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'owner'], true)) : ?>
                    <label for="role">Pilih Role</label>
                    <select name="role" id="role" required>
                        <option value="customer">Customer</option>
                        <option value="kasir">Kasir</option>
                        <option value="kitchen">Kitchen</option>
                        <option value="karyawan">Karyawan</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                <?php endif; ?>

                <div style="margin-top: 20px;">
                    <button type="submit" name="register" class="btn">DAFTAR</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="login-link">
            <p>Sudah punya akun?</p>
            <a href="login.php">← Kembali ke Login</a>
        </div>
    </div>

</body>

</html>