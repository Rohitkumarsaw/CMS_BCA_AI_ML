<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Exams';
$extraCSS = ['style.css', 'exam.css'];
$extraJS = ['main.js'];

$userId = $_SESSION['user_id'];
$semester = $_GET['semester'] ?? $_SESSION['user_semester'] ?? 1;
$subjectFilter = $_GET['subject'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$where = "WHERE user_id = ?";
$params = [$userId];

if(!empty($semester)) {
    $where .= " AND semester = ?";
    $params[] = $semester;
}
if(!empty($subjectFilter)) {
    $where .= " AND subject LIKE ?";
    $params[] = '%' . $subjectFilter . '%';
}
if(!empty($typeFilter)) {
    $where .= " AND type = ?";
    $params[] = $typeFilter;
}

$pagi = paginate($pdo, 'exams', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM exams $where ORDER BY date ASC, start_time ASC" . $pagi['sql']);
$stmt->execute($params);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtSubjects = $pdo->prepare("SELECT DISTINCT subject FROM exams WHERE user_id = ? ORDER BY subject");
$stmtSubjects->execute([$userId]);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_COLUMN);

$upcomingExams = array_filter($exams, function($exam) {
    $examDate = strtotime($exam['date']);
    $threeDaysLater = strtotime('+3 days');
    return $examDate >= strtotime('today') && $examDate <= $threeDaysLater;
});

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">

    <?php if(!empty($upcomingExams)): ?>
    <div class="exam-upcoming-banner">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Upcoming Exams (Next 3 Days)</h5>
        <ul>
            <?php foreach($upcomingExams as $exam): ?>
            <li><strong><?php echo htmlspecialchars($exam['subject']); ?></strong> — <?php echo htmlspecialchars($exam['exam_name']); ?> on <?php echo formatDate($exam['date']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="page-header">
        <div>
            <h2><i class="fas fa-file-alt"></i> Examinations</h2>
            <p>Manage and view your exam schedule</p>
        </div>
        <a href="add_exam.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>Add Exam
        </a>
    </div>

    <div class="exam-counter-grid">
        <div class="counter-card">
            <div class="counter-top">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Total Exams</span>
            </div>
            <h3 class="counter-number"><?php echo count($exams); ?></h3>
        </div>
        <div class="counter-card">
            <div class="counter-top" style="color: #22d3ee;">
                <i class="fa-solid fa-pencil"></i>
                <span>Theory</span>
            </div>
            <h3 class="counter-number" style="color: #22d3ee;"><?php echo count(array_filter($exams, function($e) { return $e['type'] == 'Theory'; })); ?></h3>
        </div>
        <div class="counter-card">
            <div class="counter-top" style="color: #86efac;">
                <i class="fa-solid fa-flask"></i>
                <span>Practical</span>
            </div>
            <h3 class="counter-number" style="color: #86efac;"><?php echo count(array_filter($exams, function($e) { return $e['type'] == 'Practical'; })); ?></h3>
        </div>
        <div class="counter-card">
            <div class="counter-top" style="color: #fde68a;">
                <i class="fa-solid fa-clock"></i>
                <span>Upcoming</span>
            </div>
            <h3 class="counter-number" style="color: #fde68a;"><?php echo count($upcomingExams); ?></h3>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <select class="form-select" name="semester">
                        <option value="">All Semesters</option>
                        <?php for($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                            Semester <?php echo $i; ?>
                        </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" name="subject" 
                           placeholder="Search subject..." 
                           value="<?php echo htmlspecialchars($subjectFilter); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="Theory" <?php echo $typeFilter == 'Theory' ? 'selected' : ''; ?>>Theory</option>
                        <option value="Practical" <?php echo $typeFilter == 'Practical' ? 'selected' : ''; ?>>Practical</option>
                        <option value="Viva" <?php echo $typeFilter == 'Viva' ? 'selected' : ''; ?>>Viva</option>
                        <option value="Internal" <?php echo $typeFilter == 'Internal' ? 'selected' : ''; ?>>Internal</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>Filter</button>
                    <a href="exam.php" class="btn btn-outline-secondary"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if(empty($exams)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h5>No exams found</h5>
                <p>Add your first exam to get started</p>
                <a href="add_exam.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Exam</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Exam Name</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($exams as $index => $exam): 
                            $examDate = strtotime($exam['date']);
                            $isUpcoming = $examDate >= strtotime('today') && $examDate <= strtotime('+3 days');
                            $isPast = $examDate < strtotime('today');
                        ?>
                        <tr class="<?php echo $isUpcoming ? 'exam-upcoming' : ''; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td><strong><?php echo htmlspecialchars($exam['exam_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($exam['subject']); ?></td>
                            <td><?php echo formatDate($exam['date']); ?></td>
                            <td><?php echo date('h:i A', strtotime($exam['start_time'])).' - '.date('h:i A', strtotime($exam['end_time'])); ?></td>
                            <td><?php echo htmlspecialchars($exam['room_no']); ?></td>
                            <td>
                                <span class="status-badge type-<?php echo strtolower($exam['type']); ?>">
                                    <?php echo $exam['type']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($isPast): ?>
                                    <span class="badge bg-secondary">Completed</span>
                                <?php elseif($isUpcoming): ?>
                                    <span class="badge bg-danger">Upcoming</span>
                                <?php else: ?>
                                    <span class="badge bg-info">Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_exam.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="view.php?type=exam&id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="delete.php?type=exam&id=<?= $exam['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this exam"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
</div>

<?php require 'includes/footer.php'; ?>
