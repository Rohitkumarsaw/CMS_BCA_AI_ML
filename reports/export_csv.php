<?php
require_once '../config/config.php';
require_once '../config/db_connection.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) { header("Location: ../login.php"); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') { header('Location: ../dashboard.php'); exit; }

$period = $_GET['period'] ?? 'daily';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$module = $_GET['module'] ?? '';
$action = $_GET['action'] ?? '';

$where = []; $params = [];
switch ($period) {
    case 'daily': $where[] = "DATE(logged_at) = CURDATE()"; break;
    case 'weekly': $where[] = "logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
    case 'monthly': $where[] = "logged_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"; break;
    case 'custom':
        if (!empty($startDate)) { $where[] = "logged_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
        if (!empty($endDate)) { $where[] = "logged_at <= ?"; $params[] = $endDate . ' 23:59:59'; }
        break;
}
if (!empty($module)) { $where[] = "section_name = ?"; $params[] = $module; }
if (!empty($action)) { $where[] = "action_type = ?"; $params[] = $action; }
$whereClause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM activity_logs $whereClause ORDER BY logged_at DESC");
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="activity_report_' . $period . '_' . date('Y-m-d') . '.csv"');
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['Date & Time', 'User', 'Module', 'Action', 'Description']);
foreach ($activities as $row) {
    fputcsv($output, [
        date('Y-m-d h:i A', strtotime($row['logged_at'])),
        $row['user_name'],
        ucfirst($row['section_name']),
        $row['action_type'],
        $row['details'] ?? ''
    ]);
}
fclose($output);
