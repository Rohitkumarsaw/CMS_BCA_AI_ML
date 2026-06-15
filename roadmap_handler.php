<?php
try {
    require_once 'config/db_connection.php';
    require_once 'includes/functions.php';
    requireLogin();

    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_name'] ?? 'User';
    $action = $_POST['action'] ?? '';
    header('Content-Type: application/json');

    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS academic_roadmaps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            roadmap_title VARCHAR(255) NOT NULL,
            total_nodes INT DEFAULT 0,
            completed_nodes INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS roadmap_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            roadmap_id INT NOT NULL,
            item_title VARCHAR(255) NOT NULL,
            is_completed TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (roadmap_id) REFERENCES academic_roadmaps(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (PDOException $e) {}

    switch ($action) {
    case 'add_roadmap':
        $title = sanitize($_POST['title'] ?? '');
        if (empty($title)) {
            echo json_encode(['status'=>'error','message'=>'Title is required.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO academic_roadmaps (user_id, roadmap_title, total_nodes, completed_nodes) VALUES (?, ?, 0, 0)");
        $stmt->execute([$userId, $title]);
        $id = (int)$pdo->lastInsertId();
        logActivity($pdo, $userId, $userName, 'Created Roadmap', 'Academic Roadmap', $id, 'Created roadmap: ' . $title);
        echo json_encode(['status'=>'success','message'=>'Roadmap created.', 'id'=>$id]);
        exit;

    case 'edit_roadmap':
        $id = (int)($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        if ($id <= 0 || empty($title)) {
            echo json_encode(['status'=>'error','message'=>'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE academic_roadmaps SET roadmap_title = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $id, $userId]);
        if ($stmt->rowCount() === 0) {
            echo json_encode(['status'=>'error','message'=>'Roadmap not found.']);
            exit;
        }
        logActivity($pdo, $userId, $userName, 'Edited Roadmap', 'Academic Roadmap', $id, 'Renamed to: ' . $title);
        echo json_encode(['status'=>'success','message'=>'Roadmap updated.']);
        exit;

    case 'add_item':
        $roadmapId = (int)($_POST['roadmap_id'] ?? 0);
        $itemTitle = sanitize($_POST['item_title'] ?? '');
        if ($roadmapId <= 0 || empty($itemTitle)) {
            echo json_encode(['status'=>'error','message'=>'Invalid data.']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO roadmap_items (roadmap_id, item_title) VALUES (?, ?)");
        $stmt->execute([$roadmapId, $itemTitle]);

        // Update total_nodes
        $pdo->prepare("UPDATE academic_roadmaps SET total_nodes = (SELECT COUNT(*) FROM roadmap_items WHERE roadmap_id = ?) WHERE id = ? AND user_id = ?")
            ->execute([$roadmapId, $roadmapId, $userId]);

        $itemId = (int)$pdo->lastInsertId();
        // Fetch updated counts
        $rm = $pdo->prepare("SELECT total_nodes, completed_nodes FROM academic_roadmaps WHERE id = ?");
        $rm->execute([$roadmapId]);
        $r = $rm->fetch();
        echo json_encode(['status'=>'success','message'=>'Item added.', 'item_id'=>$itemId, 'total_nodes'=>$r['total_nodes'], 'completed_nodes'=>$r['completed_nodes']]);
        exit;

    case 'toggle_item':
        $itemId = (int)($_POST['item_id'] ?? 0);
        $completed = (int)($_POST['completed'] ?? 0);
        if ($itemId <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid item.']);
            exit;
        }
        // Get roadmap_id first
        $gi = $pdo->prepare("SELECT roadmap_id FROM roadmap_items WHERE id = ?");
        $gi->execute([$itemId]);
        $ri = $gi->fetch();
        if (!$ri) {
            echo json_encode(['status'=>'error','message'=>'Item not found.']);
            exit;
        }
        $roadmapId = (int)$ri['roadmap_id'];
        $pdo->prepare("UPDATE roadmap_items SET is_completed = ? WHERE id = ?")
            ->execute([$completed, $itemId]);

        // Update completed_nodes
        $pdo->prepare("UPDATE academic_roadmaps SET completed_nodes = (SELECT COUNT(*) FROM roadmap_items WHERE roadmap_id = ? AND is_completed = 1) WHERE id = ? AND user_id = ?")
            ->execute([$roadmapId, $roadmapId, $userId]);

        $rm = $pdo->prepare("SELECT total_nodes, completed_nodes FROM academic_roadmaps WHERE id = ?");
        $rm->execute([$roadmapId]);
        $r = $rm->fetch();
        echo json_encode(['status'=>'success', 'total_nodes'=>$r['total_nodes'], 'completed_nodes'=>$r['completed_nodes']]);
        exit;

    case 'delete_roadmap':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM academic_roadmaps WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $userId]);
            logActivity($pdo, $userId, $userName, 'Deleted Roadmap', 'Academic Roadmap', $id);
        }
        echo json_encode(['status'=>'success','message'=>'Roadmap deleted.']);
        exit;

    case 'edit_item':
        $itemId = (int)($_POST['item_id'] ?? 0);
        $itemTitle = sanitize($_POST['item_title'] ?? '');
        if ($itemId <= 0 || empty($itemTitle)) {
            echo json_encode(['status'=>'error','message'=>'Invalid data.']);
            exit;
        }
        // Verify ownership via roadmap -> user_id
        $chk = $pdo->prepare("SELECT ri.roadmap_id FROM roadmap_items ri JOIN academic_roadmaps ar ON ar.id = ri.roadmap_id WHERE ri.id = ? AND ar.user_id = ?");
        $chk->execute([$itemId, $userId]);
        if (!$chk->fetch()) {
            echo json_encode(['status'=>'error','message'=>'Item not found.']);
            exit;
        }
        $pdo->prepare("UPDATE roadmap_items SET item_title = ? WHERE id = ?")->execute([$itemTitle, $itemId]);
        logActivity($pdo, $userId, $userName, 'Edited Item', 'Academic Roadmap', $itemId, 'Renamed item to: ' . $itemTitle);
        echo json_encode(['status'=>'success','message'=>'Item updated.']);
        exit;

    case 'delete_item':
        $itemId = (int)($_POST['item_id'] ?? 0);
        if ($itemId <= 0) {
            echo json_encode(['status'=>'error','message'=>'Invalid item.']);
            exit;
        }
        // Get roadmap_id before deleting
        $gi = $pdo->prepare("SELECT roadmap_id FROM roadmap_items WHERE id = ?");
        $gi->execute([$itemId]);
        $ri = $gi->fetch();
        if (!$ri) {
            echo json_encode(['status'=>'error','message'=>'Item not found.']);
            exit;
        }
        $roadmapId = (int)$ri['roadmap_id'];
        // Verify ownership
        $chk = $pdo->prepare("SELECT id FROM academic_roadmaps WHERE id = ? AND user_id = ?");
        $chk->execute([$roadmapId, $userId]);
        if (!$chk->fetch()) {
            echo json_encode(['status'=>'error','message'=>'Permission denied.']);
            exit;
        }
        $delStmt = $pdo->prepare("DELETE FROM roadmap_items WHERE id = ?");
        $delStmt->execute([$itemId]);
        // Recalculate counts
        $pdo->prepare("UPDATE academic_roadmaps SET total_nodes = (SELECT COUNT(*) FROM roadmap_items WHERE roadmap_id = ?), completed_nodes = (SELECT COUNT(*) FROM roadmap_items WHERE roadmap_id = ? AND is_completed = 1) WHERE id = ?")
            ->execute([$roadmapId, $roadmapId, $roadmapId]);
        $rm = $pdo->prepare("SELECT total_nodes, completed_nodes FROM academic_roadmaps WHERE id = ?");
        $rm->execute([$roadmapId]);
        $r = $rm->fetch();
        logActivity($pdo, $userId, $userName, 'Deleted Item', 'Academic Roadmap', $itemId);
        echo json_encode(['status'=>'success', 'total_nodes'=>$r['total_nodes'], 'completed_nodes'=>$r['completed_nodes']]);
        exit;

    default:
        echo json_encode(['status'=>'error','message'=>'Invalid action.']);
        exit;
}
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status'=>'error','message'=>'Server error: ' . $e->getMessage()]);
    exit;
}