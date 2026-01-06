<?php
$pageTitle = "Register";
require_once 'config/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            try {
                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$full_name, $username, $email, $hashed_password])) {
                    $newId = $pdo->lastInsertId();
                    $_SESSION['user_id'] = $newId;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    if (!empty($_SESSION['stashed_result'])) {
                        $data = $_SESSION['stashed_result'];
                        try {
                            $stmt2 = $pdo->prepare("INSERT INTO typing_results (user_id, wpm, cpm, accuracy, mistakes, time_taken, language, tester_name, mistyped_words) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $mistyped = isset($data['mistyped_words']) ? json_encode($data['mistyped_words']) : null;
                            $lang = isset($data['language']) ? $data['language'] : 'english';
                            $tester = $full_name ?: $username;
                            $stmt2->execute([$newId, (int)$data['wpm'], (int)$data['cpm'], (int)$data['accuracy'], (int)$data['mistakes'], (int)$data['time_taken'], $lang, $tester, $mistyped]);
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
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-5">
                <h3 class="text-center mb-4">Create Account</h3>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
