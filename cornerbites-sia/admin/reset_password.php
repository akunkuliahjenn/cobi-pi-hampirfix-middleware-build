<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /cornerbites-sia/pages/dashboard.php");
    exit();
}

// Function to generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    // Prevent admin from resetting their own password
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['reset_message'] = ['text' => 'Anda tidak dapat mereset password sendiri.', 'type' => 'error'];
        header("Location: /cornerbites-sia/admin/users.php");
        exit();
    }

    try {
        // Get user info first
        $userStmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user_info = $userStmt->fetch();

        if (!$user_info) {
            $_SESSION['reset_message'] = ['text' => 'User tidak ditemukan.', 'type' => 'error'];
            header("Location: /cornerbites-sia/admin/users.php");
            exit();
        }

        // Generate random password
        $new_password = generateRandomPassword(8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password and force change
        $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");

        if ($stmt->execute([$hashed_password, $user_id])) {
            // Log reset password activity
            require_once __DIR__ . '/../includes/activity_logger.php';
            logActivity($_SESSION['user_id'], $_SESSION['username'], 'reset_password', 'Admin mereset password untuk User ' . $user_info['username'], $db);

            // Password berhasil direset dengan password random
            $_SESSION['reset_message'] = [
                'text' => "Password berhasil direset untuk user: {$user_info['username']}. Password sementara: <strong>{$new_password}</strong> (Salin dan berikan ke user secara aman)", 
                'type' => 'success'
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['reset_message'] = ['text' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }

    header("Location: /cornerbites-sia/admin/users.php");
    exit();
}
?>