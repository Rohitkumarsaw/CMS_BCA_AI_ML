<?php
$pageTitle = 'Edit Study Plan';

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();
requireCSRF();

$user_id = $_SESSION['user_id'];

$plan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($plan_id <= 0) {
    setFlashMessage('danger', 'Invalid study plan ID.');
    redirect('study_plan.php');
}

$stmt = $pdo->prepare("SELECT * FROM study_plans WHERE id = ? AND user_id = ?");
$stmt->execute([$plan_id, $user_id]);
$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    setFlashMessage('danger', 'Study plan not found.');
    redirect('study_plan.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = sanitize($_POST['subject'] ?? '');
    $topic = sanitize($_POST['topic'] ?? '');
    $date = sanitize($_POST['date'] ?? '');
    $timeSlot = sanitize($_POST['time_slot'] ?? '');
    $priority = sanitize($_POST['priority'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    if (empty($topic)) {
        $errors[] = 'Topic is required.';
    }
    if (empty($date)) {
        $errors[] = 'Date is required.';
    }
    if (empty($priority)) {
        $errors[] = 'Priority is required.';
    }
    if (empty($status)) {
        $errors[] = 'Status is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE study_plans SET subject = ?, topic = ?, date = ?, time_slot = ?, priority = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$subject, $topic, $date, $timeSlot, $priority, $status, $plan_id, $user_id]);

        $detail = "Topic: " . $topic;
        setFlashMessage('success', 'Study plan updated successfully.');
        notifyEmail('Study Plan', 'updated', $detail);
        redirect('study_plan.php');
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
                    <h4 class="mb-1"><i class="fas fa-edit me-2 text-primary"></i>Edit Study Plan</h4>
                    <p class="text-muted mb-0">Update your study session</p>
                </div>
                <a href="study_plan.php" class="btn btn-secondary">
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

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST">
                                <?= csrfField() ?>
<form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required placeholder="e.g., Machine Learning" list="subjectList" value="<?= htmlspecialchars($plan['subject']) ?>">
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
                                        <input type="text" class="form-control" id="topic" name="topic" required placeholder="e.g., Study SVM Algorithm" value="<?= htmlspecialchars($plan['topic']) ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label">Date *</label>
                                        <input type="date" class="form-control" id="date" name="date" required value="<?= $plan['date'] ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="time_slot" class="form-label">Time Slot</label>
                                        <input type="text" class="form-control" id="time_slot" name="time_slot" list="time_slot_list" value="<?= htmlspecialchars($plan['time_slot'] ?? '') ?>" placeholder="Type or select a time slot">
                                        <datalist id="time_slot_list">
                                            <?php foreach (getTimeSlots() as $slot): ?>
                                                <option value="<?= $slot ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="priority" class="form-label">Priority *</label>
                                        <input type="text" class="form-control" id="priority" name="priority" list="priority_list" required value="<?= htmlspecialchars($plan['priority'] ?? '') ?>" placeholder="High, Medium, Low, or custom">
                                        <datalist id="priority_list">
                                            <option value="High">
                                            <option value="Medium">
                                            <option value="Low">
                                        </datalist>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status *</label>
                                        <input type="text" class="form-control" id="status" name="status" list="status_list" required value="<?= htmlspecialchars($plan['status'] ?? '') ?>" placeholder="Planned, In Progress, Completed, or custom">
                                        <datalist id="status_list">
                                            <option value="Planned">
                                            <option value="In Progress">
                                            <option value="Completed">
                                        </datalist>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Study Plan
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
