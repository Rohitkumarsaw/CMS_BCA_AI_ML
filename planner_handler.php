<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('planner.php');
}

$action = $_POST['action'] ?? '';
header('Content-Type: application/json');

switch ($action) {

    // ── Shopping Planner ──

    case 'add_shopping':
        requireCSRF();
        $itemName = sanitize($_POST['item_name'] ?? '');
        $reasonWhy = sanitize($_POST['reason_why'] ?? '');
        $purposeWork = sanitize($_POST['purpose_work'] ?? '');
        $targetDate = $_POST['target_date'] ?? '';

        if (empty($itemName)) {
            echo json_encode(['status' => 'error', 'message' => 'Item name is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO shopping_planner (user_id, item_name, reason_why, purpose_work, target_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $itemName, $reasonWhy, $purposeWork, $targetDate ?: null]);
        echo json_encode(['status' => 'success', 'message' => 'Item added to shopping list.']);
        notifyEmail('Planner', 'added');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Shopping Planner', $pdo->lastInsertId(), 'Item: ' . $itemName);
        exit;

    case 'edit_shopping':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $itemName = sanitize($_POST['item_name'] ?? '');
        $reasonWhy = sanitize($_POST['reason_why'] ?? '');
        $purposeWork = sanitize($_POST['purpose_work'] ?? '');
        $targetDate = $_POST['target_date'] ?? '';

        if ($id <= 0 || empty($itemName)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE shopping_planner SET item_name = ?, reason_why = ?, purpose_work = ?, target_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$itemName, $reasonWhy, $purposeWork, $targetDate ?: null, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Shopping item updated.']);
        notifyEmail('Planner', 'updated');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Updated', 'Shopping Planner', $id, 'Item: ' . $itemName);
        exit;

    case 'delete_shopping':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM shopping_planner WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Item removed from shopping list.']);
        notifyEmail('Planner', 'deleted');
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Deleted', 'Shopping Planner', $id);
        exit;

    case 'toggle_shopping':
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT status FROM shopping_planner WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        $item = $stmt->fetch();
        if ($item) {
            $newStatus = $item['status'] === 'purchased' ? 'pending' : 'purchased';
            $stmt = $pdo->prepare("UPDATE shopping_planner SET status = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$newStatus, $id, $userId]);
            echo json_encode(['status' => 'success', 'message' => $newStatus === 'purchased' ? 'Marked as purchased.' : 'Marked as pending.']);
            notifyEmail('Planner', 'status updated');
            logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Updated', 'Shopping Planner', $id, 'Toggled status to: ' . $newStatus);
            exit;
        }
        echo json_encode(['status' => 'error', 'message' => 'Item not found.']);
        exit;

    // ── Current Inventory ──

    case 'add_inventory':
        requireCSRF();
        $itemName = sanitize($_POST['item_name'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 1);
        $availability = $_POST['availability_status'] ?? 'Available';

        if (empty($itemName) || $quantity < 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO current_inventory (user_id, item_name, quantity, availability_status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $itemName, $quantity, $availability]);
        echo json_encode(['status' => 'success', 'message' => 'Inventory item added.']);
        notifyEmail('Planner', 'added');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Current Inventory', $pdo->lastInsertId(), 'Item: ' . $itemName . ' - Qty: ' . $quantity);
        exit;

    case 'edit_inventory':
        requireCSRF();
        $id = (int)($_POST['id'] ?? 0);
        $itemName = sanitize($_POST['item_name'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 1);
        $availability = $_POST['availability_status'] ?? 'Available';

        if ($id <= 0 || empty($itemName)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE current_inventory SET item_name = ?, quantity = ?, availability_status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$itemName, $quantity, $availability, $id, $userId]);
        echo json_encode(['status' => 'success', 'message' => 'Inventory item updated.']);
        notifyEmail('Planner', 'updated');
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Updated', 'Current Inventory', $id, 'Item: ' . $itemName);
        exit;

    case 'delete_inventory':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM current_inventory WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Inventory item removed.']);
        notifyEmail('Planner', 'deleted');
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Deleted', 'Current Inventory', $id);
        exit;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        exit;
}
