<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        category ENUM('award','hackathon','extracurricular','other') DEFAULT 'other',
        description TEXT,
        issuer VARCHAR(255),
        date_achieved DATE,
        link VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('achievements.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    case 'add':
        requireCSRF();
        $title = sanitize($_POST['title'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $description = sanitize($_POST['description'] ?? '');
        $issuer = sanitize($_POST['issuer'] ?? '');
        $dateAchieved = $_POST['date_achieved'] ?? '';
        $link = sanitize($_POST['link'] ?? '');

        if (empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Title is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO achievements (user_id, title, category, description, issuer, date_achieved, link) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $category, $description, $issuer, $dateAchieved, $link]);
        echo json_encode(['status' => 'success', 'message' => 'Achievement added.']);
        notifyEmail('Achievement', 'added');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Achievement', $pdo->lastInsertId(), 'Title: ' . $title);
        exit;

    case 'edit':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $category = $_POST['category'] ?? 'other';
        $description = sanitize($_POST['description'] ?? '');
        $issuer = sanitize($_POST['issuer'] ?? '');
        $dateAchieved = $_POST['date_achieved'] ?? '';
        $link = sanitize($_POST['link'] ?? '');

        if ($id <= 0 || empty($title)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE achievements SET title = ?, category = ?, description = ?, issuer = ?, date_achieved = ?, link = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $category, $description, $issuer, $dateAchieved, $link, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Achievement updated.']);
        notifyEmail('Achievement', 'updated');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Updated', 'Achievement', $id, 'Title: ' . $title);
        exit;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM achievements WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Achievement removed.']);
        notifyEmail('Achievement', 'deleted');
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Deleted', 'Achievement', $id);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
