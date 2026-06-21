<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Edit Circular';
$extraCSS = ['style.css', 'circular.css'];
$extraJS = ['main.js', 'circular.js'];

$user_id = $_SESSION['user_id'];

$circular_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($circular_id <= 0) {
    setFlashMessage('danger', 'Invalid circular ID.');
    redirect('circular.php');
}

$stmt = $pdo->prepare("SELECT * FROM circulars WHERE id = ? AND user_id = ?");
$stmt->execute([$circular_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Circular not found.');
    redirect('circular.php');
}

$errors = [];
$old_file = $record['file_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $date = sanitize($_POST['date'] ?? '');

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    if (empty($date)) {
        $errors[] = 'Date is required.';
    }

    $file_path = $old_file;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $new_file = uploadFile($_FILES['file'], 'circulars', ALLOWED_NOTE_TYPES);
        if ($new_file === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_NOTE_TYPES) . '. Max size: 5MB.';
        } else {
            if (!empty($old_file) && file_exists($old_file)) unlink($old_file);
            $file_path = $new_file;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE circulars SET title = ?, message = ?, date = ?, file_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $message, $date, $file_path, $circular_id, $user_id]);

        setFlashMessage('success', 'Circular updated successfully.');
        notifyEmail('Circular', 'updated');
        redirect('circular.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Circular</h4>
                    <p class="text-muted mb-0">Update college circular</p>
                </div>
                <a href="circular.php" class="btn btn-outline-secondary">
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
                            <h6 class="card-title mb-0"><i class="fas fa-scroll me-2 text-primary"></i>Circular Details</h6>
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
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              required><?= htmlspecialchars($record['message']) ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= $record['date'] ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="file" class="form-label">Attachment (Optional)</label>
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
                                        <i class="fas fa-save me-1"></i>Update Circular
                                    </button>
                                    <a href="circular.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
