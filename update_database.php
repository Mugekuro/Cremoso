<?php
// Simple database update script
// Run this file once in your browser: http://localhost/your-project/update_database.php

require_once __DIR__ . '/config/database.php';

echo "<h2>Database Update Script</h2>";
echo "<p>Updating items table to set default values for variant and description...</p>";

try {
    // Step 1: Update existing NULL values to 'N/A'
    echo "<p><strong>Step 1:</strong> Updating existing NULL values...</p>";
    
    $stmt1 = $pdo->exec("UPDATE items SET variant = 'N/A' WHERE variant IS NULL OR variant = ''");
    echo "<p style='color: green;'>✓ Updated {$stmt1} rows in variant column</p>";
    
    $stmt2 = $pdo->exec("UPDATE items SET description = 'N/A' WHERE description IS NULL OR description = ''");
    echo "<p style='color: green;'>✓ Updated {$stmt2} rows in description column</p>";
    
    // Step 2: Modify columns to have default values
    echo "<p><strong>Step 2:</strong> Setting default values for columns...</p>";
    
    $pdo->exec("ALTER TABLE items MODIFY COLUMN variant VARCHAR(100) DEFAULT 'N/A' COMMENT 'Flavor or variant name'");
    echo "<p style='color: green;'>✓ Variant column now defaults to 'N/A'</p>";
    
    $pdo->exec("ALTER TABLE items MODIFY COLUMN description TEXT DEFAULT 'N/A'");
    echo "<p style='color: green;'>✓ Description column now defaults to 'N/A'</p>";
    
    echo "<h3 style='color: green;'>✓ Database updated successfully!</h3>";
    echo "<p>You can now delete this file (update_database.php) for security.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}
?>
