<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$_SESSION['stashed_result'] = $data ?: [];
echo json_encode(['success' => true]);
