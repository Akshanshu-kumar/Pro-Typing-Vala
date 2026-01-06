<?php
$pageTitle = "Typing Tutor";
require_once 'includes/header.php';
require_once 'config/db.php';

// Fetch Lessons
$language = $_GET['lang'] ?? 'english';
try {
    $stmt = $pdo->prepare("SELECT * FROM lessons WHERE language = ? ORDER BY level ASC");
    $stmt->execute([$language]);
    $lessons = $stmt->fetchAll();
} catch(PDOException $e) {
    $lessons = [];
}
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">Typing Tutor</h1>
        <div>
            <a href="?lang=english" class="btn <?php echo $language=='english'?'btn-primary':'btn-outline-primary'; ?>">English</a>
            <a href="?lang=hindi_krutidev" class="btn <?php echo $language=='hindi_krutidev'?'btn-primary':'btn-outline-primary'; ?>">Hindi (Kruti Dev)</a>
        </div>
    </div>

    <div class="row">
        <?php foreach($lessons as $lesson): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm hover-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Level <?php echo $lesson['level']; ?>: <?php echo htmlspecialchars($lesson['title']); ?></h5>
                    <p class="card-text text-muted small"><?php echo htmlspecialchars($lesson['instructions']); ?></p>
                    <div class="d-grid mt-3">
                        <a href="lesson.php?id=<?php echo $lesson['id']; ?>" class="btn btn-outline-success">
                            Start Lesson <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if(empty($lessons)): ?>
        <div class="col-12 text-center text-muted">
            <p>No lessons available for this language yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
