<?php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['unread' => 0, 'notifications' => []]);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Ensure notifications are synced
syncNotifications($pdo, $userId);

if ($action === 'mark_read') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
    }
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'mark_all_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    echo json_encode(['ok' => true]);
    exit;
}

// Return notifications
$unread = getUnreadNotificationCount($pdo, $userId);
$notifs = getNotifications($pdo, $userId);

// Format time for display
foreach ($notifs as &$n) {
    $ts = strtotime($n['created_at']);
    $diff = time() - $ts;
    if ($diff < 60) $n['created_at'] = 'Just now';
    elseif ($diff < 3600) $n['created_at'] = floor($diff / 60) . 'm ago';
    elseif ($diff < 86400) $n['created_at'] = floor($diff / 3600) . 'h ago';
    else $n['created_at'] = date('M d', $ts);
}

echo json_encode(['unread' => $unread, 'notifications' => $notifs]);
