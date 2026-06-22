<?php
require_once '../config/config.php';
require_once '../config/db_connection.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) { header("Location: ../login.php"); exit; }

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$period = $_GET['period'] ?? 'daily';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 50;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

switch ($period) {
    case 'daily':
        $where[] = "DATE(logged_at) = CURDATE()";
        break;
    case 'weekly':
        $where[] = "logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;
    case 'monthly':
        $where[] = "logged_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        break;
    case 'custom':
        if (!empty($startDate)) {
            $where[] = "logged_at >= ?";
            $params[] = $startDate . ' 00:00:00';
        }
        if (!empty($endDate)) {
            $where[] = "logged_at <= ?";
            $params[] = $endDate . ' 23:59:59';
        }
        break;
}

if (!empty($module)) {
    $where[] = "section_name = ?";
    $params[] = $module;
}

if (!empty($action)) {
    $where[] = "action_type = ?";
    $params[] = $action;
}

$whereClause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs $whereClause");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / $limit));

$dataStmt = $pdo->prepare("SELECT * FROM activity_logs $whereClause ORDER BY logged_at DESC LIMIT $limit OFFSET $offset");
$dataStmt->execute($params);
$activities = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$statsStmt = $pdo->prepare("SELECT action_type, COUNT(*) as cnt FROM activity_logs $whereClause GROUP BY action_type");
$statsStmt->execute($params);
$stats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

echo json_encode([
    'status' => 'success',
    'data' => $activities,
    'stats' => [
        'total' => $totalRecords,
        'actions' => $stats,
    ],
    'page' => $page,
    'totalPages' => $totalPages,
    'period' => $period
]);
