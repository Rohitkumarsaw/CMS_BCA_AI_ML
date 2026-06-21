<?php
$pageTitle = 'Edit Group';
$extraCSS = ['style.css', 'groups.css'];
$extraJS = ['main.js', 'groups.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$group_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($group_id <= 0) {
    setFlashMessage('danger', 'Invalid group ID.');
    redirect('group.php');
}

$stmt = $pdo->prepare("SELECT * FROM groups WHERE id = ? AND created_by = ?");
$stmt->execute([$group_id, $user_id]);
$group = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$group) {
    setFlashMessage('danger', 'Group not found or you do not have permission to edit it.');
    redirect('group.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupName = sanitize($_POST['group_name'] ?? '');
    $groupType = sanitize($_POST['group_type'] ?? '');

    if (empty($groupName)) {
        $errors[] = 'Group name is required.';
    }

    $allowedTypes = ['Attendance', 'Homework', 'Projects', 'General'];
    if (empty($groupType) || !in_array($groupType, $allowedTypes)) {
        $errors[] = 'Please select a valid group type.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE groups SET group_name = ?, group_type = ? WHERE id = ? AND created_by = ?");
            $stmt->execute([$groupName, $groupType, $group_id, $user_id]);

            setFlashMessage('success', 'Group "' . htmlspecialchars($groupName) . '" updated successfully.');
            notifyEmail('Group', 'updated');
            redirect('group.php');
        } catch (PDOException $e) {
            $errors[] = 'Error updating group. Please try again.';
        }
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Group</h4>
                    <p class="text-muted mb-0">Update group details</p>
                </div>
                <a href="group.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Groups
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

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form method="POST" action="">
                                <?= csrfField() ?>
<form method="POST" action="">
                                <div class="mb-3">
                                    <label for="group_name" class="form-label">
                                        <i class="fas fa-tag me-1 text-muted"></i>Group Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="group_name" name="group_name"
                                           placeholder="Enter group name" required maxlength="100"
                                           value="<?= htmlspecialchars($group['group_name']) ?>">
                                    <div class="form-text">Choose a descriptive name for your group.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-list me-1 text-muted"></i>Group Type <span class="text-danger">*</span>
                                    </label>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="form-check card p-3 border">
                                                <input class="form-check-input" type="radio" name="group_type" id="typeAttendance" value="Attendance"
                                                       <?= $group['group_type'] === 'Attendance' ? 'checked' : '' ?> required>
                                                <label class="form-check-label d-flex align-items-center" for="typeAttendance">
                                                    <i class="fas fa-check-circle text-primary me-2 fa-lg"></i>
                                                    <div>
                                                        <strong>Attendance</strong>
                                                        <small class="d-block text-muted">Track attendance together</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check card p-3 border">
                                                <input class="form-check-input" type="radio" name="group_type" id="typeHomework" value="Homework"
                                                       <?= $group['group_type'] === 'Homework' ? 'checked' : '' ?>>
                                                <label class="form-check-label d-flex align-items-center" for="typeHomework">
                                                    <i class="fas fa-book text-warning me-2 fa-lg"></i>
                                                    <div>
                                                        <strong>Homework</strong>
                                                        <small class="d-block text-muted">Share homework & notes</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check card p-3 border">
                                                <input class="form-check-input" type="radio" name="group_type" id="typeProjects" value="Projects"
                                                       <?= $group['group_type'] === 'Projects' ? 'checked' : '' ?>>
                                                <label class="form-check-label d-flex align-items-center" for="typeProjects">
                                                    <i class="fas fa-project-diagram text-success me-2 fa-lg"></i>
                                                    <div>
                                                        <strong>Projects</strong>
                                                        <small class="d-block text-muted">Collaborate on projects</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check card p-3 border">
                                                <input class="form-check-input" type="radio" name="group_type" id="typeGeneral" value="General"
                                                       <?= $group['group_type'] === 'General' ? 'checked' : '' ?>>
                                                <label class="form-check-label d-flex align-items-center" for="typeGeneral">
                                                    <i class="fas fa-users text-info me-2 fa-lg"></i>
                                                    <div>
                                                        <strong>General</strong>
                                                        <small class="d-block text-muted">General discussion group</small>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Group
                                    </button>
                                    <a href="group.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
