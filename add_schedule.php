<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$pageTitle = 'Add Schedule';
$extraCSS = ['style.css', 'schedule.css'];
$extraJS = ['main.js', 'schedule.js'];

$daysOfWeek = getDaysOfWeek();
$semester = $_SESSION['user_semester'] ?? 1;
$errors = [];
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = intval($_POST['semester'] ?? 1);
    $day = sanitize($_POST['day'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $startTime = sanitize($_POST['start_time'] ?? '');
    $endTime = sanitize($_POST['end_time'] ?? '');
    $teacherName = sanitize($_POST['teacher_name'] ?? '');
    $roomNo = sanitize($_POST['room_no'] ?? '');
    $type = sanitize($_POST['type'] ?? '');

    if(empty($subject)) $errors[] = 'Subject is required';
    if(empty($day)) $errors[] = 'Day is required';
    if(empty($startTime)) $errors[] = 'Start time is required';
    if(empty($endTime)) $errors[] = 'End time is required';
    if(empty($teacherName)) $errors[] = 'Teacher name is required';
    if(empty($roomNo)) $errors[] = 'Room number is required';
    if(empty($type)) $errors[] = 'Type is required';
    if($startTime >= $endTime) $errors[] = 'End time must be after start time';

    if(empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO schedule (semester, day, subject, start_time, end_time, teacher_name, room_no, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$semester, $day, $subject, $startTime, $endTime, $teacherName, $roomNo, $type]);
        
        setFlashMessage('success', 'Schedule added successfully!');
        notifyEmail('Schedule', 'added');
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
            <h2><i class="fas fa-calendar-plus me-2"></i>Add Schedule</h2>
            <a href="schedule.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Schedule
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
                <form method="POST" action="" id="scheduleForm">
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
                            <label for="day" class="form-label">Day *</label>
                            <input type="text" class="form-control" id="day" name="day" list="day_list" required value="<?php echo htmlspecialchars($_POST['day'] ?? ''); ?>" placeholder="Type or select a day">
                            <datalist id="day_list">
                                <?php foreach($daysOfWeek as $day): ?>
                                <option value="<?php echo $day; ?>">
                                <?php endforeach; ?>
                            </datalist>
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
                            <label for="teacher_name" class="form-label">Teacher Name *</label>
                            <input type="text" class="form-control" id="teacher_name" name="teacher_name" 
                                   value="<?php echo htmlspecialchars($_POST['teacher_name'] ?? ''); ?>" 
                                   placeholder="e.g., Dr. Smith" required>
                        </div>
                    </div>

                    <div class="row">
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

                        <div class="col-md-4 mb-3">
                            <label for="room_no" class="form-label">Room No *</label>
                            <input type="text" class="form-control" id="room_no" name="room_no" 
                                   value="<?php echo htmlspecialchars($_POST['room_no'] ?? ''); ?>" 
                                   placeholder="e.g., Room 101" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Type *</label>
                            <input type="text" class="form-control" id="type" name="type" list="type_list" required value="<?php echo htmlspecialchars($_POST['type'] ?? ''); ?>" placeholder="Lecture, Lab, Tutorial, Activity, or custom">
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
                            <i class="fas fa-save me-1"></i>Add Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
