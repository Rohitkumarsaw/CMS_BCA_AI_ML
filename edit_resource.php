<?php
$pageTitle = 'Edit Resource';
$extraCSS = ['style.css', 'resources.css'];
$extraJS = ['main.js', 'resources.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$resource_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($resource_id <= 0) {
    setFlashMessage('danger', 'Invalid resource ID.');
    redirect('resources.php');
}

$stmt = $pdo->prepare("SELECT * FROM resources WHERE id = ? AND user_id = ?");
$stmt->execute([$resource_id, $user_id]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resource) {
    setFlashMessage('danger', 'Resource not found.');
    redirect('resources.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $type = sanitize($_POST['type'] ?? '');
    $link = sanitize($_POST['link'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $tags = sanitize($_POST['tags'] ?? '');

    if (empty($name)) {
        $errors[] = 'Resource name is required.';
    }
    if (empty($type)) {
        $errors[] = 'Type is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE resources SET name = ?, type = ?, link = ?, subject = ?, tags = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $type, $link, $subject, $tags, $resource_id, $user_id]);

        $detail = "Title: " . $name . " - Type: " . $type;
        setFlashMessage('success', 'Resource updated successfully.');
        notifyEmail('Resource', 'updated', $detail);
        redirect('resources.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Resource</h4>
                    <p class="text-muted mb-0">Update learning resource</p>
                </div>
                <a href="resources.php" class="btn btn-secondary">
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

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
<form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Resource Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= htmlspecialchars($resource['name']) ?>"
                                       placeholder="e.g., Python Crash Course">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?= htmlspecialchars($resource['type'] ?? '') ?>" placeholder="Video, Article, Course, Book, Other, or custom">
                                <datalist id="type_list">
                                    <option value="Video">
                                    <option value="Article">
                                    <option value="Course">
                                    <option value="Book">
                                    <option value="Other">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="link" class="form-label">Link</label>
                                <input type="url" class="form-control" id="link" name="link"
                                       value="<?= htmlspecialchars($resource['link'] ?? '') ?>"
                                       placeholder="https://...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required
                                       value="<?= htmlspecialchars($resource['subject']) ?>"
                                       placeholder="e.g., Machine Learning">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags"
                                       value="<?= htmlspecialchars($resource['tags'] ?? '') ?>"
                                       placeholder="Comma separated tags, e.g., python, beginner, tutorial">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Resource
                            </button>
                            <a href="resources.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
