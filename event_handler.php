<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('event.php');
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add' || $action === 'update') {
    requireCSRF();
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? null;
    $type = $_POST['type'] ?? 'Other';
    $month = (int)($_POST['month'] ?? date('m'));
    $year = (int)($_POST['year'] ?? date('Y'));

    if (empty($title) || empty($date)) {
        setFlashMessage('danger', 'Title and date are required.');
    } elseif ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO events (user_id, event_name, date, time, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $date, $time ?: null, $type]);
        setFlashMessage('success', 'Event added successfully.');
        notifyEmail('Event', 'added');
    } else {
        $eventId = (int)($_POST['event_id'] ?? 0);
        if ($eventId > 0) {
            $stmt = $pdo->prepare("UPDATE events SET event_name = ?, date = ?, time = ?, type = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $date, $time ?: null, $type, $eventId, $userId]);
            setFlashMessage('success', 'Event updated successfully.');
        notifyEmail('Event', 'updated');
        }
    }

    redirect('event.php?month=' . $month . '&year=' . $year);
}

if ($action === 'delete_json') {
    requireCSRF();
    header('Content-Type: application/json');
    $eventId = (int)($_POST['id'] ?? 0);
    if ($eventId > 0) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Event deleted']);
        notifyEmail('Event', 'deleted');
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Invalid event']);
    exit;
}

if ($action === 'get') {
    header('Content-Type: application/json');
    $eventId = (int)($_POST['id'] ?? 0);
    if ($eventId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($event ?: ['status' => 'error', 'message' => 'Not found']);
        exit;
    }
    echo json_encode(['status' => 'error', 'message' => 'Invalid event']);
    exit;
}

redirect('event.php');
