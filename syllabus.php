<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Syllabus Tracker';
$extraCSS = ['style.css', 'syllabus.css'];
$extraJS = ['main.js', 'syllabus.js'];

$userId = $_SESSION['user_id'];
$semester = $_GET['semester'] ?? $_SESSION['user_semester'] ?? 1;

$subjects = getSemesterSubjects($semester);

$subjectProgress = [];
$totalTopics = 0;
$completedTopics = 0;
$inProgressTopics = 0;

foreach($subjects as $subject) {
    $stmt = $pdo->prepare("SELECT * FROM syllabus WHERE user_id = ? AND semester = ? AND subject = ?");
    $stmt->execute([$userId, $semester, $subject]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $subjectTotal = count($topics);
    $subjectCompleted = count(array_filter($topics, function($t) { return $t['status'] == 'Completed'; }));
    $subjectInProgress = count(array_filter($topics, function($t) { return $t['status'] == 'In Progress'; }));
    
    $progress = $subjectTotal > 0 ? round(($subjectCompleted / $subjectTotal) * 100) : 0;
    
    $subjectProgress[$subject] = [
        'topics' => $topics,
        'total' => $subjectTotal,
        'completed' => $subjectCompleted,
        'in_progress' => $subjectInProgress,
        'not_started' => $subjectTotal - $subjectCompleted - $subjectInProgress,
        'progress' => $progress
    ];
    
    $totalTopics += $subjectTotal;
    $completedTopics += $subjectCompleted;
    $inProgressTopics += $subjectInProgress;
}

$overallProgress = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book-open me-2"></i>Syllabus Tracker</h2>
            <div class="d-flex gap-2">
                <a href="manage_subjects.php?semester=<?php echo $semester; ?>" class="btn btn-outline-primary">
                    <i class="fas fa-book me-1"></i>Manage Subjects
                </a>
                <a href="update_syllabus.php?semester=<?php echo $semester; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>Add / Edit Topics
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-list-ol me-2"></i>Total Topics</h5>
                        <h3><?php echo $totalTopics; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-check-circle me-2"></i>Completed</h5>
                        <h3><?php echo $completedTopics; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-spinner me-2"></i>In Progress</h5>
                        <h3><?php echo $inProgressTopics; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-percentage me-2"></i>Overall Progress</h5>
                        <h3><?php echo $overallProgress; ?>%</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label">Select Semester</label>
                        <select class="form-select" id="semesterFilter" onchange="filterSyllabus()">
                            <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                Semester <?php echo $i; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Overall Syllabus Progress</label>
                        <div class="progress" style="height: 30px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?php echo $overallProgress; ?>%"
                                 aria-valuenow="<?php echo $overallProgress; ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <?php echo $overallProgress; ?>% Complete
                            </div>
                        </div>
                        <small class="text-muted">
                            <?php echo $completedTopics; ?> completed, <?php echo $inProgressTopics; ?> in progress, 
                            <?php echo $totalTopics - $completedTopics - $inProgressTopics; ?> not started out of <?php echo $totalTopics; ?> topics
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <?php foreach($subjectProgress as $subject => $data): ?>
        <div class="card mb-3 subject-card">
            <div class="card-header d-flex justify-content-between align-items-center" 
                 data-bs-toggle="collapse" 
                 data-bs-target="#collapse<?php echo md5($subject); ?>"
                 style="cursor: pointer;">
                <h5 class="mb-0">
                    <i class="fas fa-book me-2"></i><?php echo htmlspecialchars($subject); ?>
                </h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-<?php echo $data['progress'] == 100 ? 'success' : ($data['progress'] >= 50 ? 'warning' : 'danger'); ?> me-2">
                        <?php echo $data['progress']; ?>%
                    </span>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div id="collapse<?php echo md5($subject); ?>" class="collapse show">
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progress</span>
                            <span><?php echo $data['completed']; ?>/<?php echo $data['total']; ?> topics completed</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo $data['progress']; ?>%"
                                 aria-valuenow="<?php echo $data['progress']; ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <?php if(!empty($data['topics'])): ?>
                    <div class="section-search-container">
                        <i class="fas fa-search section-search-icon"></i>
                        <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#syllabus-<?php echo md5($subject); ?> tbody">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm" id="syllabus-<?php echo md5($subject); ?>">
                            <thead>
                                <tr>
                                    <th>Topic</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data['topics'] as $topic): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($topic['topic']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $topic['status'] == 'Completed' ? 'success' : 
                                                 ($topic['status'] == 'In Progress' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo $topic['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $topic['status']; ?></td>
                                    <td>
                                        <a href="update_syllabus.php?semester=<?php echo $semester; ?>&subject=<?php echo urlencode($subject); ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="delete.php?type=syllabus&id=<?= $topic['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this topic"><i class="fas fa-trash"></i></a>
                                        <a href="view.php?type=syllabus&id=<?= $topic['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-info-circle me-1"></i>No topics added yet. 
                        <a class="btn btn-primary btn-sm" href="update_syllabus.php?semester=<?php echo $semester; ?>&subject=<?php echo urlencode($subject); ?>"><i class="fas fa-plus"></i> Add topics</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterSyllabus() {
    const semester = document.getElementById('semesterFilter').value;
    window.location.href = 'syllabus.php?semester=' + semester;
}
</script>

<?php require 'includes/footer.php'; ?>
