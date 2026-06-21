<?php
$pageTitle = 'Edit Event';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    setFlashMessage('danger', 'Invalid event ID.');
    redirect('event.php');
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->execute([$event_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Event not found.');
    redirect('event.php');
}

$errors = [];
$old_image = $record['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = sanitize($_POST['event_name']);
    $date = sanitize($_POST['date']);
    $time = sanitize($_POST['time']);
    $location = sanitize($_POST['location']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);

    if (empty($event_name) || empty($date) || empty($type)) {
        $errors[] = 'Please fill in all required fields.';
    }

    $image_path = $old_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image = uploadFile($_FILES['image'], 'events', ALLOWED_IMAGE_TYPES);
        if ($new_image === false) {
            $errors[] = 'Image upload failed. Allowed types: ' . implode(', ', ALLOWED_IMAGE_TYPES) . '.';
        } else {
            if (!empty($old_image) && file_exists($old_image)) unlink($old_image);
            $image_path = $new_image;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE events SET event_name = ?, date = ?, time = ?, location = ?, description = ?, type = ?, image_path = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([
            $event_name,
            $date,
            $time,
            $location,
            $description,
            $type,
            $image_path,
            $event_id,
            $user_id
        ]);

        $detail = "Event: " . $event_name . " - Date: " . $date;
        setFlashMessage('success', 'Event updated successfully.');
        notifyEmail('Event', 'updated', $detail);
        redirect('event.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Event</h4>
                    <p class="text-muted mb-0">Update event details</p>
                </div>
                <a href="event.php" class="btn btn-secondary">
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
                            <form method="POST" enctype="multipart/form-data">
                                <?= csrfField() ?>
<form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="event_name" class="form-label">Event Name *</label>
                                        <input type="text" class="form-control" id="event_name" name="event_name" required 
                                               value="<?= htmlspecialchars($record['event_name']) ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Type *</label>
                                        <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?= htmlspecialchars($record['type'] ?? '') ?>" placeholder="Sports, Technical, Cultural, Workshop, Other, or custom">
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
                                        <input type="date" class="form-control" id="date" name="date" required 
                                               value="<?= $record['date'] ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="time" class="form-label">Time</label>
                                        <input type="time" class="form-control" id="time" name="time" 
                                               value="<?= htmlspecialchars($record['time'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location" 
                                               value="<?= htmlspecialchars($record['location'] ?? '') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="image" class="form-label">Event Image</label>
                                        <?php if (!empty($old_image)): ?>
                                            <div class="mb-2">
                                                <span class="badge bg-success"><i class="fas fa-image me-1"></i>Current image</span>
                                                <a href="<?= htmlspecialchars($old_image) ?>" class="ms-2 small" download>Download</a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
                                        <small class="text-muted">Allowed: JPG, PNG, GIF. Leave empty to keep current image.</small>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($record['description'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Event
                                    </button>
                                    <a href="event.php" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
