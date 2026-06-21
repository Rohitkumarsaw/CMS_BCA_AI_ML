<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('routine.php');
}

$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'add':
        requireCSRF();
        $taskName = sanitize($_POST['task_name'] ?? '');
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $category = $_POST['category'] ?? 'study';
        $taskDate = date('Y-m-d');

        if (empty($taskName) || empty($startTime) || empty($endTime)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        } elseif ($startTime >= $endTime) {
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO routine_tasks (user_id, task_name, start_time, end_time, category, task_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $taskName, $startTime, $endTime, $category, $taskDate]);
            echo json_encode(['status' => 'success', 'message' => 'Task added successfully.']);
            notifyEmail('Routine Task', 'added');
        }
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $taskName = sanitize($_POST['task_name'] ?? '');
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $category = $_POST['category'] ?? 'study';

        if ($id <= 0 || empty($taskName) || empty($startTime) || empty($endTime)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        } elseif ($startTime >= $endTime) {
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
        } else {
            $stmt = $pdo->prepare("UPDATE routine_tasks SET task_name = ?, start_time = ?, end_time = ?, category = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$taskName, $startTime, $endTime, $category, $id, $userId]);
            echo json_encode(['status' => 'success', 'message' => 'Task updated successfully.']);
            notifyEmail('Routine Task', 'updated');
        }
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM routine_tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            echo json_encode(['status' => 'success', 'message' => 'Task deleted successfully.']);
            notifyEmail('Routine Task', 'deleted');
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid task ID.']);
        }
        exit;

    case 'toggle':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT status FROM routine_tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $task = $stmt->fetch();
        if ($task) {
            $newStatus = ($task['status'] === 'completed') ? 'pending' : 'completed';
            $stmt = $pdo->prepare("UPDATE routine_tasks SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$newStatus, $id, $userId]);
            echo json_encode(['status' => 'success', 'message' => 'Task status updated.', 'newStatus' => $newStatus]);
            notifyEmail('Routine Task', 'status updated');
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
