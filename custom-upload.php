<?php
$pageTitle = "Upload Custom Paragraph";
require_once 'includes/header.php';
require_once 'config/db.php';

$message = "";
$justUploaded = null;

// Guest Cookie Handling
$guestIds = isset($_COOKIE['guest_uploads']) ? json_decode($_COOKIE['guest_uploads'], true) : [];
if (!is_array($guestIds)) $guestIds = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $uploadLang = isset($_POST['upload_language']) ? trim($_POST['upload_language']) : 'hindi_krutidev';
    $allowedLangs = ['english','hindi_krutidev','hindi_inscript'];
    if (!in_array($uploadLang, $allowedLangs, true)) { $uploadLang = 'hindi_krutidev'; }
    if (strlen($content) > 5000) {
        $content = substr($content, 0, 5000);
    }
    if ($title === '') {
        $title = "Custom Paragraph";
    }
    if ($content === '' || strlen($content) < 150) {
        $message = "⚠ Minimum 150 characters required in the textbox!";
    } else {
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // Check Limit (30 paragraphs)
        $currentCount = 0;
        if ($userId) {
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM user_paragraphs WHERE user_id = ?");
            $stmtCount->execute([$userId]);
            $currentCount = $stmtCount->fetchColumn();
        } else {
            $currentCount = count($guestIds);
        }

        if ($currentCount >= 30) {
            $message = "Limit reached! You can only upload up to 30 paragraphs. Please delete some old ones.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO user_paragraphs (language, title, content, user_id) VALUES (:language, :title, :content, :user_id)");
                $stmt->execute([
                    ':language' => $uploadLang,
                    ':title' => $title,
                    ':content' => $content,
                    ':user_id' => $userId
                ]);
            } catch (PDOException $e) {
                // If user_id is null/invalid constraint, try without user_id (though user_id is nullable)
                if ($userId !== -1) {
                    $stmt = $pdo->prepare("INSERT INTO user_paragraphs (language, title, content) VALUES (:language, :title, :content)");
                    $stmt->execute([
                        ':language' => $uploadLang,
                        ':title' => $title,
                        ':content' => $content
                    ]);
                }
            }
        }
    }
        
    if (empty($message) || $message === "Uploaded successfully.") {
        $newId = $pdo->lastInsertId();
        if ($newId) {
            $justUploaded = ['id' => (int)$newId, 'title' => $title, 'content' => $content, 'language' => $uploadLang];
            // If guest, save to cookie
            if (!$userId) {
                $guestIds[] = (int)$newId;
                $guestIds = array_unique($guestIds);
                setcookie('guest_uploads', json_encode(array_values($guestIds)), time() + 365*24*60*60, '/');
            }
            $message = "Uploaded successfully.";
        
            $_SESSION['test_settings'] = [
                'tester_name' => $_SESSION['test_settings']['tester_name'] ?? '',
                'language' => $uploadLang,
                'time' => $_SESSION['test_settings']['time'] ?? 60,
                'default_paragraph' => false,
                'paragraph_type' => 'database',
                'paragraph_source' => 'custom',
                'custom_text' => $content,
                'custom_title' => $title,
                'custom_id' => $newId,
                'word_limit_enabled' => $_SESSION['test_settings']['word_limit_enabled'] ?? false,
                'word_limit' => $_SESSION['test_settings']['word_limit'] ?? 35,
                'backspace' => $_SESSION['test_settings']['backspace'] ?? 'enable',
                'highlight' => $_SESSION['test_settings']['highlight'] ?? 'enable',
                'mode' => 'practice'
            ];
        }
    }
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    
    // Check if guest owns this
    $guestIds = isset($_COOKIE['guest_uploads']) ? json_decode($_COOKIE['guest_uploads'], true) : [];
    if (!is_array($guestIds)) $guestIds = [];

    try {
        $canDelete = false;
        if ($userId) {
            $check = $pdo->prepare("SELECT user_id FROM user_paragraphs WHERE id = ?");
            $check->execute([$id]);
            $row = $check->fetch();
            if ($row && (int)$row['user_id'] === $userId) {
                $canDelete = true;
            }
        } else {
            if (in_array($id, $guestIds)) {
                $canDelete = true;
            }
        }

        if ($canDelete) {
            $del = $pdo->prepare("DELETE FROM user_paragraphs WHERE id = ?");
            $del->execute([$id]);
            $message = "Paragraph deleted.";
            
            // Update cookie if guest
            if (!$userId) {
                $guestIds = array_diff($guestIds, [$id]);
                setcookie('guest_uploads', json_encode(array_values($guestIds)), time() + 365*24*60*60, '/');
            }
        } else {
            $message = "You cannot delete this paragraph.";
        }
    } catch (PDOException $e) {
        $message = "Error deleting paragraph.";
    }
}

$list = [];
try {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT id, title, content, language, user_id FROM user_paragraphs WHERE user_id = ? ORDER BY id DESC LIMIT 30");
        $stmt->execute([ (int)$_SESSION['user_id'] ]);
        $list = $stmt->fetchAll();
    } else {
        $guestIds = isset($_COOKIE['guest_uploads']) ? json_decode($_COOKIE['guest_uploads'], true) : [];
        if (!empty($guestIds) && is_array($guestIds)) {
            $inQuery = implode(',', array_map('intval', $guestIds));
            if (!empty($inQuery)) {
                $stmt = $pdo->query("SELECT id, title, content, language, user_id FROM user_paragraphs WHERE id IN ($inQuery) ORDER BY id DESC");
                $list = $stmt->fetchAll();
            }
        }
    }
} catch (PDOException $e) {
    $list = [];
}
?>
<div class="container my-4">
    <div class="row">
        <!-- Left Ad -->
        <div class="col-md-2 d-none d-md-block">
            <div class="card shadow-sm h-100">
                <img src="https://via.placeholder.com/160x600?text=Ad" class="card-img-top" alt="Ad">
                <div class="card-body text-center">
                    <div class="small text-muted">Sponsored</div>
                    <a href="#" class="btn btn-sm btn-outline-primary mt-2">Learn More</a>
                </div>
            </div>
        </div>
        <!-- Center Content -->
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-3">
                <a href="test-settings.php" class="btn btn-link text-decoration-none"><i class="bi bi-arrow-left"></i> Go Back</a>
            </div>
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-danger text-white">
                    Type Your Custom Paragraph {KrutiDev Font} (Max 5000 Chars)
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Language</label>
                            <select name="upload_language" class="form-select" id="uploadLanguageSelect">
                                <option value="english">English</option>
                                <option value="hindi_krutidev" selected>Hindi (Kruti Dev 010)</option>
                                <option value="hindi_inscript">Hindi (Unicode Mangal / Inscript)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Heading</label>
                            <input type="text" name="title" class="form-control" placeholder="Enter heading" id="titleInput">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Paragraph</label>
                            <textarea name="content" class="form-control font-krutidev" rows="8" maxlength="5000" placeholder=";agk —frnso esa Vkbi djsa ---  " id="contentText"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Characters: <span id="charCount">0</span></small>
                                <small id="minCharWarning" class="text-danger d-none">⚠ Minimum 150 characters required in the textbox!</small>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-danger" id="uploadBtn">Upload</button>
                    </form>
                </div>
            </div>
            
            <!-- Paragraph List -->
            <div class="card shadow border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span>Paragraph List [<?php echo count($list); ?>/30]</span>
                    <small class="text-muted">Latest First</small>
                </div>
                <div class="card-body">
                   <!-- Paragraphs List -->
                    <?php if (!empty($list)): ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($list as $index => $item): ?>
                                <div class="card border-danger shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
                                        <div class="fw-bold text-danger">
                                            <?php if ($index === 0): ?>
                                                <span class="badge bg-danger me-2">Latest</span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="custom-upload.php?delete=<?php echo (int)$item['id']; ?>" class="btn btn-sm btn-outline-danger delete-link"><i class="bi bi-x-lg"></i></a>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($item['language'] === 'english' ? 'English' : ($item['language'] === 'hindi_krutidev' ? 'KrutiDev' : 'Mangal')); ?>
                                            </span>
                                        </div>
                                        <div class="<?php echo ($item['language']==='hindi_krutidev'?'font-krutidev':($item['language']==='hindi_inscript'?'font-mangal':'')); ?>" 
                                             style="font-size: 0.9rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; background: #fff5f5; padding: 10px; border-radius: 5px;">
                                            <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text display-4"></i>
                            <p class="mt-2">No Paragraph Found! Please Upload The Text.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Right Ad -->
        <div class="col-md-2 d-none d-md-block">
            <div class="card shadow-sm h-100">
                <img src="https://via.placeholder.com/160x600?text=Ad" class="card-img-top" alt="Ad">
                <div class="card-body text-center">
                    <div class="small text-muted">Sponsored</div>
                    <a href="#" class="btn btn-sm btn-outline-primary mt-2">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function(){
        const contentEl = document.getElementById('contentText');
        const countEl = document.getElementById('charCount');
        const warnEl = document.getElementById('minCharWarning');
        const uploadBtn = document.getElementById('uploadBtn');
        const langSel = document.getElementById('uploadLanguageSelect');
        function update() {
            const len = contentEl.value.length;
            countEl.innerText = len;
            const ok = len >= 150;
            if (!ok) {
                warnEl.classList.remove('d-none');
                uploadBtn.disabled = true;
            } else {
                warnEl.classList.add('d-none');
                uploadBtn.disabled = false;
            }
        }
        contentEl.addEventListener('input', update);
        update();
        function applyFontByLang() {
            contentEl.classList.remove('font-krutidev','font-mangal');
            const val = langSel ? langSel.value : 'hindi_krutidev';
            if (val === 'hindi_krutidev') contentEl.classList.add('font-krutidev');
            if (val === 'hindi_inscript') contentEl.classList.add('font-mangal');
        }
        if (langSel) {
            langSel.addEventListener('change', applyFontByLang);
            applyFontByLang();
        }
        // Delete confirmation
        document.querySelectorAll('.delete-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this paragraph?')) {
                    e.preventDefault();
                }
            });
        });
    })();
</script>
<?php require_once 'includes/footer.php'; ?>
