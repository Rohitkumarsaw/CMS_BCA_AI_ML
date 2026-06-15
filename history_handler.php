<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'csv') {
    // CSV Export — stream directly to browser
    $stmt = $pdo->prepare("SELECT user_name, action_type, section_name, details, logged_at FROM activity_logs WHERE user_id = ? ORDER BY logged_at DESC");
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['User', 'Action', 'Section', 'Details', 'Timestamp']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['user_name'], $r['action_type'], $r['section_name'], $r['details'], $r['logged_at']]);
    }
    fclose($out);
    exit;
}

// Default: JSON list
header('Content-Type: application/json');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs WHERE user_id = ?");
$countStmt->execute([$userId]);
$total = (int)$countStmt->fetchColumn();

$stmt = $pdo->prepare("SELECT id, user_name, action_type, section_name, details, logged_at FROM activity_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute([$userId]);
$logs = $stmt->fetchAll();

echo json_encode(['status'=>'success', 'logs'=>$logs, 'total'=>$total, 'page'=>$page, 'hasMore'=>($offset + $limit < $total)]);