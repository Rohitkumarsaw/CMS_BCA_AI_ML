<?php
$pageTitle = 'Edit Announcement';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$announce_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($announce_id <= 0) {
    setFlashMessage('danger', 'Invalid announcement ID.');
    redirect('announcement.php');
}

$stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ? AND user_id = ?");
$stmt->execute([$announce_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Announcement not found.');
    redirect('announcement.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    $type = sanitize($_POST['type']);
    $priority = sanitize($_POST['priority']);

    if (empty($title) || empty($message) || empty($type) || empty($priority)) {
        $errors[] = 'Please fill in all required fields.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE announcements SET title = ?, message = ?, type = ?, priority = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $message, $type, $priority, $announce_id, $user_id]);

        setFlashMessage('success', 'Announcement updated successfully.');
        redirect('announcement.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Announcement</h4>
                    <p class="text-muted mb-0">Update announcement</p>
                </div>
                <a href="announcement.php" class="btn btn-secondary">
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
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required 
                                           value="<?= htmlspecialchars($record['title']) ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($record['message']) ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Type *</label>
                                        <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?= htmlspecialchars($record['type'] ?? '') ?>" placeholder="Homework, Exam, Holiday, Event, General, or custom">
                                        <datalist id="type_list">
                                            <option value="Homework">
                                            <option value="Exam">
                                            <option value="Holiday">
                                            <option value="Event">
                                            <option value="General">
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">Priority *</label>
                                        <input type="text" class="form-control" id="priority" name="priority" list="priority_list" required value="<?= htmlspecialchars($record['priority'] ?? '') ?>" placeholder="High, Medium, Low, or custom">
                                        <datalist id="priority_list">
                                            <option value="High">
                                            <option value="Medium">
                                            <option value="Low">
                                        </datalist>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Announcement
                                    </button>
                                    <a href="announcement.php" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
