<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Attendance';
$extraCSS = ['style.css', 'attendance.css'];
$extraJS = ['main.js', 'attendance.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];

$filter_semester = isset($_GET['semester']) ? (int)$_GET['semester'] : $user_semester;
$filter_subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filter_date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : '';
$filter_date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : '';

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
if (!empty($filter_date_from)) {
    $where .= " AND date >= ?";
    $params[] = $filter_date_from;
}
if (!empty($filter_date_to)) {
    $where .= " AND date <= ?";
    $params[] = $filter_date_to;
}

$pagi = paginate($pdo, 'attendance', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM attendance $where ORDER BY date DESC" . $pagi['sql']);
$stmt->execute($params);
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalDays = count($attendanceRecords);
$presentCount = 0;
$absentCount = 0;
$lateCount = 0;

foreach ($attendanceRecords as $record) {
    if (strtolower($record['status']) === 'present') $presentCount++;
    elseif (strtolower($record['status']) === 'absent') $absentCount++;
    elseif (strtolower($record['status']) === 'late') $lateCount++;
}

$overallPercentage = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 0;

$stmt = $pdo->prepare("SELECT subject, 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count
    FROM attendance $where GROUP BY subject ORDER BY subject");
$stmt->execute($params);
$subjectWiseStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($filter_semester);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-check-circle me-2 text-primary"></i>Attendance</h4>
                    <p class="text-muted mb-0">Track your attendance records</p>
                </div>
                <a href="add_attendance.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Attendance
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary"><i class="fas fa-calendar-check"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $totalDays ?></h3>
                                    <span class="text-muted">Total Days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success"><i class="fas fa-check"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0 text-success"><?= $presentCount ?></h3>
                                    <span class="text-muted">Present</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger"><i class="fas fa-times"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0 text-danger"><?= $absentCount ?></h3>
                                    <span class="text-muted">Absent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning"><i class="fas fa-clock"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0 text-warning"><?= $lateCount ?></h3>
                                    <span class="text-muted">Late</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h6 class="mb-0 me-3">Overall Attendance:</h6>
                        <div class="progress flex-grow-1" style="height: 24px;">
                            <div class="progress-bar <?= $overallPercentage >= 75 ? 'bg-success' : ($overallPercentage >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                 style="width: <?= $overallPercentage ?>%">
                                <?= $overallPercentage ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Records</h6>
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
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= $filter_date_from ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= $filter_date_to ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="attendance.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Attendance Records</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($attendanceRecords)): ?>
                                    <?php foreach ($attendanceRecords as $index => $record): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($record['date']) ?></td>
                                            <td><strong><?= htmlspecialchars($record['subject']) ?></strong></td>
                                            <td>
                                                <?php
                                                $statusClass = 'secondary';
                                                $statusIcon = 'question-circle';
                                                if (strtolower($record['status']) === 'present') {
                                                    $statusClass = 'success';
                                                    $statusIcon = 'check-circle';
                                                } elseif (strtolower($record['status']) === 'absent') {
                                                    $statusClass = 'danger';
                                                    $statusIcon = 'times-circle';
                                                } elseif (strtolower($record['status']) === 'late') {
                                                    $statusClass = 'warning';
                                                    $statusIcon = 'clock';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <i class="fas fa-<?= $statusIcon ?> me-1"></i><?= ucfirst($record['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_attendance.php?id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=attendance&id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=attendance&id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this attendance record"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No attendance records found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($subjectWiseStats)): ?>
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Subject-wise Attendance</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Total</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Late</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectWiseStats as $stat): ?>
                                    <?php
                                    $subjectPct = $stat['total'] > 0 ? round(($stat['present_count'] / $stat['total']) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($stat['subject']) ?></strong></td>
                                        <td><?= $stat['total'] ?></td>
                                        <td class="text-success"><?= $stat['present_count'] ?></td>
                                        <td class="text-danger"><?= $stat['absent_count'] ?></td>
                                        <td class="text-warning"><?= $stat['late_count'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar <?= $subjectPct >= 75 ? 'bg-success' : ($subjectPct >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                                         style="width: <?= $subjectPct ?>%"></div>
                                                </div>
                                                <span class="<?= $subjectPct >= 75 ? 'text-success' : ($subjectPct >= 50 ? 'text-warning' : 'text-danger') ?> fw-bold">
                                                    <?= $subjectPct ?>%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>