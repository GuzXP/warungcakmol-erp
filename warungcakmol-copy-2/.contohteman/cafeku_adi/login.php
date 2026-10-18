<?php
// FILE: login.php
session_start();
require 'fungsi.php';

// ===== CEK COOKIE =====
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

// ===== CEK SESSION =====
if (isset($_SESSION['login'])) {
    redirectByRole($_SESSION['role']);
}

$error = false;
$debug = '';

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {

        $row = mysqli_fetch_assoc($result);
        
        $debug .= "User ditemukan: " . $row['username'] . "<br>";
        $debug .= "Role: " . $row['role'] . "<br>";

        if (password_verify($password, $row['password'])) {
            $debug .= "✅ Password cocok!<br>";
            
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = normalizeRole($row['role']);

            if (isset($_POST['remember'])) {
                setcookie('id', $row['id'], time() + 3600, '/');
                setcookie('key', hash('sha256', $row['username']), time() + 3600, '/');
            }

            redirectByRole($_SESSION['role']);
            
        } elseif ($password === $row['password']) {
            $debug .= "✅ Password cocok! (plain text)<br>";
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE username = '$username'");
            
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = normalizeRole($row['role']);

            if (isset($_POST['remember'])) {
                setcookie('id', $row['id'], time() + 3600, '/');
                setcookie('key', hash('sha256', $row['username']), time() + 3600, '/');
            }

            redirectByRole($_SESSION['role']);
            
        } else {
            $debug .= "❌ Password tidak cocok!<br>";
        }
    } else {
        $debug .= "❌ User tidak ditemukan!<br>";
    }

    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafeku</title>
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
        .login-box {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 35px;
            background: #1f1f1f;
            border-radius: 15px;
            border: 1px solid #c08b5c;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
        }
        .login-box h2 {
            color: #c08b5c;
            text-align: center;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .login-box .subtitle {
            text-align: center;
            color: #888;
            font-size: 14px;
            margin-bottom: 25px;
        }
        .login-box label {
            color: #ddd;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #333;
            border-radius: 8px;
            background: #2a2a2a;
            color: #ddd;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.3s;
            margin-bottom: 18px;
        }
        .login-box input:focus {
            outline: none;
            border-color: #c08b5c;
            box-shadow: 0 0 0 3px rgba(192, 139, 92, 0.15);
        }
        .login-box input::placeholder {
            color: #666;
        }
        .login-box .btn-login {
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
        .login-box .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(192, 139, 92, 0.3);
        }
        .login-box .error-msg {
            color: #e74c3c;
            font-weight: 600;
            padding: 12px;
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.2);
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .login-box .debug-box {
            color: #f1c40f;
            font-weight: 400;
            padding: 12px;
            background: rgba(241, 196, 15, 0.1);
            border: 1px solid rgba(241, 196, 15, 0.2);
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 12px;
            font-family: monospace;
            word-wrap: break-word;
        }
        .login-box .remember {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .login-box .remember label {
            margin-bottom: 0;
            font-weight: 400;
            color: #aaa;
            cursor: pointer;
            font-size: 14px;
        }
        .login-box .remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #c08b5c;
            cursor: pointer;
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
            color: #c08b5c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .login-box .register-link a:hover {
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
            margin-bottom: 0;
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
        
        @media (max-width: 480px) {
            .login-box { padding: 25px 20px; }
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>☕ Cafeku</h2>
    <p class="subtitle">Masuk dengan akun Anda</p>

    <?php if ($error) : ?>
        <div class="error-msg">❌ Username / Password salah!</div>
    <?php endif; ?>

    <?php if (!empty($debug)) : ?>
        <div class="debug-box">
            <strong>🔍 DEBUG:</strong><br>
            <?= $debug ?>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required placeholder="Masukkan username" autocomplete="off">

        <label for="password">Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password" required placeholder="Masukkan password" autocomplete="off">
            <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <div class="remember">
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Ingat saya</label>
        </div>

        <button type="submit" name="login" class="btn-login">LOGIN</button>
    </form>

    <div class="register-link">
        <p>Belum punya akun?</p>
        <a href="registrasi.php">Buat Akun Baru →</a>
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