<?php
$pageTitle = 'Skills';
$extraCSS = ['style.css', 'skills.css'];
$extraJS = ['main.js', 'skills.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filterCategory = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$filterLevel = isset($_GET['level']) ? sanitize($_GET['level']) : '';
$filterStatus = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filterCategory)) {
    $where .= " AND category = ?";
    $params[] = $filterCategory;
}
if (!empty($filterLevel)) {
    $where .= " AND level = ?";
    $params[] = $filterLevel;
}
if (!empty($filterStatus)) {
    $where .= " AND status = ?";
    $params[] = $filterStatus;
}

$pagi = paginate($pdo, 'skills', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM skills $where ORDER BY created_at DESC" . $pagi['sql']);
$stmt->execute($params);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->prepare("SELECT category, COUNT(*) as count FROM skills WHERE user_id = ? GROUP BY category ORDER BY category");
$countStmt->execute([$userId]);
$categoryCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$levelStmt = $pdo->prepare("SELECT level, COUNT(*) as count FROM skills WHERE user_id = ? GROUP BY level");
$levelStmt->execute([$userId]);
$levelCounts = $levelStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$statusStmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM skills WHERE user_id = ? GROUP BY status");
$statusStmt->execute([$userId]);
$statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$levelColors = [
    'Beginner' => 'primary',
    'Intermediate' => 'warning',
    'Advanced' => 'success'
];

$levelProgress = [
    'Beginner' => 33,
    'Intermediate' => 66,
    'Advanced' => 100
];

$categories = ['Programming', 'AI', 'ML', 'Data', 'WebDev', 'Other'];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-cogs me-2 text-primary"></i>Skills</h4>
                    <p class="text-muted mb-0">Track your programming and technical skills</p>
                </div>
                <a href="add_skill.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Skill
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= count($skills) ?></h3>
                            <small class="text-muted">Total Skills</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-1"><?= $statusCounts['Learning'] ?? 0 ?></h3>
                            <small class="text-muted">Learning</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= $statusCounts['Completed'] ?? 0 ?></h3>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-warning mb-1"><?= $levelCounts['Advanced'] ?? 0 ?></h3>
                            <small class="text-muted">Advanced</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Skills by Category</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="skillsChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Category Breakdown</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($categories as $cat): ?>
                                <?php $count = $categoryCounts[$cat] ?? 0; ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted"><?= $cat ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?= count($skills) > 0 ? ($count / count($skills)) * 100 : 0 ?>%"></div>
                                </div>
                            <?php endforeach; ?>
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
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Level</label>
                            <select name="level" class="form-select">
                                <option value="">All Levels</option>
                                <option value="Beginner" <?= $filterLevel === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="Intermediate" <?= $filterLevel === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="Advanced" <?= $filterLevel === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Learning" <?= $filterStatus === 'Learning' ? 'selected' : '' ?>>Learning</option>
                                <option value="Completed" <?= $filterStatus === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="skills.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#skillsGrid">
            </div>
            <div class="row" id="skillsGrid">
                <?php if (!empty($skills)): ?>
                    <?php foreach ($skills as $skill): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm skill-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-code me-1 text-primary"></i>
                                            <?= htmlspecialchars($skill['skill_name']) ?>
                                        </h5>
                                        <span class="badge bg-<?= $levelColors[$skill['level']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($skill['level']) ?>
                                        </span>
                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($skill['category']) ?>
                                    </p>

                                    <div class="mb-2">
                                        <small class="text-muted">Level Progress</small>
                                        <div class="progress mt-1" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $levelColors[$skill['level']] ?? 'secondary' ?>"
                                                 style="width: <?= $levelProgress[$skill['level']] ?? 0 ?>%"></div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= $skill['status'] === 'Completed' ? 'success' : 'info' ?>">
                                            <?= htmlspecialchars($skill['status']) ?>
                                        </span>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i><?= formatDate($skill['date_completed'] ?? $skill['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent text-end">
                                    <a href="edit_skill.php?id=<?= $skill['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="view.php?type=skill&id=<?= $skill['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="delete.php?type=skill&id=<?= $skill['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this skill"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No skills found</p>
                                <a href="add_skill.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Your First Skill
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('skillsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($categories) ?>,
                datasets: [{
                    label: 'Skills',
                    data: <?= json_encode(array_map(fn($c) => $categoryCounts[$c] ?? 0, $categories)) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
