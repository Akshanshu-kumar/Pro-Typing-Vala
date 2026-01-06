<?php
require_once 'config/db.php';
// session_start();

if (!isset($_SESSION['user_id'])) {
    die("Please login to view certificate.");
}

$result_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch result and verify ownership
$stmt = $pdo->prepare("
    SELECT tr.*, u.username, u.email, u.is_premium, COALESCE(u.full_name, u.username) AS display_name
    FROM typing_results tr 
    JOIN users u ON tr.user_id = u.id 
    WHERE tr.id = ? AND tr.user_id = ?
");
$stmt->execute([$result_id, $user_id]);
$result = $stmt->fetch();

if (!$result) {
    die("Certificate not found or access denied.");
}

// Basic validation for certificate eligibility (premium bypass)
if ((int)($result['is_premium'] ?? 0) !== 1 && ($result['wpm'] < 10 || $result['accuracy'] < 80)) {
    die("Score too low for certificate generation. Keep practicing!");
}

// Resolve display date safely (prefer test_date, then created_at, else today)
$rawDate = null;
if (!empty($result['test_date']) && $result['test_date'] !== '0000-00-00 00:00:00') {
    $rawDate = $result['test_date'];
} elseif (!empty($result['created_at']) && $result['created_at'] !== '0000-00-00 00:00:00') {
    $rawDate = $result['created_at'];
}
$displayDate = $rawDate ? date("F j, Y", strtotime($rawDate)) : date("F j, Y");

// Optional: record certificate issuance if a certificates table exists
try {
    $pdo->query("SELECT 1 FROM certificates LIMIT 1");
    $code = 'CERT-' . str_pad($result['id'], 6, '0', STR_PAD_LEFT);
    $ins = $pdo->prepare("INSERT INTO certificates (user_id, result_id, issued_at, certificate_code) VALUES (?, ?, NOW(), ?)");
    $ins->execute([ (int)$result['user_id'], (int)$result['id'], $code ]);
} catch (PDOException $e) { /* ignore if table doesn't exist */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        @page { size: landscape; margin: 0; }
        body { margin: 0; padding: 0; font-family: 'Georgia', serif; background: #f0f0f0; }
        .certificate-container {
            width: 1000px;
            height: 700px;
            margin: 20px auto;
            background: #fff;
            border: 10px solid #333;
            position: relative;
            padding: 40px;
            box-sizing: border-box;
            background-image: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('assets/img/cert-bg.png');
            z-index: 1;
        }
        .header { text-align: center; margin-bottom: 50px; }
        .title { font-size: 60px; font-weight: bold; color: #c49b3d; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 24px; color: #555; }
        
        .content { text-align: center; font-size: 20px; line-height: 1.6; }
        .name { font-size: 40px; font-weight: bold; color: #000; border-bottom: 2px solid #c49b3d; display: inline-block; padding: 0 20px; margin: 10px 0; }
        .stats { margin-top: 30px; display: flex; justify-content: center; gap: 40px; }
        .stat-box { text-align: center; border: 2px solid #ddd; padding: 15px 30px; border-radius: 8px; }
        .stat-val { font-size: 30px; font-weight: bold; color: #333; }
        .stat-label { font-size: 14px; text-transform: uppercase; color: #777; }
        
        .footer { position: absolute; bottom: 40px; left: 40px; right: 40px; display: flex; justify-content: space-between; align-items: flex-end; }
        .date { font-size: 16px; color: #555; }
        .signature { text-align: center; padding-top: 10px; width: 200px; }
        
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 10000; pointer-events: auto; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px; font-size: 16px; }
        
        @media print {
            .no-print { display: none; }
            body { background: none; }
            .certificate-container { margin: 0; border: none; width: 100%; height: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Download / Print PDF</button>
    <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #333;">Back to Home</a>
</div>

<div class="certificate-container">
    <div class="header">
        <div class="subtitle">CERTIFICATE OF ACHIEVEMENT</div>
        <h1 class="title">TYPING MASTERY</h1>
    </div>
    
    <div class="content">
        <p>This is to certify that</p>
        <div class="name"><?php echo htmlspecialchars($result['display_name']); ?></div>
        <p>has successfully completed the typing test assessment on</p>
        <p><strong><?php echo $displayDate; ?></strong></p>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-val"><?php echo $result['wpm']; ?></div>
                <div class="stat-label">WPM Speed</div>
            </div>
            <div class="stat-box">
                <div class="stat-val"><?php echo $result['accuracy']; ?>%</div>
                <div class="stat-label">Accuracy</div>
            </div>
             <div class="stat-box">
                <div class="stat-val"><?php echo $result['language']; ?></div>
                <div class="stat-label">Language</div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="date">
            Date Issued: <?php echo date("d/m/Y"); ?><br>
            Certificate ID: TYP-<?php echo str_pad($result['id'], 6, '0', STR_PAD_LEFT); ?>
        </div>
        <div class="signature">
            <img src="assets/img/sign.jpg" alt="Signature" style="height: 40px; margin: 0 auto 5px;"><br>
            Authorized Signature
        </div>
    </div>
</div>

</body>
</html>
