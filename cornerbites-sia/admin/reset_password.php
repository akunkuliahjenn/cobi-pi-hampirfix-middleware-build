
<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: /cornerbites-sia/pages/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    
    if (strlen($new_password) < 6) {
        $_SESSION['reset_message'] = ['text' => 'Password minimal 6 karakter.', 'type' => 'error'];
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $_SESSION['reset_message'] = ['text' => 'Password berhasil direset. User wajib ganti password saat login.', 'type' => 'success'];
            }
        } catch (PDOException $e) {
            $_SESSION['reset_message'] = ['text' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
        }
    }
    
    header("Location: /cornerbites-sia/admin/users.php");
    exit();
}
?>
