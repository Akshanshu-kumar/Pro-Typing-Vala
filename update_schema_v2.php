<?php
require_once 'config/db.php';

try {
    $sql = "ALTER TABLE typing_results 
            ADD COLUMN full_mistakes INT DEFAULT 0,
            ADD COLUMN half_mistakes INT DEFAULT 0,
            ADD COLUMN typed_content TEXT DEFAULT NULL,
            ADD COLUMN original_content TEXT DEFAULT NULL";
            
    $pdo->exec($sql);
    echo "Database schema updated successfully.";
} catch (PDOException $e) {
    echo "Error updating schema (might already exist): " . $e->getMessage();
}
?>