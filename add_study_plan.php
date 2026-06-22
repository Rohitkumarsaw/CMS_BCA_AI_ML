<?php
$pageTitle = 'Add Study Plan';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject']);
    $topic = sanitize($_POST['topic']);
    $date = sanitize($_POST['date']);
    $timeSlot = sanitize($_POST['time_slot']);
    $priority = sanitize($_POST['priority']);
    $status = sanitize($_POST['status']);

    if (empty($subject) || empty($topic) || empty($date) || empty($priority) || empty($status)) {
        setFlashMessage('danger', 'Please fill in all required fields');
        header('Location: add_study_plan.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO study_plans (user_id, subject, topic, date, time_slot, priority, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $subject, $topic, $date, $timeSlot, $priority, $status]);

    $detail = "Topic: " . $topic;
    setFlashMessage('success', 'Study plan added successfully');
    notifyEmail('Study Plan', 'added', $detail);
    logActivity($pdo, $userId, $_SESSION['user_name'] ?? 'User', 'Added', 'Study Plan', $pdo->lastInsertId(), $detail);
    header('Location: study_plan.php');
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
                    <h4 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Study Plan</h4>
                    <p class="text-muted mb-0">Schedule a new study session</p>
                </div>
                <a href="study_plan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="e.g., Machine Learning" list="subjectList">
                                        <datalist id="subjectList">
                                            <?php
                                            $userSemester = $_SESSION['user_semester'] ?? 1;
                                            $semesterSubjects = getSemesterSubjects($userSemester);
                                            foreach ($semesterSubjects as $subj):
                                            ?>
                                                <option value="<?= htmlspecialchars($subj) ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="topic" class="form-label">Topic *</label>
                                        <input type="text" class="form-control" id="topic" name="topic" required placeholder="e.g., Study SVM Algorithm">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label">Date *</label>
                                        <input type="date" class="form-control" id="date" name="date" required value="<?= date('Y-m-d') ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="time_slot" class="form-label">Time Slot</label>
                                        <input type="text" class="form-control" id="time_slot" name="time_slot" list="time_slot_list" placeholder="Type or select a time slot">
                                        <datalist id="time_slot_list">
                                            <?php foreach (getTimeSlots() as $slot): ?>
                                                <option value="<?= $slot ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">Priority *</label>
                                        <input type="text" class="form-control" id="priority" name="priority" list="priority_list" required placeholder="High, Medium, Low, or custom">
                                        <datalist id="priority_list">
                                            <option value="High">
                                            <option value="Medium">
                                            <option value="Low">
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <input type="text" class="form-control" id="status" name="status" list="status_list" required placeholder="Planned, In Progress, Completed, or custom">
                                        <datalist id="status_list">
                                            <option value="Planned">
                                            <option value="In Progress">
                                            <option value="Completed">
                                        </datalist>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Save Study Plan
                                    </button>
                                    <a href="study_plan.php" class="btn btn-secondary ms-2">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Study Tips</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Break study into 1-2 hour blocks</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set clear goals for each session</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Review completed topics regularly</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Prioritize difficult subjects</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Take short breaks between sessions</li>
                            </ul>
                            <hr>
                            <h6>Time Slots Available:</h6>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach (getTimeSlots() as $slot): ?>
                                    <span class="badge bg-light text-dark"><?= $slot ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
