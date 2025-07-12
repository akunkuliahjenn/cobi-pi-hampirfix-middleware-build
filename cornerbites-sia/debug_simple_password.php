
<?php
session_start();
require_once __DIR__ . '/config/db.php';

echo "<h1>Simple Password Change Test</h1>";

if (!isset($_SESSION['user_id'])) {
    echo "No user logged in!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    
    if (!empty($new_password)) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                echo "<div style='color: green; font-weight: bold; border: 2px solid green; padding: 10px; margin: 10px 0;'>";
                echo "✅ PASSWORD BERHASIL DIUBAH!<br>";
                echo "User ID: {$_SESSION['user_id']}<br>";
                echo "Password baru: {$new_password}<br>";
                echo "Hash: " . substr($hashed_password, 0, 20) . "...<br>";
                echo "</div>";
                
                // Clear all password change flags
                unset($_SESSION['must_change_password']);
                unset($_SESSION['force_password_change']);
                unset($_SESSION['password_change_token']);
                
                // Update database check
                $stmt = $db->prepare("SELECT must_change_password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch();
                echo "<p>Database must_change_password: " . $result['must_change_password'] . "</p>";
                
                echo "<p><strong>Sekarang coba login dengan:</strong></p>";
                echo "<ul>";
                echo "<li>Username: {$_SESSION['username']}</li>";
                echo "<li>Password: {$new_password}</li>";
                echo "</ul>";
                
                echo "<p><a href='/cornerbites-sia/auth/logout.php' style='background: blue; color: white; padding: 10px; text-decoration: none;'>LOGOUT DAN TEST LOGIN</a></p>";
                
            } else {
                echo "<div style='color: red; font-weight: bold;'>❌ Gagal update password</div>";
            }
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
}

// Show current user info
echo "<h2>Current User Info:</h2>";
$stmt = $db->prepare("SELECT id, username, must_change_password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
echo "<pre>";
print_r($user);
echo "</pre>";
?>

<h2>🔧 Simple Password Change (No Token Required)</h2>
<form method="POST" style="border: 2px solid #ddd; padding: 20px; margin: 20px 0;">
    <label><strong>Password Baru:</strong></label><br>
    <input type="text" name="new_password" placeholder="Masukkan password baru" required style="width: 300px; padding: 10px; margin: 10px 0;">
    <br>
    <button type="submit" style="background: green; color: white; padding: 10px 20px; border: none; cursor: pointer;">UBAH PASSWORD</button>
</form>

<p><a href="/cornerbites-sia/auth/change_password.php">🔙 Kembali ke Change Password</a></p>
