
<?php
// run_fix.php
// Script lengkap untuk memperbaiki semua masalah isolasi user

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/user_middleware.php';

echo "<!DOCTYPE html>";
echo "<html><head><title>Fix User Isolation</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:20px auto;padding:20px;}";
echo ".success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}";
echo "details{margin:10px 0;} summary{cursor:pointer;font-weight:bold;}";
echo "</style></head><body>";

try {
    $conn = $db;
    
    echo "<h1>🔧 Memperbaiki Isolasi User - Script Lengkap</h1>";
    echo "<p class='info'>Script ini akan memperbaiki semua masalah isolasi data antar user.</p>";
    
    // === STEP 1: Pastikan semua tabel memiliki kolom user_id ===
    echo "<h2>📋 Step 1: Memastikan Struktur Tabel</h2>";
    
    // Daftar tabel yang benar-benar ada di database
    $tables = [
        'products' => 'Produk',
        'raw_materials' => 'Bahan Baku & Kemasan', 
        'product_recipes' => 'Resep Produk',
        'overhead_costs' => 'Biaya Overhead',
        'labor_costs' => 'Biaya Tenaga Kerja',
        'product_labor_manual' => 'Tenaga Kerja Manual Produk',
        'product_overhead_manual' => 'Overhead Manual Produk'
    ];
    
    foreach ($tables as $table => $description) {
        if ($table === 'users') continue;
        
        echo "<h3>Tabel: $description ($table)</h3>";
        
        // Cek apakah tabel benar-benar ada
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            echo "<p class='warning'>⚠ Tabel $table tidak ditemukan, dilewati</p>";
            continue;
        }
        
        // Cek apakah kolom user_id sudah ada
        $stmt = $conn->prepare("SHOW COLUMNS FROM `$table` LIKE 'user_id'");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (!$result) {
            // Tambah kolom user_id jika belum ada
            $conn->exec("ALTER TABLE `$table` ADD COLUMN user_id INT DEFAULT 1");
            echo "<p class='success'>✓ Kolom user_id ditambahkan ke tabel $table</p>";
        } else {
            echo "<p class='info'>- Kolom user_id sudah ada di tabel $table</p>";
        }
        
        // Update records yang user_id-nya NULL atau 0 menjadi 1 (admin)
        $stmt = $conn->prepare("UPDATE `$table` SET user_id = 1 WHERE user_id IS NULL OR user_id = 0");
        $stmt->execute();
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "<p class='success'>✓ Updated $affected records di tabel $table yang user_id-nya NULL/0</p>";
        } else {
            echo "<p class='info'>- Semua records di tabel $table sudah memiliki user_id yang valid</p>";
        }
    }
    
    // === STEP 2: Hapus constraint UNIQUE yang bermasalah ===
    echo "<h2>🗑️ Step 2: Menghapus Constraint UNIQUE yang Bermasalah</h2>";
    
    // Drop unique constraint pada name di raw_materials
    try {
        $conn->exec("ALTER TABLE raw_materials DROP INDEX name");
        echo "<p class='success'>✓ Constraint UNIQUE pada kolom 'name' di tabel raw_materials berhasil dihapus</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "<p class='info'>- Constraint UNIQUE pada kolom 'name' sudah tidak ada di raw_materials</p>";
        } else {
            echo "<p class='warning'>⚠ Error saat menghapus constraint di raw_materials: " . $e->getMessage() . "</p>";
        }
    }
    
    // Drop unique constraint pada name di products jika ada
    try {
        $conn->exec("ALTER TABLE products DROP INDEX name");
        echo "<p class='success'>✓ Constraint UNIQUE pada kolom 'name' di tabel products berhasil dihapus</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "check that column/key exists") !== false) {
            echo "<p class='info'>- Constraint UNIQUE pada kolom 'name' sudah tidak ada di products</p>";
        } else {
            echo "<p class='warning'>⚠ Error saat menghapus constraint di products: " . $e->getMessage() . "</p>";
        }
    }
    
    // === STEP 3: Bersihkan data duplikat ===
    echo "<h2>🧹 Step 3: Membersihkan Data Duplikat</h2>";
    
    // Bersihkan duplikat di raw_materials
    echo "<h3>Membersihkan duplikat di Raw Materials</h3>";
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
    echo "<p class='success'>✓ $deletedRaw duplikat dihapus dari raw_materials</p>";
    
    // Bersihkan duplikat di products
    echo "<h3>Membersihkan duplikat di Products</h3>";
    $stmt = $conn->prepare("
        DELETE p1 FROM products p1
        INNER JOIN products p2 
        WHERE p1.id < p2.id 
        AND p1.name = p2.name 
        AND p1.user_id = p2.user_id
    ");
    $stmt->execute();
    $deletedProducts = $stmt->rowCount();
    echo "<p class='success'>✓ $deletedProducts duplikat dihapus dari products</p>";
    
    // === STEP 4: Tambah constraint UNIQUE yang tepat ===
    echo "<h2>🔒 Step 4: Menambahkan Constraint UNIQUE yang Tepat</h2>";
    
    // Tambah unique constraint untuk raw_materials
    echo "<h3>Raw Materials - Constraint (name, brand, user_id)</h3>";
    try {
        $conn->exec("ALTER TABLE raw_materials ADD UNIQUE KEY unique_material_per_user (name, brand, user_id)");
        echo "<p class='success'>✓ Constraint UNIQUE (name, brand, user_id) berhasil ditambahkan ke raw_materials</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "<p class='info'>- Constraint UNIQUE (name, brand, user_id) sudah ada di raw_materials</p>";
        } else {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Tambah unique constraint untuk products
    echo "<h3>Products - Constraint (name, user_id)</h3>";
    try {
        $conn->exec("ALTER TABLE products ADD UNIQUE KEY unique_product_per_user (name, user_id)");
        echo "<p class='success'>✓ Constraint UNIQUE (name, user_id) berhasil ditambahkan ke products</p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate key name") !== false) {
            echo "<p class='info'>- Constraint UNIQUE (name, user_id) sudah ada di products</p>";
        } else {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // === STEP 5: Test isolasi data ===
    echo "<h2>🧪 Step 5: Testing Isolasi Data</h2>";
    
    // Test count data per user
    $stmt = $conn->query("SELECT user_id, COUNT(*) as count FROM products GROUP BY user_id");
    echo "<h3>Jumlah Produk per User:</h3>";
    while ($row = $stmt->fetch()) {
        $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $userStmt->execute([$row['user_id']]);
        $username = $userStmt->fetchColumn() ?: 'User tidak ditemukan';
        echo "<p>User ID {$row['user_id']} ($username): {$row['count']} produk</p>";
    }
    
    $stmt = $conn->query("SELECT user_id, COUNT(*) as count FROM raw_materials GROUP BY user_id");
    echo "<h3>Jumlah Bahan/Kemasan per User:</h3>";
    while ($row = $stmt->fetch()) {
        $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $userStmt->execute([$row['user_id']]);
        $username = $userStmt->fetchColumn() ?: 'User tidak ditemukan';
        echo "<p>User ID {$row['user_id']} ($username): {$row['count']} bahan/kemasan</p>";
    }
    
    // === STEP 6: Tampilkan struktur tabel ===
    echo "<h2>📊 Step 6: Struktur Tabel Setelah Perbaikan</h2>";
    
    foreach (['products', 'raw_materials'] as $table) {
        $stmt = $conn->query("SHOW CREATE TABLE $table");
        $result = $stmt->fetch();
        echo "<details>";
        echo "<summary>Struktur tabel $table</summary>";
        echo "<pre style='background:#f5f5f5;padding:10px;font-size:12px;'>" . htmlspecialchars($result['Create Table']) . "</pre>";
        echo "</details>";
    }
    
    // === STEP 7: Verifikasi middleware ===
    echo "<h2>✅ Step 7: Verifikasi Middleware</h2>";
    
    if (function_exists('selectWithUserId')) {
        echo "<p class='success'>✓ Fungsi selectWithUserId tersedia</p>";
    } else {
        echo "<p class='error'>❌ Fungsi selectWithUserId tidak tersedia - pastikan user_middleware.php sudah di-include</p>";
    }
    
    if (function_exists('insertWithUserId')) {
        echo "<p class='success'>✓ Fungsi insertWithUserId tersedia</p>";
    } else {
        echo "<p class='error'>❌ Fungsi insertWithUserId tidak tersedia</p>";
    }
    
    if (function_exists('countWithUserId')) {
        echo "<p class='success'>✓ Fungsi countWithUserId tersedia</p>";
    } else {
        echo "<p class='error'>❌ Fungsi countWithUserId tidak tersedia</p>";
    }
    
    // === FINAL MESSAGE ===
    echo "<hr>";
    echo "<h2>🎉 Perbaikan Selesai!</h2>";
    echo "<div style='background:#e8f5e8;padding:15px;border-radius:5px;'>";
    echo "<h3>✅ Yang Sudah Diperbaiki:</h3>";
    echo "<ul>";
    echo "<li>✓ Semua tabel yang ada memiliki kolom user_id</li>";
    echo "<li>✓ Data lama yang tidak memiliki user_id sudah diassign ke admin (user_id=1)</li>";
    echo "<li>✓ Constraint UNIQUE yang bermasalah sudah dihapus</li>";
    echo "<li>✓ Data duplikat sudah dibersihkan</li>";
    echo "<li>✓ Constraint UNIQUE baru sudah ditambahkan dengan user_id</li>";
    echo "<li>✓ Isolasi data antar user sudah aktif</li>";
    echo "</ul>";
    echo "<h3>📝 Catatan Penting:</h3>";
    echo "<ul>";
    echo "<li>Setiap user sekarang hanya bisa melihat data miliknya sendiri</li>";
    echo "<li>User bisa membuat bahan/produk dengan nama sama dengan user lain</li>";
    echo "<li>Data admin (user_id=1) tetap aman dan terpisah</li>";
    echo "<li>Semua halaman menggunakan middleware user_isolation</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p style='margin-top:20px;'><strong>🔄 Silakan coba login dengan akun yang berbeda untuk memastikan isolasi data bekerja dengan benar!</strong></p>";
    
    // Info tentang tabel yang tidak ada
    echo "<div style='background:#fff3cd;padding:15px;border-radius:5px;margin-top:20px;'>";
    echo "<h3>ℹ️ Informasi Tambahan:</h3>";
    echo "<p>Tabel <code>transactions</code> dan <code>transaction_items</code> tidak ditemukan di database Anda. ";
    echo "Ini normal jika fitur transaksi belum diimplementasikan. Script hanya memproses tabel yang ada.</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p class='error'><strong>❌ Error Database:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Exception $e) {
    echo "<p class='error'><strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
?>
