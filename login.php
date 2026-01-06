<?php
$pageTitle = "Login";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, username, email, full_name, password FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        if ($user) {
            $hash = $user['password'];
            $ok = false;
            if (strpos($hash, '$2y$') === 0 || strpos($hash, '$argon2') === 0) {
                $ok = password_verify($password, $hash);
            } else {
                $ok = ($password === $hash);
            }
            if ($ok) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'] ?? null;
                if (!empty($_SESSION['stashed_result'])) {
                    $data = $_SESSION['stashed_result'];
                    try {
                        $stmt = $pdo->prepare("INSERT INTO typing_results (user_id, wpm, cpm, accuracy, mistakes, time_taken, language, tester_name, mistyped_words) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $mistyped = isset($data['mistyped_words']) ? json_encode($data['mistyped_words']) : null;
                        $lang = isset($data['language']) ? $data['language'] : 'english';
                        $tester = ($user['full_name'] ?? $user['username']);
                        $stmt->execute([$user['id'], (int)$data['wpm'], (int)$data['cpm'], (int)$data['accuracy'], (int)$data['mistakes'], (int)$data['time_taken'], $lang, $tester, $mistyped]);
                        $resultId = $pdo->lastInsertId();
                        unset($_SESSION['stashed_result']);
                        header("Location: result.php?id=" . $resultId);
                        exit;
                    } catch (PDOException $e) {
                        unset($_SESSION['stashed_result']);
                        header("Location: index.php");
                        exit;
                    }
                } else {
                    header("Location: index.php");
                    exit;
                }
            }
        }
        $error = "Invalid credentials.";
    } else {
        $error = "Database connection failed.";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Login</h3>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
