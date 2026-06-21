<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Add Circular';
$extraCSS = ['style.css', 'circular.css'];
$extraJS = ['main.js', 'circular.js'];

$user_id = $_SESSION['user_id'];
$errors = [];

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

    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_path = uploadFile($_FILES['file'], 'circulars', ALLOWED_NOTE_TYPES);
        if ($file_path === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_NOTE_TYPES) . '. Max size: 5MB.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO circulars (user_id, title, message, date, file_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $message, $date, $file_path]);

        $detail = "Title: " . $title;
        setFlashMessage('success', 'Circular added successfully.');
        notifyEmail('Circular', 'added', $detail);
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Circular</h4>
                    <p class="text-muted mb-0">Add a new college circular</p>
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
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($title ?? '') ?>" required placeholder="Enter circular title">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              required placeholder="Enter circular message"><?= htmlspecialchars($message ?? '') ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= $date ?? date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="file" class="form-label">Attachment (Optional)</label>
                                    <input type="file" class="form-control" id="file" name="file">
                                    <div class="form-text">Allowed: <?= implode(', ', ALLOWED_NOTE_TYPES) ?> | Max size: 5MB</div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Circular
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
