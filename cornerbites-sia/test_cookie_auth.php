
<?php
// test_cookie_auth.php
// File untuk testing JWT cookies

require_once __DIR__ . '/includes/cookie_auth_check.php';

echo "<h1>Test JWT Cookies</h1>";

echo "<h2>Cookies yang tersimpan:</h2>";
echo "<pre>";
if (!empty($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        if ($name === 'auth_token') {
            echo "auth_token: " . substr($value, 0, 50) . "... (truncated for security)<br>";
        } else {
            echo "$name: $value<br>";
        }
    }
} else {
    echo "Tidak ada cookies yang tersimpan";
}
echo "</pre>";

echo "<h2>Validasi JWT:</h2>";
$userData = getUserDataFromCookie();

if ($userData) {
    echo "<div style='color: green;'>✅ JWT Cookie Valid!</div>";
    echo "<strong>User Data (from JWT decode):</strong><br>";
    echo "User ID: " . $userData['user_id'] . "<br>";
    echo "Username: " . $userData['username'] . "<br>";
    echo "Role: " . $userData['role'] . "<br>";
} else {
    echo "<div style='color: red;'>❌ JWT Cookie Tidak Valid atau Tidak Ada</div>";
}

echo "<h2>Status Token:</h2>";
if (isTokenValid()) {
    echo "<div style='color: green;'>✅ Token masih valid</div>";
} else {
    echo "<div style='color: red;'>❌ Token tidak valid atau expired</div>";
}

echo "<h2>Debug Info:</h2>";
echo "Cookie auth_token exists: " . (isset($_COOKIE['auth_token']) ? 'YES' : 'NO') . "<br>";
if (isset($_COOKIE['auth_token'])) {
    echo "Token length: " . strlen($_COOKIE['auth_token']) . " characters<br>";
}
?>
