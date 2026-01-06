<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') { die('Access denied'); }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $language = isset($_POST['language']) ? trim($_POST['language']) : 'english';
    if ($title !== '' && $content !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO default_paragraphs (language, title, content) VALUES (:language, :title, :content)");
            $stmt->execute([':language' => $language, ':title' => $title, ':content' => $content]);
            $msg = 'Paragraph added';
        } catch (PDOException $e) { $msg = 'Error adding paragraph'; }
    } else { $msg = 'Title and content required'; }
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $del = $pdo->prepare("DELETE FROM default_paragraphs WHERE id = ?");
        $del->execute([$id]);
        $msg = 'Paragraph deleted';
    } catch (PDOException $e) { $msg = 'Error deleting paragraph'; }
}
$list = [];
try {
    $list = $pdo->query("SELECT id, title, language, created_at FROM default_paragraphs ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) { $list = []; }
?>
<div class="container my-4">
    <div class="d-flex align-items-center mb-3">
        <a href="../index.php" class="btn btn-link text-decoration-none"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="row">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">Add Paragraph</div>
                <div class="card-body">
                    <?php if ($msg): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select">
                                <option value="english">English</option>
                                <option value="hindi_krutidev">Hindi (Kruti Dev 010)</option>
                                <option value="hindi_inscript">Hindi (Unicode Mangal / Inscript)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Paragraph</label>
                            <textarea name="content" class="form-control" rows="8" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow border-0">
                <div class="card-header bg-light">Paragraphs</div>
                <div class="card-body">
                    <?php if (!empty($list)): ?>
                        <div class="list-group">
                            <?php foreach ($list as $item): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($item['language']); ?></span>
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="?delete=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">No paragraphs found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
