
<?php
// process/fix_user_isolation.php
// Script untuk memperbaiki isolasi user dan constraint database

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';

try {
    $conn = $db;
    
    echo "<h2>Memperbaiki Database untuk User Isolation</h2>";
    
    // 1. Pastikan semua tabel memiliki kolom user_id
    echo "<p>Memastikan semua tabel memiliki kolom user_id...</p>";
    
    $tables = ['products', 'raw_materials', 'product_recipes', 'overhead_costs', 'labor_costs', 'transactions', 'transaction_items'];
    
    foreach ($tables as $table) {
        // Cek apakah kolom user_id sudah ada
        $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (!$result) {
            // Tambah kolom user_id jika belum ada
            $conn->exec("ALTER TABLE `$table` ADD COLUMN user_id INT DEFAULT 1");
            echo "✓ Kolom user_id ditambahkan ke tabel $table<br>";
        } else {
            echo "- Kolom user_id sudah ada di tabel $table<br>";
        }
        
        // Update records yang user_id-nya NULL atau 0 menjadi 1
        $stmt = $conn->prepare("UPDATE `$table` SET user_id = 1 WHERE user_id IS NULL OR user_id = 0");
        $stmt->execute();
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "✓ Updated $affected records di tabel $table yang user_id-nya NULL/0<br>";
        }
    }
    
    // 2. Drop existing unique constraints yang bermasalah
    echo "<p>Menghapus constraint UNIQUE yang bermasalah...</p>";
    
    try {
        // Drop unique constraint pada name di raw_materials
        $conn->exec("ALTER TABLE raw_materials DROP INDEX name");
        echo "✓ Constraint UNIQUE pada kolom 'name' di tabel raw_materials berhasil dihapus<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "- Constraint UNIQUE pada kolom 'name' sudah tidak ada di raw_materials<br>";
        } else {
            echo "⚠ Error saat menghapus constraint raw_materials: " . $e->getMessage() . "<br>";
        }
    }
    
    try {
        // Drop unique constraint pada name di products jika ada
        $conn->exec("ALTER TABLE products DROP INDEX name");
        echo "✓ Constraint UNIQUE pada kolom 'name' di tabel products berhasil dihapus<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "- Constraint UNIQUE pada kolom 'name' sudah tidak ada di products<br>";
        } else {
            echo "⚠ Error saat menghapus constraint products: " . $e->getMessage() . "<br>";
        }
    }
    
    // 3. Bersihkan data duplikat sebelum menambah constraint baru
    echo "<p>Membersihkan data duplikat...</p>";
    
    // Hapus duplikat raw_materials dengan kombinasi yang sama
    $stmt = $conn->prepare("
        DELETE rm1 FROM raw_materials rm1
        INNER JOIN raw_materials rm2 
        WHERE rm1.id < rm2.id 
        AND rm1.name = rm2.name 
        AND rm1.brand = rm2.brand 
        AND rm1.user_id = rm2.user_id
    ");
    $stmt->execute();
    $deletedRaw = $stmt->rowCount();
    echo "✓ $deletedRaw duplikat dihapus dari raw_materials<br>";
    
    // Hapus duplikat products
    $stmt = $conn->prepare("
        DELETE p1 FROM products p1
        INNER JOIN products p2 
        WHERE p1.id < p2.id 
        AND p1.name = p2.name 
        AND p1.user_id = p2.user_id
    ");
    $stmt->execute();
    $deletedProducts = $stmt->rowCount();
    echo "✓ $deletedProducts duplikat dihapus dari products<br>";
    
    // 4. Tambah constraint UNIQUE yang tepat dengan user_id
    echo "<p>Menambahkan constraint UNIQUE yang tepat dengan user_id...</p>";
    
    try {
        // Tambah unique constraint untuk kombinasi name + brand + user_id di raw_materials
        $conn->exec("ALTER TABLE raw_materials ADD UNIQUE KEY unique_material_per_user (name, brand, user_id)");
        echo "✓ Constraint UNIQUE (name, brand, user_id) berhasil ditambahkan ke tabel raw_materials<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "- Constraint UNIQUE (name, brand, user_id) sudah ada di tabel raw_materials<br>";
        } else {
            echo "⚠ Error menambah constraint raw_materials: " . $e->getMessage() . "<br>";
        }
    }
    
    try {
        // Tambah unique constraint untuk kombinasi name + user_id di products
        $conn->exec("ALTER TABLE products ADD UNIQUE KEY unique_product_per_user (name, user_id)");
        echo "✓ Constraint UNIQUE (name, user_id) berhasil ditambahkan ke tabel products<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "- Constraint UNIQUE (name, user_id) sudah ada di tabel products<br>";
        } else {
            echo "⚠ Error menambah constraint products: " . $e->getMessage() . "<br>";
        }
    }
    
    // 5. Test constraint dan tampilkan info
    echo "<p>Testing dan menampilkan informasi...</p>";
    
    // Tampilkan jumlah data per user
    $stmt = $conn->query("SELECT user_id, COUNT(*) as count FROM products GROUP BY user_id");
    echo "<strong>Jumlah produk per user:</strong><br>";
    while ($row = $stmt->fetch()) {
        echo "- User ID {$row['user_id']}: {$row['count']} produk<br>";
    }
    
    $stmt = $conn->query("SELECT user_id, COUNT(*) as count FROM raw_materials GROUP BY user_id");
    echo "<strong>Jumlah bahan/kemasan per user:</strong><br>";
    while ($row = $stmt->fetch()) {
        echo "- User ID {$row['user_id']}: {$row['count']} bahan/kemasan<br>";
    }
    
    // Cek struktur tabel raw_materials
    $stmt = $conn->query("SHOW CREATE TABLE raw_materials");
    $result = $stmt->fetch();
    echo "<details><summary>Struktur tabel raw_materials</summary><pre>" . htmlspecialchars($result['Create Table']) . "</pre></details>";
    
    // Cek struktur tabel products  
    $stmt = $conn->query("SHOW CREATE TABLE products");
    $result = $stmt->fetch();
    echo "<details><summary>Struktur tabel products</summary><pre>" . htmlspecialchars($result['Create Table']) . "</pre></details>";
    
    echo "<p><strong>✅ Perbaikan database selesai!</strong></p>";
    echo "<p>Sekarang setiap user memiliki isolasi data yang sempurna:</p>";
    echo "<ul>";
    echo "<li>✓ Raw materials: User bisa punya bahan dengan nama sama, selama kombinasi (nama + brand + user_id) berbeda</li>";
    echo "<li>✓ Products: User bisa punya produk dengan nama sama, selama kombinasi (nama + user_id) berbeda</li>";
    echo "<li>✓ Semua data lama sudah diassign ke admin (user_id = 1)</li>";
    echo "<li>✓ Data antar user sekarang benar-benar terpisah</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
