<?php
// setup.php

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server without selecting database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully.<br>";

    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    $advanced_sql = file_get_contents(__DIR__ . '/sql/advanced_schema.sql');

    if ($sql) {
        // Execute SQL commands
        $pdo->exec($sql);
        if($advanced_sql) {
            $pdo->exec($advanced_sql);
        }
        echo "Database and tables created successfully.<br>";
        echo "Default admin user created: admin / admin123<br>";
        echo "<a href='index.php'>Go to Home</a>";
    } else {
        echo "Error reading schema.sql file.";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
