<?php
$pageTitle = "Home";
require_once 'includes/header.php';
require_once 'config/db.php';

// Fetch stats
$totalUsers = 0;
$totalTests = 0;

try {
    $stmtUser = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmtUser->fetchColumn();

    $stmtTests = $pdo->query("SELECT COUNT(*) FROM typing_results");
    $totalTests = $stmtTests->fetchColumn();
} catch (PDOException $e) {
    // Silent fail for stats on home page
}
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 class="display-3 fw-bold mb-4">Improve Your Typing Speed</h1>
        <p class="lead mb-4">Master the art of touch typing with our free, customizable typing tests. Track your progress and compete with others.</p>
        <a href="test-settings.php" class="btn btn-light btn-lg fw-bold px-5 py-3 shadow">
            <i class="bi bi-keyboard-fill me-2"></i> Start Typing
        </a>
    </div>
</div>

<!-- Stats Counter -->
<div class="row text-center mb-5">
    <div class="col-md-6 mb-3">
        <div class="stats-box">
            <div class="stats-value" id="userCount"><?php echo number_format($totalUsers); ?></div>
            <div class="text-muted">Happy Typists</div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="stats-box">
            <div class="stats-value" id="testCount"><?php echo number_format($totalTests); ?></div>
            <div class="text-muted">Tests Taken</div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card border-warning shadow-sm">
            <div class="card-header bg-warning text-dark">
                <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Important Note for Government Typing Exams</strong>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Put accuracy before speed; speed without accuracy harms your result.</li>
                    <li>Remain alert with spine erect and maintain even posture during test.</li>
                    <li>Do not rest your palms on the keyboard while typing.</li>
                    <li>Avoid looking at the keyboard; build touch typing habit.</li>
                    <li>Keep nails trimmed before the test.</li>
                    <li>Do not try to memorize the keyboard; develop finger memory.</li>
                    <li>Use a good keyboard if possible; it improves consistency.</li>
                </ul>
            </div>
        </div>
    </div>
 </div>

<!-- Features Section -->
<div class="row mb-5">
    <div class="col-12 text-center mb-4">
        <h2 class="fw-bold">Why Choose <?php echo SITE_NAME; ?>?</h2>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body text-center p-4">
                <div class="display-4 text-primary mb-3">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <h4 class="card-title">Real-time Analysis</h4>
                <p class="card-text">See your WPM, accuracy, and errors in real-time as you type. Get detailed insights after every test.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body text-center p-4">
                <div class="display-4 text-primary mb-3">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h4 class="card-title">Track Progress</h4>
                <p class="card-text">Create an account to save your test history. visualize your improvement over time with detailed charts.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card feature-card h-100">
            <div class="card-body text-center p-4">
                <div class="display-4 text-primary mb-3">
                    <i class="bi bi-translate"></i>
                </div>
                <h4 class="card-title">Multi-Language</h4>
                <p class="card-text">Practice in English and Hindi. Support for various keyboard layouts to master your preferred language.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
