<?php
$pageTitle = 'Reports & Analytics';
$extraCSS = ['style.css', 'reports.css'];
$extraJS = ['main.js', 'reports.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];

$attendanceStmt = $pdo->prepare("SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count
    FROM attendance WHERE user_id = ?");
$attendanceStmt->execute([$userId]);
$attendanceSummary = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

$attendanceMonthStmt = $pdo->prepare("SELECT
    DATE_FORMAT(date, '%Y-%m') as month,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count
    FROM attendance WHERE user_id = ?
    GROUP BY month ORDER BY month");
$attendanceMonthStmt->execute([$userId]);
$attendanceMonthly = $attendanceMonthStmt->fetchAll(PDO::FETCH_ASSOC);

$attendanceSubjectStmt = $pdo->prepare("SELECT
    subject,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late_count
    FROM attendance WHERE user_id = ?
    GROUP BY subject ORDER BY subject");
$attendanceSubjectStmt->execute([$userId]);
$attendanceSubjectWise = $attendanceSubjectStmt->fetchAll(PDO::FETCH_ASSOC);

$hwStmt = $pdo->prepare("SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
    SUM(CASE WHEN status != 'Submitted' THEN 1 ELSE 0 END) as not_submitted
    FROM homework WHERE user_id = ?");
$hwStmt->execute([$userId]);
$homeworkSummary = $hwStmt->fetch(PDO::FETCH_ASSOC);

$hwSubjectStmt = $pdo->prepare("SELECT
    subject,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as submitted,
    SUM(CASE WHEN status != 'Submitted' THEN 1 ELSE 0 END) as not_submitted
    FROM homework WHERE user_id = ?
    GROUP BY subject ORDER BY subject");
$hwSubjectStmt->execute([$userId]);
$homeworkSubjectWise = $hwSubjectStmt->fetchAll(PDO::FETCH_ASSOC);

$gradeStmt = $pdo->prepare("SELECT
    semester,
    AVG(marks_obtained / total_marks * 100) as avg_percentage,
    COUNT(*) as count
    FROM grades WHERE user_id = ?
    GROUP BY semester ORDER BY semester");
$gradeStmt->execute([$userId]);
$gradeSemesterAvg = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

$gradeSubjectStmt = $pdo->prepare("SELECT
    subject,
    AVG(marks_obtained / total_marks * 100) as avg_percentage,
    COUNT(*) as count
    FROM grades WHERE user_id = ?
    GROUP BY subject ORDER BY subject");
$gradeSubjectStmt->execute([$userId]);
$gradeSubjectWise = $gradeSubjectStmt->fetchAll(PDO::FETCH_ASSOC);

$syllabusProgress = [];
for ($i = 1; $i <= 8; $i++) {
    $subjects = getSemesterSubjects($i);
    $totalTopics = 0;
    $completedTopics = 0;
    foreach ($subjects as $subject) {
        $topicStmt = $pdo->prepare("SELECT COUNT(*) as total,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed
            FROM syllabus WHERE user_id = ? AND semester = ? AND subject = ?");
        $topicStmt->execute([$userId, $i, $subject]);
        $topicData = $topicStmt->fetch(PDO::FETCH_ASSOC);
        $totalTopics += $topicData['total'];
        $completedTopics += $topicData['completed'];
    }
    $syllabusProgress[$i] = [
        'total' => $totalTopics,
        'completed' => $completedTopics,
        'percentage' => $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0
    ];
}

$projectStmt = $pdo->prepare("SELECT
    semester,
    COUNT(*) as count,
    category,
    COUNT(category) as cat_count
    FROM projects WHERE user_id = ?
    GROUP BY semester, category ORDER BY semester");
$projectStmt->execute([$userId]);
$projectData = $projectStmt->fetchAll(PDO::FETCH_ASSOC);

$projectSemesterStmt = $pdo->prepare("SELECT
    semester, COUNT(*) as count
    FROM projects WHERE user_id = ?
    GROUP BY semester ORDER BY semester");
$projectSemesterStmt->execute([$userId]);
$projectSemesterCounts = $projectSemesterStmt->fetchAll(PDO::FETCH_ASSOC);

$projectCategoryStmt = $pdo->prepare("SELECT
    category, COUNT(*) as count
    FROM projects WHERE user_id = ?
    GROUP BY category ORDER BY count DESC");
$projectCategoryStmt->execute([$userId]);
$projectCategoryCounts = $projectCategoryStmt->fetchAll(PDO::FETCH_ASSOC);

$internshipStmt = $pdo->prepare("SELECT
    status,
    COUNT(*) as count,
    SUM(CASE WHEN payment IS NOT NULL AND payment != '' THEN 1 ELSE 0 END) as paid_count
    FROM internships WHERE user_id = ?
    GROUP BY status");
$internshipStmt->execute([$userId]);
$internshipStatusCounts = $internshipStmt->fetchAll(PDO::FETCH_ASSOC);

$internshipTotalStmt = $pdo->prepare("SELECT COUNT(*) as total FROM internships WHERE user_id = ?");
$internshipTotalStmt->execute([$userId]);
$internshipTotal = $internshipTotalStmt->fetchColumn();

$paymentSemesterStmt = $pdo->prepare("SELECT
    semester,
    SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as paid,
    SUM(CASE WHEN status = 'Unpaid' THEN amount ELSE 0 END) as unpaid,
    SUM(CASE WHEN status = 'Partial' THEN amount ELSE 0 END) as partial,
    SUM(amount) as total
    FROM payments WHERE user_id = ?
    GROUP BY semester ORDER BY semester");
$paymentSemesterStmt->execute([$userId]);
$paymentSemesterData = $paymentSemesterStmt->fetchAll(PDO::FETCH_ASSOC);

$skillCatStmt = $pdo->prepare("SELECT
    category, COUNT(*) as count
    FROM skills WHERE user_id = ?
    GROUP BY category ORDER BY count DESC");
$skillCatStmt->execute([$userId]);
$skillCategoryCounts = $skillCatStmt->fetchAll(PDO::FETCH_ASSOC);

$certYearStmt = $pdo->prepare("SELECT
    YEAR(date) as year, COUNT(*) as count
    FROM certifications WHERE user_id = ?
    GROUP BY YEAR(date) ORDER BY year DESC");
$certYearStmt->execute([$userId]);
$certYearCounts = $certYearStmt->fetchAll(PDO::FETCH_ASSOC);

$overallAttendance = $attendanceSummary['total'] > 0
    ? round(($attendanceSummary['present_count'] / $attendanceSummary['total']) * 100, 1) : 0;
$overallGradeAvg = 0;
if (!empty($gradeSemesterAvg)) {
    $totalPct = 0;
    foreach ($gradeSemesterAvg as $g) $totalPct += $g['avg_percentage'];
    $overallGradeAvg = round($totalPct / count($gradeSemesterAvg), 1);
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-chart-bar me-2 text-primary"></i>Reports & Analytics</h4>
                    <p class="text-muted mb-0">Comprehensive overview of your academic progress</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print Report
                    </button>
                </div>
            </div>

            <?= getFlashMessage() ?>

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary"><i class="fas fa-check-circle"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $overallAttendance ?>%</h3>
                                    <span class="text-muted">Attendance</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success"><i class="fas fa-chart-line"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $overallGradeAvg ?>%</h3>
                                    <span class="text-muted">Avg Grade</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info"><i class="fas fa-project-diagram"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= count($projectSemesterCounts) > 0 ? array_sum(array_column($projectSemesterCounts, 'count')) : 0 ?></h3>
                                    <span class="text-muted">Projects</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning"><i class="fas fa-cogs"></i></div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= count($skillCategoryCounts) > 0 ? array_sum(array_column($skillCategoryCounts, 'count')) : 0 ?></h3>
                                    <span class="text-muted">Skills</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-check-circle me-2 text-primary"></i>Attendance Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=attendance" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=attendance" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0"><?= $attendanceSummary['total'] ?></h2>
                                    <small class="text-muted">Total Days</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-success mb-0"><?= $attendanceSummary['present_count'] ?></h2>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center gap-3">
                                        <div>
                                            <h4 class="text-danger mb-0"><?= $attendanceSummary['absent_count'] ?></h4>
                                            <small class="text-muted">Absent</small>
                                        </div>
                                        <div class="vr"></div>
                                        <div>
                                            <h4 class="text-warning mb-0"><?= $attendanceSummary['late_count'] ?></h4>
                                            <small class="text-muted">Late</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <canvas id="attendanceChart" height="250"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <h6 class="mb-0 me-3">Overall:</h6>
                                <div class="progress flex-grow-1" style="height: 24px;">
                                    <div class="progress-bar <?= $overallAttendance >= 75 ? 'bg-success' : ($overallAttendance >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                         style="width: <?= $overallAttendance ?>%">
                                        <?= $overallAttendance ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($attendanceSubjectWise)): ?>
                    <h6 class="mb-3"><i class="fas fa-book me-1 text-muted"></i>Subject-wise Attendance</h6>
                    <div class="section-search-container">
                        <i class="fas fa-search section-search-icon"></i>
                        <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#report1Table tbody">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="report1Table">
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
                                <?php foreach ($attendanceSubjectWise as $row): ?>
                                    <?php $pct = $row['total'] > 0 ? round(($row['present_count'] / $row['total']) * 100, 1) : 0; ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['subject']) ?></strong></td>
                                        <td><?= $row['total'] ?></td>
                                        <td class="text-success"><?= $row['present_count'] ?></td>
                                        <td class="text-danger"><?= $row['absent_count'] ?></td>
                                        <td class="text-warning"><?= $row['late_count'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar <?= $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                         style="width: <?= $pct ?>%"></div>
                                                </div>
                                                <span class="fw-bold <?= $pct >= 75 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') ?>">
                                                    <?= $pct ?>%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-book me-2 text-warning"></i>Homework Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=homework" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=homework" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0"><?= $homeworkSummary['total'] ?></h2>
                                    <small class="text-muted">Total Homework</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-success mb-0"><?= $homeworkSummary['submitted'] ?></h2>
                                    <small class="text-muted">Submitted</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-danger mb-0"><?= $homeworkSummary['not_submitted'] ?></h2>
                                    <small class="text-muted">Not Submitted</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <canvas id="homeworkChart" height="250"></canvas>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Grade Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=grades" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=grades" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($gradeSemesterAvg)): ?>
                    <div class="row mb-4">
                        <?php foreach ($gradeSemesterAvg as $g): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <small class="text-muted">Semester <?= $g['semester'] ?></small>
                                        <h3 class="mb-0 mt-1"><?= number_format($g['avg_percentage'], 1) ?>%</h3>
                                        <small class="text-muted"><?= $g['count'] ?> exams</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <canvas id="gradeChart" height="250"></canvas>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No grade data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-list-check me-2 text-info"></i>Syllabus Progress</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=syllabus" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=syllabus" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <canvas id="syllabusChart" height="250"></canvas>
                        </div>
                        <div class="col-md-4">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Sem <?= $i ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $syllabusProgress[$i]['percentage'] ?>%</span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $syllabusProgress[$i]['percentage'] == 100 ? 'success' : ($syllabusProgress[$i]['percentage'] >= 50 ? 'warning' : 'primary') ?>"
                                         style="width: <?= $syllabusProgress[$i]['percentage'] ?>%"></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-project-diagram me-2 text-secondary"></i>Projects Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=projects" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=projects" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">Projects Per Semester</h6>
                            <?php if (!empty($projectSemesterCounts)): ?>
                                <div class="section-search-container">
                                    <i class="fas fa-search section-search-icon"></i>
                                    <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#report2Table tbody">
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="report2Table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Semester</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($projectSemesterCounts as $ps): ?>
                                                <tr>
                                                    <td><strong>Semester <?= $ps['semester'] ?></strong></td>
                                                    <td><?= $ps['count'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No project data</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-3">Category Breakdown</h6>
                            <?php if (!empty($projectCategoryCounts)): ?>
                                <?php foreach ($projectCategoryCounts as $pc): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span><?= htmlspecialchars($pc['category']) ?></span>
                                        <span class="badge bg-info rounded-pill"><?= $pc['count'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No category data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-building me-2 text-dark"></i>Internship Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=overall" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=overall" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <h2 class="text-primary mb-0"><?= $internshipTotal ?></h2>
                                    <small class="text-muted">Total Internships</small>
                                </div>
                            </div>
                        </div>
                        <?php foreach ($internshipStatusCounts as $is): ?>
                            <div class="col-md-3">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <h3 class="mb-0"><?= $is['count'] ?></h3>
                                        <small class="text-muted"><?= htmlspecialchars($is['status']) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-credit-card me-2 text-success"></i>Payment Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=payments" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=payments" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($paymentSemesterData)): ?>
                    <canvas id="paymentReportChart" height="250"></canvas>
                    <div class="section-search-container">
                        <i class="fas fa-search section-search-icon"></i>
                        <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#report3Table tbody">
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-hover" id="report3Table">
                            <thead class="table-light">
                                <tr>
                                    <th>Semester</th>
                                    <th>Paid</th>
                                    <th>Unpaid</th>
                                    <th>Partial</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentSemesterData as $ps): ?>
                                    <tr>
                                        <td><strong>Sem <?= $ps['semester'] ?></strong></td>
                                        <td class="text-success">₹<?= number_format($ps['paid']) ?></td>
                                        <td class="text-danger">₹<?= number_format($ps['unpaid']) ?></td>
                                        <td class="text-warning">₹<?= number_format($ps['partial']) ?></td>
                                        <td class="text-primary fw-bold">₹<?= number_format($ps['total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No payment data
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-cogs me-2 text-warning"></i>Skills Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=overall" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=overall" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($skillCategoryCounts)): ?>
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="skillsReportChart" height="250"></canvas>
                        </div>
                        <div class="col-md-4">
                            <?php foreach ($skillCategoryCounts as $sc): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><?= htmlspecialchars($sc['category']) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $sc['count'] ?></span>
                                </div>
                                <div class="progress mb-3" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?= $sc['count'] > 0 ? ($sc['count'] / array_sum(array_column($skillCategoryCounts, 'count'))) * 100 : 0 ?>%"></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No skills data
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-certificate me-2 text-secondary"></i>Certifications Report</h6>
                    <div class="d-flex gap-2">
                        <a href="export_pdf.php?type=overall" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>Export PDF
                        </a>
                        <a href="export_excel.php?type=overall" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($certYearCounts)): ?>
                        <div class="row">
                            <?php foreach ($certYearCounts as $year => $count): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card border">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary mb-0"><?= $count ?></h3>
                                            <small class="text-muted"><?= $year ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No certification data
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const attendanceLabels = <?= json_encode(array_map(function($m) { return date('M Y', strtotime($m['month'] . '-01')); }, $attendanceMonthly)) ?>;
    const attendancePresent = <?= json_encode(array_map(function($m) { return $m['present_count']; }, $attendanceMonthly)) ?>;
    const attendanceTotal = <?= json_encode(array_map(function($m) { return $m['total']; }, $attendanceMonthly)) ?>;

    if (document.getElementById('attendanceChart')) {
        new Chart(document.getElementById('attendanceChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: attendanceLabels.length > 0 ? attendanceLabels : ['No Data'],
                datasets: [
                    {
                        label: 'Present',
                        data: attendancePresent.length > 0 ? attendancePresent : [0],
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total',
                        data: attendanceTotal.length > 0 ? attendanceTotal : [0],
                        backgroundColor: 'rgba(108, 117, 125, 0.3)',
                        borderColor: 'rgba(108, 117, 125, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    if (document.getElementById('homeworkChart')) {
        new Chart(document.getElementById('homeworkChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($homeworkSubjectWise, 'subject')) ?>,
                datasets: [
                    {
                        label: 'Submitted',
                        data: <?= json_encode(array_map(function($h) { return (int)$h['submitted']; }, $homeworkSubjectWise)) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderWidth: 1
                    },
                    {
                        label: 'Not Submitted',
                        data: <?= json_encode(array_map(function($h) { return (int)$h['not_submitted']; }, $homeworkSubjectWise)) ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    if (document.getElementById('gradeChart')) {
        new Chart(document.getElementById('gradeChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($g) { return 'Sem ' . $g['semester']; }, $gradeSemesterAvg)) ?>,
                datasets: [{
                    label: 'Average %',
                    data: <?= json_encode(array_map(function($g) { return round($g['avg_percentage'], 1); }, $gradeSemesterAvg)) ?>,
                    backgroundColor: ['rgba(13, 110, 253, 0.7)', 'rgba(25, 135, 84, 0.7)', 'rgba(255, 193, 7, 0.7)', 'rgba(220, 53, 69, 0.7)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: 100 } },
                plugins: { legend: { display: false } }
            }
        });
    }

    if (document.getElementById('syllabusChart')) {
        new Chart(document.getElementById('syllabusChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_map(function($i) { return 'Sem ' . $i; }, range(1, 8))) ?>,
                datasets: [{
                    data: <?= json_encode(array_map(function($i) use ($syllabusProgress) { return $syllabusProgress[$i]['total']; }, range(1, 8))) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d', '#f8f9fa', '#343a40']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    }

    if (document.getElementById('paymentReportChart')) {
        new Chart(document.getElementById('paymentReportChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(function($p) { return 'Sem ' . $p['semester']; }, $paymentSemesterData)) ?>,
                datasets: [
                    {
                        label: 'Paid',
                        data: <?= json_encode(array_map(function($p) { return (float)$p['paid']; }, $paymentSemesterData)) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderWidth: 1
                    },
                    {
                        label: 'Unpaid',
                        data: <?= json_encode(array_map(function($p) { return (float)$p['unpaid']; }, $paymentSemesterData)) ?>,
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderWidth: 1
                    },
                    {
                        label: 'Partial',
                        data: <?= json_encode(array_map(function($p) { return (float)$p['partial']; }, $paymentSemesterData)) ?>,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return '₹' + v.toLocaleString(); } } } },
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    if (document.getElementById('skillsReportChart')) {
        new Chart(document.getElementById('skillsReportChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($skillCategoryCounts, 'category')) ?>,
                datasets: [{
                    label: 'Skills',
                    data: <?= json_encode(array_map(function($s) { return (int)$s['count']; }, $skillCategoryCounts)) ?>,
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6c757d'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
