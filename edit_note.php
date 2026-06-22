<?php
$pageTitle = 'Edit Note';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($note_id <= 0) {
    setFlashMessage('danger', 'Invalid note ID.');
    redirect('notes.php');
}

$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$note_id, $user_id]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$note) {
    setFlashMessage('danger', 'Note not found.');
    redirect('notes.php');
}

$errors = [];
$old_file = $note['file_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $type = sanitize($_POST['type'] ?? '');
    $semester = (int)($_POST['semester'] ?? 0);
    $tags = sanitize($_POST['tags'] ?? '');

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($type)) {
        $errors[] = 'Type is required.';
    }

    $file_path = $old_file;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $new_file = uploadFile($_FILES['file'], 'notes', ALLOWED_NOTE_TYPES);
        if ($new_file === false) {
            $errors[] = 'Invalid file. Allowed types: ' . implode(', ', ALLOWED_NOTE_TYPES) . '. Max size: 5MB.';
        } else {
            if (!empty($old_file) && file_exists($old_file)) unlink($old_file);
            $file_path = $new_file;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE notes SET title = ?, subject = ?, type = ?, file_path = ?, semester = ?, tags = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $subject, $type, $file_path, $semester, $tags, $note_id, $user_id]);

        $detail = "Title: " . $title . " - Subject: " . $subject;
        setFlashMessage('success', 'Note updated successfully.');
        notifyEmail('Note', 'updated', $detail);
        logActivity($pdo, $user_id, $_SESSION['user_name'] ?? 'User', 'Updated', 'Note', $note_id, $detail);
        redirect('notes.php');
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Note</h2>
            <a href="notes.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Notes
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
                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
<form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?= htmlspecialchars($note['title']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required value="<?= htmlspecialchars($note['subject'] ?? '') ?>" placeholder="Type or select a subject">
                            <datalist id="subject_list">
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?= $subj ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?= htmlspecialchars($note['type'] ?? '') ?>" placeholder="PDF, Image, Text, Video, or custom">
                            <datalist id="type_list">
                                <option value="PDF">
                                <option value="Image">
                                <option value="Text">
                                <option value="Video">
                            </datalist>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $note['semester'] == $i ? 'selected' : '' ?>>
                                        Semester <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="file" class="form-label">File</label>
                            <?php if (!empty($old_file)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="fas fa-file me-1"></i>Current file attached</span>
                                    <a href="<?= htmlspecialchars($old_file) ?>" class="ms-2 small" download>Download</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="file" name="file">
                            <small class="text-muted">Leave empty to keep current file. PDF, Images, Text files, Videos</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                   value="<?= htmlspecialchars($note['tags'] ?? '') ?>"
                                   placeholder="Comma separated tags">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Note
                        </button>
                        <a href="notes.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
