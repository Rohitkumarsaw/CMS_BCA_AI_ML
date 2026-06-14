<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Edit Presentation';
$extraCSS = ['style.css', 'presentation.css'];
$extraJS = ['main.js', 'presentation.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$pres_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pres_id <= 0) {
    setFlashMessage('danger', 'Invalid presentation ID.');
    redirect('presentation.php');
}

$stmt = $pdo->prepare("SELECT * FROM presentations WHERE id = ? AND user_id = ?");
$stmt->execute([$pres_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Presentation not found.');
    redirect('presentation.php');
}

$errors = [];
$old_file = $record['file_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $date = sanitize($_POST['date'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if (empty($title)) {
        $errors[] = 'Title is required.';
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

    $file_path = $old_file;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $new_file = uploadFile($_FILES['file'], 'presentations', ALLOWED_NOTE_TYPES);
        if ($new_file === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_NOTE_TYPES) . '. Max size: 5MB.';
        } else {
            if (!empty($old_file) && file_exists($old_file)) unlink($old_file);
            $file_path = $new_file;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE presentations SET title = ?, subject = ?, date = ?, status = ?, file_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $subject, $date, $status, $file_path, $pres_id, $user_id]);

        setFlashMessage('success', 'Presentation updated successfully.');
        redirect('presentation.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Presentation</h4>
                    <p class="text-muted mb-0">Update presentation details</p>
                </div>
                <a href="presentation.php" class="btn btn-outline-secondary">
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
                            <h6 class="card-title mb-0"><i class="fas fa-desktop me-2 text-primary"></i>Presentation Details</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
<form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($record['title']) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required value="<?= htmlspecialchars($record['subject'] ?? '') ?>" placeholder="Type or select a subject">
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
                                               value="<?= $record['date'] ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                        <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($record['status'] ?? '') ?>" placeholder="Planned, Done, or custom">
                                    </div>
                                    <datalist id="status_list">
                                        <option value="Planned">
                                        <option value="Done">
                                    </datalist>
                                </div>

                                <div class="mb-4">
                                    <label for="file" class="form-label">Presentation File (Optional)</label>
                                    <?php if (!empty($old_file)): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-success"><i class="fas fa-file me-1"></i>Current file attached</span>
                                            <a href="<?= htmlspecialchars($old_file) ?>" class="ms-2 small" download>Download</a>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="file" name="file">
                                    <div class="form-text">Allowed: <?= implode(', ', ALLOWED_NOTE_TYPES) ?> | Max size: 5MB. Leave empty to keep current file.</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Presentation
                                    </button>
                                    <a href="presentation.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
