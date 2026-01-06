<?php
$pageTitle = "Test Result";
require_once 'config/db.php';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_result']) && isset($_POST['result_id']) && isset($_SESSION['user_id'])) {
    $rid = (int)$_POST['result_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM typing_results WHERE id = ? AND user_id = ?");
        $stmt->execute([$rid, (int)$_SESSION['user_id']]);
        echo "<div class='container mt-5'><div class='alert alert-success'>Result deleted successfully.</div><div><a href='typing-test.php' class='btn btn-primary'>Back to Test</a></div></div>";
        require_once 'includes/footer.php';
        exit;
    } catch (PDOException $e) {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Could not delete result.</div></div>";
        require_once 'includes/footer.php';
        exit;
    }
}

if (isset($_GET['preview']) && !empty($_SESSION['stashed_result'])) {
    $stashed = $_SESSION['stashed_result'];
    // Normalize stashed data to match DB structure
    $result = [
        'wpm' => $stashed['wpm'] ?? 0,
        'accuracy' => $stashed['accuracy'] ?? 0,
        'mistakes' => $stashed['mistakes'] ?? 0,
        'cpm' => $stashed['cpm'] ?? 0,
        'time_taken' => $stashed['time_taken'] ?? 0,
        'mistyped_words' => 'null', // Not passed in stash currently, can be ignored for preview
        'test_date' => date('Y-m-d H:i:s'),
        'username' => $stashed['tester_name'] ?: 'Guest',
        'is_preview' => true
    ];
} elseif (!isset($_GET['id'])) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid Result ID.</div></div>";
    require_once 'includes/footer.php';
    exit;
} else {
    if (!isset($_SESSION['user_id'])) {
        echo "<div class='container mt-5'><div class='alert alert-danger'>Please login to view this result.</div></div>";
        require_once 'includes/footer.php';
        exit;
    }
    $result_id = (int)$_GET['id'];
    $owner_id = (int)$_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT tr.*, u.username FROM typing_results tr JOIN users u ON tr.user_id = u.id WHERE tr.id = ? AND tr.user_id = ?");
        $stmt->execute([$result_id, $owner_id]);
        $result = $stmt->fetch();
    } catch (PDOException $e) {
        die("Error fetching result");
    }
}

if (!$result) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Result not found.</div></div>";
    require_once 'includes/footer.php';
    exit;
}

// Owner-only access enforced above
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0 mb-4" id="resultCard">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h2 class="mb-0">Typing Test Result</h2>
                    <p class="mb-0 small">Taken on <?php echo date('F j, Y, g:i a', strtotime($result['test_date'])); ?></p>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <h3 class="display-4 fw-bold text-primary"><?php echo $result['wpm']; ?></h3>
                            <div class="text-muted text-uppercase small ls-1">WPM</div>
                        </div>
                        <div class="col-4">
                            <h3 class="display-4 fw-bold text-success"><?php echo $result['accuracy']; ?>%</h3>
                            <div class="text-muted text-uppercase small ls-1">Accuracy</div>
                        </div>
                        <div class="col-4">
                            <h3 class="display-4 fw-bold text-danger"><?php echo $result['mistakes']; ?></h3>
                            <div class="text-muted text-uppercase small ls-1">Errors</div>
                        </div>
                    </div>
                    
                    <div class="row text-center mb-4">
                         <div class="col-6">
                            <h4 class="fw-bold"><?php echo $result['cpm']; ?></h4>
                            <div class="text-muted small">CPM</div>
                         </div>
                         <div class="col-6">
                            <h4 class="fw-bold"><?php echo gmdate("i:s", $result['time_taken']); ?></h4>
                            <div class="text-muted small">Time Taken</div>
                         </div>
                    </div>
                    
                    <?php if(!empty($result['mistyped_words']) && $result['mistyped_words'] !== 'null'): ?>
                    <div class="alert alert-warning">
                        <strong>Mistyped Words:</strong> 
                        <?php 
                            $mistakes = json_decode($result['mistyped_words'], true);
                            if(is_array($mistakes)) {
                                echo implode(', ', $mistakes); 
                            }
                        ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="typing-test.php" class="btn btn-primary btn-lg"><i class="bi bi-arrow-repeat"></i> Retake Test</a>
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-lg"><i class="bi bi-file-pdf"></i> Download PDF</button>
                        <?php if (!isset($result['is_preview']) && isset($_SESSION['user_id']) && isset($result['user_id']) && (int)$result['user_id'] === (int)$_SESSION['user_id']): ?>
                            <a href="certificate.php?id=<?php echo (int)$_GET['id']; ?>" class="btn btn-outline-success btn-lg"><i class="bi bi-award"></i> Certificate</a>
                        <?php endif; ?>
                        
                        <?php if (isset($result['is_preview']) && $result['is_preview']): ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button id="saveResultBtn" class="btn btn-success btn-lg"><i class="bi bi-save"></i> Save Result</button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-success btn-lg"><i class="bi bi-box-arrow-in-right"></i> Login to Save</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (!isset($result['is_preview']) && isset($_SESSION['user_id']) && isset($result['user_id']) && (int)$result['user_id'] === (int)$_SESSION['user_id']): ?>
                            <form method="post" class="ms-2">
                                <input type="hidden" name="result_id" value="<?php echo (int)$_GET['id']; ?>">
                                <button type="submit" name="delete_result" class="btn btn-outline-danger btn-lg" onclick="return confirm('Are you sure you want to delete this result?');">
                                    <i class="bi bi-trash"></i> Delete Result
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($result['is_preview']) && $result['is_preview'] && isset($_SESSION['user_id'])): ?>
            <script>
                document.getElementById('saveResultBtn').addEventListener('click', function() {
                    fetch('api/save_result.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(<?php echo json_encode($_SESSION['stashed_result']); ?>)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            window.location.href = `result.php?id=${data.result_id}`;
                        } else {
                            alert('Error saving result: ' + data.message);
                        }
                    });
                });
            </script>
            <?php endif; ?>
            
            <!-- Performance Chart -->
            <div class="card shadow border-0">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Performance Overview</h5>
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['WPM', 'Accuracy (%)', 'CPM'],
            datasets: [{
                label: 'Test Score',
                data: [<?php echo $result['wpm']; ?>, <?php echo $result['accuracy']; ?>, <?php echo $result['cpm']; ?>],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)'
                ],
                borderColor: [
                    'rgba(13, 110, 253, 1)',
                    'rgba(25, 135, 84, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Typing Speed Calculation</h5>
                </div>
                <div class="card-body">
                    <p class="mb-4">These formulas are used to calculate the speed of the typing test accurately. These formulas are considered standard formulas for calculating speed.</p>
                    
                    <?php 
                        $time_minutes = $result['time_taken'] > 0 ? ($result['time_taken'] / 60) : 1;
                        $estimated_chars = round($result['cpm'] * $time_minutes);
                        $gross_wpm = round(($estimated_chars / 5) / $time_minutes);
                        $error_rate_wpm = round(($result['mistakes']) / $time_minutes);
                        $net_wpm = max(0, $gross_wpm - $error_rate_wpm);
                        $correct_chars = max(0, $estimated_chars - $result['mistakes'] * 5);
                        $accuracy_calc = $estimated_chars > 0 ? round(($correct_chars / $estimated_chars) * 100, 2) : 0;
                    ?>

                    <div class="accordion" id="speedFormulaAccordion">
                        
                        <!-- Net Speed Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingNet">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNet" aria-expanded="true" aria-controls="collapseNet">
                                    <i class="bi bi-speedometer2 me-2 text-primary"></i> <strong>Net Speed (WPM)</strong>
                                </button>
                            </h2>
                            <div id="collapseNet" class="accordion-collapse collapse show" aria-labelledby="headingNet" data-bs-parent="#speedFormulaAccordion">
                                <div class="accordion-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center mb-3 mb-md-0">
                                            <img src="assets/img/formulas/english_typing_test_net_speed_formula.png" alt="Net Speed Formula" class="img-fluid border rounded p-2 shadow-sm">
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small text-muted">Net Word Per Minute is the actual speed on which you can trust completely because it takes into consideration the error rate.</p>
                                            <table class="table table-sm table-bordered">
                                                <tbody>
                                                    <tr><td>Error Rate (WPM)</td><td class="text-end text-danger"><?php echo $error_rate_wpm; ?></td></tr>
                                                    <tr><td>Net WPM (Gross − Error Rate)</td><td class="text-end fw-bold text-primary"><?php echo $net_wpm; ?></td></tr>
                                                    <tr><td>Recorded WPM</td><td class="text-end fw-bold"><?php echo $result['wpm']; ?></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accuracy Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingAcc">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAcc" aria-expanded="false" aria-controls="collapseAcc">
                                    <i class="bi bi-bullseye me-2 text-success"></i> <strong>Accuracy (%)</strong>
                                </button>
                            </h2>
                            <div id="collapseAcc" class="accordion-collapse collapse" aria-labelledby="headingAcc" data-bs-parent="#speedFormulaAccordion">
                                <div class="accordion-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center mb-3 mb-md-0">
                                            <img src="assets/img/formulas/english_typing_test_accuracy_formula.png" alt="Accuracy Formula" class="img-fluid border rounded p-2 shadow-sm">
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small text-muted">Accuracy is attained by dividing the correct words by total words and multiplying by 100.</p>
                                            <table class="table table-sm table-bordered">
                                                <tbody>
                                                    <tr><td>Correct Characters (approx)</td><td class="text-end text-success"><?php echo $correct_chars; ?></td></tr>
                                                    <tr><td>Total Characters (approx)</td><td class="text-end"><?php echo $estimated_chars; ?></td></tr>
                                                    <tr><td>Calculated Accuracy</td><td class="text-end fw-bold"><?php echo $accuracy_calc; ?>%</td></tr>
                                                    <tr><td>Recorded Accuracy</td><td class="text-end fw-bold"><?php echo $result['accuracy']; ?>%</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gross Speed Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingGross">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGross" aria-expanded="false" aria-controls="collapseGross">
                                    <i class="bi bi-lightning-charge me-2 text-warning"></i> <strong>Gross Speed (GWPM)</strong>
                                </button>
                            </h2>
                            <div id="collapseGross" class="accordion-collapse collapse" aria-labelledby="headingGross" data-bs-parent="#speedFormulaAccordion">
                                <div class="accordion-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center mb-3 mb-md-0">
                                            <img src="assets/img/formulas/english_typing_test_gross_speed_formula.png" alt="Gross Speed Formula" class="img-fluid border rounded p-2 shadow-sm">
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small text-muted">Gross WPM is calculated by dividing total characters by 5, then by time in minutes. It ignores errors.</p>
                                            <table class="table table-sm table-bordered">
                                                <tbody>
                                                    <tr><td>Characters Typed</td><td class="text-end"><?php echo $estimated_chars; ?></td></tr>
                                                    <tr><td>Total Time (min)</td><td class="text-end"><?php echo number_format($time_minutes, 2); ?></td></tr>
                                                    <tr><td>Gross WPM</td><td class="text-end fw-bold text-warning"><?php echo $gross_wpm; ?></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Rate Item -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingError">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseError" aria-expanded="false" aria-controls="collapseError">
                                    <i class="bi bi-x-circle me-2 text-danger"></i> <strong>Error Rate (WPM)</strong>
                                </button>
                            </h2>
                            <div id="collapseError" class="accordion-collapse collapse" aria-labelledby="headingError" data-bs-parent="#speedFormulaAccordion">
                                <div class="accordion-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-5 text-center mb-3 mb-md-0">
                                            <img src="assets/img/formulas/english_typing_test_error_rate_formula.png" alt="Error Rate Formula" class="img-fluid border rounded p-2 shadow-sm">
                                        </div>
                                        <div class="col-md-7">
                                            <p class="small text-muted">Error Rate is incorrect entries divided by total time in minutes.</p>
                                            <table class="table table-sm table-bordered">
                                                <tbody>
                                                    <tr><td>Mistakes (words)</td><td class="text-end text-danger"><?php echo $result['mistakes']; ?></td></tr>
                                                    <tr><td>Total Time (min)</td><td class="text-end"><?php echo number_format($time_minutes, 2); ?></td></tr>
                                                    <tr><td>Error Rate (WPM)</td><td class="text-end fw-bold text-danger"><?php echo $error_rate_wpm; ?></td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Analysis Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-steps me-2"></i>Advanced Analysis</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Retrieve detailed stats
                        $full_mistakes = isset($result['full_mistakes']) ? (int)$result['full_mistakes'] : 0;
                        $half_mistakes = isset($result['half_mistakes']) ? (int)$result['half_mistakes'] : 0;
                        $typed_content = isset($result['typed_content']) ? $result['typed_content'] : '';
                        $original_content = isset($result['original_content']) ? $result['original_content'] : '';
                        
                        // Method 2 (Weighted): (GrossChars/5 - (Full + Half/2))/Time
                        // Assuming 1 word = 5 chars
                        $weighted_errors = $full_mistakes + ($half_mistakes / 2);
                        // Net WPM calculation for Method 2
                        // Gross WPM is estimated_chars/5/time
                        // Error WPM is weighted_errors/time
                        $method2_wpm = 0;
                        if ($time_minutes > 0) {
                             $method2_wpm = max(0, round((($estimated_chars / 5) - $weighted_errors) / $time_minutes));
                        }
                        ?>
                        <div class="row">
                            <!-- Method 1 -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-primary">
                                    <div class="card-header bg-primary text-white">Method 1 (Standard)</div>
                                    <div class="card-body text-center">
                                        <h3 class="display-4"><?php echo $net_wpm; ?> <span class="fs-6">WPM</span></h3>
                                        <p class="text-muted">Net Speed = Gross Speed - Error Rate</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Method 2 -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-danger">
                                    <div class="card-header bg-danger text-white">Method 2 (Weighted Penalties)</div>
                                    <div class="card-body text-center">
                                        <h3 class="display-4"><?php echo $method2_wpm; ?> <span class="fs-6">WPM</span></h3>
                                        <p class="text-muted">Half mistakes count as 0.5 errors.</p>
                                        <div class="small">
                                            <span class="badge bg-danger">Full: <?php echo $full_mistakes; ?></span>
                                            <span class="badge bg-primary">Half: <?php echo $half_mistakes; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Typed Paragraph Visualization -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-text-paragraph me-2"></i>Typed Paragraph Analysis</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <span class="badge bg-danger">Red</span> Full Mistake &bull; 
                            <span class="badge bg-primary">Blue</span> Half Mistake &bull; 
                            <span class="badge bg-dark">Black</span> Correct
                        </p>
                        <div class="border p-3 rounded bg-light" style="font-family: 'Courier New', monospace; font-size: 1.1rem; line-height: 1.6; white-space: pre-wrap;">
<?php
if ($typed_content && $original_content) {
    $len = mb_strlen($original_content);
    $typedLen = mb_strlen($typed_content);
    $maxLen = max($len, $typedLen);
    
    for ($i = 0; $i < $maxLen; $i++) {
        $t = ($i < $typedLen) ? mb_substr($typed_content, $i, 1) : '';
        $o = ($i < $len) ? mb_substr($original_content, $i, 1) : '';
        
        // Extra characters
        if ($i >= $len) {
             echo "<span class='text-danger' style='background-color: #ffe6e6;'>$t</span>";
             continue;
        }
        // Missing characters
        if ($i >= $typedLen) {
            echo "<span class='text-muted' style='opacity: 0.5;'>$o</span>";
            continue;
        }

        if ($t === $o) {
            echo "<span class='text-dark'>$t</span>";
        } else {
             $isHalf = false;
             if (mb_strtolower($t) === mb_strtolower($o)) $isHalf = true;
             elseif (preg_match('/^[.,;:\'"!?-]$/', $o) || preg_match('/^[.,;:\'"!?-]$/', $t)) $isHalf = true;
             elseif ($o === ' ' || $t === ' ') $isHalf = true;
             
             if ($isHalf) {
                 echo "<span class='text-primary fw-bold' style='background-color: #e6f0ff;'>$t</span>";
             } else {
                 echo "<span class='text-danger fw-bold' style='background-color: #ffe6e6;'>$t</span>";
             }
        }
    }
} else {
    echo "<p class='text-muted'>Detailed paragraph data not available for this test.</p>";
}
?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
