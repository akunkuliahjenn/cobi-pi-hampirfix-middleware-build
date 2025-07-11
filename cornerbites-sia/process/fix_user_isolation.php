<?php
// process/fix_user_isolation.php
// Script untuk memperbaiki isolasi data user yang sudah ada

session_start();
require_once __DIR__ . '/../config/db.php';

echo "<h2>Memperbaiki Isolasi Data User...</h2>";

try {
    $conn = $db;
    
    // Daftar user yang ada
    $users_stmt = $conn->query("SELECT id, username, role FROM users ORDER BY id");
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>User yang ditemukan:</h3>";
    foreach ($users as $user) {
        echo "<p>ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}</p>";
    }
    
    echo "<hr>";
    
    // Tables yang perlu diperbaiki
    $tables = [
        'products',
        'raw_materials', 
        'overhead_costs',
        'labor_costs',
        'product_recipes',
        'product_labor_manual',
        'product_overhead_manual'
    ];

    echo "<h3>Status Data Per Tabel:</h3>";
    
    foreach ($tables as $table) {
        // Check if table exists
        $check_table = $conn->prepare("SHOW TABLES LIKE '$table'");
        $check_table->execute();
        
        if ($check_table->rowCount() > 0) {
            // Check if user_id column exists
            $check_column = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
            $check_column->execute();
            
            if ($check_column->rowCount() > 0) {
                // Count records per user
                $count_stmt = $conn->prepare("SELECT user_id, COUNT(*) as count FROM `$table` GROUP BY user_id ORDER BY user_id");
                $count_stmt->execute();
                $counts = $count_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "<h4>Tabel: $table</h4>";
                foreach ($counts as $count) {
                    $user_info = array_filter($users, function($u) use ($count) {
                        return $u['id'] == $count['user_id'];
                    });
                    $user_info = reset($user_info);
                    $username = $user_info ? $user_info['username'] : 'Unknown';
                    echo "<p>User ID {$count['user_id']} ({$username}): {$count['count']} records</p>";
                }
                
                // Check for records with user_id = 1 that should be distributed
                $admin_records = $conn->prepare("SELECT COUNT(*) FROM `$table` WHERE user_id = 1");
                $admin_records->execute();
                $admin_count = $admin_records->fetchColumn();
                
                if ($admin_count > 0) {
                    echo "<p style='color: orange;'>⚠️ {$admin_count} records dengan user_id = 1 (mungkin perlu didistribusi)</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Tabel $table: kolom user_id tidak ditemukan</p>";
            }
        } else {
            echo "<p style='color: gray;'>⚪ Tabel $table: tidak ditemukan</p>";
        }
        echo "<br>";
    }
    
    echo "<hr>";
    echo "<h3>Rekomendasi Perbaikan:</h3>";
    echo "<p>1. Semua data saat ini memiliki user_id = 1 (admin)</p>";
    echo "<p>2. Untuk testing, buat data baru dengan user yang berbeda</p>";
    echo "<p>3. Atau gunakan form di bawah untuk memindahkan data ke user tertentu</p>";
    
    // Form untuk memindahkan data
    echo "<hr>";
    echo "<h3>Pindahkan Data ke User Lain (Opsional):</h3>";
    echo "<form method='POST' style='background: #f0f0f0; padding: 20px; border-radius: 5px;'>";
    echo "<p><strong>⚠️ HATI-HATI: Ini akan memindahkan SEMUA data dari user sumber ke user tujuan!</strong></p>";
    echo "<label>Dari User ID: <select name='from_user_id'>";
    foreach ($users as $user) {
        echo "<option value='{$user['id']}'>{$user['id']} - {$user['username']} ({$user['role']})</option>";
    }
    echo "</select></label><br><br>";
    
    echo "<label>Ke User ID: <select name='to_user_id'>";
    foreach ($users as $user) {
        echo "<option value='{$user['id']}'>{$user['id']} - {$user['username']} ({$user['role']})</option>";
    }
    echo "</select></label><br><br>";
    
    echo "<input type='submit' name='move_data' value='Pindahkan Data' style='background: #ff6b6b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
    echo "</form>";
    
    // Process form submission
    if (isset($_POST['move_data'])) {
        $from_user_id = (int)$_POST['from_user_id'];
        $to_user_id = (int)$_POST['to_user_id'];
        
        if ($from_user_id != $to_user_id) {
            echo "<h3>Memindahkan data dari User ID $from_user_id ke User ID $to_user_id...</h3>";
            
            foreach ($tables as $table) {
                try {
                    $update_stmt = $conn->prepare("UPDATE `$table` SET user_id = ? WHERE user_id = ?");
                    $update_stmt->execute([$to_user_id, $from_user_id]);
                    $affected = $update_stmt->rowCount();
                    echo "<p>✅ Tabel $table: $affected records dipindahkan</p>";
                } catch (PDOException $e) {
                    echo "<p style='color: red;'>❌ Error pada tabel $table: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<p style='color: green; font-weight: bold;'>✅ Selesai! Refresh halaman untuk melihat hasil terbaru.</p>";
        } else {
            echo "<p style='color: red;'>❌ User sumber dan tujuan tidak boleh sama!</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Testing Isolasi:</h3>";
    echo "<p>1. Login sebagai user yang berbeda</p>";
    echo "<p>2. Buat data baru (produk, bahan baku, dll)</p>";
    echo "<p>3. Logout dan login sebagai user lain</p>";
    echo "<p>4. Pastikan data tidak terlihat oleh user lain</p>";
    
    echo "<p><a href='/cornerbites-sia/auth/login.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>