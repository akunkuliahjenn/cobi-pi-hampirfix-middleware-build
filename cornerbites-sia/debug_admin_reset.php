
<?php
session_start();
require_once __DIR__ . '/config/db.php';

echo "<h1>🔧 Debug Admin Password Reset Flow</h1>";

// Step 1: Reset password seperti yang admin lakukan
echo "<h2>Step 1: Reset Password untuk User 'testo'</h2>";

$username = 'testo';
$temp_password = '123456';

try {
    // Cari user
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✓ User ditemukan: ID {$user['id']}, Username: {$user['username']}</p>";
        
        // Hash password temporary
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
        echo "<p>✓ Password temporary: <strong>{$temp_password}</strong></p>";
        echo "<p>✓ Password hash: " . substr($hashed_password, 0, 30) . "...</p>";
        
        // Update password seperti yang admin lakukan
        $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
        $success = $updateStmt->execute([$hashed_password, $user['id']]);
        
        if ($success) {
            echo "<p style='color:green;'>✓ Password berhasil direset oleh admin!</p>";
            
            // Verify update
            $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE id = ?");
            $verifyStmt->execute([$user['id']]);
            $updated_user = $verifyStmt->fetch();
            
            echo "<p>✓ Database updated - must_change_password: {$updated_user['must_change_password']}</p>";
            
            // Test password verification
            $verify_result = password_verify($temp_password, $updated_user['password']);
            echo "<p>✓ Password verification test: " . ($verify_result ? '<span style="color:green;">SUCCESS</span>' : '<span style="color:red;">FAILED</span>') . "</p>";
            
            if ($verify_result) {
                echo "<div style='background:#d4edda;padding:10px;margin:10px 0;border:1px solid #c3e6cb;'>";
                echo "<strong>✅ ADMIN RESET BERHASIL!</strong><br>";
                echo "Username: <strong>{$username}</strong><br>";
                echo "Password temporary: <strong>{$temp_password}</strong><br>";
                echo "Status: Harus ganti password setelah login<br>";
                echo "</div>";
            } else {
                echo "<div style='background:#f8d7da;padding:10px;margin:10px 0;border:1px solid #f5c6cb;'>";
                echo "<strong>❌ PASSWORD VERIFICATION GAGAL!</strong><br>";
                echo "Ada masalah dengan hashing atau verifikasi password";
                echo "</div>";
            }
        } else {
            echo "<p style='color:red;'>❌ Gagal update password</p>";
        }
    } else {
        echo "<p style='color:red;'>❌ User tidak ditemukan</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Step 2: Test login process
echo "<h2>Step 2: Test Login Process</h2>";
echo "<p>Simulasi login dengan username: <strong>{$username}</strong> dan password: <strong>{$temp_password}</strong></p>";

try {
    // Simulasi login process
    $loginStmt = $db->prepare("SELECT id, username, password, role, must_change_password FROM users WHERE username = ?");
    $loginStmt->execute([$username]);
    $login_user = $loginStmt->fetch();
    
    if ($login_user) {
        echo "<p>✓ User ditemukan untuk login</p>";
        
        $password_check = password_verify($temp_password, $login_user['password']);
        echo "<p>✓ Password verification: " . ($password_check ? '<span style="color:green;">SUCCESS</span>' : '<span style="color:red;">FAILED</span>') . "</p>";
        
        if ($password_check) {
            echo "<div style='background:#d4edda;padding:10px;margin:10px 0;border:1px solid #c3e6cb;'>";
            echo "<strong>✅ LOGIN AKAN BERHASIL!</strong><br>";
            echo "User akan diarahkan ke halaman change password karena must_change_password = {$login_user['must_change_password']}<br>";
            echo "</div>";
        } else {
            echo "<div style='background:#f8d7da;padding:10px;margin:10px 0;border:1px solid #f5c6cb;'>";
            echo "<strong>❌ LOGIN AKAN GAGAL!</strong><br>";
            echo "Password temporary tidak cocok dengan yang ada di database";
            echo "</div>";
        }
    } else {
        echo "<p style='color:red;'>❌ User tidak ditemukan untuk login</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error saat test login: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>💡 Instruksi Test:</h3>";
echo "<ol>";
echo "<li>Jalankan file debug ini untuk reset password user 'testo'</li>";
echo "<li>Buka <a href='/cornerbites-sia/auth/login.php' target='_blank'>halaman login</a></li>";
echo "<li>Login dengan username: <strong>testo</strong> dan password: <strong>123456</strong></li>";
echo "<li>Jika berhasil, user akan diarahkan ke halaman change password</li>";
echo "</ol>";
?>
