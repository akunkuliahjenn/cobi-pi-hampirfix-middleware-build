
<?php
// includes/password_change_middleware.php
// Middleware super strict untuk memaksa user ganti password

function enforcePasswordChangeMiddleware() {
    global $db;
    
    // Skip jika di halaman change_password.php atau logout
    $current_page = $_SERVER['PHP_SELF'];
    if (strpos($current_page, 'change_password.php') !== false || 
        strpos($current_page, 'logout.php') !== false) {
        return;
    }
    
    // Cek database untuk memastikan status terkini
    $stmt = $db->prepare("SELECT must_change_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Jika user harus ganti password, paksa redirect
    if ($user && $user['must_change_password'] == 1) {
        // Set session flag
        $_SESSION['must_change_password'] = true;
        $_SESSION['redirect_attempted'] = $current_page;
        
        // Clear any bypass attempts
        session_regenerate_id(true);
        
        // Force redirect dengan header yang tidak bisa di-bypass
        header("Location: /cornerbites-sia/auth/change_password.php", true, 302);
        
        // Multiple redirect methods untuk memastikan
        echo "<script>window.location.replace('/cornerbites-sia/auth/change_password.php');</script>";
        echo "<meta http-equiv='refresh' content='0; url=/cornerbites-sia/auth/change_password.php'>";
        
        exit();
    }
}

// Auto-apply middleware jika file ini di-include
enforcePasswordChangeMiddleware();
?>
