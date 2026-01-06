<?php
$pageTitle = "English Typing Test - Settings";
require_once 'includes/header.php';
require_once 'config/db.php';
$isPremium = 0;
try {
    if (isset($_SESSION['user_id'])) {
        $stmtP = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
        $stmtP->execute([ (int)$_SESSION['user_id'] ]);
        $rowP = $stmtP->fetch();
        if ($rowP) { $isPremium = (int)$rowP['is_premium']; }
    }
} catch (PDOException $e) {}
$examSettings = [];
try {
    $examSettings = $pdo->query("SELECT id, exam_name, duration_minutes, allow_backspace, highlight_errors FROM exam_settings")->fetchAll();
} catch (PDOException $e) {}
$customSelectedTitle = isset($_SESSION['test_settings']['custom_title']) ? $_SESSION['test_settings']['custom_title'] : '';
if ($customSelectedTitle === '') {
        try {
            $stmt = $pdo->query("SELECT title FROM user_paragraphs WHERE language = 'hindi_krutidev' ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch();
            if ($row) { $customSelectedTitle = $row['title']; }
        } catch (PDOException $e) { /* ignore */ }
    }
    $customList = [];
    try {
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("SELECT id, title, language FROM user_paragraphs WHERE user_id = ? ORDER BY id DESC LIMIT 30");
            $stmt->execute([ (int)$_SESSION['user_id'] ]);
            $customList = $stmt->fetchAll();
        } else {
            $guestIds = isset($_COOKIE['guest_uploads']) ? json_decode($_COOKIE['guest_uploads'], true) : [];
            if (!empty($guestIds) && is_array($guestIds)) {
                $inQuery = implode(',', array_map('intval', $guestIds));
                if (!empty($inQuery)) {
                    $stmt = $pdo->query("SELECT id, title, language FROM user_paragraphs WHERE id IN ($inQuery) ORDER BY id DESC");
                    $customList = $stmt->fetchAll();
                }
            }
        }
    } catch (PDOException $e) { $customList = []; }
    $defaultList = [];
    try {
        $stmt = $pdo->query("SELECT id, title, language FROM default_paragraphs ORDER BY id DESC LIMIT 100");
        $defaultList = $stmt->fetchAll();
    } catch (PDOException $e) { $defaultList = []; }
$selectedCustomId = isset($_SESSION['test_settings']['custom_id']) ? $_SESSION['test_settings']['custom_id'] : 'none';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Typing Test</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="typing-test.php" id="settingsForm">
                        <?php if (!empty($_SESSION['settings_error'])): ?>
                            <div class="alert alert-danger mb-3"><?php echo htmlspecialchars($_SESSION['settings_error']); ?></div>
                            <?php $_SESSION['settings_error'] = null; ?>
                        <?php endif; ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Language</label></div>
                            <div class="col-7 col-md-8">
                                <select class="form-select" name="language">
                                    <option value="select" selected>Select Language</option>
                                    <option value="english">English</option>
                                    <option value="hindi_krutidev">Hindi (Kruti Dev 010)</option>
                                    <option value="hindi_inscript">Hindi (Unicode Mangal / Inscript)</option>
                                </select>
                            </div>
                        </div>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <div class="row align-items-center mb-3">
                                <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Enter Your Name</label></div>
                                <div class="col-7 col-md-8">
                                    <input type="text" name="tester_name" class="form-control" placeholder="Optional">
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Select Test Time</label></div>
                            <div class="col-7 col-md-8">
                                <select class="form-select" name="time">
                                    <option value="60">1 Minute</option>
                                    <option value="120">2 Minutes</option>
                                    <option value="300">5 Minutes</option>
                                    <option value="600">10 Minutes</option>
                                    <option value="900">15 Minutes</option>
                                    <option value="1200">20 Minutes</option>
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Exam Practice</label></div>
                            <div class="col-7 col-md-8">
                                <?php if (!empty($examSettings)): ?>
                                    <select class="form-select" name="exam_id" <?php echo ($isPremium === 1 ? '' : 'disabled'); ?>>
                                        <option value="none">Select Exam (SSC, RRB, CPCT)</option>
                                        <?php foreach ($examSettings as $ex): ?>
                                            <option value="<?php echo (int)$ex['id']; ?>"><?php echo htmlspecialchars($ex['exam_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($isPremium !== 1): ?>
                                        <div class="form-text text-danger">Premium required for exam practice. <a href="subscription.php">Upgrade</a></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-muted small">No exam presets available.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Select Paragraph</label></div>
                            <div class="col-7 col-md-8">
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="defaultParagraphCheck2" name="paragraph_default" checked>
                                        <label class="form-check-label" for="defaultParagraphCheck2">Default</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="customParagraphCheck2">
                                        <label class="form-check-label" for="customParagraphCheck2">Custom</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label mb-0">Default:</label></div>
                            <div class="col-7 col-md-8">
                                <select class="form-select" name="default_type" id="defaultTypeSelect">
                                    <option value="random" selected>Random Words</option>
                                    
                                            <?php foreach ($defaultList as $item): ?>
                                                <option value="def_<?php echo (int)$item['id']; ?>" data-lang="<?php echo htmlspecialchars($item['language']); ?>">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="custom_paragraph_id" id="adminSelectedParagraphId" value="none">
                                <input type="hidden" name="paragraph_source" id="paragraphSource" value="none">
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label mb-0">Custom:</label></div>
                            <div class="col-7 col-md-8">
                                <?php
                                    $hasSessionCustom = isset($_SESSION['test_settings']['custom_id']) && isset($_SESSION['test_settings']['custom_title']);
                                    if (!empty($customList) || $hasSessionCustom):
                                ?>
                                    <select class="form-select" name="custom_paragraph_id">
                                        <option value="none"<?php echo ($selectedCustomId === 'none' ? ' selected' : ''); ?>>No Paragraph Selected</option>
                                        <?php
                                            $renderedSession = false;
                                            if ($hasSessionCustom) {
                                                $sid = (int)$_SESSION['test_settings']['custom_id'];
                                                $stit = $_SESSION['test_settings']['custom_title'];
                                                $existsInList = false;
                                                foreach ($customList as $ci) { if ((int)$ci['id'] === $sid) { $existsInList = true; break; } }
                                                if (!$existsInList) {
                                                    $renderedSession = true;
                                        ?>
                                                    <option value="<?php echo $sid; ?>"<?php echo ($selectedCustomId == $sid ? ' selected' : ''); ?>><?php echo htmlspecialchars($stit); ?></option>
                                        <?php
                                                }
                                            }
                                            foreach ($customList as $item):
                                        ?>
                                            <option value="<?php echo (int)$item['id']; ?>" data-lang="<?php echo htmlspecialchars($item['language']); ?>"<?php echo ($selectedCustomId == $item['id'] ? ' selected' : ''); ?>>
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <div class="text-danger small mb-2">No list found! Please upload the text.</div>
                                <?php endif; ?>
                                <a href="custom-upload.php" class="btn btn-outline-danger btn-sm mt-2">Upload / Change</a>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Set Word Limit</label></div>
                            <div class="col-7 col-md-8">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="wordLimitEnable2" name="word_limit_enabled">
                                        <label class="form-check-label" for="wordLimitEnable2">Enable</label>
                                    </div>
                                    <input type="number" min="10" max="1000" value="35" class="form-control" name="word_limit" style="max-width:120px;">
                                </div>
                                <div class="form-text">*You have to type whole paragraph.</div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Backspace</label></div>
                            <div class="col-7 col-md-8">
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backspace" id="backspaceEnable2" value="enable" checked>
                                        <label class="form-check-label" for="backspaceEnable2">Enable</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="backspace" id="backspaceDisable2" value="disable">
                                        <label class="form-check-label" for="backspaceDisable2">Disable</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mb-4">
                            <div class="col-5 col-md-4"><label class="form-label fw-bold mb-0">Highlight & Auto Scroll</label></div>
                            <div class="col-7 col-md-8">
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="highlight" id="highlightEnable2" value="enable" checked>
                                        <label class="form-check-label" for="highlightEnable2">Enable</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="highlight" id="highlightDisable2" value="disable">
                                        <label class="form-check-label" for="highlightDisable2">Disable</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <button type="submit" name="start_exam" class="btn btn-outline-danger">
                                *Start In Exam Mode
                            </button>
                            <button type="submit" name="start_practice" class="btn btn-primary">
                                Start In Practice Mode
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function(){
        const form = document.getElementById('settingsForm');
        const langSel = form ? form.querySelector('select[name="language"]') : null;
        const customSel = form ? form.querySelector('select[name="custom_paragraph_id"]') : null;
        if (!form) return;
        form.addEventListener('submit', function(e){
            const noLangSelected = (langSel && langSel.value === 'select');
            const customChosen = (customSel && customSel.value && customSel.value !== 'none');
            if (noLangSelected && !customChosen) {
                e.preventDefault();
                alert('Please select a language or choose a custom paragraph.');
            }
        });
        function filterCustomByLanguage() {
            if (!langSel || !customSel) return;
            const targetLang = langSel.value;
            const opts = Array.from(customSel.options);
            opts.forEach(opt => {
                if (opt.value === 'none') { opt.hidden = false; return; }
                const ol = opt.getAttribute('data-lang');
                if (!targetLang || targetLang === 'select') {
                    opt.hidden = false;
                } else {
                    opt.hidden = (ol !== targetLang);
                }
            });
            // If current selected is hidden, reset to 'none'
            const cur = customSel.selectedOptions[0];
            if (cur && cur.hidden) {
                customSel.value = 'none';
            }
        }
        function filterAdminListByLanguage() {
            const sel = document.getElementById('defaultTypeSelect');
            if (!langSel || !sel) return;
            const targetLang = langSel.value;
            // Filter options inside optgroup
            const group = document.getElementById('adminParagraphOptGroup');
            if (!group) return;
            
            Array.from(group.querySelectorAll('option')).forEach(opt => {
                const ol = opt.getAttribute('data-lang');
                if (!targetLang || targetLang === 'select') {
                    opt.hidden = false;
                } else {
                    opt.hidden = (ol !== targetLang);
                }
            });
            
            // If currently selected is hidden, revert to 'random'
            const cur = sel.selectedOptions[0];
            if (cur && cur.hidden) {
                sel.value = 'random';
            }
        }
        function syncLanguageWithCustom() {
            if (!langSel || !customSel) return;
            const sel = customSel.selectedOptions[0];
            if (!sel) return;
            const ol = sel.getAttribute('data-lang');
            if (ol && langSel.value !== ol) {
                langSel.value = ol;
                filterCustomByLanguage();
            }
            // Disable exam selection when custom selected
            const examSel = form.querySelector('select[name="exam_id"]');
            if (examSel) {
                const useCustom = (customSel.value && customSel.value !== 'none');
                examSel.disabled = useCustom ? true : (<?php echo ($isPremium === 1 ? 'false' : 'true'); ?>);
            }
            // Auto toggle default/custom checkboxes visual
            const defChk = document.getElementById('defaultParagraphCheck2');
            const cusChk = document.getElementById('customParagraphCheck2');
            if (defChk && cusChk) {
                if (customSel.value && customSel.value !== 'none') {
                    defChk.checked = false;
                    cusChk.checked = true;
                }
            }
        }
        function syncLanguageWithDefault() {
            // When user picks a default paragraph (admin), sync language
            const sel = document.getElementById('defaultTypeSelect');
            if (!sel || !langSel) return;
            const opt = sel.selectedOptions[0];
            if (!opt) return;
            const ol = opt.getAttribute('data-lang');
            if (ol && langSel.value !== ol) {
                langSel.value = ol;
                filterCustomByLanguage(); // Re-filter custom
                // Note: filterAdminListByLanguage would hide this option if we ran it now, 
                // so we rely on the fact that if we just selected it, it matches.
            }
            
            // Set paragraph source
            const sourceInput = document.getElementById('paragraphSource');
            const idInput = document.getElementById('adminSelectedParagraphId');
            
            if (opt.value.startsWith('def_')) {
                if (sourceInput) sourceInput.value = 'default';
                if (idInput) idInput.value = opt.value.replace('def_', '');
            } else {
                if (sourceInput) sourceInput.value = 'none';
                if (idInput) idInput.value = 'none';
            }
        }
        
        if (langSel) {
            langSel.addEventListener('change', filterCustomByLanguage);
            langSel.addEventListener('change', filterAdminListByLanguage);
        }
        if (customSel) {
            customSel.addEventListener('change', syncLanguageWithCustom);
        }
        const defSel = document.getElementById('defaultTypeSelect');
        if (defSel) {
            defSel.addEventListener('change', syncLanguageWithDefault);
        }
        
        // Run once on init
        syncLanguageWithCustom();
        filterCustomByLanguage();
        filterAdminListByLanguage();
    })();
    </script>
<?php require_once 'includes/footer.php'; ?>
