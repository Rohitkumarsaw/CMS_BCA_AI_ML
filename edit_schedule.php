<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$pageTitle = 'Edit Schedule';
$extraCSS = ['style.css', 'schedule.css'];
$extraJS = ['main.js', 'schedule.js'];

$daysOfWeek = getDaysOfWeek();

$schedule_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($schedule_id <= 0) {
    setFlashMessage('danger', 'Invalid schedule ID.');
    redirect('schedule.php');
}

$stmt = $pdo->prepare("SELECT * FROM schedule WHERE id = ?");
$stmt->execute([$schedule_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Schedule not found.');
    redirect('schedule.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = intval($_POST['semester'] ?? 1);
    $day = sanitize($_POST['day'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $startTime = sanitize($_POST['start_time'] ?? '');
    $endTime = sanitize($_POST['end_time'] ?? '');
    $teacherName = sanitize($_POST['teacher_name'] ?? '');
    $roomNo = sanitize($_POST['room_no'] ?? '');
    $type = sanitize($_POST['type'] ?? '');

    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($day)) $errors[] = 'Day is required';
    if (empty($startTime)) $errors[] = 'Start time is required';
    if (empty($endTime)) $errors[] = 'End time is required';
    if (empty($teacherName)) $errors[] = 'Teacher name is required';
    if (empty($roomNo)) $errors[] = 'Room number is required';
    if (empty($type)) $errors[] = 'Type is required';
    if ($startTime >= $endTime) $errors[] = 'End time must be after start time';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE schedule SET semester = ?, day = ?, subject = ?, start_time = ?, end_time = ?, teacher_name = ?, room_no = ?, type = ? WHERE id = ?");
        $stmt->execute([$semester, $day, $subject, $startTime, $endTime, $teacherName, $roomNo, $type, $schedule_id]);

        $detail = "Subject: " . $subject . " - Day: " . $day . " - Time: " . $startTime;
        setFlashMessage('success', 'Schedule updated successfully!');
        notifyEmail('Schedule', 'updated', $detail);
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['user_name'] ?? 'User', 'Updated', 'Schedule', $schedule_id, $detail);
        header('Location: schedule.php?semester=' . $semester . '&day=' . $day);
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
            <h2><i class="fas fa-edit me-2"></i>Edit Schedule</h2>
            <a href="schedule.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Schedule
            </a>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="" id="scheduleForm">
                    <?= csrfField() ?>
<form method="POST" action="" id="scheduleForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="semester" class="form-label">Semester *</label>
                            <select class="form-select" id="semester" name="semester" required>
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $record['semester'] == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="day" class="form-label">Day *</label>
                            <input type="text" class="form-control" id="day" name="day" list="day_list" required value="<?php echo htmlspecialchars($record['day']); ?>" placeholder="Type or select a day">
                            <datalist id="day_list">
                                <?php foreach ($daysOfWeek as $day): ?>
                                <option value="<?php echo $day; ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($record['subject']); ?>" 
                                   placeholder="e.g., Machine Learning" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="teacher_name" class="form-label">Teacher Name *</label>
                            <input type="text" class="form-control" id="teacher_name" name="teacher_name" 
                                   value="<?php echo htmlspecialchars($record['teacher_name']); ?>" 
                                   placeholder="e.g., Dr. Smith" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" 
                                   value="<?php echo htmlspecialchars($record['start_time']); ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" 
                                   value="<?php echo htmlspecialchars($record['end_time']); ?>" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="room_no" class="form-label">Room No *</label>
                            <input type="text" class="form-control" id="room_no" name="room_no" 
                                   value="<?php echo htmlspecialchars($record['room_no']); ?>" 
                                   placeholder="e.g., Room 101" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?php echo htmlspecialchars($record['type']); ?>" placeholder="Lecture, Lab, Tutorial, Activity, or custom">
                            <datalist id="type_list">
                                <option value="Lecture">
                                <option value="Lab">
                                <option value="Tutorial">
                                <option value="Activity">
                            </datalist>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="schedule.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
