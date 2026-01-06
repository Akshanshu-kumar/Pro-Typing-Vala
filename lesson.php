<?php
require_once 'includes/header.php';
require_once 'config/db.php';

if(!isset($_GET['id'])) {
    header("Location: tutor.php");
    exit;
}

$lessonId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();

if(!$lesson) {
    echo "Lesson not found.";
    exit;
}
?>

<div class="container my-5">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
            <p class="text-muted"><?php echo htmlspecialchars($lesson['instructions']); ?></p>
        </div>
        <div class="col-md-4 text-end">
            <a href="tutor.php" class="btn btn-secondary">Back to Lessons</a>
        </div>
    </div>

    <!-- Typing Area Reuse -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4 text-center">
            <div id="typing-area" class="border rounded p-4 mb-3" style="font-size: 2rem; min-height: 150px; outline: none;">
                <!-- Content injected via JS -->
            </div>
            <textarea id="hiddenInput" class="opacity-0 position-absolute"></textarea>
            
            <div id="keyboard-guide" class="mt-4">
                <!-- Visual Keyboard Guide Here -->
                 <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Follow the highlighted keys on the keyboard below.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const lessonContent = <?php echo json_encode($lesson['content'] ?? ''); ?>;
    const lessonLanguage = <?php echo json_encode($lesson['language'] ?? 'english'); ?>;
</script>
<script src="<?php echo BASE_URL; ?>assets/js/converters/converter-map.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/engines/typing-engine-core.js"></script>
<script>
    // Simple Tutor Logic
    document.addEventListener('DOMContentLoaded', () => {
        const typingArea = document.getElementById('typing-area');
        const hiddenInput = document.getElementById('hiddenInput');
        
        // Render
        renderText(lessonContent);
        
        const engine = new TypingEngine({
            text: lessonContent,
            language: lessonLanguage,
            mode: 'tutor',
            duration: 9999, // Unlimited time
            onInput: (res) => {
                const chars = typingArea.querySelectorAll('.char');
                if(res.action === 'input') {
                    if(res.isCorrect) {
                        chars[res.index].classList.add('text-success');
                        if(chars[res.index+1]) chars[res.index+1].classList.add('bg-warning');
                        chars[res.index].classList.remove('bg-warning');
                    } else {
                        chars[res.index].classList.add('text-danger');
                    }
                    
                    if(res.index >= lessonContent.length - 1) {
                        alert("Lesson Completed!");
                        window.location.href = "tutor.php";
                    }
                }
            }
        });

        typingArea.addEventListener('click', () => hiddenInput.focus());
        
        hiddenInput.addEventListener('input', (e) => {
            if(e.data) engine.processInput(e.data, 'insertText');
        });

        function renderText(text) {
            typingArea.innerHTML = '';
            text.split('').forEach((char, idx) => {
                const span = document.createElement('span');
                span.innerText = char;
                span.className = 'char fw-bold';
                if(idx===0) span.classList.add('bg-warning');
                typingArea.appendChild(span);
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>
