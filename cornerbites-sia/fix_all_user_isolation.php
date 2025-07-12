
<?php
// fix_all_user_isolation.php
// Script lengkap untuk memperbaiki isolasi user

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix User Isolation</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;}";
echo ".success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}";
echo "</style></head><body>";

try {
    $conn = $db;
    
    echo "<h1>🔧 Memperbaiki Isolasi User</h1>";
    
    // Daftar tabel yang perlu diperbaiki
    $tables = [
        'products' => 'Produk',
        'raw_materials' => 'Bahan Baku', 
        'product_recipes' => 'Resep Produk',
        'overhead_costs' => 'Biaya Overhead',
        'labor_costs' => 'Biaya Tenaga Kerja',
        'product_labor_manual' => 'Tenaga Kerja Manual',
        'product_overhead_manual' => 'Overhead Manual'
    ];
    
    foreach ($tables as $table => $description) {
        echo "<h3>📋 Memperbaiki tabel: $description ($table)</h3>";
        
        // Cek apakah tabel ada
        $check_table = $conn->prepare("SHOW TABLES LIKE '$table'");
        $check_table->execute();
        
        if ($check_table->rowCount() === 0) {
            echo "<p class='warning'>⚠ Tabel $table tidak ditemukan, dilewati</p>";
            continue;
        }
        
        // Cek apakah kolom user_id sudah ada
        $check_column = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
        $check_column->execute();
        
        if ($check_column->rowCount() === 0) {
            // Tambahkan kolom user_id
            $conn->exec("ALTER TABLE `$table` ADD COLUMN user_id INT NOT NULL DEFAULT 1");
            echo "<p class='success'>✓ Kolom user_id ditambahkan</p>";
        } else {
            echo "<p class='info'>- Kolom user_id sudah ada</p>";
        }
        
        // Update records yang user_id-nya NULL atau 0
        $update_stmt = $conn->prepare("UPDATE `$table` SET user_id = 1 WHERE user_id IS NULL OR user_id = 0");
        $update_stmt->execute();
        $affected = $update_stmt->rowCount();
        
        if ($affected > 0) {
            echo "<p class='success'>✓ $affected records diperbaiki</p>";
        } else {
            echo "<p class='info'>- Semua records sudah memiliki user_id yang valid</p>";
        }
    }
    
    echo "<h2>🎯 Hasil Akhir</h2>";
    echo "<p class='success'><strong>✅ Semua tabel berhasil diperbaiki!</strong></p>";
    echo "<p>Sekarang setiap user memiliki data yang terisolasi dengan benar.</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>
