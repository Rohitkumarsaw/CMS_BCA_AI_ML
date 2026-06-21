<?php
$pageTitle = 'Add Announcement';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $message = sanitize($_POST['message']);
    $type = sanitize($_POST['type']);
    $priority = sanitize($_POST['priority']);

    if (empty($title) || empty($message) || empty($type) || empty($priority)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_announcement.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO announcements (user_id, title, message, type, priority, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $title, $message, $type, $priority]);

    $detail = "Title: " . $title . " - Priority: " . $priority;
    setFlashMessage('success', 'Announcement added successfully');
    notifyEmail('Announcement', 'added', $detail);
    header('Location: announcement.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Announcement</h4>
                    <p class="text-muted mb-0">Create a new announcement</p>
                </div>
                <a href="announcement.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
<form method="POST">
    <?= csrfField() ?>
    <div class="mb-3">
                                    <label for="title" class="form-label">Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required placeholder="Enter announcement title">
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Write your announcement message..."></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Type *</label>
                                        <input type="text" class="form-control" id="type" name="type" list="type_list" required placeholder="Homework, Exam, Holiday, Event, General, or custom">
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
                                        <input type="text" class="form-control" id="priority" name="priority" list="priority_list" required placeholder="High, Medium, Low, or custom">
                                        <datalist id="priority_list">
                                            <option value="High">
                                            <option value="Medium">
                                            <option value="Low">
                                        </datalist>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Announcement
                                    </button>
                                    <a href="announcement.php" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Guidelines</h6>
                        </div>
                        <div class="card-body">
                            <h6>Announcement Types:</h6>
                            <ul class="list-unstyled small">
                                <li class="mb-1"><span class="badge bg-warning me-1">Homework</span> Assignment related</li>
                                <li class="mb-1"><span class="badge bg-danger me-1">Exam</span> Exam notifications</li>
                                <li class="mb-1"><span class="badge bg-success me-1">Holiday</span> Holiday notices</li>
                                <li class="mb-1"><span class="badge bg-purple me-1">Event</span> Event announcements</li>
                                <li class="mb-1"><span class="badge bg-secondary me-1">General</span> General announcements</li>
                            </ul>
                            <hr>
                            <h6>Priority Levels:</h6>
                            <ul class="list-unstyled small">
                                <li class="mb-1"><span class="badge bg-danger me-1">High</span> Urgent / Important</li>
                                <li class="mb-1"><span class="badge bg-warning me-1">Medium</span> Moderate</li>
                                <li class="mb-1"><span class="badge bg-info me-1">Low</span> Informational</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
