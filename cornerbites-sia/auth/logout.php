<?php
// auth/logout.php
// File ini untuk proses logout pengguna.

// Memulai session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log logout activity before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    require_once __DIR__ . '/../includes/activity_logger.php';
    require_once __DIR__ . '/../config/db.php';
    logActivity($_SESSION['user_id'], $_SESSION['username'], 'logout', 'User ' . $_SESSION['username'] . ' baru saja logout', $db);
}

// Hapus semua session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// HAPUS JWT COOKIE
$cookie_options = [
    'expires' => time() - 3600, // 1 jam yang lalu
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
];

// HANYA hapus JWT token
setcookie('auth_token', '', $cookie_options);

// Destroy session
session_destroy();

// Redirect ke halaman login setelah logout
header("Location: /cornerbites-sia/auth/login.php");
exit(); // Penting untuk menghentikan eksekusi skrip
?>