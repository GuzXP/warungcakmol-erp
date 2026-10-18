<?php
// FILE: registrasi.php
session_start();
require 'fungsi.php';

// ===== CEK DAN TAMBAHKAN ROLE KE ENUM =====
$cekKolom = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if (mysqli_num_rows($cekKolom) > 0) {
    $colInfo = mysqli_fetch_assoc($cekKolom);
    $colType = $colInfo['Type'] ?? '';
    
    if (strpos($colType, 'enum') !== false) {
        preg_match("/^enum\((.*)\)$/", $colType, $matches);
        if (isset($matches[1])) {
            $enumValues = str_getcsv($matches[1], ',', "'");
            $enumValues = array_map(function($val) {
                return trim($val, "'");
            }, $enumValues);
            
            $valuesToAdd = ['customer', 'kitchen', 'kasir', 'karyawan', 'admin', 'owner', 'manager', 'waiter', 'barista'];
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

// ===== CEK SESSION =====
if (isset($_SESSION['login'])) {
    // Jika sudah login, redirect ke dashboard sesuai role
    $role = $_SESSION['role'] ?? 'customer';
    if ($role === 'admin' || $role === 'owner') {
        header("Location: admin/index.php");
    } elseif ($role === 'kasir') {
        header("Location: kasir/index.php");
    } elseif ($role === 'kitchen') {
        header("Location: kitchen/dashboard.php");
    } elseif ($role === 'karyawan') {
        header("Location: karyawan/index.php");
    } elseif ($role === 'manager') {
        header("Location: admin/manager.php");
    } else {
        header("Location: user/index.php");
    }
    exit;
}

$error = false;
$success = false;
$errorMessage = '';

// ===== CEK APAKAH USER SEDANG LOGIN SEBAGAI ADMIN =====
$isAdmin = isset($_SESSION['login']) && in_array($_SESSION['role'], ['admin', 'owner']);

// Role yang diizinkan (hanya untuk admin)
$allowedRoles = ['customer', 'kasir', 'kitchen', 'karyawan', 'admin', 'owner', 'manager', 'waiter', 'barista'];
$role = 'customer'; // Default untuk customer baru

if (isset($_POST["register"])) {
    $nama = htmlspecialchars(trim($_POST["nama"] ?? ""));
    $username = strtolower(trim($_POST["username"] ?? ""));
    $password = $_POST["password"] ?? "";
    $password2 = $_POST["password2"] ?? "";
    $selectedRole = strtolower(trim($_POST['role'] ?? 'customer'));

    // ===== VALIDASI ROLE =====
    // Jika user adalah admin, bisa pilih role. Jika bukan, role = customer
    if ($isAdmin && in_array($selectedRole, $allowedRoles, true)) {
        $role = $selectedRole;
    } else {
        $role = 'customer'; // Customer biasa hanya bisa daftar sebagai customer
    }

    // Validasi input
    if (empty($nama) || empty($username) || empty($password) || empty($password2)) {
        $error = true;
        $errorMessage = "Semua field harus diisi!";
    } elseif (strlen($password) < 5) {
        $error = true;
        $errorMessage = "Password minimal 5 karakter!";
    } elseif ($password !== $password2) {
        $error = true;
        $errorMessage = "Konfirmasi password tidak sesuai!";
    } else {
        // Cek username sudah ada
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $error = true;
            $errorMessage = "Username sudah terdaftar!";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $password_hash, $role);

            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $error = true;
                $errorMessage = "Gagal mendaftar: " . mysqli_error($conn);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style-admin.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #0d0d0d;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .reg-box {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 35px;
            background: #1f1f1f;
            border-radius: 15px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }
        .reg-box h2 {
            color: #c08b5c;
            text-align: center;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .reg-box .subtitle {
            text-align: center;
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .reg-box label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
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
            box-sizing: border-box;
            transition: all 0.3s;
        }
        .reg-box input[type="text"],
        .reg-box input[type="password"],
        .reg-box select {
            margin-bottom: 18px;
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
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        .alert-success {
            color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            border: 1px solid rgba(39, 174, 96, 0.2);
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        .login-link p {
            color: #888;
            margin-bottom: 10px;
        }
        .login-link a {
            color: #c08b5c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .login-link a:hover {
            color: #a67b5b;
        }
        
        /* ===== PASSWORD WRAPPER ===== */
        .password-wrapper {
            position: relative;
            margin-bottom: 18px;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 50px;
            height: 55px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s;
            padding-left: 15px;
        }
        .password-wrapper input:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }
        .password-wrapper input::placeholder {
            color: #666;
        }
        .password-wrapper .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #888;
            transition: all 0.3s;
            padding: 0;
        }
        .password-wrapper .toggle-password:hover {
            color: #c08b5c;
        }
        .password-wrapper .toggle-password i {
            font-size: 20px;
            line-height: 1;
            pointer-events: none;
        }
        
        /* ===== ADMIN NOTE ===== */
        .admin-note {
            background: rgba(192, 139, 92, 0.1);
            border: 1px solid #c08b5c;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 15px;
            color: #c08b5c;
            font-size: 13px;
            text-align: center;
        }
        
        @media (max-width: 480px) {
            .reg-box { padding: 25px 20px; }
        }
    </style>
</head>
<body>

<div class="reg-box">
    <h2>☕ Registrasi Akun</h2>
    <p class="subtitle">Buat akun untuk bergabung di Cafeku</p>

    <?php if ($error) : ?>
        <div class="alert-error">❌ <?= $errorMessage ?></div>
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
            <input type="text" name="nama" id="nama" required placeholder="Nama Anda...">

            <label for="username">Username</label>
            <input type="text" name="username" id="username" required placeholder="Username untuk login">

            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" required placeholder="Minimal 5 karakter">
                <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <label for="password2">Konfirmasi Password</label>
            <div class="password-wrapper">
                <input type="password" name="password2" id="password2" required placeholder="Ulangi password">
                <button type="button" class="toggle-password" onclick="togglePassword('password2')" aria-label="Toggle password visibility">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <?php if ($isAdmin) : ?>
                <!-- ===== HANYA ADMIN YANG BISA LIHAT ROLE ===== -->
                <div class="admin-note">
                    🔑 Anda login sebagai <strong>Admin</strong>. Anda dapat memilih role untuk akun baru.
                </div>
                <label for="role">Pilih Role</label>
                <select name="role" id="role" required>
                    <option value="customer">👤 Customer</option>
                    <option value="kasir">💵 Kasir</option>
                    <option value="kitchen">🍳 Kitchen</option>
                    <option value="karyawan">👥 Karyawan</option>
                    <option value="manager">👨‍💼 Manager</option>
                    <option value="waiter">🍽️ Waiter</option>
                    <option value="barista">☕ Barista</option>
                    <option value="admin">🔧 Admin</option>
                    <option value="owner">🏢 Owner</option>
                </select>
            <?php else : ?>
                <!-- ===== CUSTOMER BIASA ===== -->
                <input type="hidden" name="role" value="customer">
                <div style="background: rgba(46, 204, 113, 0.1); border: 1px solid #27ae60; border-radius: 8px; padding: 10px 15px; margin-bottom: 18px; color: #2ecc71; font-size: 13px; text-align: center;">
                    ✅ Akun Anda akan dibuat sebagai <strong>Customer</strong>
                </div>
            <?php endif; ?>

            <button type="submit" name="register" class="btn">🚀 DAFTAR</button>
        </form>
    <?php endif; ?>

    <div class="login-link">
        <p>Sudah punya akun?</p>
        <a href="login.php">← Login Sekarang</a>
    </div>
</div>

<script>
    function togglePassword(id) {
        var input = document.getElementById(id);
        var button = input.parentElement.querySelector('.toggle-password');
        var icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
        input.focus();
    }
</script>

</body>
</html>