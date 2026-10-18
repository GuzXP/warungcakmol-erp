<?php
// FILE: login.php
session_start();
require 'fungsi.php';

// Cek Cookie
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $id = (int) $_COOKIE['id'];

    $stmt = mysqli_prepare($conn, "SELECT username, role, nama FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row && $_COOKIE['key'] === hash('sha256', $row['username'])) {
        $_SESSION['login'] = true;
        $_SESSION['role'] = normalizeRole($row['role']);
        $_SESSION['nama'] = $row['nama'];
    }
}

// Normalisasi Role
function normalizeRole($role)
{
    $role = strtolower(trim($role));
    
    // Mapping role
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
        'customer' => 'user'
    ];
    
    return $roleMap[$role] ?? $role;
}

// Fungsi redirect berdasarkan role
function redirectByRole($role)
{
    $role = normalizeRole($role);

    switch ($role) {

        case 'admin':
        case 'owner':
            header("Location: admin/index.php");
            break;

        case 'kasir':
            header("Location: kasir/index.php");
            break;

        case 'karyawan':
            header("Location: karyawan/index.php");
            break;

        case 'user':
            header("Location: user/index.php");
            break;

        case 'kitchen':
            header("Location: kitchen/dashboard.php");
            break;

        default:
            // Jika role tidak dikenal, arahkan ke halaman default
            header("Location: user/index.php");
            break;
    }

    exit;
}

// Cek Session
if (isset($_SESSION['login'])) {
    redirectByRole($_SESSION['role']);
}

$error = false;

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {

        $row = mysqli_fetch_assoc($result);

        // Cek password (hash atau plain)
        if (
            password_verify($password, $row['password']) ||
            $password === $row['password']
        ) {

            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = normalizeRole($row['role']);

            if (isset($_POST['remember'])) {

                setcookie(
                    'id',
                    $row['id'],
                    time() + 3600,
                    '/'
                );

                setcookie(
                    'key',
                    hash('sha256', $row['username']),
                    time() + 3600,
                    '/'
                );
            }

            redirectByRole($_SESSION['role']);
        }
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - Cafeku</title>
    <link rel="stylesheet" href="css/style-admin.css">
    <style>
        .login-box {
            max-width: 400px;
            margin: 50px auto;
            background: #1f1f1f;
            padding: 40px 30px;
            border-radius: 12px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0,0,0,0.5);
        }

        .login-box h2 {
            color: #c08b5c;
            text-align: center;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .login-box .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .login-box label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #c08b5c;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 15px;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .login-box input[type="text"]:focus,
        .login-box input[type="password"]:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.2);
        }

        .login-box .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #c08b5c, #a67b5b);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-box .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(192, 139, 92, 0.3);
        }

        .login-box .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .login-box .remember label {
            margin-bottom: 0;
            font-weight: 400;
            font-size: 14px;
            color: #aaa;
            cursor: pointer;
        }

        .login-box .remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .login-box .error-msg {
            color: #e74c3c;
            font-weight: 700;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        .login-box .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }

        .login-box .register-link p {
            color: #888;
            margin-bottom: 10px;
        }

        .login-box .register-link a {
            display: inline-block;
            padding: 10px 25px;
            background: #2a2a2a;
            color: #c08b5c;
            border: 1px solid #c08b5c;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-box .register-link a:hover {
            background: #333;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>

    <div class="login-box">
        <h2>☕ Cafeku</h2>
        <p class="subtitle">Masuk dengan akun Anda</p>

        <?php if ($error) : ?>
            <div class="error-msg">
                ❌ Username / Password salah!
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required autocomplete="off" placeholder="Masukkan username">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required autocomplete="off" placeholder="Masukkan password">

            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" name="login" class="btn-login">
                LOGIN
            </button>
        </form>

        <div class="register-link">
            <p>Belum punya akun?</p>
            <a href="registrasi.php">Buat Akun Baru</a>
        </div>
    </div>

</body>

</html>