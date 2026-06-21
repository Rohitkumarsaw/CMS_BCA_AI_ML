<?php
$pageTitle = 'Edit Skill';
$extraCSS = ['style.css', 'skills.css'];
$extraJS = ['main.js', 'skills.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$skill_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($skill_id <= 0) {
    setFlashMessage('danger', 'Invalid skill ID.');
    redirect('skills.php');
}

$stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ? AND user_id = ?");
$stmt->execute([$skill_id, $user_id]);
$skill = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$skill) {
    setFlashMessage('danger', 'Skill not found.');
    redirect('skills.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skillName = sanitize($_POST['skill_name'] ?? '');
    $level = sanitize($_POST['level'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $dateCompleted = sanitize($_POST['date_completed'] ?? '');

    if (empty($skillName)) {
        $errors[] = 'Skill name is required.';
    }
    if (empty($level)) {
        $errors[] = 'Level is required.';
    }
    if (empty($category)) {
        $errors[] = 'Category is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE skills SET skill_name = ?, level = ?, category = ?, status = ?, date_completed = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$skillName, $level, $category, $status, $dateCompleted, $skill_id, $user_id]);

        $detail = "Skill: " . $skillName . " - Level: " . $level;
        setFlashMessage('success', 'Skill updated successfully.');
        notifyEmail('Skill', 'updated', $detail);
        redirect('skills.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Skill</h4>
                    <p class="text-muted mb-0">Update your skill details</p>
                </div>
                <a href="skills.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>
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
                                <label for="skill_name" class="form-label">Skill Name *</label>
                                <input type="text" class="form-control" id="skill_name" name="skill_name" required placeholder="e.g., Python, Machine Learning, React" value="<?= htmlspecialchars($skill['skill_name']) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">Level *</label>
                                <input type="text" class="form-control" id="level" name="level" list="level_list" required value="<?= htmlspecialchars($skill['level'] ?? '') ?>" placeholder="Beginner, Intermediate, Advanced, Expert, or custom">
                                <datalist id="level_list">
                                    <option value="Beginner">
                                    <option value="Intermediate">
                                    <option value="Advanced">
                                    <option value="Expert">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <input type="text" class="form-control" id="category" name="category" list="category_list" required value="<?= htmlspecialchars($skill['category'] ?? '') ?>" placeholder="Programming, AI, ML, Data, WebDev, Other, or custom">
                                <datalist id="category_list">
                                    <option value="Programming">
                                    <option value="AI">
                                    <option value="ML">
                                    <option value="Data">
                                    <option value="WebDev">
                                    <option value="Other">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($skill['status'] ?? '') ?>" placeholder="Learning, Completed, or custom">
                                <datalist id="status_list">
                                    <option value="Learning">
                                    <option value="Completed">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_completed" class="form-label">Date Completed</label>
                                <input type="date" class="form-control" id="date_completed" name="date_completed" value="<?= $skill['date_completed'] ?>">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Skill
                            </button>
                            <a href="skills.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
