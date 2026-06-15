<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('event.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    requireCSRF();
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? null;
    $type = $_POST['type'] ?? 'Other';
    $month = (int)($_POST['month'] ?? date('m'));
    $year = (int)($_POST['year'] ?? date('Y'));

    if (empty($title) || empty($date)) {
        $_SESSION['flash_error'] = 'Title and date are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO events (user_id, event_name, date, time, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $date, $time ?: null, $type]);
        $_SESSION['flash_success'] = 'Event added successfully.';
    }

    redirect('event.php?month=' . $month . '&year=' . $year);
}

redirect('event.php');
