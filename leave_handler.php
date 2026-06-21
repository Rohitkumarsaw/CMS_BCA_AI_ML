<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('leave.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'apply':
        requireCSRF();
        $subject = sanitize($_POST['subject'] ?? '');
        $reason = sanitize($_POST['reason'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        if (empty($subject) || empty($reason) || empty($startDate) || empty($endDate)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }
        if ($startDate > $endDate) {
            echo json_encode(['status' => 'error', 'message' => 'End date must be after start date.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO leave_applications (user_id, subject, reason, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $subject, $reason, $startDate, $endDate]);
        echo json_encode(['status' => 'success', 'message' => 'Leave application submitted.']);
        notifyEmail('Leave', 'submitted');
        exit;

    case 'cancel':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE leave_applications SET status = 'rejected', admin_remark = 'Cancelled by student' WHERE id = ? AND user_id = ? AND status = 'pending'");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Leave application cancelled.']);
        notifyEmail('Leave', 'cancelled');
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $subject = sanitize($_POST['subject'] ?? '');
        $reason = sanitize($_POST['reason'] ?? '');
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        if ($id <= 0 || empty($subject) || empty($reason) || empty($startDate) || empty($endDate)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            exit;
        }
        if ($startDate > $endDate) {
            echo json_encode(['status' => 'error', 'message' => 'End date must be after start date.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE leave_applications SET subject = ?, reason = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$subject, $reason, $startDate, $endDate, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Leave application updated.']);
        notifyEmail('Leave', 'updated');
        exit;

    // Admin actions
    case 'approve':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE leave_applications SET status = 'approved', reviewed_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Leave approved.']);
        notifyEmail('Leave', 'approved');
        exit;

    case 'reject':
        $id = (int)($_POST['id'] ?? 0);
        $remark = sanitize($_POST['remark'] ?? '');
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE leave_applications SET status = 'rejected', admin_remark = ?, reviewed_at = NOW() WHERE id = ?");
            $stmt->execute([$remark, $id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Leave rejected.']);
        notifyEmail('Leave', 'rejected');
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
