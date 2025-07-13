
<?php
session_start();
require_once __DIR__ . '/config/db.php';

echo "<h1>🔍 Debug Password Verification</h1>";

// Cek user mana yang sedang login
if (isset($_SESSION['user_id'])) {
    echo "<h2>Current User Info:</h2>";
    $stmt = $db->prepare("SELECT id, username, password, must_change_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<pre>";
        echo "User ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Password Hash: " . substr($user['password'], 0, 30) . "...\n";
        echo "Must Change Password: " . $user['must_change_password'] . "\n";
        echo "</pre>";
        
        // Test password verification
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_password'])) {
            $test_password = $_POST['test_password'];
            $is_valid = password_verify($test_password, $user['password']);
            
            echo "<div style='padding:10px; margin:10px; border:2px solid " . ($is_valid ? "green" : "red") . ";'>";
            echo "<h3>Test Password: '$test_password'</h3>";
            echo "<p><strong>Result: " . ($is_valid ? "✅ VALID" : "❌ INVALID") . "</strong></p>";
            echo "</div>";
        }
        
        echo "<h3>Test Password:</h3>";
        echo "<form method='POST'>";
        echo "<input type='text' name='test_password' placeholder='Masukkan password untuk test' required>";
        echo "<button type='submit'>Test Password</button>";
        echo "</form>";
        
    } else {
        echo "User tidak ditemukan di database!";
    }
} else {
    echo "Tidak ada user yang login!";
}

echo "<hr>";
echo "<h3>All Users (for debug):</h3>";
$stmt = $db->prepare("SELECT id, username, must_change_password FROM users ORDER BY id");
$stmt->execute();
$users = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Username</th><th>Must Change</th><th>Action</th></tr>";
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . $user['username'] . "</td>";
    echo "<td>" . ($user['must_change_password'] ? 'YES' : 'NO') . "</td>";
    echo "<td><a href='?simulate_login=" . $user['id'] . "'>Simulate Login</a></td>";
    echo "</tr>";
}
echo "</table>";

// Simulate login untuk testing
if (isset($_GET['simulate_login'])) {
    $user_id = $_GET['simulate_login'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        
        echo "<script>alert('Simulated login for: " . $user['username'] . "'); location.reload();</script>";
    }
}
?>
