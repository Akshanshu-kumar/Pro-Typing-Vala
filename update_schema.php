<?php
// update_schema.php
require_once 'config/db.php';

function execSafe(PDO $pdo, string $sql) {
    try { $pdo->exec($sql); } catch (PDOException $e) { /* ignore */ }
}

execSafe($pdo, "ALTER TABLE typing_results ADD COLUMN mistyped_words TEXT DEFAULT NULL");
execSafe($pdo, "ALTER TABLE typing_results ADD COLUMN tester_name VARCHAR(150) DEFAULT NULL");
execSafe($pdo, "ALTER TABLE paragraphs ADD COLUMN user_id INT DEFAULT NULL");
execSafe($pdo, "CREATE INDEX IF NOT EXISTS idx_paragraphs_user ON paragraphs(user_id)");
execSafe($pdo, "ALTER TABLE users ADD COLUMN full_name VARCHAR(150) DEFAULT NULL");
execSafe($pdo, "ALTER TABLE users ADD COLUMN is_premium TINYINT(1) DEFAULT 0");
echo "Schema update attempted.";
?>
