<?php
$pageTitle = 'Announcements';
$extraCSS = ['style.css', 'announcement.css'];
$extraJS = ['main.js', 'announcement.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filter_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$filter_priority = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filter_type)) {
    $where .= " AND type = ?";
    $params[] = $filter_type;
}
if (!empty($filter_priority)) {
    $where .= " AND priority = ?";
    $params[] = $filter_priority;
}

$pagi = paginate($pdo, 'announcements', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM announcements $where ORDER BY FIELD(priority, 'High', 'Medium', 'Low'), created_at DESC" . $pagi['sql']);
$stmt->execute($params);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->prepare("SELECT type, COUNT(*) as count FROM announcements WHERE user_id = ? GROUP BY type ORDER BY type");
$countStmt->execute([$userId]);
$typeCounts = [];
while ($row = $countStmt->fetch()) {
    $typeCounts[$row['type']] = $row['count'];
}

$priorityColors = [
    'High' => 'danger',
    'Medium' => 'warning',
    'Low' => 'info'
];

$typeColors = [
    'Homework' => 'warning',
    'Exam' => 'danger',
    'Holiday' => 'success',
    'Event' => 'purple',
    'General' => 'secondary'
];

$typeIcons = [
    'Homework' => 'fas fa-book',
    'Exam' => 'fas fa-file-alt',
    'Holiday' => 'fas fa-umbrella-beach',
    'Event' => 'fas fa-calendar-day',
    'General' => 'fas fa-bullhorn'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-bullhorn me-2 text-primary"></i>Announcements</h4>
                    <p class="text-muted mb-0">View all your announcements</p>
                </div>
                <a href="add_announcement.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Announcement
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <?php
                $allTypes = ['Homework', 'Exam', 'Holiday', 'Event', 'General'];
                foreach ($allTypes as $type):
                    $count = $typeCounts[$type] ?? 0;
                ?>
                    <div class="col-lg col-md-4 col-6 mb-2">
                        <div class="card announcement-stat-card border-start border-4 border-<?= $typeColors[$type] ?>">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <i class="<?= $typeIcons[$type] ?> fa-2x me-3 text-<?= $typeColors[$type] ?>"></i>
                                    <div>
                                        <h6 class="mb-0 text-muted"><?= $type ?></h6>
                                        <h4 class="mb-0"><?= $count ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <?php foreach ($allTypes as $type): ?>
                                    <option value="<?= $type ?>" <?= $filter_type === $type ? 'selected' : '' ?>><?= $type ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>High</option>
                                <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="announcement.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($announcements)): ?>
            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#announcementGrid">
            </div>
                <div class="row" id="announcementGrid">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card announcement-card h-100 <?php echo $announcement['priority'] === 'High' ? 'high-priority' : ''; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <i class="<?= $typeIcons[$announcement['type']] ?? 'fas fa-bullhorn' ?> me-2 text-<?= $typeColors[$announcement['type']] ?? 'secondary' ?>"></i>
                                            <?= htmlspecialchars($announcement['title']) ?>
                                        </h5>
                                    </div>
                                    <div class="mb-3">
                                        <span class="badge bg-<?= $typeColors[$announcement['type']] ?? 'secondary' ?> me-1">
                                            <?= htmlspecialchars($announcement['type']) ?>
                                        </span>
                                        <span class="badge bg-<?= $priorityColors[$announcement['priority']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($announcement['priority']) ?> Priority
                                        </span>
                                    </div>
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars($announcement['message']) ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= formatDateTime($announcement['created_at']) ?>
                                        </small>
                                        <div>
                                            <a href="edit_announcement.php?id=<?= $announcement['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=announcement&id=<?= $announcement['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=announcement&id=<?= $announcement['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this announcement"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No announcements found</p>
                        <a href="add_announcement.php" class="btn btn-primary mt-2">
                            <i class="fas fa-plus me-1"></i>Add Your First Announcement
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
