<?php
require_once 'config/db.php';

echo "<h2>Updating Schema v4...</h2>";

try {
    // 1. Create user_paragraphs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_paragraphs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT DEFAULT NULL,
      language VARCHAR(50) NOT NULL,
      title VARCHAR(200) NOT NULL,
      content TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "Table 'user_paragraphs' created/checked.<br>";

    // 2. Create default_paragraphs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS default_paragraphs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      language VARCHAR(50) NOT NULL,
      title VARCHAR(200) NOT NULL,
      content TEXT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'default_paragraphs' created/checked.<br>";

    // 3. Migrate Default Paragraphs
    $stmt = $pdo->query("SELECT COUNT(*) FROM default_paragraphs");
    if ($stmt->fetchColumn() == 0) {
        echo "Migrating default paragraphs...<br>";
        $pdo->exec("INSERT INTO default_paragraphs (language, title, content, created_at) 
                    SELECT language, title, content, created_at FROM paragraphs WHERE difficulty = 'default'");
        echo "Default paragraphs migrated.<br>";
    } else {
        echo "Default paragraphs already exist. Skipping migration.<br>";
    }

    // 4. Migrate User/Custom Paragraphs
    $stmt = $pdo->query("SELECT COUNT(*) FROM user_paragraphs");
    if ($stmt->fetchColumn() == 0) {
        echo "Migrating user paragraphs...<br>";
        $pdo->exec("INSERT INTO user_paragraphs (user_id, language, title, content, created_at) 
                    SELECT user_id, language, title, content, created_at FROM paragraphs WHERE difficulty IN ('custom', 'medium')");
        echo "User paragraphs migrated.<br>";
    } else {
        echo "User paragraphs already exist. Skipping migration.<br>";
    }

    echo "<h3>Schema Update Complete!</h3>";
    echo "<a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
