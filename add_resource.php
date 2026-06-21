<?php
$pageTitle = 'Add Resource';
$extraCSS = ['style.css', 'resources.css'];
$extraJS = ['main.js', 'resources.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $type = sanitize($_POST['type']);
    $link = sanitize($_POST['link']);
    $subject = sanitize($_POST['subject']);
    $tags = sanitize($_POST['tags']);

    if (empty($name) || empty($type) || empty($subject)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_resource.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO resources (user_id, name, type, link, subject, tags, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $name, $type, $link, $subject, $tags]);

    $detail = "Title: " . $name . " - Type: " . $type;
    setFlashMessage('success', 'Resource added successfully');
    notifyEmail('Resource', 'added', $detail);
    header('Location: resources.php');
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Resource</h4>
                    <p class="text-muted mb-0">Add a learning resource</p>
                </div>
                <a href="resources.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Resource Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Python Crash Course">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <input type="text" class="form-control" id="type" name="type" list="type_list" required placeholder="Video, Article, Course, Book, Other, or custom">
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
                                <input type="url" class="form-control" id="link" name="link" placeholder="https://...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" required placeholder="e.g., Machine Learning">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="Comma separated tags, e.g., python, beginner, tutorial">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Resource
                            </button>
                            <a href="resources.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
