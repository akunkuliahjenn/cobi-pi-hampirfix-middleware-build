
<?php
require_once __DIR__ . '/config/db.php';

echo "<h1>🔧 Debug Password Change Test</h1>";

$username = 'testo';

// Step 1: Check current status
echo "<h2>Step 1: Current Status</h2>";
try {
    $stmt = $db->prepare("SELECT id, username, password, must_change_password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>✓ User found: ID {$user['id']}, Username: {$user['username']}</p>";
        echo "<p>✓ Must change password: {$user['must_change_password']}</p>";
        echo "<p>✓ Password hash: " . substr($user['password'], 0, 30) . "...</p>";
    } else {
        echo "<p style='color:red;'>❌ User not found</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

// Step 2: Test password change simulation
echo "<h2>Step 2: Simulate Password Change</h2>";

if (isset($_POST['test_password_change'])) {
    $new_password = $_POST['new_password'];
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $db->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
        $success = $stmt->execute([$hashed_password, $user['id']]);
        
        if ($success) {
            // Commit transaction
            $db->commit();
            
            echo "<p style='color:green;'>✓ Password updated successfully!</p>";
            
            // Verify update
            $verifyStmt = $db->prepare("SELECT password, must_change_password FROM users WHERE id = ?");
            $verifyStmt->execute([$user['id']]);
            $updated_user = $verifyStmt->fetch();
            
            // Test verification
            $verify_test = password_verify($new_password, $updated_user['password']);
            
            echo "<p>✓ Password verification: " . ($verify_test ? '<span style="color:green;">SUCCESS</span>' : '<span style="color:red;">FAILED</span>') . "</p>";
            echo "<p>✓ Must change password: {$updated_user['must_change_password']}</p>";
            
            if ($verify_test && $updated_user['must_change_password'] == 0) {
                echo "<div style='background:#d4edda;padding:15px;border-radius:8px;margin:15px 0;'>";
                echo "<h3 style='color:green;'>✅ PASSWORD CHANGE SUCCESSFUL!</h3>";
                echo "<p>User can now login with password: <strong>{$new_password}</strong></p>";
                echo "</div>";
            }
        } else {
            $db->rollBack();
            echo "<p style='color:red;'>❌ Failed to update password</p>";
        }
        
    } catch (Exception $e) {
        $db->rollBack();
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST" style="background:#f8f9fa;padding:20px;border-radius:8px;margin:20px 0;">
    <h3>Test Password Change</h3>
    <p>
        <label><strong>New Password:</strong></label><br>
        <input type="text" name="new_password" value="newpass123" style="padding:8px;width:200px;border:1px solid #ddd;border-radius:4px;">
    </p>
    <p>
        <button type="submit" name="test_password_change" style="background:#007bff;color:white;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;">
            Test Password Change
        </button>
    </p>
</form>

<div style="background:#fff3cd;padding:15px;border-radius:8px;margin:15px 0;">
    <h3>💡 Instructions:</h3>
    <ol>
        <li>Click "Test Password Change" to simulate password change</li>
        <li>If successful, try logging in with the new password</li>
        <li>User should be able to access dashboard without being forced to change password</li>
    </ol>
</div>
