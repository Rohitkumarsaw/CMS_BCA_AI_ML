<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS meeting_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        platform ENUM('zoom','google_meet','microsoft_teams','other') DEFAULT 'zoom',
        url VARCHAR(500) NOT NULL,
        description TEXT,
        meeting_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('meetings.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'add':
        requireCSRF();
        $title = sanitize($_POST['title'] ?? '');
        $platform = $_POST['platform'] ?? 'zoom';
        $url = sanitize($_POST['url'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $meetingDate = $_POST['meeting_date'] ?? '';

        if (empty($title) || empty($url)) {
            echo json_encode(['status' => 'error', 'message' => 'Title and URL are required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO meeting_links (user_id, title, platform, url, description, meeting_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $platform, $url, $description, $meetingDate]);
        echo json_encode(['status' => 'success', 'message' => 'Meeting link added.']);
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $platform = $_POST['platform'] ?? 'zoom';
        $url = sanitize($_POST['url'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $meetingDate = $_POST['meeting_date'] ?? '';

        if ($id <= 0 || empty($title) || empty($url)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE meeting_links SET title = ?, platform = ?, url = ?, description = ?, meeting_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $platform, $url, $description, $meetingDate, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Meeting link updated.']);
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM meeting_links WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Meeting link removed.']);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
