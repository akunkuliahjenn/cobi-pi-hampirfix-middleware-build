<?php
// debug_users.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Users</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background-color:#f2f2f2;}</style>";
echo "</head><body>";

echo "<h1>🔍 Debug Users Database</h1>";

// Debug session info
echo "<h2>Session Debug Info</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p><strong>Username:</strong> " . ($_SESSION['username'] ?? 'Not set') . "</p>";
echo "<p><strong>Role:</strong> " . ($_SESSION['role'] ?? 'Not set') . "</p>";
echo "<p><strong>User Role (old):</strong> " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";
echo "<hr>";

try {
    $conn = $db;

    // Check if users table exists (MySQL syntax)
    $tables = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tables->rowCount() > 0) {
        echo "<p style='color:green;'>✓ Table 'users' exists</p>";

        // Get all users
        $stmt = $conn->query("SELECT id, username, role, created_at, must_change_password FROM users ORDER BY id");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>All Users (" . count($users) . " total):</h3>";
        if (count($users) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Created At</th><th>Must Change Password</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                echo "<td>" . ($user['must_change_password'] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found in database</p>";
        }

        // Count by role
        $adminCount = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $userCount = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $totalCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

        echo "<h3>Statistics:</h3>";
        echo "<p><strong>Total Users:</strong> " . $totalCount . "</p>";
        echo "<p><strong>Admins:</strong> " . $adminCount . "</p>";
        echo "<p><strong>Regular Users:</strong> " . $userCount . "</p>";

    } else {
        echo "<p style='color:red;'>❌ Table 'users' does not exist</p>";

        // Show all tables (MySQL syntax)
        $tables = $conn->query("SHOW TABLES");
        echo "<h3>Available tables:</h3>";
        while ($table = $tables->fetch(PDO::FETCH_NUM)) {
            echo "<p>- " . $table[0] . "</p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>