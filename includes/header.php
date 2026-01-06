<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section{background:linear-gradient(135deg,#0d6efd,#6610f2);color:#fff;padding:60px 0;margin-bottom:40px}
        .stats-box{background:#fff;border-radius:12px;padding:30px;box-shadow:0 10px 25px rgba(0,0,0,.08)}
        .stats-value{font-size:48px;font-weight:800}
        .feature-card{border:0;box-shadow:0 10px 25px rgba(0,0,0,.08)}
        .hover-card:hover{transform:translateY(-4px);transition:transform .2s ease}
        .font-krutidev{font-family:"Kruti Dev 010", "Mangal", sans-serif}
        .font-mangal{font-family:"Mangal", sans-serif}
        .char.current{background:#fff3cd}
        .char.correct{color:#198754}
        .char.incorrect{color:#dc3545}

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] body { background-color: #212529; color: #f8f9fa; }
        [data-bs-theme="dark"] .stats-box { background: #2b3035; color: #fff; }
        [data-bs-theme="dark"] .text-dark { color: #f8f9fa !important; }
        [data-bs-theme="dark"] .text-muted { color: #adb5bd !important; }
        [data-bs-theme="dark"] .card { background-color: #2b3035; border-color: #373b3e; }
        [data-bs-theme="dark"] .card-header { border-bottom-color: #373b3e; }
        [data-bs-theme="dark"] .card-header.bg-white { background-color: #2c3034 !important; color: #fff; }
        [data-bs-theme="dark"] .badge.bg-light { background-color: #343a40 !important; color: #f8f9fa !important; border-color: #495057 !important; }
        [data-bs-theme="dark"] .table { color: #f8f9fa; border-color: #373b3e; }
        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * { color: #f8f9fa; box-shadow: inset 0 0 0 9999px rgba(255,255,255,0.05); }
        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(even) > * { color: #f8f9fa; box-shadow: none; }
        
        /* Specific Fixes */
        [data-bs-theme="dark"] #liveStats { background-color: #2b3035 !important; border: 1px solid #373b3e; }
        [data-bs-theme="dark"] #liveStats .border-end { border-right-color: #373b3e !important; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>test-settings.php">Typing Test</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>tutor.php">Tutor</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>converter.php">Converter</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>subscription.php">Subscription</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>history.php">History</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button id="themeToggle" class="btn btn-outline-light"><i class="bi bi-moon-fill"></i></button>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php 
                        // Fetch user info for profile dropdown
                        $userInfo = null;
                        try {
                            require_once __DIR__ . '/../config/db.php';
                            if ($pdo) {
                                $stmtHeaderUser = $pdo->prepare("SELECT username, email, COALESCE(full_name, username) AS full_name, is_premium FROM users WHERE id = ?");
                                $stmtHeaderUser->execute([ (int)$_SESSION['user_id'] ]);
                                $userInfo = $stmtHeaderUser->fetch();
                            }
                        } catch (Exception $e) { /* ignore */ }
                    ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($userInfo['full_name'] ?? 'Profile'); ?>
                            <?php if (!empty($userInfo['is_premium'])): ?>
                                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-stars"></i> Premium</span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2">
                                <div class="small text-muted">Name</div>
                                <div><?php echo htmlspecialchars($userInfo['full_name'] ?? ''); ?></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="px-3 py-2">
                                <div class="small text-muted">Username</div>
                                <div><?php echo htmlspecialchars($userInfo['username'] ?? ''); ?></div>
                            </li>
                            <li class="px-3 py-2">
                                <div class="small text-muted">Email</div>
                                <div><?php echo htmlspecialchars($userInfo['email'] ?? ''); ?></div>
                            </li>
                            <?php if (!empty($userInfo['is_premium'])): ?>
                                <li class="px-3 py-2">
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-check"></i> Premium Active</span>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>subscription.php">Manage Subscription</a></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-outline-light">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<div class="container my-4">
