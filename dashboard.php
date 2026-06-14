<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_semester = $_SESSION['user_semester'];

// Fetch attendance percentage
$attendance_query = "SELECT 
    (SELECT COUNT(*) FROM attendance WHERE user_id = :uid AND status = 'Present') as present_count,
    (SELECT COUNT(*) FROM attendance WHERE user_id = :uid2) as total_count";
$stmt = $pdo->prepare($attendance_query);
$stmt->execute([':uid' => $user_id, ':uid2' => $user_id]);
$attendance = $stmt->fetch(PDO::FETCH_ASSOC);
$attendance_pct = $attendance['total_count'] > 0 ? round(($attendance['present_count'] / $attendance['total_count']) * 100, 1) : 0;

// Fetch homework stats
$hw_query = "SELECT 
    (SELECT COUNT(*) FROM homework WHERE user_id = :uid AND status = 'Submitted') as submitted,
    (SELECT COUNT(*) FROM homework WHERE user_id = :uid2) as total";
$stmt = $pdo->prepare($hw_query);
$stmt->execute([':uid' => $user_id, ':uid2' => $user_id]);
$hw_stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch syllabus progress
$syllabus_query = "SELECT 
    (SELECT COUNT(*) FROM syllabus WHERE user_id = :uid AND semester = :sem AND status = 'Completed') as completed,
    (SELECT COUNT(*) FROM syllabus WHERE user_id = :uid2 AND semester = :sem2) as total";
$stmt = $pdo->prepare($syllabus_query);
$stmt->execute([':uid' => $user_id, ':sem' => $user_semester, ':uid2' => $user_id, ':sem2' => $user_semester]);
$syllabus_stats = $stmt->fetch(PDO::FETCH_ASSOC);
$syllabus_pct = $syllabus_stats['total'] > 0 ? round(($syllabus_stats['completed'] / $syllabus_stats['total']) * 100, 1) : 0;

// Fetch average grade percentage
$grade_query = "SELECT ROUND(AVG((marks_obtained / total_marks) * 100), 1) as avg_grade FROM grades WHERE user_id = :uid";
$stmt = $pdo->prepare($grade_query);
$stmt->execute([':uid' => $user_id]);
$grade_result = $stmt->fetch(PDO::FETCH_ASSOC);
$avg_grade = $grade_result['avg_grade'] ?? 0;

// Fetch total projects
$proj_query = "SELECT COUNT(*) as total FROM projects WHERE user_id = :uid";
$stmt = $pdo->prepare($proj_query);
$stmt->execute([':uid' => $user_id]);
$total_projects = $stmt->fetchColumn();

// Fetch total internships
$intern_query = "SELECT COUNT(*) as total FROM internships WHERE user_id = :uid";
$stmt = $pdo->prepare($intern_query);
$stmt->execute([':uid' => $user_id]);
$total_internships = $stmt->fetchColumn();

// Fetch total fee paid
$fee_query = "SELECT COALESCE(SUM(amount), 0) as total_paid FROM payments WHERE user_id = :uid AND status = 'Paid'";
$stmt = $pdo->prepare($fee_query);
$stmt->execute([':uid' => $user_id]);
$total_fee = $stmt->fetchColumn();

// Fetch today's schedule
$schedule_query = "SELECT * FROM schedule WHERE semester = :sem AND day = :day ORDER BY start_time ASC";
$stmt = $pdo->prepare($schedule_query);
$stmt->execute([':sem' => $user_semester, ':day' => date('l')]);
$today_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming exams (next 7 days)
$exam_query = "SELECT * FROM exams WHERE user_id = :uid AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY date ASC";
$stmt = $pdo->prepare($exam_query);
$stmt->execute([':uid' => $user_id]);
$upcoming_exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming homework (next 7 days)
$upcoming_hw_query = "SELECT * FROM homework WHERE user_id = :uid AND due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status != 'Submitted' ORDER BY due_date ASC";
$stmt = $pdo->prepare($upcoming_hw_query);
$stmt->execute([':uid' => $user_id]);
$upcoming_hw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent announcements
$announcements_query = "SELECT * FROM announcements WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5";
$stmt = $pdo->prepare($announcements_query);
$stmt->execute([':uid' => $user_id]);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly attendance for chart
$monthly_att_query = "SELECT MONTH(date) as month, 
    ROUND(SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as pct 
    FROM attendance WHERE user_id = :uid AND YEAR(date) = YEAR(CURDATE()) 
    GROUP BY MONTH(date) ORDER BY month ASC";
$stmt = $pdo->prepare($monthly_att_query);
$stmt->execute([':uid' => $user_id]);
$monthly_attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch grades by subject for chart
$grades_chart_query = "SELECT subject, ROUND(AVG((marks_obtained / total_marks) * 100), 1) as avg_pct FROM grades WHERE user_id = :uid GROUP BY subject ORDER BY subject ASC";
$stmt = $pdo->prepare($grades_chart_query);
$stmt->execute([':uid' => $user_id]);
$grades_chart = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Welcome back, <?= htmlspecialchars($user_name) ?>!</h4>
                    <p class="text-muted mb-0">Here's your dashboard overview for Semester <?= $user_semester ?></p>
                </div>
                <div>
                    <span class="badge bg-primary fs-6"><i class="fas fa-calendar-day me-1"></i> <?= date('l, F j, Y') ?></span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $attendance_pct ?>%</h3>
                                    <span class="text-muted">Attendance</span>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: <?= $attendance_pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $hw_stats['submitted'] ?? 0 ?>/<?= $hw_stats['total'] ?? 0 ?></h3>
                                    <span class="text-muted">Homework Done</span>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?= ($hw_stats['total'] ?? 0) > 0 ? round(($hw_stats['submitted'] / $hw_stats['total']) * 100) : 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $syllabus_pct ?>%</h3>
                                    <span class="text-muted">Syllabus Done</span>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?= $syllabus_pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-warning h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $avg_grade ?>%</h3>
                                    <span class="text-muted">Avg Grade</span>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: <?= $avg_grade ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-secondary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-secondary">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $total_projects ?></h3>
                                    <span class="text-muted">Projects</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-dark h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-dark">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= $total_internships ?></h3>
                                    <span class="text-muted">Internships</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card stat-card-danger h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0"><?= number_format($total_fee, 2) ?></h3>
                                    <span class="text-muted">Fee Paid</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Attendance Overview</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="attendanceChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-line me-2 text-success"></i>Grade Overview</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="gradeChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-info"></i>Syllabus Progress</h6>
                        </div>
                        <div class="card-body d-flex justify-content-center">
                            <canvas id="syllabusChart" height="220"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-clock me-2 text-warning"></i>Today's Schedule</h6>
                            <span class="badge bg-primary"><?= date('l') ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Faculty</th>
                                            <th>Room</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($today_schedule)): ?>
                                            <?php foreach ($today_schedule as $sched): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?= date('h:i A', strtotime($sched['start_time'])) ?> - <?= date('h:i A', strtotime($sched['end_time'])) ?>
                                                        </span>
                                                    </td>
                                                    <td><strong><?= htmlspecialchars($sched['subject']) ?></strong></td>
                                                    <td><?= htmlspecialchars($sched['teacher_name'] ?: 'TBD') ?></td>
                                                    <td><?= htmlspecialchars($sched['room_no'] ?: 'TBD') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted">No classes scheduled for today</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-file-alt me-2 text-danger"></i>Upcoming Exams (Next 7 Days)</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($upcoming_exams)): ?>
                                <div class="upcoming-list">
                                    <?php foreach ($upcoming_exams as $exam): ?>
                                        <div class="upcoming-item border-start border-danger border-3 ps-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($exam['exam_name']) ?> - <?= htmlspecialchars($exam['subject']) ?></h6>
                                                    <small class="text-muted"><i class="fas fa-calendar me-1"></i><?= date('D, M j, Y', strtotime($exam['date'])) ?></small>
                                                </div>
                                                <span class="badge bg-danger">
                                                    <?= max(0, round((strtotime($exam['date']) - strtotime('now')) / 86400)) ?> days left
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p class="mb-0">No upcoming exams in the next 7 days</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-tasks me-2 text-warning"></i>Upcoming Homework (Next 7 Days)</h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($upcoming_hw)): ?>
                                <div class="upcoming-list">
                                    <?php foreach ($upcoming_hw as $hw): ?>
                                        <div class="upcoming-item border-start border-warning border-3 ps-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($hw['title']) ?></h6>
                                                    <small class="text-muted"><i class="fas fa-calendar me-1"></i><?= date('D, M j, Y', strtotime($hw['due_date'])) ?></small>
                                                </div>
                                                <span class="badge bg-warning text-dark">
                                                    <?= max(0, round((strtotime($hw['due_date']) - strtotime('now')) / 86400)) ?> days left
                                                </span>
                                            </div>
                                            <span class="badge bg-light text-dark mt-1"><?= htmlspecialchars($hw['subject']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                    <p class="mb-0">No upcoming homework in the next 7 days</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Announcements -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-bullhorn me-2 text-primary"></i>Recent Announcements</h6>
                            <a href="announcement.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($announcements)): ?>
                                <div class="row g-3">
                                    <?php foreach ($announcements as $ann): ?>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="card border h-100">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="badge bg-<?= ($ann['priority'] ?? 'Medium') === 'High' ? 'danger' : (($ann['priority'] ?? 'Medium') === 'Medium' ? 'warning' : 'info') ?>">
                                                            <?= ucfirst($ann['priority'] ?? 'Medium') ?>
                                                        </span>
                                                        <small class="text-muted"><?= date('M j, Y', strtotime($ann['created_at'])) ?></small>
                                                    </div>
                                                    <h6 class="card-title"><?= htmlspecialchars($ann['title']) ?></h6>
                                                    <p class="card-text small text-muted mb-0"><?= htmlspecialchars(substr($ann['message'], 0, 100)) ?>...</p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p class="mb-0">No recent announcements</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-link me-2 text-secondary"></i>Quick Links</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="attendance.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-primary"><i class="fas fa-clipboard-check"></i></div>
                                        <span>Attendance</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="homework.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-success"><i class="fas fa-book-open"></i></div>
                                        <span>Homework</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="syllabus.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-info"><i class="fas fa-list-alt"></i></div>
                                        <span>Syllabus</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="grades.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-warning"><i class="fas fa-star"></i></div>
                                        <span>Grades</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="projects.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-secondary"><i class="fas fa-project-diagram"></i></div>
                                        <span>Projects</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="internship.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-dark"><i class="fas fa-building"></i></div>
                                        <span>Internships</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="exam.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon bg-danger"><i class="fas fa-file-alt"></i></div>
                                        <span>Exams</span>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="payment.php" class="quick-link-card text-decoration-none">
                                        <div class="quick-link-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);"><i class="fas fa-rupee-sign"></i></div>
                                        <span>Fees</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Attendance Chart
    const attCtx = document.getElementById('attendanceChart').getContext('2d');
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const attData = <?= json_encode($monthly_attendance) ?>;
    const attLabels = attData.map(d => monthNames[d.month - 1]);
    const attValues = attData.map(d => parseFloat(d.pct));

    new Chart(attCtx, {
        type: 'bar',
        data: {
            labels: attLabels,
            datasets: [{
                label: 'Attendance %',
                data: attValues,
                backgroundColor: 'rgba(79, 70, 229, 0.7)',
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: function(v) { return v + '%'; } } } },
            plugins: { legend: { display: false } }
        }
    });

    // Grade Chart
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    const gradeData = <?= json_encode($grades_chart) ?>;
    const gradeLabels = gradeData.map(d => d.subject);
    const gradeValues = gradeData.map(d => parseFloat(d.avg_pct));
    const gradeColors = gradeValues.map(v => v >= 80 ? 'rgba(16, 185, 129, 0.7)' : v >= 60 ? 'rgba(59, 130, 246, 0.7)' : 'rgba(245, 158, 11, 0.7)');

    new Chart(gradeCtx, {
        type: 'bar',
        data: {
            labels: gradeLabels,
            datasets: [{
                label: 'Grade %',
                data: gradeValues,
                backgroundColor: gradeColors,
                borderColor: gradeColors.map(c => c.replace('0.7', '1')),
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 100 } },
            plugins: { legend: { display: false } }
        }
    });

    // Syllabus Pie Chart
    const syllabusCtx = document.getElementById('syllabusChart').getContext('2d');
    new Chart(syllabusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Remaining'],
            datasets: [{
                data: [<?= $syllabus_stats['completed'] ?? 0 ?>, <?= ($syllabus_stats['total'] ?? 0) - ($syllabus_stats['completed'] ?? 0) ?>],
                backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(209, 213, 219, 0.5)'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { padding: 20 } } }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
