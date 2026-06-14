<?php
$pageTitle = 'Study Plan';
$extraCSS = ['style.css', 'study_plan.css'];
$extraJS = ['main.js', 'study_plan.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filter_subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filter_date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';
$filter_priority = isset($_GET['priority']) ? sanitize($_GET['priority']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filter_subject)) {
    $where .= " AND subject = ?";
    $params[] = $filter_subject;
}
if (!empty($filter_date_from)) {
    $where .= " AND date >= ?";
    $params[] = $filter_date_from;
}
if (!empty($filter_date_to)) {
    $where .= " AND date <= ?";
    $params[] = $filter_date_to;
}
if (!empty($filter_priority)) {
    $where .= " AND priority = ?";
    $params[] = $filter_priority;
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'study_plans', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM study_plans $where ORDER BY date ASC, time_slot ASC" . $pagi['sql']);
$stmt->execute($params);
$studyPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjectStmt = $pdo->prepare("SELECT DISTINCT subject FROM study_plans WHERE user_id = ? ORDER BY subject");
$subjectStmt->execute([$userId]);
$subjects = $subjectStmt->fetchAll(PDO::FETCH_COLUMN);

$subjectTimeStmt = $pdo->prepare("SELECT subject, time_slot, status FROM study_plans WHERE user_id = ?");
$subjectTimeStmt->execute([$userId]);
$subjectTimeData = $subjectTimeStmt->fetchAll(PDO::FETCH_ASSOC);

$subjectTimes = [];
foreach ($subjectTimeData as $row) {
    $subject = $row['subject'];
    if (!isset($subjectTimes[$subject])) {
        $subjectTimes[$subject] = 0;
    }
    if (!empty($row['time_slot']) && preg_match('/(\d{2}):(\d{2})-(\d{2}):(\d{2})/', $row['time_slot'], $m)) {
        $startMin = (int)$m[1] * 60 + (int)$m[2];
        $endMin = (int)$m[3] * 60 + (int)$m[4];
        $subjectTimes[$subject] += max(0, ($endMin - $startMin)) / 60;
    }
}

$priorityColors = [
    'High' => 'danger',
    'Medium' => 'warning',
    'Low' => 'success'
];

$statusColors = [
    'Planned' => 'primary',
    'In Progress' => 'warning',
    'Completed' => 'success'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-clock me-2 text-primary"></i>Study Plan</h4>
                    <p class="text-muted mb-0">Organize and track your study sessions</p>
                </div>
                <a href="add_study_plan.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Study Plan
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?= htmlspecialchars($subj) ?>" <?= $filter_subject === $subj ? 'selected' : '' ?>><?= htmlspecialchars($subj) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="">All Priorities</option>
                                <option value="High" <?= $filter_priority === 'High' ? 'selected' : '' ?>>High</option>
                                <option value="Medium" <?= $filter_priority === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="Low" <?= $filter_priority === 'Low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Planned" <?= $filter_status === 'Planned' ? 'selected' : '' ?>>Planned</option>
                                <option value="In Progress" <?= $filter_status === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="study_plan.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($subjectTimes)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Study Time Per Subject</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="studyTimeChart" height="120"></canvas>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <?php
                $allSubjects = [];
                foreach ($subjectTimes as $subj => $hours) {
                    $allSubjects[$subj] = $hours;
                }
                foreach ($allSubjects as $subj => $hours):
                ?>
                    <div class="col-lg-3 col-md-4 col-6 mb-3">
                        <div class="card subject-time-card h-100">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1"><?= htmlspecialchars($subj) ?></h6>
                                <h4 class="mb-0 text-primary"><?= number_format($hours, 1) ?>h</h4>
                                <small class="text-muted">study time</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Study Plans</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Topic</th>
                                    <th>Date</th>
                                    <th>Time Slot</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($studyPlans)): ?>
                                    <?php foreach ($studyPlans as $index => $plan): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($plan['subject']) ?></strong></td>
                                            <td><?= htmlspecialchars($plan['topic']) ?></td>
                                            <td><i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($plan['date']) ?></td>
                                            <td><i class="fas fa-clock me-1 text-muted"></i><?= htmlspecialchars($plan['time_slot']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $priorityColors[$plan['priority']] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($plan['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusColors[$plan['status']] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($plan['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_study_plan.php?id=<?= $plan['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=study_plan&id=<?= $plan['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=study_plan&id=<?= $plan['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this study plan"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No study plans found
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
</div>

<?php if (!empty($subjectTimes)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('studyTimeChart').getContext('2d');
    const labels = <?php echo json_encode(array_keys($subjectTimes)); ?>;
    const data = <?php echo json_encode(array_values($subjectTimes)); ?>;
    const colors = labels.map(function(_, i) {
        const palette = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#ec4899'];
        return palette[i % palette.length];
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Study Hours',
                data: data,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Hours' }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
