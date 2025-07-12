<?php
// process/login_process.php
// File ini menangani logika proses login dengan keamanan tinggi.

require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../config/db.php';

// Configure session cookie parameters BEFORE starting session
if (!headers_sent()) {
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Start secure session with proper cookie settings
secureSessionStart();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $_SESSION['error_message'] = 'Username atau password tidak valid. Silakan coba lagi.';
        header("Location: /cornerbites-sia/auth/login.php");
        exit();
    }

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = 'Username dan password harus diisi.';
        header("Location: /cornerbites-sia/auth/login.php");
        exit();
    }

    // Check rate limiting
    $login_identifier = hash('sha256', $username . $_SERVER['REMOTE_ADDR']);
    if (!checkLoginAttempts($login_identifier)) {
        $_SESSION['error_message'] = 'Terlalu banyak percobaan login. Coba lagi dalam 5 menit.';
        header("Location: /cornerbites-sia/auth/login.php");
        exit();
    }

    try {
        $conn = $db; // Menggunakan koneksi $db dari db.php

        // Siapkan query untuk mencari user berdasarkan username dan mengambil role
        $stmt = $conn->prepare("SELECT id, username, password, role, must_change_password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi user dan password
        if ($user && password_verify($password, $user['password'])) {

            // Record successful login attempt
            recordLoginAttempt($login_identifier, true);

            // Regenerate session ID untuk keamanan
            session_regenerate_id(true);

            // Generate JWT token
            $jwt_token = generateJWT($user['id'], $user['username'], $user['role']);

            // Login berhasil - set session dengan token
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['auth_token'] = $jwt_token;
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            // Log activity
            require_once __DIR__ . '/../includes/activity_logger.php';
            logActivity($user['id'], $user['username'], 'login', 'User ' . $user['username'] . ' baru saja login', $conn);

            // Log successful login (optional)
            error_log("Successful login: User ID {$user['id']} ({$user['username']}) from IP {$_SERVER['REMOTE_ADDR']}");

            // Cek apakah user harus ganti password
            if (isset($user['must_change_password']) && $user['must_change_password'] == 1) {
                $_SESSION['must_change_password'] = true;
                $_SESSION['force_password_change'] = true;
                $_SESSION['password_change_token'] = bin2hex(random_bytes(32));
                $_SESSION['password_change_start_time'] = time();
                $_SESSION['success_message'] = 'Login berhasil! Anda harus mengganti password sebelum melanjutkan.';
                header("Location: /cornerbites-sia/auth/change_password.php");
                exit();
            }

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: /cornerbites-sia/admin/dashboard.php");
            } else {
                header("Location: /cornerbites-sia/pages/dashboard.php");
            }
            exit();
        } else {
            // Record failed login attempt
            recordLoginAttempt($login_identifier, false);

            // Log failed login attempt
            error_log("Failed login attempt: Username '{$username}' from IP {$_SERVER['REMOTE_ADDR']}");

            // Login gagal
            $_SESSION['error_message'] = 'Username atau password salah.';
            header("Location: /cornerbites-sia/auth/login.php");
            exit();
        }

    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $_SESSION['error_message'] = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
        header("Location: /cornerbites-sia/auth/login.php");
        exit();
    }
} else {
    // Jika diakses langsung tanpa POST request, redirect ke login
    header("Location: /cornerbites-sia/auth/login.php");
    exit();
}
?>