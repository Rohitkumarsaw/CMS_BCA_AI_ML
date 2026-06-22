<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Add Lab';
$extraCSS = ['style.css', 'lab.css'];
$extraJS = ['main.js', 'lab.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lab_name = sanitize($_POST['lab_name'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $date = sanitize($_POST['date'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if (empty($lab_name)) {
        $errors[] = 'Lab name is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($date)) {
        $errors[] = 'Date is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    $report_path = '';
    if (isset($_FILES['report']) && $_FILES['report']['error'] === UPLOAD_ERR_OK) {
        $report_path = uploadFile($_FILES['report'], 'labs', ALLOWED_NOTE_TYPES);
        if ($report_path === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_NOTE_TYPES) . '. Max size: 5MB.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO labs (user_id, lab_name, subject, date, status, report_path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $lab_name, $subject, $date, $status, $report_path]);

        $detail = "Title: " . $lab_name . " - Subject: " . $subject;
        setFlashMessage('success', 'Lab work added successfully.');
        notifyEmail('Lab', 'added', $detail);
        logActivity($pdo, $user_id, $_SESSION['user_name'] ?? 'User', 'Added', 'Lab', $pdo->lastInsertId(), $detail);
        redirect('lab.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Lab Work</h4>
                    <p class="text-muted mb-0">Add a new lab assignment</p>
                </div>
                <a href="lab.php" class="btn btn-outline-secondary">
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
                            <h6 class="card-title mb-0"><i class="fas fa-flask me-2 text-primary"></i>Lab Details</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="lab_name" class="form-label">Lab Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-flask"></i></span>
                                        <input type="text" class="form-control" id="lab_name" name="lab_name" 
                                               value="<?= htmlspecialchars($lab_name ?? '') ?>" required placeholder="Enter lab name">
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
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= $date ?? date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                        <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($status ?? '') ?>" placeholder="Completed, In Progress, Not Started, or custom">
                                    </div>
                                    <datalist id="status_list">
                                        <option value="Completed">
                                        <option value="In Progress">
                                        <option value="Not Started">
                                    </datalist>
                                </div>

                                <div class="mb-4">
                                    <label for="report" class="form-label">Lab Report (Optional)</label>
                                    <input type="file" class="form-control" id="report" name="report">
                                    <div class="form-text">Allowed: <?= implode(', ', ALLOWED_NOTE_TYPES) ?> | Max size: 5MB</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Lab
                                    </button>
                                    <a href="lab.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
