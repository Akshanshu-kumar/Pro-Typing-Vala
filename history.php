<?php
$pageTitle = "Test History";
require_once 'config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['delete_result'])) {
    $delId = (int)$_GET['delete_result'];
    try {
        $stmtDel = $pdo->prepare("DELETE FROM typing_results WHERE id = ? AND user_id = ?");
        $stmtDel->execute([$delId, (int)$_SESSION['user_id']]);
        header("Location: history.php");
        exit;
    } catch (PDOException $e) {
        // silent error
    }
}

require_once 'includes/header.php';

$results = [];
$isPremium = 0;
$percentile = null;
$latestWpm = null;
try {
    // Premium flag
    $stU = $pdo->prepare("SELECT is_premium FROM users WHERE id = ?");
    $stU->execute([ (int)$_SESSION['user_id'] ]);
    $u = $stU->fetch();
    if ($u) { $isPremium = (int)$u['is_premium']; }

    // Pagination Setup
    $limit = 10;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    // Count total records for pagination
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM typing_results WHERE user_id = ?");
    $stmtCount->execute([ (int)$_SESSION['user_id'] ]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRecords / $limit);

    // Fetch paginated results
    $stmt = $pdo->prepare("SELECT id, wpm, cpm, accuracy, mistakes, time_taken, language, test_date FROM typing_results WHERE user_id = ? ORDER BY test_date DESC LIMIT $limit OFFSET $offset");
    $stmt->execute([ (int)$_SESSION['user_id'] ]);
    $results = $stmt->fetchAll();

    if (!empty($results)) {
        // Calculate percentile based on ALL records
        $latestWpm = (int)$results[0]['wpm']; 
        $total = (int)$pdo->query("SELECT COUNT(*) FROM typing_results")->fetchColumn();
        $le = 0;
        if ($total > 0) {
            $stP = $pdo->prepare("SELECT COUNT(*) FROM typing_results WHERE wpm <= ?");
            $stP->execute([ $latestWpm ]);
            $le = (int)$stP->fetchColumn();
            $percentile = round(($le / $total) * 100);
        }
    }
} catch (PDOException $e) {
    $results = [];
}

// Build analytics data (premium only)
$labels = [];
$dataWpm = [];
$dataAcc = [];
$dataMistakes = [];
$wordFreq = [];
foreach ($results as $r) {
    $labels[] = date('M j', strtotime($r['test_date']));
    $dataWpm[] = (int)$r['wpm'];
    $dataAcc[] = (int)$r['accuracy'];
    $dataMistakes[] = (int)$r['mistakes'];
    if (!empty($r['mistyped_words']) && $r['mistyped_words'] !== 'null') {
        $mw = json_decode($r['mistyped_words'], true);
        if (is_array($mw)) {
            foreach ($mw as $w) {
                $w = strtolower(trim($w));
                if ($w === '') continue;
                $wordFreq[$w] = ($wordFreq[$w] ?? 0) + 1;
            }
        }
    }
}
arsort($wordFreq);
$topWords = array_slice($wordFreq, 0, 10, true);
?>
<div class="container my-5">
    <?php if ($isPremium === 1): ?>
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Progress Trends</h5>
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Errors Over Time</h5>
                        <canvas id="mistakeChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Top Mistyped Words</h5>
                        <?php if (!empty($topWords)): ?>
                        <ul class="list-group">
                            <?php foreach ($topWords as $w => $c): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($w); ?>
                                    <span class="badge bg-primary rounded-pill"><?php echo (int)$c; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <div class="text-muted">Not enough data</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Your Percentile</h5>
                        <?php if ($percentile !== null): ?>
                            <div class="display-3 fw-bold text-primary"><?php echo $percentile; ?>%</div>
                            <div class="text-muted">Based on latest WPM: <?php echo (int)$latestWpm; ?></div>
                        <?php else: ?>
                            <div class="text-muted">Run some tests to compute percentile.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Your Test History</h2>
        <a href="typing-test.php" class="btn btn-secondary">Back to Test</a>
    </div>
    <?php if (empty($results)): ?>
        <div class="alert alert-info">No test results found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>WPM</th>
                        <th>Accuracy</th>
                        <th>Errors</th>
                        <th>Time</th>
                        <th>Language</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $r): ?>
                        <tr class="align-middle">
                            <td>
                                <div class="fw-bold text-dark"><?php echo date('M j, Y', strtotime($r['test_date'])); ?></div>
                                <div class="small text-muted"><?php echo date('g:i a', strtotime($r['test_date'])); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size:0.9rem;">
                                    <?php echo (int)$r['wpm']; ?> WPM
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 6px; width: 80px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo (int)$r['accuracy']; ?>%"></div>
                                </div>
                                <small class="text-muted"><?php echo (int)$r['accuracy']; ?>%</small>
                            </td>
                            <td class="text-danger fw-bold"><?php echo (int)$r['mistakes']; ?></td>
                            <td><?php echo gmdate('i:s', (int)$r['time_taken']); ?></td>
                            <td>
                                <?php 
                                    $langMap = [
                                        'english' => 'English', 
                                        'hindi_krutidev' => 'Hindi (KrutiDev)', 
                                        'hindi_inscript' => 'Hindi (Mangal)'
                                    ];
                                    echo htmlspecialchars($langMap[$r['language']] ?? $r['language']); 
                                ?>
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group" role="group">
                                    <a href="result.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Result">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="certificate.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-success" title="Download Certificate">
                                        <i class="bi bi-award"></i> Cert
                                    </a>
                                    <a href="history.php?delete_result=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-outline-danger delete-result-link" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
            document.querySelectorAll('.delete-result-link').forEach(l => {
                l.addEventListener('click', e => {
                    if(!confirm('Are you sure you want to delete this test result?')) e.preventDefault();
                });
            });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        (function(){
            const labels = <?php echo json_encode($labels); ?>;
            const wpm = <?php echo json_encode($dataWpm); ?>;
            const acc = <?php echo json_encode($dataAcc); ?>;
            const mistakes = <?php echo json_encode($dataMistakes); ?>;
            const tc = document.getElementById('trendChart');
            if (tc) {
                new Chart(tc.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            { label: 'WPM', data: wpm, borderColor: 'rgba(13,110,253,1)', backgroundColor: 'rgba(13,110,253,0.2)' },
                            { label: 'Accuracy (%)', data: acc, borderColor: 'rgba(25,135,84,1)', backgroundColor: 'rgba(25,135,84,0.2)' }
                        ]
                    },
                    options: { responsive: true, scales: { y: { beginAtZero: true } } }
                });
            }
            const mc = document.getElementById('mistakeChart');
            if (mc) {
                new Chart(mc.getContext('2d'), {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'Errors', data: mistakes, backgroundColor: 'rgba(255,193,7,0.6)' }] },
                    options: { responsive: true, scales: { y: { beginAtZero: true } } }
                });
            }
        })();
        </script>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
