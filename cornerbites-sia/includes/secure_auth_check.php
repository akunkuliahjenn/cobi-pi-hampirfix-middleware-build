
<?php
// includes/secure_auth_check.php
// Middleware keamanan tingkat tinggi untuk autentikasi

require_once __DIR__ . '/../config/auth_config.php';
require_once __DIR__ . '/../config/db.php';

// Start secure session
secureSessionStart();

/**
 * Verify user authentication with multiple layers
 */
function verifyAuthentication() {
    // Check session-based auth first
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['auth_token'])) {
        return false;
    }
    
    // Verify JWT token if exists
    if (isset($_SESSION['auth_token'])) {
        $tokenData = verifyJWT($_SESSION['auth_token']);
        if (!$tokenData) {
            return false;
        }
        
        // Verify token data matches session
        if ($tokenData['user_id'] != $_SESSION['user_id'] || 
            $tokenData['username'] != $_SESSION['username']) {
            return false;
        }
        
        // Update session with fresh token data
        $_SESSION['user_role'] = $tokenData['role'];
        $_SESSION['token_expires'] = $tokenData['exp'];
        
        return true;
    }
    
    return false;
}

/**
 * Check if user has required role
 */
function hasRole($required_role) {
    if (!verifyAuthentication()) {
        return false;
    }
    
    $user_role = $_SESSION['user_role'] ?? 'guest';
    
    if ($required_role === 'admin') {
        return $user_role === 'admin';
    }
    
    return true; // For 'user' role or general access
}

/**
 * Get current user info securely
 */
function getCurrentUser() {
    if (!verifyAuthentication()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role'],
        'token_expires' => $_SESSION['token_expires'] ?? null
    ];
}

/**
 * Main authentication check
 */
if (!verifyAuthentication()) {
    // Clear potentially corrupted session
    session_destroy();
    secureSessionStart();
    
    $_SESSION['error_message'] = 'Sesi login Anda telah berakhir. Silakan login kembali.';
    header("Location: /cornerbites-sia/auth/login.php");
    exit();
}

// Check role-based access
$current_page = $_SERVER['PHP_SELF'];
$admin_pages = [
    '/cornerbites-sia/admin/dashboard.php',
    '/cornerbites-sia/admin/users.php',
    '/cornerbites-sia/admin/semua_transaksi.php',
    '/cornerbites-sia/admin/statistik.php',
];

if (in_array($current_page, $admin_pages)) {
    if (!hasRole('admin')) {
        $_SESSION['error_message'] = 'Akses ditolak. Anda tidak memiliki izin admin.';
        header("Location: /cornerbites-sia/pages/dashboard.php");
        exit();
    }
}

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();
?>
