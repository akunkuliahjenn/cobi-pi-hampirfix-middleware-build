
<?php
require_once __DIR__ . '/config/db.php';

echo "<h1>🔥 FINAL TEST - Admin Reset Password Flow</h1>";

$username = 'testo';
$temp_password = 'finaltest123'; // Password temporary baru untuk test terakhir

echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h2>📋 Test Steps:</h2>";
echo "<ol>";
echo "<li>Reset password user 'testo' dengan password temporary: <strong>{$temp_password}</strong></li>";
echo "<li>Verify password tersimpan dengan benar</li>";
echo "<li>Test login simulation</li>";
echo "<li>Verify redirect ke change password</li>";
echo "</ol>";
echo "</div>";

try {
    // Step 1: Find user
    $stmt = $db->prepare("SELECT id, username FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p style='color:red;'>❌ User '{$username}' tidak ditemukan!</p>";
        exit;
    }

    echo "<p style='color:green;'>✓ User ditemukan: ID {$user['id']}, Username: {$user['username']}</p>";

    // Step 2: Reset password (sama seperti admin reset)
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    
    echo "<p><strong>Password temporary:</strong> {$temp_password}</p>";
    echo "<p><strong>Password hash:</strong> " . substr($hashed_password, 0, 30) . "...</p>";

    // Step 3: Update database
    $updateStmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
    $result = $updateStmt->execute([$hashed_password, $user['id']]);

    if ($result) {
        echo "<p style='color:green;'>✓ Password berhasil direset oleh admin!</p>";

        // Step 4: Verify update
        $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE id = ?");
        $verifyStmt->execute([$user['id']]);
        $updated_user = $verifyStmt->fetch();

        echo "<p><strong>Database updated:</strong></p>";
        echo "<p>- must_change_password: " . $updated_user['must_change_password'] . "</p>";
        echo "<p>- Password hash: " . substr($updated_user['password'], 0, 30) . "...</p>";

        // Step 5: Test password verification
        $verify_test = password_verify($temp_password, $updated_user['password']);
        echo "<p>- Password verification test: " . ($verify_test ? '<span style="color:green;">SUCCESS</span>' : '<span style="color:red;">FAILED</span>') . "</p>";

        if ($verify_test) {
            echo "<div style='background: #dcfce7; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #16a34a;'>";
            echo "<h3 style='color: #16a34a; margin: 0 0 10px 0;'>✅ ADMIN RESET BERHASIL!</h3>";
            echo "<p><strong>Username:</strong> {$username}</p>";
            echo "<p><strong>Password temporary:</strong> {$temp_password}</p>";
            echo "<p><strong>Status:</strong> Harus ganti password setelah login</p>";
            echo "</div>";

            // Step 6: Simulate login process
            echo "<h3>🧪 Test Login Process</h3>";
            echo "<p>Simulasi login dengan username: {$username} dan password: {$temp_password}</p>";

            // Simulate login verification
            $loginStmt = $db->prepare("SELECT id, username, password, must_change_password FROM users WHERE username = ?");
            $loginStmt->execute([$username]);
            $login_user = $loginStmt->fetch();

            if ($login_user && password_verify($temp_password, $login_user['password'])) {
                echo "<p style='color:green;'>✓ User ditemukan untuk login</p>";
                echo "<p style='color:green;'>✓ Password verification: SUCCESS</p>";
                
                if ($login_user['must_change_password'] == 1) {
                    echo "<div style='background: #dcfce7; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #16a34a;'>";
                    echo "<h3 style='color: #16a34a; margin: 0 0 10px 0;'>✅ LOGIN AKAN BERHASIL!</h3>";
                    echo "<p>User akan diarahkan ke halaman change password karena must_change_password = 1</p>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color:red;'>❌ Login simulation failed!</p>";
            }
        }
    } else {
        echo "<p style='color:red;'>❌ Gagal update password!</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107;'>";
echo "<h3>💡 Instruksi Test:</h3>";
echo "<ol>";
echo "<li>Jalankan file ini untuk reset password user 'testo'</li>";
echo "<li>Buka halaman login</li>";
echo "<li>Login dengan username: <strong>testo</strong> dan password: <strong>{$temp_password}</strong></li>";
echo "<li>Jika berhasil, user akan diarahkan ke halaman change password</li>";
echo "</ol>";
echo "</div>";
?>

<div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
    <h3>🎯 Test Manual Reset dari Admin</h3>
    <form method="POST" action="/cornerbites-sia/final_test_admin_reset.php">
        <p>
            <label><strong>Password Temporary:</strong></label><br>
            <input type="text" name="temp_password" value="<?php echo $temp_password; ?>" style="padding: 8px; width: 200px; border: 1px solid #ddd; border-radius: 4px;">
        </p>
        <p>
            <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                Reset Password User 'testo'
            </button>
        </p>
    </form>
</div>
