<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Lab Work';
$extraCSS = ['style.css', 'lab.css'];
$extraJS = ['main.js', 'lab.js'];

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

$pagi = paginate($pdo, 'labs', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM labs $where ORDER BY date DESC" . $pagi['sql']);
$stmt->execute($params);
$labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($user_semester);

$stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed FROM labs WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
$completionPercentage = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-flask me-2 text-primary"></i>Lab Work</h4>
                    <p class="text-muted mb-0">Manage your lab assignments and reports</p>
                </div>
                <a href="add_lab.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Lab
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= $stats['total'] ?></h3>
                            <p class="mb-0 text-muted">Total Labs</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= $stats['completed'] ?></h3>
                            <p class="mb-0 text-muted">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-1"><?= $completionPercentage ?>%</h3>
                            <p class="mb-0 text-muted">Completion</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Labs</h6>
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
                                <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Not Started" <?= $filter_status === 'Not Started' ? 'selected' : '' ?>>Not Started</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="lab.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Lab Work List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Lab Name</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Report</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($labs)): ?>
                                    <?php foreach ($labs as $index => $lab): ?>
                                        <?php
                                        $statusClass = 'secondary';
                                        if ($lab['status'] === 'Completed') $statusClass = 'success';
                                        elseif ($lab['status'] === 'In Progress') $statusClass = 'warning';
                                        elseif ($lab['status'] === 'Not Started') $statusClass = 'secondary';
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($lab['lab_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($lab['subject']) ?></td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($lab['date']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?php if ($lab['status'] === 'Completed'): ?>
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    <?php elseif ($lab['status'] === 'In Progress'): ?>
                                                        <i class="fas fa-spinner me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($lab['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($lab['report_path'])): ?>
                                                    <a href="<?= htmlspecialchars($lab['report_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No report</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_lab.php?id=<?= $lab['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=lab&id=<?= $lab['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=lab&id=<?= $lab['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this lab"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No lab work found
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
