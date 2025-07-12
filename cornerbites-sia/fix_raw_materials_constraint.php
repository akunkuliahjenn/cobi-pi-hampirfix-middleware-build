
<?php
// fix_raw_materials_constraint.php
// Memperbaiki constraint di tabel raw_materials

require_once __DIR__ . '/config/db.php';

echo "<h2>Memperbaiki Constraint Raw Materials...</h2>";

try {
    // Drop constraint lama yang bermasalah
    try {
        $db->exec("ALTER TABLE raw_materials DROP INDEX name");
        echo "✓ Constraint lama 'name' berhasil dihapus<br>";
    } catch (PDOException $e) {
        echo "- Constraint 'name' sudah tidak ada atau sudah dihapus<br>";
    }
    
    // Hapus duplikat data jika ada
    $stmt = $db->prepare("
        DELETE rm1 FROM raw_materials rm1
        INNER JOIN raw_materials rm2 
        WHERE rm1.id < rm2.id 
        AND rm1.name = rm2.name 
        AND rm1.brand = rm2.brand 
        AND rm1.user_id = rm2.user_id
    ");
    $stmt->execute();
    $deleted = $stmt->rowCount();
    echo "✓ $deleted data duplikat dibersihkan<br>";
    
    // Tambah constraint baru yang benar
    try {
        $db->exec("ALTER TABLE raw_materials ADD UNIQUE KEY unique_material_per_user (name, brand, user_id)");
        echo "✓ Constraint baru (name, brand, user_id) berhasil ditambahkan<br>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "- Constraint baru sudah ada<br>";
        } else {
            echo "⚠ Error: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<p><strong>✅ Perbaikan raw_materials selesai!</strong></p>";
    echo "<p>Sekarang setiap user bisa memiliki bahan baku dengan nama yang sama.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
