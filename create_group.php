<?php
$pageTitle = 'Create Group';
$extraCSS = ['style.css', 'groups.css'];
$extraJS = ['main.js', 'groups.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupName = sanitize($_POST['group_name'] ?? '');
    $groupType = sanitize($_POST['group_type'] ?? '');

    if (empty($groupName)) {
        setFlashMessage('danger', 'Group name is required.');
        redirect('create_group.php');
    }

    if (empty($groupType)) {
        setFlashMessage('danger', 'Please select a group type.');
        redirect('create_group.php');
    }

    $allowedTypes = ['Attendance', 'Homework', 'Projects', 'General'];
    if (!in_array($groupType, $allowedTypes)) {
        setFlashMessage('danger', 'Invalid group type.');
        redirect('create_group.php');
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO groups (group_name, group_type, created_by, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$groupName, $groupType, $userId]);
        $groupId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, added_at) VALUES (?, ?, NOW())");
        $stmt->execute([$groupId, $userId]);

        setFlashMessage('success', 'Group "' . htmlspecialchars($groupName) . '" created successfully!');
        notifyEmail('Group', 'created', 'Group name: ' . $groupName);
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Created', 'Group', $groupId, 'Group: ' . $groupName . ' - Type: ' . $groupType);
        redirect('group.php');
    } catch (PDOException $e) {
        setFlashMessage('danger', 'Error creating group. Please try again.');
        redirect('create_group.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Create Group</h4>
                    <p class="text-muted mb-0">Set up a new group for collaboration</p>
                </div>
                <a href="group.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Groups
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="group_name" class="form-label">
                                        <i class="fas fa-tag me-1 text-muted"></i>Group Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="group_name" name="group_name"
                                           placeholder="Enter group name" required maxlength="100"
                                           value="<?php echo htmlspecialchars($_POST['group_name'] ?? ''); ?>">
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
                                                       <?php echo ($_POST['group_type'] ?? '') === 'Attendance' ? 'checked' : ''; ?> required>
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
                                                       <?php echo ($_POST['group_type'] ?? '') === 'Homework' ? 'checked' : ''; ?>>
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
                                                       <?php echo ($_POST['group_type'] ?? '') === 'Projects' ? 'checked' : ''; ?>>
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
                                                       <?php echo ($_POST['group_type'] ?? '') === 'General' ? 'checked' : ''; ?>>
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

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>Create Group
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
