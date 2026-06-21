<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Add Homework';
$extraCSS = ['style.css', 'homework.css'];
$extraJS = ['main.js', 'homework.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $due_date = sanitize($_POST['due_date'] ?? '');

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    if (empty($due_date)) {
        $errors[] = 'Due date is required.';
    }

    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_path = uploadFile($_FILES['file'], 'homework', ALLOWED_HOMEWORK_TYPES);
        if ($file_path === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_HOMEWORK_TYPES) . '. Max size: 5MB.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO homework (user_id, semester, title, subject, description, due_date, status, file_path, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Not Submitted', ?, NOW())");
        $stmt->execute([$user_id, $user_semester, $title, $subject, $description, $due_date, $file_path]);

        setFlashMessage('success', 'Homework added successfully.');
        notifyEmail('Homework', 'added');
        redirect('homework.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Homework</h4>
                    <p class="text-muted mb-0">Create a new homework assignment</p>
                </div>
                <a href="homework.php" class="btn btn-outline-secondary">
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
                            <h6 class="card-title mb-0"><i class="fas fa-book me-2 text-primary"></i>Homework Details</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($title ?? '') ?>" required placeholder="Enter homework title">
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
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" 
                                              required placeholder="Describe the homework assignment"><?= htmlspecialchars($description ?? '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="due_date" name="due_date" 
                                               value="<?= $due_date ?? '' ?>" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="file" class="form-label">Attachment (Optional)</label>
                                    <input type="file" class="form-control" id="file" name="file">
                                    <div class="form-text">Allowed: <?= implode(', ', ALLOWED_HOMEWORK_TYPES) ?> | Max size: 5MB</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Homework
                                    </button>
                                    <a href="homework.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>