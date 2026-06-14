<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();
requireCSRF();

$pageTitle = 'Edit Attendance';
$extraCSS = ['style.css', 'attendance.css'];
$extraJS = ['main.js', 'attendance.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];
$subjects = getSemesterSubjects($user_semester);

$att_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($att_id <= 0) {
    setFlashMessage('danger', 'Invalid attendance ID.');
    redirect('attendance.php');
}

$stmt = $pdo->prepare("SELECT * FROM attendance WHERE id = ? AND user_id = ?");
$stmt->execute([$att_id, $user_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Attendance record not found.');
    redirect('attendance.php');
}

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
        $stmt = $pdo->prepare("UPDATE attendance SET date = ?, subject = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$date, $subject, $status, $att_id, $user_id]);

        setFlashMessage('success', 'Attendance updated successfully.');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Attendance</h4>
                    <p class="text-muted mb-0">Update attendance record</p>
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
<form method="POST" action="" id="attendanceForm">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="<?= $record['date'] ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book"></i></span>
                                        <input type="text" class="form-control" id="subject" name="subject" list="subject_list" required value="<?= htmlspecialchars($record['subject'] ?? '') ?>" placeholder="Type or select a subject">
                                    </div>
                                    <datalist id="subject_list">
                                        <?php foreach ($subjects as $subject): ?>
                                            <option value="<?= $subject ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($record['status'] ?? '') ?>" placeholder="Present, Absent, Late, or custom">
                                    <datalist id="status_list">
                                        <option value="Present">
                                        <option value="Absent">
                                        <option value="Late">
                                    </datalist>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Attendance
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



<?php include 'includes/footer.php'; ?>
