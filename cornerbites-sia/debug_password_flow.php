
<?php
session_start();
require_once __DIR__ . '/config/db.php';

echo "<h2>Reset Password untuk User: testo</h2>";

$username = 'testo';
$new_password = '123456'; // Password temporary baru

try {
    // Cari user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p><strong>User ditemukan:</strong> ID {$user['id']}</p>";
        
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password dan set must_change_password = 1
        $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE username = ?");
        $success = $updateStmt->execute([$hashed_password, $username]);
        
        if ($success) {
            echo "<p style='color:green;'>✓ Password berhasil direset!</p>";
            echo "<p><strong>Password baru:</strong> {$new_password}</p>";
            echo "<p><strong>Status must_change_password:</strong> 1 (WAJIB GANTI)</p>";
            
            // Verify update
            $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE username = ?");
            $verifyStmt->execute([$username]);
            $updated_user = $verifyStmt->fetch();
            
            echo "<p><strong>Password hash:</strong> " . substr($updated_user['password'], 0, 30) . "...</p>";
            echo "<p><strong>Must change password:</strong> " . $updated_user['must_change_password'] . "</p>";
            
            // Test password verification
            if (password_verify($new_password, $updated_user['password'])) {
                echo "<p style='color:green;'>✓ Password verification SUCCESS!</p>";
            } else {
                echo "<p style='color:red;'>✗ Password verification FAILED!</p>";
            }
            
            echo "<hr>";
            echo "<h3>Instruksi Login:</h3>";
            echo "<ol>";
            echo "<li>Buka halaman login: <a href='/cornerbites-sia/auth/login.php' target='_blank'>Login Page</a></li>";
            echo "<li>Username: <strong>{$username}</strong></li>";
            echo "<li>Password: <strong>{$new_password}</strong></li>";
            echo "<li>Setelah login, sistem akan otomatis redirect ke halaman ganti password</li>";
            echo "<li>Ganti password dengan password baru sesuai keinginan</li>";
            echo "</ol>";
            
        } else {
            echo "<p style='color:red;'>✗ Gagal update password!</p>";
        }
        
    } else {
        echo "<p style='color:red;'>✗ User '{$username}' tidak ditemukan!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/cornerbites-sia/auth/login.php'>← Kembali ke Login</a></p>";
?>
