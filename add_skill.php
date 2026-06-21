<?php
$pageTitle = 'Add Skill';
$extraCSS = ['style.css', 'skills.css'];
$extraJS = ['main.js', 'skills.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skillName = sanitize($_POST['skill_name']);
    $level = sanitize($_POST['level']);
    $category = sanitize($_POST['category']);
    $status = sanitize($_POST['status']);
    $dateCompleted = sanitize($_POST['date_completed']);

    if (empty($skillName) || empty($level) || empty($category) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_skill.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name, level, category, status, date_completed, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $skillName, $level, $category, $status, $dateCompleted]);

    setFlashMessage('success', 'Skill added successfully');
    notifyEmail('Skill', 'added');
    header('Location: skills.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Skill</h4>
                    <p class="text-muted mb-0">Add a new skill to your profile</p>
                </div>
                <a href="skills.php" class="btn btn-secondary">
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
                                <label for="skill_name" class="form-label">Skill Name *</label>
                                <input type="text" class="form-control" id="skill_name" name="skill_name" required placeholder="e.g., Python, Machine Learning, React">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level" class="form-label">Level *</label>
                                <input type="text" class="form-control" id="level" name="level" list="level_list" required placeholder="Beginner, Intermediate, Advanced, or custom">
                                <datalist id="level_list">
                                    <option value="Beginner">
                                    <option value="Intermediate">
                                    <option value="Advanced">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <input type="text" class="form-control" id="category" name="category" list="category_list" required placeholder="Programming, AI, ML, Data, WebDev, Other, or custom">
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
                                <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Learning, Completed, or custom">
                                <datalist id="status_list">
                                    <option value="Learning">
                                    <option value="Completed">
                                </datalist>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_completed" class="form-label">Date Completed</label>
                                <input type="date" class="form-control" id="date_completed" name="date_completed">
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Skill
                            </button>
                            <a href="skills.php" class="btn btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
