<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Update Syllabus';
$extraCSS = ['style.css', 'syllabus.css'];
$extraJS = ['main.js', 'syllabus.js'];

$userId = $_SESSION['user_id'];
$semester = $_GET['semester'] ?? $_SESSION['user_semester'] ?? 1;
$selectedSubject = $_GET['subject'] ?? '';
$errors = [];
$success = '';

$subjects = getSemesterSubjects($semester);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = intval($_POST['semester'] ?? 1);
    $subject = sanitize($_POST['subject'] ?? '');
    $topicName = sanitize($_POST['topic_name'] ?? '');
    $status = sanitize($_POST['status'] ?? 'Not Started');

    if(empty($subject)) $errors[] = 'Subject is required';
    if(empty($topicName)) $errors[] = 'Topic name is required';
    if(empty($status)) $errors[] = 'Status is required';

    if(empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM syllabus WHERE user_id = ? AND semester = ? AND subject = ? AND topic = ?");
        $stmt->execute([$userId, $semester, $subject, $topicName]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if($existing) {
            $stmt = $pdo->prepare("UPDATE syllabus SET status = ? WHERE id = ?");
            $stmt->execute([$status, $existing['id']]);
            setFlashMessage('success', 'Topic status updated successfully!');
            notifyEmail('Syllabus', 'updated');
        } else {
            $stmt = $pdo->prepare("INSERT INTO syllabus (user_id, semester, subject, topic, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $semester, $subject, $topicName, $status]);
            setFlashMessage('success', 'New topic added successfully!');
            notifyEmail('Syllabus', 'added');
        }
        
        header('Location: syllabus.php?semester=' . $semester);
        exit();
    }
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Update Syllabus</h2>
            <a href="syllabus.php?semester=<?php echo $semester; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Syllabus
            </a>
        </div>

        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="" id="syllabusForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="semester" class="form-label">Semester *</label>
                                    <select class="form-select" id="semester" name="semester" required onchange="loadSubjects()">
                                        <?php for($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                            Semester <?php echo $i; ?>
                                        </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required value="<?php echo htmlspecialchars($selectedSubject ?? ''); ?>" placeholder="Type or select a subject">
                                    <datalist id="subject_list">
                                        <?php foreach($subjects as $subject): ?>
                                        <option value="<?php echo htmlspecialchars($subject); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="topic" class="form-label">Topic Name *</label>
                                    <input type="text" class="form-control" id="topic" name="topic_name" 
                                           value="<?php echo htmlspecialchars($_POST['topic_name'] ?? ''); ?>" 
                                           placeholder="e.g., Introduction to Neural Networks" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?php echo htmlspecialchars($_POST['status'] ?? ''); ?>" placeholder="Not Started, In Progress, Completed, or custom">
                                    <datalist id="status_list">
                                        <option value="Not Started">
                                        <option value="In Progress">
                                        <option value="Completed">
                                    </datalist>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="syllabus.php?semester=<?php echo $semester; ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Topic
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-success me-2">Completed</span>
                            <small>Topic has been fully covered</small>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-warning me-2">In Progress</span>
                            <small>Currently studying this topic</small>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-secondary me-2">Not Started</span>
                            <small>Topic not yet started</small>
                        </div>
                        <hr>
                        <h6>Tips:</h6>
                        <ul class="small text-muted">
                            <li>Update status regularly to track progress</li>
                            <li>Mark topics as completed once fully understood</li>
                            <li>Add all topics from your syllabus</li>
                        </ul>
                    </div>
                </div>

                <?php if(!empty($selectedSubject)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Topics</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM syllabus WHERE user_id = ? AND semester = ? AND subject = ? ORDER BY created_at DESC");
                        $stmt->execute([$userId, $semester, $selectedSubject]);
                        $existingTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <?php if(!empty($existingTopics)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($existingTopics as $topic): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?php echo htmlspecialchars($topic['topic']); ?></span>
                                <span class="badge bg-<?php 
                                    echo $topic['status'] == 'Completed' ? 'success' : 
                                         ($topic['status'] == 'In Progress' ? 'warning' : 'secondary'); 
                                ?>"><?php echo $topic['status']; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center mb-0">No topics added yet</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
