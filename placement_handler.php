<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS placement_tracker (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        role VARCHAR(255),
        application_date DATE,
        status ENUM('applied','shortlisted','interviewed','selected','rejected') DEFAULT 'applied',
        round_details TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('placement.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'add':
        requireCSRF();
        $company = sanitize($_POST['company_name'] ?? '');
        $role = sanitize($_POST['role'] ?? '');
        $appDate = $_POST['application_date'] ?? '';
        $status = $_POST['status'] ?? 'applied';
        $roundDetails = sanitize($_POST['round_details'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');

        if (empty($company)) {
            echo json_encode(['status' => 'error', 'message' => 'Company name is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO placement_tracker (user_id, company_name, role, application_date, status, round_details, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $company, $role, $appDate, $status, $roundDetails, $notes]);
        echo json_encode(['status' => 'success', 'message' => 'Application added.']);
        notifyEmail('Placement', 'added');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Placement', $pdo->lastInsertId(), 'Company: ' . $company . ' - Role: ' . $role);
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $company = sanitize($_POST['company_name'] ?? '');
        $role = sanitize($_POST['role'] ?? '');
        $appDate = $_POST['application_date'] ?? '';
        $status = $_POST['status'] ?? 'applied';
        $roundDetails = sanitize($_POST['round_details'] ?? '');
        $notes = sanitize($_POST['notes'] ?? '');

        if ($id <= 0 || empty($company)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE placement_tracker SET company_name = ?, role = ?, application_date = ?, status = ?, round_details = ?, notes = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$company, $role, $appDate, $status, $roundDetails, $notes, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Application updated.']);
        notifyEmail('Placement', 'updated');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Updated', 'Placement', $id, 'Company: ' . $company . ' - Role: ' . $role);
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM placement_tracker WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Application removed.']);
        notifyEmail('Placement', 'deleted');
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Deleted', 'Placement', $id);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
