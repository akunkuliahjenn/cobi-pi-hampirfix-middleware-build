
<?php
require_once __DIR__ . '/config/db.php';

echo "<h1>🔧 Test Admin Reset Password</h1>";

$username = 'testo';
$temp_password = 'tt1234'; // Password yang mau di-set

try {
    // Cari user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p><strong>✓ User ditemukan:</strong> ID {$user['id']}, Username: {$user['username']}</p>";
        
        // Hash password temporary
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
        echo "<p><strong>Password temporary:</strong> $temp_password</p>";
        echo "<p><strong>Password hash:</strong> " . substr($hashed_password, 0, 30) . "...</p>";
        
        // Update password dan set must_change_password = 1
        $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
        $success = $updateStmt->execute([$hashed_password, $user['id']]);
        
        if ($success) {
            echo "<p style='color:green;'>✅ Password berhasil direset!</p>";
            
            // Verify update
            $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE id = ?");
            $verifyStmt->execute([$user['id']]);
            $updated_user = $verifyStmt->fetch();
            
            // Test password verification
            $verify_test = password_verify($temp_password, $updated_user['password']);
            
            echo "<p><strong>Database status:</strong></p>";
            echo "<ul>";
            echo "<li>must_change_password: {$updated_user['must_change_password']}</li>";
            echo "<li>Password verification test: " . ($verify_test ? '<span style=\"color:green;\">SUCCESS</span>' : '<span style=\"color:red;\">FAILED</span>') . "</li>";
            echo "</ul>";
            
            if ($verify_test) {
                echo "<div style='background:lightgreen;padding:10px;margin:10px 0;'>";
                echo "<h3>✅ RESET BERHASIL!</h3>";
                echo "<p><strong>Username:</strong> $username</p>";
                echo "<p><strong>Password temporary:</strong> $temp_password</p>";
                echo "<p><strong>Status:</strong> Harus ganti password setelah login</p>";
                echo "</div>";
                
                echo "<h3>🧪 Test Login Process</h3>";
                echo "<p>Simulasi login dengan username: $username dan password: $temp_password</p>";
                
                // Test login simulation
                $loginStmt = $db->prepare("SELECT id, username, password, role, must_change_password FROM users WHERE username = ?");
                $loginStmt->execute([$username]);
                $login_user = $loginStmt->fetch();
                
                if ($login_user && password_verify($temp_password, $login_user['password'])) {
                    echo "<p style='color:green;'>✅ LOGIN AKAN BERHASIL!</p>";
                    echo "<p>User akan diarahkan ke halaman change password karena must_change_password = 1</p>";
                } else {
                    echo "<p style='color:red;'>❌ LOGIN AKAN GAGAL!</p>";
                }
            }
        } else {
            echo "<p style='color:red;'>❌ Gagal update password</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ User tidak ditemukan</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>💡 Cara Test:</h3>";
echo "<ol>";
echo "<li>Jalankan file ini untuk reset password user '$username'</li>";
echo "<li>Buka halaman login</li>";
echo "<li>Login dengan username: $username dan password: $temp_password</li>";
echo "<li>Jika berhasil, user akan diarahkan ke halaman change password</li>";
echo "</ol>";
?>
