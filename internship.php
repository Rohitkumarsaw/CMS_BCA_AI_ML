<?php
$pageTitle = 'Internships';
$extraCSS = ['style.css', 'internship.css'];
$extraJS = ['main.js', 'internship.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'internships', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM internships $where ORDER BY created_at DESC" . $pagi['sql']);
$stmt->execute($params);
$internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusCounts = [];
$stmtCount = $pdo->prepare("SELECT status, COUNT(*) as count FROM internships WHERE user_id = ? GROUP BY status");
$stmtCount->execute([$userId]);
foreach ($stmtCount->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $statusCounts[$row['status']] = $row['count'];
}

$badgeColors = [
    'Applied' => 'primary',
    'Interviewing' => 'warning',
    'Selected' => 'success',
    'Completed' => 'secondary'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-building me-2 text-primary"></i>Internships</h4>
                    <p class="text-muted mb-0">Track your internship applications and experiences</p>
                </div>
                <a href="add_internship.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Internship
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= array_sum($statusCounts) ?></h3>
                            <small class="text-muted">Total Internships</small>
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
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Applied" <?= $filter_status === 'Applied' ? 'selected' : '' ?>>Applied</option>
                                <option value="Interviewing" <?= $filter_status === 'Interviewing' ? 'selected' : '' ?>>Interviewing</option>
                                <option value="Selected" <?= $filter_status === 'Selected' ? 'selected' : '' ?>>Selected</option>
                                <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="internship.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <?php if (!empty($internships)): ?>
                    <?php foreach ($internships as $internship): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm internship-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-building me-1 text-primary"></i>
                                            <?= htmlspecialchars($internship['company']) ?>
                                        </h5>
                                        <span class="badge bg-<?= $badgeColors[$internship['status']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($internship['status']) ?>
                                        </span>
                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($internship['role']) ?>
                                    </p>

                                    <p class="mb-2">
                                        <i class="fas fa-calendar me-1 text-muted"></i>
                                        <small>
                                            <?= formatDate($internship['duration_start']) ?>
                                            - <?= formatDate($internship['duration_end']) ?>
                                        </small>
                                    </p>

                                    <?php if (!empty($internship['location'])): ?>
                                        <p class="mb-2">
                                            <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                            <small><?= htmlspecialchars($internship['location']) ?></small>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($internship['skills_gained'])): ?>
                                        <div class="mb-2">
                                            <?php
                                            $skills = array_slice(explode(',', $internship['skills_gained']), 0, 3);
                                            foreach ($skills as $skill): ?>
                                                <span class="badge bg-light text-dark"><?= trim(htmlspecialchars($skill)) ?></span>
                                            <?php endforeach;
                                            $totalSkills = count(explode(',', $internship['skills_gained']));
                                            if ($totalSkills > 3): ?>
                                                <span class="badge bg-light text-dark">+<?= $totalSkills - 3 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($internship['payment'])): ?>
                                        <p class="mb-2">
                                            <i class="fas fa-money-bill me-1 text-success"></i>
                                            <small class="fw-bold text-success"><?= htmlspecialchars($internship['payment']) ?></small>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($internship['description'])): ?>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <?= htmlspecialchars(substr($internship['description'], 0, 100)) ?>
                                                <?= strlen($internship['description']) > 100 ? '...' : '' ?>
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?php if (!empty($internship['certificate_path'])): ?>
                                                <a href="<?= htmlspecialchars($internship['certificate_path']) ?>" class="btn btn-sm btn-outline-success" download>
                                                    <i class="fas fa-certificate me-1"></i>Certificate
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="edit_internship.php?id=<?= $internship['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=internship&id=<?= $internship['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=internship&id=<?= $internship['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this internship"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No internships found</p>
                                <a href="add_internship.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Your First Internship
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
