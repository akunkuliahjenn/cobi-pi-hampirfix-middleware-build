
<?php
// fix_user_data.php
// Script untuk memperbaiki data yang sudah ada dengan menambahkan user_id yang benar

require_once __DIR__ . '/config/db.php';

echo "<h2>Memperbaiki Isolasi Data User...</h2>";

try {
    // Tables yang perlu diperbaiki
    $tables = [
        'products',
        'raw_materials', 
        'overhead_costs',
        'labor_costs',
        'transactions',
        'transaction_items',
        'product_recipes'
    ];

    foreach ($tables as $table) {
        // Check if table exists and has user_id column
        $check_table = $db->prepare("SHOW TABLES LIKE '$table'");
        $check_table->execute();
        
        if ($check_table->rowCount() > 0) {
            $check_column = $db->prepare("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
            $check_column->execute();
            
            if ($check_column->rowCount() > 0) {
                // Update records with NULL or 0 user_id to user_id = 1 (admin)
                $update_stmt = $db->prepare("UPDATE `$table` SET user_id = 1 WHERE user_id IS NULL OR user_id = 0");
                $update_stmt->execute();
                
                $affected = $update_stmt->rowCount();
                echo "<p>Table $table: $affected records updated</p>";
            } else {
                echo "<p>Table $table: user_id column not found</p>";
            }
        } else {
            echo "<p>Table $table: table not found</p>";
        }
    }
    
    echo "<h3>Selesai! Data isolation telah diperbaiki.</h3>";
    echo "<p><a href='/cornerbites-sia/auth/login.php'>Kembali ke Login</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
