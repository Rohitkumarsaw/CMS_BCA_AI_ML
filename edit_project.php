<?php
$pageTitle = 'Edit Project';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    setFlashMessage('danger', 'Invalid project ID.');
    redirect('projects.php');
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    setFlashMessage('danger', 'Project not found.');
    redirect('projects.php');
}

$errors = [];
$old_file = $project['file_path'];
$old_image = $project['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $semester = (int)($_POST['semester'] ?? 0);
    $subject = sanitize($_POST['subject'] ?? '');
    $tech_stack = sanitize($_POST['tech_stack'] ?? '');
    $link = sanitize($_POST['link'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');

    if (empty($title)) {
        $errors[] = 'Title is required.';
    }
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    if (empty($category)) {
        $errors[] = 'Category is required.';
    }

    $file_path = $old_file;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $new_file = uploadFile($_FILES['file'], 'projects');
        if ($new_file === false) {
            $errors[] = 'File upload failed.';
        } else {
            if (!empty($old_file) && file_exists($old_file)) unlink($old_file);
            $file_path = $new_file;
        }
    }

    $image_path = $old_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $new_image = uploadFile($_FILES['image'], 'projects', ALLOWED_IMAGE_TYPES);
        if ($new_image === false) {
            $errors[] = 'Image upload failed or invalid type. Allowed: ' . implode(', ', ALLOWED_IMAGE_TYPES);
        } else {
            if (!empty($old_image) && file_exists($old_image)) unlink($old_image);
            $image_path = $new_image;
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, semester = ?, subject = ?, tech_stack = ?, file_path = ?, image_path = ?, link = ?, category = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $semester, $subject, $tech_stack, $file_path, $image_path, $link, $category, $start_date, $end_date, $project_id, $user_id]);

        setFlashMessage('success', 'Project updated successfully.');
        notifyEmail('Project', 'updated');
        redirect('projects.php');
    }
}

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-edit me-2"></i>Edit Project</h2>
            <a href="projects.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Projects
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
                                   value="<?= htmlspecialchars($project['title']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category *</label>
                            <input type="text" class="form-control" id="category" name="category" list="category_list" required value="<?= htmlspecialchars($project['category'] ?? '') ?>" placeholder="Academic, Personal, Internship, Final Year, or custom">
                            <datalist id="category_list">
                                <option value="Academic">
                                <option value="Personal">
                                <option value="Internship">
                                <option value="Final Year">
                            </datalist>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($project['description']) ?></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= $project['semester'] == $i ? 'selected' : '' ?>>
                                        Semester <?= $i ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" list="subject_list" value="<?= htmlspecialchars($project['subject'] ?? '') ?>" placeholder="Type or select a subject">
                            <datalist id="subject_list">
                                <?php foreach ($subjects as $subj): ?>
                                    <option value="<?= $subj ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="tech_stack" class="form-label">Tech Stack</label>
                            <input type="text" class="form-control" id="tech_stack" name="tech_stack"
                                   value="<?= htmlspecialchars($project['tech_stack'] ?? '') ?>"
                                   placeholder="Comma separated technologies">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="file" class="form-label">Project File</label>
                            <?php if (!empty($old_file)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="fas fa-file me-1"></i>Current file attached</span>
                                    <a href="<?= htmlspecialchars($old_file) ?>" class="ms-2 small" download>Download</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="file" name="file">
                            <small class="text-muted">Leave empty to keep current file. Upload project files (ZIP, PDF, etc.)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="image" class="form-label">Project Image</label>
                            <?php if (!empty($old_image)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-success"><i class="fas fa-image me-1"></i>Current image attached</span>
                                    <a href="<?= htmlspecialchars($old_image) ?>" class="ms-2 small" download>Download</a>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image.</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="link" class="form-label">Project Link</label>
                            <input type="url" class="form-control" id="link" name="link"
                                   value="<?= htmlspecialchars($project['link'] ?? '') ?>"
                                   placeholder="https://...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="<?= $project['start_date'] ?? '' ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="<?= $project['end_date'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Project
                        </button>
                        <a href="projects.php" class="btn btn-secondary ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
