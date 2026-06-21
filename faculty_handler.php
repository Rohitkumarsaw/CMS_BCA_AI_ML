<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('faculty.php');
}

$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'add':
        requireCSRF();
        $name = sanitize($_POST['name'] ?? '');
        $subjects = sanitize($_POST['subjects'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $department = sanitize($_POST['department'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Faculty name is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO faculty (name, subjects, email, phone, department) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $subjects, $email, $phone, $department]);
        echo json_encode(['status' => 'success', 'message' => 'Faculty added successfully.']);
        notifyEmail('Faculty', 'added');
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $subjects = sanitize($_POST['subjects'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $department = sanitize($_POST['department'] ?? '');

        if ($id <= 0 || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE faculty SET name = ?, subjects = ?, email = ?, phone = ?, department = ? WHERE id = ?");
        $stmt->execute([$name, $subjects, $email, $phone, $department, $id]);
        echo json_encode(['status' => 'success', 'message' => 'Faculty updated successfully.']);
        notifyEmail('Faculty', 'updated');
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM faculty WHERE id = ?");
            $stmt->execute([$id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Faculty removed.']);
        notifyEmail('Faculty', 'deleted');
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
