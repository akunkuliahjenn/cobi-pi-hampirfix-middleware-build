
<?php
require_once __DIR__ . '/config/db.php';

$username = 'testo';
$test_passwords = ['test12345', 'testo1234', 'testo123'];

echo "<h2>Debug Login Test untuk: $username</h2>";

try {
    $stmt = $db->prepare("SELECT id, username, password, must_change_password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p><strong>User ditemukan:</strong> ID {$user['id']}</p>";
        echo "<p><strong>Password hash:</strong> " . substr($user['password'], 0, 30) . "...</p>";
        echo "<p><strong>Must change password:</strong> {$user['must_change_password']}</p>";
        
        echo "<hr><h3>Testing Passwords:</h3>";
        
        foreach ($test_passwords as $test_password) {
            echo "<p><strong>Testing password:</strong> '$test_password' - ";
            
            if (password_verify($test_password, $user['password'])) {
                echo "<span style='color:green;'>✓ COCOK!</span></p>";
            } else {
                echo "<span style='color:red;'>✗ TIDAK COCOK</span></p>";
            }
        }
        
        echo "<hr><h3>Manual Password Update Test:</h3>";
        $new_test_password = 'test12345';
        $new_hash = password_hash($new_test_password, PASSWORD_DEFAULT);
        
        echo "<p>Updating password to: $new_test_password</p>";
        
        $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE username = ?");
        if ($updateStmt->execute([$new_hash, $username])) {
            echo "<p style='color:green;'>✓ Password updated successfully!</p>";
            
            // Verify
            $verifyStmt = $db->prepare("SELECT password FROM users WHERE username = ?");
            $verifyStmt->execute([$username]);
            $updated = $verifyStmt->fetch();
            
            if (password_verify($new_test_password, $updated['password'])) {
                echo "<p style='color:green;'>✓ Verification SUCCESS!</p>";
                echo "<p><strong>Sekarang coba login dengan:</strong><br>";
                echo "Username: $username<br>";
                echo "Password: $new_test_password</p>";
            } else {
                echo "<p style='color:red;'>✗ Verification FAILED!</p>";
            }
        } else {
            echo "<p style='color:red;'>✗ Update failed!</p>";
        }
        
    } else {
        echo "<p style='color:red;'>User tidak ditemukan!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/cornerbites-sia/auth/login.php'>← Kembali ke Login</a></p>";
?>
