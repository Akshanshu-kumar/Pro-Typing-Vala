<?php
// api/save_result.php
require_once '../config/config.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $wpm = $data['wpm'];
    $cpm = $data['cpm'];
    $accuracy = $data['accuracy'];
    $mistakes = $data['mistakes'];
    $time_taken = $data['time_taken'];
    $mistyped_words = isset($data['mistyped_words']) ? json_encode($data['mistyped_words']) : null;
    $gross_wpm = isset($data['gross_wpm']) ? (int)$data['gross_wpm'] : null;
    $error_rate_wpm = isset($data['error_rate_wpm']) ? (int)$data['error_rate_wpm'] : null;
    $half_mistakes = isset($data['half_mistakes']) ? (int)$data['half_mistakes'] : 0;
    $typed_content = isset($data['typed_content']) ? $data['typed_content'] : null;
    $original_content = isset($data['original_content']) ? $data['original_content'] : null;
    $language = isset($data['language']) ? $data['language'] : 'english';
    $user_id = $_SESSION['user_id'];
    // Override tester_name with user's full name (or username)
    $tester_name = null;
    try {
        $stmtUser = $pdo->prepare("SELECT COALESCE(full_name, username) AS display_name FROM users WHERE id = ?");
        $stmtUser->execute([$user_id]);
        $u = $stmtUser->fetch();
        if ($u) { $tester_name = $u['display_name']; }
    } catch (PDOException $e) { $tester_name = null; }

    try {
        $stmt = $pdo->prepare("INSERT INTO typing_results (user_id, wpm, cpm, accuracy, mistakes, full_mistakes, half_mistakes, typed_content, time_taken, language, tester_name, mistyped_words) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $wpm, $cpm, $accuracy, $mistakes, $full_mistakes, $half_mistakes, $typed_content, $time_taken, $language, $tester_name, $mistyped_words])) {
            $resultId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'result_id' => $resultId]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save result']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
