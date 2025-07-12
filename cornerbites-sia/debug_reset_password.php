
<?php
// DEBUG SCRIPT - HAPUS SETELAH TESTING
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

echo "<h1>Debug Reset Password</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $new_password = trim($_POST['new_password']);
    
    echo "<h2>Testing Reset untuk: " . htmlspecialchars($username) . "</h2>";
    
    try {
        // Cari user
        $stmt = $db->prepare("SELECT id, username, password, role, must_change_password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color:green;'>User ditemukan: ID " . $user['id'] . "</p>";
            echo "<p><strong>Password lama hash:</strong> " . substr($user['password'], 0, 20) . "...</p>";
            
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            echo "<p><strong>Password baru hash:</strong> " . substr($hashed_password, 0, 20) . "...</p>";
            
            // Update password dan set must_change_password
            $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
            $result = $updateStmt->execute([$hashed_password, $user['id']]);
            
            if ($result) {
                echo "<p style='color:green;'>Update berhasil!</p>";
                
                // Verify update
                $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE id = ?");
                $verifyStmt->execute([$user['id']]);
                $updated_user = $verifyStmt->fetch();
                
                echo "<p><strong>Password setelah update:</strong> " . substr($updated_user['password'], 0, 20) . "...</p>";
                echo "<p><strong>Must change password:</strong> " . $updated_user['must_change_password'] . "</p>";
                
                // Test password verification
                if (password_verify($new_password, $updated_user['password'])) {
                    echo "<p style='color:green;'>✓ Password verification SUCCESS!</p>";
                } else {
                    echo "<p style='color:red;'>✗ Password verification FAILED!</p>";
                }
                
                // Test login simulation
                echo "<hr><h3>Simulasi Login Test</h3>";
                
                // Start a new session for testing
                if (session_status() != PHP_SESSION_ACTIVE) {
                    session_start();
                }
                
                // Clear existing session
                session_unset();
                session_regenerate_id(true);
                
                // Simulate login process
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // Check if must change password
                if ($updated_user['must_change_password'] == 1) {
                    $_SESSION['must_change_password'] = true;
                    $_SESSION['force_password_change'] = true;
                    echo "<p style='color:orange;'>✓ Session set untuk password change</p>";
                }
                
                echo "<p><strong>Session setelah simulasi login:</strong></p>";
                echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                
            } else {
                echo "<p style='color:red;'>Update gagal!</p>";
            }
        } else {
            echo "<p style='color:red;'>User tidak ditemukan!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST">
    <p>
        <label>Username:</label><br>
        <input type="text" name="username" value="testo" required>
    </p>
    <p>
        <label>Password Baru:</label><br>
        <input type="text" name="new_password" value="testo1234" required>
    </p>
    <p>
        <button type="submit">Test Reset Password</button>
    </p>
</form>

<hr>
<h3>Session Debug</h3>
<?php
if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data: " . print_r($_SESSION, true);
echo "Session Cookie Params: " . print_r(session_get_cookie_params(), true);
echo "</pre>";

// Test direct login link
echo "<hr>";
echo "<h3>Test Links</h3>";
echo "<p><a href='/cornerbites-sia/auth/login.php' target='_blank'>Test Login Page</a></p>";
echo "<p><a href='/cornerbites-sia/auth/change_password.php' target='_blank'>Test Change Password Page</a></p>";
?>
