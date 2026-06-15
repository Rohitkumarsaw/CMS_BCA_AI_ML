<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

requireCSRF();

$action = $_POST['action'] ?? '';
$examId = (int)($_POST['id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($action === 'update_status' && $examId > 0) {
    $status = $_POST['status'] ?? '';
    $allowed = ['upcoming', 'active', 'completed'];
    if (!in_array($status, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE exams SET status = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$status, $examId, $userId]);
    echo json_encode(['status' => 'success', 'message' => 'Status updated to ' . ucfirst($status)]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
