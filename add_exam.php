<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$pageTitle = 'Add Exam';
$extraCSS = ['style.css', 'exam.css'];
$extraJS = ['main.js', 'exam.js'];

$semester = $_SESSION['user_semester'] ?? 1;
$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $semester = intval($_POST['semester'] ?? 1);
    $examName = sanitize($_POST['exam_name'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $date = sanitize($_POST['date'] ?? '');
    $startTime = sanitize($_POST['start_time'] ?? '');
    $endTime = sanitize($_POST['end_time'] ?? '');
    $roomNo = sanitize($_POST['room_no'] ?? '');
    $type = sanitize($_POST['type'] ?? '');

    if(empty($examName)) $errors[] = 'Exam name is required';
    if(empty($subject)) $errors[] = 'Subject is required';
    if(empty($date)) $errors[] = 'Date is required';
    if(empty($startTime)) $errors[] = 'Start time is required';
    if(empty($endTime)) $errors[] = 'End time is required';
    if(empty($roomNo)) $errors[] = 'Room number is required';
    if(empty($type)) $errors[] = 'Type is required';
    if($startTime >= $endTime) $errors[] = 'End time must be after start time';
    if(strtotime($date) === false) $errors[] = 'Invalid date format';

    if(empty($errors)) {
        $status = sanitize($_POST['status'] ?? 'upcoming');
        $stmt = $pdo->prepare("INSERT INTO exams (user_id, semester, exam_name, subject, date, start_time, end_time, room_no, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $semester, $examName, $subject, $date, $startTime, $endTime, $roomNo, $type, $status]);
        
        $detail = "Exam: " . $examName . " - Subject: " . $subject . " - Date: " . $date;
        setFlashMessage('success', 'Exam added successfully!');
        notifyEmail('Exam', 'added', $detail);
        logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Exam', $pdo->lastInsertId(), $detail);
        header('Location: exam.php?semester=' . $semester);
        exit();
    }
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-medical me-2"></i>Add Exam</h2>
            <a href="exam.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Exams
            </a>
        </div>

        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="" id="examForm">
                    <?= csrfField() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="exam_name" class="form-label">Exam Name *</label>
                            <input type="text" class="form-control" id="exam_name" name="exam_name" 
                                   value="<?php echo htmlspecialchars($_POST['exam_name'] ?? ''); ?>" 
                                   placeholder="e.g., Mid Semester Exam" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                                   placeholder="e.g., Machine Learning" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Exam Type *</label>
                            <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?php echo htmlspecialchars($_POST['type'] ?? ''); ?>" placeholder="Theory, Practical, Viva, Internal, or custom">
                            <datalist id="type_list">
                                <option value="Theory">
                                <option value="Practical">
                                <option value="Viva">
                                <option value="Internal">
                            </datalist>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="date" name="date" 
                                   value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" 
                                   value="<?php echo htmlspecialchars($_POST['start_time'] ?? ''); ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" 
                                   value="<?php echo htmlspecialchars($_POST['end_time'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="room_no" class="form-label">Room No *</label>
                            <input type="text" class="form-control" id="room_no" name="room_no" 
                                   value="<?php echo htmlspecialchars($_POST['room_no'] ?? ''); ?>" 
                                   placeholder="e.g., Room 101" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="upcoming" <?php echo ($_POST['status'] ?? 'upcoming') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="active" <?php echo ($_POST['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo ($_POST['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="exam.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Add Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
