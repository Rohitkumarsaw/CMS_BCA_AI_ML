<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Add Attendance';
$extraCSS = ['style.css', 'attendance.css'];
$extraJS = ['main.js', 'attendance.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = sanitize($_POST['date'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if (empty($date)) {
        $errors[] = 'Date is required.';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, semester, subject, date, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $user_semester, $subject, $date, ucfirst(strtolower($status))]);

        $detail = "Subject: " . $subject . " - Status: " . $status;
        setFlashMessage('success', 'Attendance added successfully.');
        notifyEmail('Attendance', 'added', $detail);
        redirect('attendance.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Attendance</h4>
                    <p class="text-muted mb-0">Record your attendance for today</p>
                </div>
                <a href="attendance.php" class="btn btn-outline-secondary">
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
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-clipboard-check me-2 text-primary"></i>Attendance Form</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="attendanceForm">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= $date ?? date('Y-m-d') ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required value="<?= htmlspecialchars($subject ?? '') ?>" placeholder="Type or select a subject">
                                    </div>
                                    <datalist id="subject_list">
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Present, Absent, Late, or custom">
                                    <datalist id="status_list">
                                        <option value="Present">
                                        <option value="Absent">
                                        <option value="Late">
                                    </datalist>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Attendance
                                    </button>
                                    <a href="attendance.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include 'includes/footer.php'; ?>