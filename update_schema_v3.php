<?php
$host = '127.0.0.1';
$db = 'typing_practice_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $sql = "ALTER TABLE typing_results 
            ADD COLUMN full_mistakes INT DEFAULT 0,
            ADD COLUMN half_mistakes INT DEFAULT 0,
            ADD COLUMN typed_content TEXT DEFAULT NULL,
            ADD COLUMN original_content TEXT DEFAULT NULL";
            
    $pdo->exec($sql);
    echo "Database schema updated successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>