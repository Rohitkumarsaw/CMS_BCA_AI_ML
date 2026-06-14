<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Homework';
$extraCSS = ['style.css', 'homework.css'];
$extraJS = ['main.js', 'homework.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];

$filter_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : $user_semester;
$filter_subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if ($filter_semester > 0) {
    $where .= " AND semester = ?";
    $params[] = $filter_semester;
}
if (!empty($filter_subject)) {
    $where .= " AND subject = ?";
    $params[] = $filter_subject;
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'homework', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM homework $where ORDER BY due_date ASC" . $pagi['sql']);
$stmt->execute($params);
$homeworkRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($filter_semester);

$stmt = $pdo->prepare("SELECT subject, COUNT(*) as count FROM homework $where GROUP BY subject ORDER BY subject");
$stmt->execute($params);
$subjectCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-book me-2 text-primary"></i>Homework</h4>
                    <p class="text-muted mb-0">Manage your homework assignments</p>
                </div>
                <a href="add_homework.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Homework
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Homework</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="0">All Semesters</option>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filter_semester == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject ?>" <?= $filter_subject === $subject ? 'selected' : '' ?>><?= $subject ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Not Submitted" <?= $filter_status === 'Not Submitted' ? 'selected' : '' ?>>Not Submitted</option>
                                <option value="Submitted" <?= $filter_status === 'Submitted' ? 'selected' : '' ?>>Submitted</option>
                                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="homework.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Homework List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Description</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($homeworkRecords)): ?>
                                    <?php foreach ($homeworkRecords as $index => $hw): ?>
                                        <?php
                                        $isOverdue = (strtotime($hw['due_date']) < strtotime(date('Y-m-d'))) && strtolower($hw['status']) !== 'submitted';
                                        $statusClass = 'secondary';
                                        if (strtolower($hw['status']) === 'submitted') $statusClass = 'success';
                                        elseif (strtolower($hw['status']) === 'pending') $statusClass = 'warning';
                                        elseif ($isOverdue) $statusClass = 'danger';
                                        ?>
                                        <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($hw['title']) ?></strong></td>
                                            <td><?= htmlspecialchars($hw['subject']) ?></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($hw['description']) ?>">
                                                    <?= htmlspecialchars($hw['description']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($hw['due_date']) ?>
                                                <?php if ($isOverdue): ?>
                                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Overdue</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?php if (strtolower($hw['status']) === 'submitted'): ?>
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    <?php elseif ($isOverdue): ?>
                                                        <i class="fas fa-exclamation-circle me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($hw['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($hw['file_path'])): ?>
                                                    <a href="<?= htmlspecialchars($hw['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_homework.php?id=<?= $hw['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=homework&id=<?= $hw['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=homework&id=<?= $hw['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this homework"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No homework found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($subjectCounts)): ?>
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Subject-wise Homework Count</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($subjectCounts as $sc): ?>
                            <div class="col-md-4 col-lg-3">
                                <div class="card border h-100">
                                    <div class="card-body text-center">
                                        <h4 class="text-primary mb-1"><?= $sc['count'] ?></h4>
                                        <p class="mb-0 small text-muted"><?= htmlspecialchars($sc['subject']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>