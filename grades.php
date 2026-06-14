<?php
$pageTitle = 'Grades';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];

$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';

$where = "WHERE g.user_id = :user_id";
$params = [':user_id' => $userId];

if ($semester > 0) {
    $where .= " AND g.semester = :semester";
    $params[':semester'] = $semester;
}
if (!empty($subject)) {
    $where .= " AND g.subject = :subject";
    $params[':subject'] = $subject;
}

$pagi = paginate($pdo, 'grades g', $where, $params, 20);
$query = "SELECT g.*
          FROM grades g
          $where
          ORDER BY g.date DESC" . $pagi['sql'];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$grades = $stmt->fetchAll();

$semesterQuery = "SELECT g.semester, 
                  AVG(g.marks_obtained / g.total_marks * 100) as avg_percentage,
                  COUNT(*) as count
                  FROM grades g 
                  WHERE g.user_id = :user_id 
                  GROUP BY g.semester 
                  ORDER BY g.semester";
$semesterStmt = $pdo->prepare($semesterQuery);
$semesterStmt->execute([':user_id' => $userId]);
$semesterSummary = $semesterStmt->fetchAll();

$totalQuery = "SELECT 
               AVG(g.marks_obtained / g.total_marks * 100) as avg_percentage,
               COUNT(*) as total_exams
               FROM grades g 
               WHERE g.user_id = :user_id";
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute([':user_id' => $userId]);
$overallSummary = $totalStmt->fetch();

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-chart-line me-2"></i>Grades</h2>
            <a href="add_grade.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Grade
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Overall Average</h5>
                        <h2><?php echo number_format($overallSummary['avg_percentage'] ?? 0, 1); ?>%</h2>
                        <p class="mb-0"><?php echo $overallSummary['total_exams'] ?? 0; ?> exams</p>
                    </div>
                </div>
            </div>
            <?php foreach ($semesterSummary as $summary): ?>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Semester <?php echo $summary['semester']; ?></h5>
                            <h2><?php echo number_format($summary['avg_percentage'], 1); ?>%</h2>
                            <p class="mb-0"><?php echo $summary['count']; ?> exams</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Grade Chart</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="gradeChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="semester" class="form-select">
                            <option value="0">All Semesters</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="subject" class="form-select">
                            <option value="">All Subjects</option>
                            <?php
                            $subjects = getSemesterSubjects($userSemester);
                            foreach ($subjects as $subj): ?>
                                <option value="<?php echo $subj; ?>" <?php echo $subject === $subj ? 'selected' : ''; ?>>
                                    <?php echo $subj; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Exam Name</th>
                                <th>Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($grades)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No grades found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($grades as $grade): ?>
                                    <?php 
                                    $percentage = ($grade['marks_obtained'] / $grade['total_marks']) * 100;
                                    $letterGrade = calculateGrade($grade['marks_obtained'], $grade['total_marks']);
                                    
                                    $gradeColors = [
                                        'A+' => 'success', 'A' => 'success',
                                        'B+' => 'primary', 'B' => 'primary',
                                        'C' => 'warning',
                                        'D' => 'orange',
                                        'F' => 'danger'
                                    ];
                                    $color = $gradeColors[$letterGrade] ?? 'secondary';
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($grade['subject'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($grade['exam_name']); ?></td>
                                        <td><?php echo $grade['marks_obtained']; ?>/<?php echo $grade['total_marks']; ?></td>
                                        <td><?php echo number_format($percentage, 1); ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $color; ?> grade-badge">
                                                <?php echo $letterGrade; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($grade['date']); ?></td>
                                        <td>
                                            <a href="edit_grade.php?id=<?= $grade['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=grade&id=<?= $grade['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=grade&id=<?= $grade['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this grade"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('gradeChart').getContext('2d');
    const grades = <?php echo json_encode($grades); ?>;
    
    const labels = grades.map(g => g.exam_name);
    const data = grades.map(g => (g.marks_obtained / g.total_marks * 100).toFixed(1));
    const colors = data.map(p => {
        if (p >= 90) return '#28a745';
        if (p >= 80) return '#007bff';
        if (p >= 70) return '#ffc107';
        if (p >= 60) return '#fd7e14';
        return '#dc3545';
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Percentage',
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
                    max: 100
                }
            }
        }
    });
});
</script>
</div>

<?php include 'includes/footer.php'; ?>
