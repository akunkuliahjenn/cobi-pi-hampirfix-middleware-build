
<?php
session_start();
require_once __DIR__ . '/config/db.php';

echo "<h1>Debug Change Password Flow - ENHANCED</h1>";

// Check session data
echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check if we can find user
if (isset($_SESSION['user_id'])) {
    echo "<h2>User Database Info:</h2>";
    try {
        $stmt = $db->prepare("SELECT id, username, must_change_password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        } else {
            echo "User not found in database!";
        }
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    echo "No user_id in session!";
}

// Ensure token exists in session
if (!isset($_SESSION['password_change_token'])) {
    $_SESSION['password_change_token'] = bin2hex(random_bytes(32));
    echo "<p style='color:blue;'>🔧 Generated new token: " . $_SESSION['password_change_token'] . "</p>";
}

echo "<h2>Current Token Info:</h2>";
echo "<p><strong>Session Token:</strong> " . ($_SESSION['password_change_token'] ?? 'NOT SET') . "</p>";

// Test form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Check token validation
    if (isset($_POST['password_change_token'])) {
        $posted_token = $_POST['password_change_token'];
        $session_token = $_SESSION['password_change_token'] ?? '';
        
        echo "<h3>Token Validation:</h3>";
        echo "<p><strong>Posted Token:</strong> $posted_token</p>";
        echo "<p><strong>Session Token:</strong> $session_token</p>";
        echo "<p><strong>Match:</strong> " . ($posted_token === $session_token ? '✅ YES' : '❌ NO') . "</p>";
    }
    
    // Test password change with proper token validation
    if (isset($_POST['test_password']) && isset($_SESSION['user_id'])) {
        $new_password = $_POST['test_password'];
        
        // Validate token first
        $token_valid = true;
        if (isset($_POST['password_change_token'])) {
            if ($_POST['password_change_token'] !== $_SESSION['password_change_token']) {
                echo "<div style='color: red; font-weight: bold;'>❌ Token tidak valid!</div>";
                $token_valid = false;
            } else {
                echo "<div style='color: green; font-weight: bold;'>✅ Token valid!</div>";
            }
        }
        
        if ($token_valid) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
                
                if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                    echo "<div style='color: green; font-weight: bold;'>✅ Password berhasil diupdate!</div>";
                    
                    // Clear session flags
                    unset($_SESSION['must_change_password']);
                    unset($_SESSION['force_password_change']);
                    unset($_SESSION['password_change_token']);
                    
                    echo "<p><a href='/cornerbites-sia/auth/login.php'>Logout dan Test Login</a></p>";
                } else {
                    echo "<div style='color: red; font-weight: bold;'>❌ Gagal update password</div>";
                }
            } catch (Exception $e) {
                echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</div>";
            }
        }
    }
    
    // Direct password change without token validation (emergency)
    if (isset($_POST['emergency_password']) && isset($_SESSION['user_id'])) {
        $new_password = $_POST['emergency_password'];
        
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                echo "<div style='color: green; font-weight: bold;'>🚨 EMERGENCY: Password berhasil diupdate!</div>";
                
                // Clear session flags
                unset($_SESSION['must_change_password']);
                unset($_SESSION['force_password_change']);
                unset($_SESSION['password_change_token']);
                
                echo "<p><a href='/cornerbites-sia/auth/login.php'>Logout dan Test Login</a></p>";
            } else {
                echo "<div style='color: red; font-weight: bold;'>❌ Gagal update password</div>";
            }
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<h2>Test Password Change (With Token)</h2>
<form method="POST">
    <input type="hidden" name="password_change_token" value="<?php echo $_SESSION['password_change_token']; ?>">
    <label>Test Password (dengan token validation):</label><br>
    <input type="text" name="test_password" placeholder="Masukkan password baru" required>
    <button type="submit">Update Password (Safe)</button>
</form>

<h2>Emergency Password Change (No Token)</h2>
<form method="POST" onsubmit="return confirm('Emergency mode - bypass semua validasi?');">
    <label>Emergency Password:</label><br>
    <input type="text" name="emergency_password" placeholder="Emergency password" required>
    <button type="submit" style="background:red; color:white;">Emergency Update</button>
</form>

<h2>Reset Session Token</h2>
<form method="POST">
    <button type="submit" name="reset_token" value="1">Generate New Token</button>
</form>

<?php
if (isset($_POST['reset_token'])) {
    $_SESSION['password_change_token'] = bin2hex(random_bytes(32));
    echo "<script>window.location.reload();</script>";
}
?>

<p><a href="/cornerbites-sia/auth/change_password.php">Kembali ke Change Password</a></p>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2, h3 { color: #333; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    form { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    input, button { padding: 8px; margin: 5px; }
    button { cursor: pointer; }
</style>
