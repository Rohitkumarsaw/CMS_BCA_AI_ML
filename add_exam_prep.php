<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Add Exam Prep';
$extraCSS = ['style.css', 'exam_prep.css'];
$extraJS = ['main.js', 'exam_prep.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_name = sanitize($_POST['exam_name'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $topics_to_cover = sanitize($_POST['topics_to_cover'] ?? '');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $progress = (int)($_POST['progress'] ?? 0);

    if (empty($exam_name)) {
        $errors[] = 'Exam name is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($topics_to_cover)) {
        $errors[] = 'Topics to cover is required.';
    }
    if (empty($start_date)) {
        $errors[] = 'Start date is required.';
    }
    if (empty($end_date)) {
        $errors[] = 'End date is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }
    if ($progress < 0 || $progress > 100) {
        $errors[] = 'Progress must be between 0 and 100.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO exam_prep (user_id, exam_name, subject, topics_to_cover, start_date, end_date, status, progress, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $exam_name, $subject, $topics_to_cover, $start_date, $end_date, $status, $progress]);

        setFlashMessage('success', 'Exam preparation added successfully.');
        notifyEmail('Exam Preparation', 'added');
        redirect('exam_prep.php');
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Exam Prep</h4>
                    <p class="text-muted mb-0">Add a new exam preparation plan</p>
                </div>
                <a href="exam_prep.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-brain me-2 text-primary"></i>Exam Prep Details</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="exam_name" class="form-label">Exam Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                        <input type="text" class="form-control" id="exam_name" name="exam_name" 
                                               value="<?= htmlspecialchars($exam_name ?? '') ?>" required placeholder="Enter exam name">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required placeholder="Type or select a subject">
                                    </div>
                                    <datalist id="subject_list">
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <div class="mb-3">
                                    <label for="topics_to_cover" class="form-label">Topics to Cover <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="topics_to_cover" name="topics_to_cover" rows="4" 
                                              required placeholder="List the topics you need to cover"><?= htmlspecialchars($topics_to_cover ?? '') ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?= $start_date ?? date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?= $end_date ?? '' ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                            <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($status ?? '') ?>" placeholder="Planned, In Progress, Completed, or custom">
                                        </div>
                                    </div>
                                    <datalist id="status_list">
                                        <option value="Planned">
                                        <option value="In Progress">
                                        <option value="Completed">
                                    </datalist>
                                    <div class="col-md-6 mb-3">
                                        <label for="progress" class="form-label">Progress (%) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                                            <input type="number" class="form-control" id="progress" name="progress" 
                                                   min="0" max="100" value="<?= $progress ?? 0 ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Exam Prep
                                    </button>
                                    <a href="exam_prep.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
