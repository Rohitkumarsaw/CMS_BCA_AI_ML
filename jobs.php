<?php
$pageTitle = 'Jobs';
$extraCSS = ['style.css', 'jobs.css'];
$extraJS = ['main.js', 'jobs.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filterStatus)) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagi = paginate($pdo, 'jobs', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM jobs $where ORDER BY application_date DESC" . $pagi['sql']);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM jobs WHERE user_id = ? GROUP BY status");
$statusStmt->execute([$userId]);
$statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$badgeColors = [
    'Applied' => 'primary',
    'Interviewing' => 'warning',
    'Selected' => 'success',
    'Rejected' => 'danger'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-briefcase me-2 text-primary"></i>Jobs</h4>
                    <p class="text-muted mb-0">Track your job applications</p>
                </div>
                <a href="add_job.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Job
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= array_sum($statusCounts) ?></h3>
                            <small class="text-muted">Total Applications</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-1"><?= $statusCounts['Applied'] ?? 0 ?></h3>
                            <small class="text-muted">Applied</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-1"><?= $statusCounts['Interviewing'] ?? 0 ?></h3>
                            <small class="text-muted">Interviewing</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= $statusCounts['Selected'] ?? 0 ?></h3>
                            <small class="text-muted">Selected</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Applied" <?= $filterStatus === 'Applied' ? 'selected' : '' ?>>Applied</option>
                                <option value="Interviewing" <?= $filterStatus === 'Interviewing' ? 'selected' : '' ?>>Interviewing</option>
                                <option value="Selected" <?= $filterStatus === 'Selected' ? 'selected' : '' ?>>Selected</option>
                                <option value="Rejected" <?= $filterStatus === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="jobs.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if (!empty($jobs)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Job Title</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Date Applied</th>
                                        <th>Status</th>
                                        <th>Salary</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($job['job_title']) ?></strong>
                                            </td>
                                            <td><?= htmlspecialchars($job['company']) ?></td>
                                            <td><i class="fas fa-map-marker-alt me-1 text-muted"></i><?= htmlspecialchars($job['location']) ?></td>
                                            <td><small><?= formatDate($job['application_date']) ?></small></td>
                                            <td>
                                                <span class="badge bg-<?= $badgeColors[$job['status']] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($job['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($job['salary'])): ?>
                                                    <span class="text-success fw-bold"><?= htmlspecialchars($job['salary']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($job['job_link'])): ?>
                                                    <a href="<?= htmlspecialchars($job['job_link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="edit_job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=job&id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=job&id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this job"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No job applications found</p>
                            <a href="add_job.php" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>Add Your First Application
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
