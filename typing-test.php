<?php
$pageTitle = "Advanced Typing Test";
require_once 'includes/header.php';
require_once 'config/db.php';

// Persist incoming settings from separate settings page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['test_settings'] = [
        'tester_name' => isset($_POST['tester_name']) ? trim($_POST['tester_name']) : '',
        'language' => $_POST['language'] ?? 'english',
        'time' => isset($_POST['time']) ? (int)$_POST['time'] : 60,
        'default_paragraph' => isset($_POST['paragraph_default']),
        'paragraph_type' => $_POST['default_type'] ?? 'random',
        'custom_text' => isset($_POST['custom_text']) ? trim($_POST['custom_text']) : '',
        'word_limit_enabled' => isset($_POST['word_limit_enabled']),
        'word_limit' => isset($_POST['word_limit']) ? (int)$_POST['word_limit'] : 35,
        'backspace' => $_POST['backspace'] ?? 'enable',
        'highlight' => $_POST['highlight'] ?? 'enable',
        'mode' => isset($_POST['start_exam']) ? 'exam' : 'practice'
    ];
    if (isset($_POST['exam_id']) && $_POST['exam_id'] !== 'none') {
        $examId = (int)$_POST['exam_id'];
        $userPremium = 0;
        if (isset($_SESSION['user_id'])) {
            try {
                $stU = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
                $stU->execute([ (int)$_SESSION['user_id'] ]);
                $uR = $stU->fetch();
                if ($uR) { $userPremium = (int)$uR['is_premium']; }
            } catch (PDOException $e) {}
        }
        if ($userPremium !== 1) {
            $_SESSION['settings_error'] = 'Premium required for exam practice.';
            header("Location: test-settings.php");
            exit;
        }
        try {
            $stE = $pdo->prepare("SELECT exam_name, duration_minutes, allow_backspace, highlight_errors FROM exam_settings WHERE id = ?");
            $stE->execute([ $examId ]);
            $ex = $stE->fetch();
            if ($ex) {
                $_SESSION['test_settings']['mode'] = 'exam';
                $_SESSION['test_settings']['exam_name'] = $ex['exam_name'];
                $_SESSION['test_settings']['time'] = (int)$ex['duration_minutes'];
                $_SESSION['test_settings']['backspace'] = ((int)$ex['allow_backspace'] === 1) ? 'enable' : 'disable';
                $_SESSION['test_settings']['highlight'] = ((int)$ex['highlight_errors'] === 1) ? 'enable' : 'disable';
            }
        } catch (PDOException $e) {}
    }
    // Check if default_type contains a specific paragraph (e.g., 'def_123')
    $defType = $_POST['default_type'] ?? 'random';
    if (strpos($defType, 'def_') === 0) {
        $defId = (int)str_replace('def_', '', $defType);
        try {
            $stmt = $pdo->prepare("SELECT id, title, content, language FROM default_paragraphs WHERE id = ?");
            $stmt->execute([$defId]);
            $row = $stmt->fetch();
            if ($row) {
                $_SESSION['test_settings']['default_paragraph'] = false; // Treat as custom/database
                $_SESSION['test_settings']['paragraph_type'] = 'database';
                $_SESSION['test_settings']['language'] = $row['language'];
                $_SESSION['test_settings']['custom_text'] = $row['content'];
                $_SESSION['test_settings']['custom_title'] = $row['title'];
                $_SESSION['test_settings']['custom_id'] = (int)$row['id'];
                $_SESSION['test_settings']['paragraph_source'] = 'default';
            }
        } catch (PDOException $e) { /* ignore */ }
    } else if (isset($_POST['custom_paragraph_id']) && $_POST['custom_paragraph_id'] !== 'none') {
        $cid = (int)$_POST['custom_paragraph_id'];
        $pSource = $_POST['paragraph_source'] ?? 'none';
        
        try {
            $row = null;
            if ($pSource === 'default') {
                $stmt = $pdo->prepare("SELECT id, title, content, language FROM default_paragraphs WHERE id = ?");
                $stmt->execute([$cid]);
                $row = $stmt->fetch();
            } else if ($pSource === 'custom') {
                $stmt = $pdo->prepare("SELECT id, title, content, language FROM user_paragraphs WHERE id = ?");
                $stmt->execute([$cid]);
                $row = $stmt->fetch();
            } else {
                 // Fallback: Try user_paragraphs first, then default
                 $stmt = $pdo->prepare("SELECT id, title, content, language FROM user_paragraphs WHERE id = ?");
                 $stmt->execute([$cid]);
                 $row = $stmt->fetch();
                 if (!$row) {
                     $stmt = $pdo->prepare("SELECT id, title, content, language FROM default_paragraphs WHERE id = ?");
                     $stmt->execute([$cid]);
                     $row = $stmt->fetch();
                 }
            }

            if ($row) {
                $_SESSION['test_settings']['default_paragraph'] = false;
                $_SESSION['test_settings']['paragraph_type'] = 'database';
                $_SESSION['test_settings']['language'] = $row['language'];
                $_SESSION['test_settings']['custom_text'] = $row['content'];
                $_SESSION['test_settings']['custom_title'] = $row['title'];
                $_SESSION['test_settings']['custom_id'] = (int)$row['id'];
                $_SESSION['test_settings']['paragraph_source'] = ($pSource === 'default') ? 'default' : 'custom';
            }
        } catch (PDOException $e) { /* ignore */ }
    }
    if (isset($_POST['language']) && $_POST['language'] === 'select') {
        $hasCustom = (isset($_POST['custom_paragraph_id']) && $_POST['custom_paragraph_id'] !== 'none');
        if (!$hasCustom) {
            $_SESSION['settings_error'] = 'Please select a language before starting the test.';
            header("Location: test-settings.php");
            exit;
        }
    }
}
$settings = $_SESSION['test_settings'] ?? null;

// Fetch Exam Modes
try {
    $examModes = $pdo->query("SELECT * FROM exam_settings")->fetchAll();
} catch (PDOException $e) {
    $examModes = [];
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Settings -->
        <?php if (!$settings): ?>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm sticky-top" style="top: 80px; z-index: 1000;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Test Settings</h5>
                </div>
                <div class="card-body">
                    <form id="testSettingsForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Enter Your Name</label>
                            <input type="text" class="form-control" id="testerName" placeholder="Your Name (optional)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Language</label>
                            <select class="form-select" id="languageSelect">
                                <option value="english" selected>English</option>
                                <option value="hindi_inscript">Hindi (Inscript)</option>
                                <option value="hindi_krutidev">Hindi (Kruti Dev 010)</option>
                                <option value="marathi">Marathi</option>
                                <option value="punjabi">Punjabi</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Test Time</label>
                            <select class="form-select" id="timeSelect">
                                <option value="60">1 Minute</option>
                                <option value="120">2 Minutes</option>
                                <option value="300" selected>5 Minutes</option>
                                <option value="600">10 Minutes</option>
                                <option value="900">15 Minutes</option>
                                <option value="1200">20 Minutes</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Paragraph</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="defaultParagraphCheck" checked>
                                    <label class="form-check-label" for="defaultParagraphCheck">Default</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="customParagraphCheck">
                                    <label class="form-check-label" for="customParagraphCheck">Custom</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Default:</label>
                            <select class="form-select" id="defaultParagraphType">
                                <option value="random" selected>Random Words</option>
                                <option value="database">From Database</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Custom:</label>
                            <textarea id="customText" class="form-control" rows="4" placeholder="Paste your paragraph..."></textarea>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-outline-danger btn-sm" id="uploadTextBtn">Upload Text</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Set Word Limit</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wordLimitEnable">
                                    <label class="form-check-label" for="wordLimitEnable">Enable</label>
                                </div>
                                <input type="number" min="10" max="1000" value="35" class="form-control" id="wordLimitInput" style="max-width:120px;">
                            </div>
                            <div class="form-text">You have to type whole paragraph if disabled.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Backspace</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="backspaceOpts" id="backspaceEnable" checked>
                                    <label class="form-check-label" for="backspaceEnable">Enable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="backspaceOpts" id="backspaceDisable">
                                    <label class="form-check-label" for="backspaceDisable">Disable</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Highlight & Auto Scroll</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="highlightOpts" id="highlightEnable" checked>
                                    <label class="form-check-label" for="highlightEnable">Enable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="highlightOpts" id="highlightDisable">
                                    <label class="form-check-label" for="highlightDisable">Disable</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="keyboardToggle">
                            <label class="form-check-label" for="keyboardToggle">Virtual Keyboard</label>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-danger" id="startExamBtn">
                                *Start In Exam Mode
                            </button>
                            <button type="button" class="btn btn-primary" id="startPracticeBtn">
                                Start In Practice Mode
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Typing Area -->
        <div class="col-md-<?php echo $settings ? 12 : 9; ?>">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <!-- Stats Bar -->
                    <div class="row text-center mb-4 p-3 bg-light rounded" id="liveStats">
                        <div class="col-6 col-md-3 border-end">
                            <div class="text-muted small text-uppercase">Time Left</div>
                            <div class="fs-2 fw-bold text-primary" id="timer">00:00</div>
                        </div>
                        <div class="col-6 col-md-3 border-end">
                            <div class="text-muted small text-uppercase">WPM</div>
                            <div class="fs-2 fw-bold text-success" id="wpm">0</div>
                        </div>
                        <div class="col-6 col-md-3 border-end">
                            <div class="text-muted small text-uppercase">Accuracy</div>
                            <div class="fs-2 fw-bold text-info" id="accuracy">100%</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small text-uppercase">Errors</div>
                            <div class="fs-2 fw-bold text-danger" id="errors">0</div>
                        </div>
                    </div>

                    <!-- Font Size Controls -->
                    <div class="d-flex justify-content-end mb-2">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Font size">
                            <button type="button" class="btn btn-outline-secondary" id="fontDecBtn">A−</button>
                            <button type="button" class="btn btn-outline-secondary" id="fontIncBtn">A+</button>
                        </div>
                    </div>

                    <!-- Typing Area -->
                    <div id="typingContainer" class="position-relative">
                        <!-- Overlay for "Click to Start" -->
                        <div id="focusOverlay" class="position-absolute w-100 h-100 d-none justify-content-center align-items-center bg-white bg-opacity-75" style="z-index: 10;">
                            <h3 class="text-muted"><i class="bi bi-mouse"></i> Click to Focus</h3>
                        </div>

                        <div id="typing-area" class="border rounded p-4 mb-3" tabindex="0" style="min-height: 180px; max-height: 300px; overflow-y: auto; font-size: 1.4rem; line-height: 2; outline: none;">
                            <div class="text-center text-muted mt-5">Select settings and click Start Test</div>
                        </div>
                        <textarea id="hiddenInput" class="opacity-0 position-absolute" style="top: 0; left: 0; height: 1px; width: 1px;"></textarea>
                    </div>

                    <!-- Controls -->
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" id="resetBtn" disabled>
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </button>
                        <button class="btn btn-danger" id="submitBtn" disabled>
                            <i class="bi bi-check-circle"></i> Submit Test
                        </button>
                    </div>
                </div>
            </div>

            <!-- Virtual Keyboard Container -->
            <div id="virtualKeyboardContainer" class="d-none">
                <div class="card shadow-sm">
                    <div class="card-header bg-light small">
                        Virtual Keyboard (<span id="keyboardLayoutName">QWERTY</span>)
                    </div>
                    <div class="card-body bg-secondary bg-opacity-10 text-center">
                         <div id="keyboard" class="keyboard-base"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 </div>
 
 <!-- Result Modal -->
     <div class="modal fade" id="resultModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
         <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-trophy-fill"></i> Test Completed</h5>
                </div>
                <div class="modal-body text-center">
                    <h4 class="mb-4">Your Score</h4>
                    <div class="row mb-3">
                        <div class="col-6 border-end">
                            <h2 class="fw-bold text-primary" id="modalWpm">0</h2>
                            <small class="text-muted">WPM</small>
                        </div>
                        <div class="col-6">
                            <h2 class="fw-bold text-success" id="modalAccuracy">0%</h2>
                            <small class="text-muted">Accuracy</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <h5 class="fw-bold text-danger" id="modalMistakes">0</h5>
                            <small class="text-muted">Errors</small>
                        </div>
                        <div class="col-4">
                             <h5 class="fw-bold text-secondary" id="modalCpm">0</h5>
                            <small class="text-muted">CPM</small>
                        </div>
                         <div class="col-4">
                             <h5 class="fw-bold text-dark" id="modalTime">0s</h5>
                            <small class="text-muted">Time</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary" id="retakeBtn">
                        <i class="bi bi-arrow-repeat"></i> Retake Test
                    </button>
                    <button type="button" class="btn btn-success" id="saveResultBtn">
                        <i class="bi bi-save"></i> Save My Result
                    </button>
                </div>
            </div>
        </div>
     </div>
 
     <!-- Load Engine -->
 <script>
     const IS_LOGGED_IN = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
     window.TEST_SETTINGS = <?php echo json_encode($settings ?: []); ?>;
 </script>
 <script src="<?php echo BASE_URL; ?>assets/js/converters/converter-map.js"></script>
 <script src="<?php echo BASE_URL; ?>assets/js/typing-test.js"></script>
<!-- Keyboard Layouts (Optional) -->
<!-- <script src="<?php echo BASE_URL; ?>assets/js/keyboards/layouts.js"></script> -->

<?php require_once 'includes/footer.php'; ?>
