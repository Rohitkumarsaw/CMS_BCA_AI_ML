<?php
$pageTitle = 'Add Event';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventName = sanitize($_POST['event_name']);
    $date = sanitize($_POST['date']);
    $time = sanitize($_POST['time']);
    $location = sanitize($_POST['location']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);

    if (empty($eventName) || empty($date) || empty($type)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_event.php');
        exit;
    }

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imagePath = uploadFile($_FILES['image'], 'events', ALLOWED_IMAGE_TYPES);
        if (!$imagePath) {
            setFlashMessage('danger', 'Image upload failed or invalid file type');
            header('Location: add_event.php');
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO events (user_id, event_name, date, time, location, description, type, image_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $userId,
        $eventName,
        $date,
        $time,
        $location,
        $description,
        $type,
        $imagePath
    ]);

    setFlashMessage('success', 'Event added successfully');
    header('Location: event.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Event</h4>
                    <p class="text-muted mb-0">Record a new event</p>
                </div>
                <a href="event.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="event_name" class="form-label">Event Name *</label>
                                <input type="text" class="form-control" id="event_name" name="event_name" required placeholder="Enter event name">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type *</label>
                                <input type="text" class="form-control" id="type" name="type" list="type_list" required placeholder="Sports, Technical, Cultural, Workshop, Other, or custom">
                                <datalist id="type_list">
                                    <option value="Sports">
                                    <option value="Technical">
                                    <option value="Cultural">
                                    <option value="Workshop">
                                    <option value="Other">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="time" name="time">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., College Auditorium">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label">Event Image</label>
                                <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
                                <small class="text-muted">Allowed: JPG, PNG, GIF</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe the event"></textarea>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Event
                            </button>
                            <a href="event.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
