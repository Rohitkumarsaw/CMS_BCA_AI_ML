<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Assignments';
$extraCSS = ['style.css', 'assignment.css'];
$extraJS = ['main.js', 'assignment.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];

$filter_subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($filter_subject)) {
    $where .= " AND subject = ?";
    $params[] = $filter_subject;
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'assignments', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM assignments $where ORDER BY due_date ASC" . $pagi['sql']);
$stmt->execute($params);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($user_semester);

$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as submitted, SUM(CASE WHEN status = 'Not Submitted' THEN 1 ELSE 0 END) as not_submitted FROM assignments WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-tasks me-2 text-primary"></i>Assignments</h4>
                    <p class="text-muted mb-0">Manage your assignments and submissions</p>
                </div>
                <a href="add_assignment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Assignment
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= $stats['total'] ?></h3>
                            <p class="mb-0 text-muted">Total Assignments</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= $stats['submitted'] ?></h3>
                            <p class="mb-0 text-muted">Submitted</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body text-center">
                            <h3 class="text-danger mb-1"><?= $stats['not_submitted'] ?></h3>
                            <p class="mb-0 text-muted">Not Submitted</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Assignments</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject ?>" <?= $filter_subject === $subject ? 'selected' : '' ?>><?= $subject ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Submitted" <?= $filter_status === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                <option value="Not Submitted" <?= $filter_status === 'Not Submitted' ? 'selected' : '' ?>>Not Submitted</option>
                                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="assignment.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Assignments List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assignments)): ?>
                                    <?php foreach ($assignments as $index => $assignment): ?>
                                        <?php
                                        $isOverdue = (strtotime($assignment['due_date']) < strtotime(date('Y-m-d'))) && strtolower($assignment['status']) !== 'submitted';
                                        $statusClass = 'secondary';
                                        if ($assignment['status'] === 'Submitted') $statusClass = 'success';
                                        elseif ($assignment['status'] === 'Not Submitted') $statusClass = 'danger';
                                        elseif ($assignment['status'] === 'Pending') $statusClass = 'warning';
                                        ?>
                                        <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($assignment['title']) ?></strong></td>
                                            <td><?= htmlspecialchars($assignment['subject']) ?></td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($assignment['due_date']) ?>
                                                <?php if ($isOverdue): ?>
                                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?php if ($assignment['status'] === 'Submitted'): ?>
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    <?php elseif ($assignment['status'] === 'Not Submitted'): ?>
                                                        <i class="fas fa-times-circle me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($assignment['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($assignment['file_path'])): ?>
                                                    <a href="<?= htmlspecialchars($assignment['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=assignment&id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=assignment&id=<?= $assignment['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this assignment"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No assignments found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
