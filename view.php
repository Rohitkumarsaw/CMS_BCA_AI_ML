<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0 || empty($type)) {
    setFlashMessage('danger', 'Invalid request.');
    redirect('dashboard.php');
}

$user_id = $_SESSION['user_id'];

$allowed = [
    'attendance'    => ['table' => 'attendance',    'redirect' => 'attendance.php'],
    'holiday'       => ['table' => 'holidays',       'redirect' => 'holiday.php'],
    'homework'      => ['table' => 'homework',       'redirect' => 'homework.php'],
    'payment'       => ['table' => 'payments',       'redirect' => 'payment.php'],
    'exam'          => ['table' => 'exams',          'redirect' => 'exam.php'],
    'schedule'      => ['table' => 'schedule',       'redirect' => 'schedule.php'],
    'grade'         => ['table' => 'grades',         'redirect' => 'grades.php'],
    'note'          => ['table' => 'notes',          'redirect' => 'notes.php'],
    'project'       => ['table' => 'projects',       'redirect' => 'projects.php'],
    'internship'    => ['table' => 'internships',    'redirect' => 'internship.php'],
    'resource'      => ['table' => 'resources',      'redirect' => 'resources.php'],
    'book'          => ['table' => 'books',          'redirect' => 'library.php'],
    'lab'           => ['table' => 'labs',           'redirect' => 'lab.php'],
    'assignment'    => ['table' => 'assignments',    'redirect' => 'assignment.php'],
    'presentation'  => ['table' => 'presentations',  'redirect' => 'presentation.php'],
    'exam_prep'     => ['table' => 'exam_prep',      'redirect' => 'exam_prep.php'],
    'circular'      => ['table' => 'circulars',      'redirect' => 'circular.php'],
    'announcement'  => ['table' => 'announcements',  'redirect' => 'announcement.php'],
    'event'         => ['table' => 'events',         'redirect' => 'event.php'],
    'syllabus'      => ['table' => 'syllabus',       'redirect' => 'syllabus.php'],
    'study_plan'    => ['table' => 'study_plans',    'redirect' => 'study_plan.php'],
    'skill'         => ['table' => 'skills',         'redirect' => 'skills.php'],
    'certification' => ['table' => 'certifications', 'redirect' => 'certifications.php'],
    'job'           => ['table' => 'jobs',           'redirect' => 'jobs.php'],
    'group'         => ['table' => 'groups',         'redirect' => 'group.php'],
];

if (!isset($allowed[$type])) {
    setFlashMessage('danger', 'Invalid entity type.');
    redirect('dashboard.php');
}

$table = $allowed[$type]['table'];
$redirect = $allowed[$type]['redirect'];

$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    setFlashMessage('danger', 'Record not found.');
    redirect($redirect);
}

if ($record['user_id'] != $user_id && $_SESSION['user_role'] !== 'admin') {
    setFlashMessage('danger', 'You do not have permission to view this record.');
    redirect($redirect);
}

$fieldLabels = [
    'attendance'    => ['semester'=>'Semester', 'subject'=>'Subject', 'date'=>'Date', 'status'=>'Status', 'remarks'=>'Remarks'],
    'holiday'       => ['semester'=>'Semester', 'title'=>'Title', 'type'=>'Type', 'date'=>'Date', 'description'=>'Description'],
    'homework'      => ['semester'=>'Semester', 'title'=>'Title', 'subject'=>'Subject', 'description'=>'Description', 'due_date'=>'Due Date', 'status'=>'Status', 'file_path'=>'Attachment'],
    'payment'       => ['semester'=>'Semester', 'payment_type'=>'Payment Type', 'amount'=>'Amount', 'payment_date'=>'Payment Date', 'payment_method'=>'Payment Method', 'transaction_id'=>'Transaction ID', 'receipt_path'=>'Receipt', 'status'=>'Status'],
    'exam'          => ['semester'=>'Semester', 'subject'=>'Subject', 'type'=>'Type', 'date'=>'Date', 'time'=>'Time', 'room_no'=>'Room No', 'description'=>'Description'],
    'schedule'      => ['semester'=>'Semester', 'day'=>'Day', 'time'=>'Time', 'subject'=>'Subject', 'type'=>'Type', 'teacher_name'=>'Teacher', 'room_no'=>'Room No'],
    'grade'         => ['semester'=>'Semester', 'subject'=>'Subject', 'exam_name'=>'Exam Name', 'marks_obtained'=>'Marks Obtained', 'total_marks'=>'Total Marks', 'grade'=>'Grade', 'date'=>'Date'],
    'note'          => ['semester'=>'Semester', 'title'=>'Title', 'subject'=>'Subject', 'type'=>'Type', 'file_path'=>'File', 'description'=>'Description'],
    'project'       => ['semester'=>'Semester', 'title'=>'Title', 'category'=>'Category', 'subject'=>'Subject', 'description'=>'Description', 'tech_stack'=>'Tech Stack', 'file_path'=>'Project File', 'image_path'=>'Project Image'],
    'internship'    => ['company'=>'Company', 'title'=>'Title', 'description'=>'Description', 'start_date'=>'Start Date', 'end_date'=>'End Date', 'certificate_path'=>'Certificate', 'status'=>'Status', 'skills_gained'=>'Skills Gained'],
    'resource'      => ['semester'=>'Semester', 'title'=>'Title', 'type'=>'Type', 'subject'=>'Subject', 'description'=>'Description', 'tags'=>'Tags'],
    'book'          => ['semester'=>'Semester', 'title'=>'Title', 'author'=>'Author', 'subject'=>'Subject', 'isbn'=>'ISBN', 'status'=>'Status'],
    'lab'           => ['semester'=>'Semester', 'subject'=>'Subject', 'title'=>'Title', 'description'=>'Description', 'due_date'=>'Due Date', 'report_path'=>'Lab Report', 'status'=>'Status'],
    'assignment'    => ['semester'=>'Semester', 'subject'=>'Subject', 'title'=>'Title', 'description'=>'Description', 'due_date'=>'Due Date', 'file_path'=>'Attachment', 'status'=>'Status'],
    'presentation'  => ['semester'=>'Semester', 'subject'=>'Subject', 'title'=>'Title', 'description'=>'Description', 'due_date'=>'Due Date', 'file_path'=>'Presentation File', 'status'=>'Status'],
    'exam_prep'     => ['semester'=>'Semester', 'subject'=>'Subject', 'title'=>'Title', 'description'=>'Description', 'status'=>'Status', 'progress'=>'Progress'],
    'circular'      => ['title'=>'Title', 'message'=>'Message', 'date'=>'Date', 'file_path'=>'Attachment'],
    'announcement'  => ['title'=>'Title', 'type'=>'Type', 'priority'=>'Priority', 'description'=>'Description', 'link'=>'Link'],
    'event'         => ['title'=>'Title', 'type'=>'Type', 'date'=>'Date', 'time'=>'Time', 'location'=>'Location', 'image_path'=>'Event Image', 'description'=>'Description'],
    'syllabus'      => ['semester'=>'Semester', 'subject'=>'Subject', 'topic'=>'Topic', 'status'=>'Status', 'due_date'=>'Due Date'],
    'study_plan'    => ['semester'=>'Semester', 'subject'=>'Subject', 'title'=>'Title', 'time_slot'=>'Time Slot', 'priority'=>'Priority', 'status'=>'Status', 'description'=>'Description'],
    'skill'         => ['name'=>'Name', 'level'=>'Level', 'category'=>'Category', 'status'=>'Status', 'date_completed'=>'Date Completed'],
    'certification' => ['name'=>'Name', 'organization'=>'Organization', 'date'=>'Date', 'certificate_path'=>'Certificate File', 'description'=>'Description', 'link'=>'Link'],
    'job'           => ['company'=>'Company', 'title'=>'Title', 'description'=>'Description', 'location'=>'Location', 'salary'=>'Salary', 'job_link'=>'Job Link', 'status'=>'Status'],
    'group'         => ['name'=>'Name', 'description'=>'Description', 'group_type'=>'Group Type'],
];

$labels = $fieldLabels[$type] ?? [];
$excludeFields = ['id', 'user_id', 'created_at', 'updated_at'];
$pageTitle = 'View ' . ucfirst(str_replace('_', ' ', $type));

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-eye me-2 text-primary"></i><?= $pageTitle ?></h4>
                <p class="text-muted mb-0">Viewing record details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="edit_<?= $type ?>.php?id=<?= $id ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Edit
                </a>
                <a href="<?= $redirect ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <?= getFlashMessage() ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tbody>
                            <?php foreach ($labels as $field => $label):
                                if (in_array($field, $excludeFields)) continue;
                                $value = $record[$field] ?? '';
                            ?>
                            <tr>
                                <th style="width: 200px;" class="bg-light"><?= htmlspecialchars($label) ?></th>
                                <td>
                                    <?php if ($field === 'semester' && $value): ?>
                                        Semester <?= (int)$value ?>
                                    <?php elseif (in_array($field, ['file_path', 'report_path', 'receipt_path', 'certificate_path', 'image_path', 'photo_path']) && !empty($value)): ?>
                                        <a href="<?= htmlspecialchars($value) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-file me-1"></i>View File
                                        </a>
                                        <a href="<?= htmlspecialchars($value) ?>" class="btn btn-sm btn-outline-success" download>
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    <?php elseif (in_array($field, ['link', 'job_link']) && !empty($value)): ?>
                                        <a href="<?= htmlspecialchars($value) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-external-link-alt me-1"></i>Open Link
                                        </a>
                                    <?php elseif (in_array($field, ['amount', 'salary']) && $value): ?>
                                        ₹<?= number_format((float)$value, 2) ?>
                                    <?php elseif (in_array($field, ['marks_obtained', 'total_marks']) && $value !== ''): ?>
                                        <?= (float)$value ?>
                                    <?php elseif (in_array($field, ['created_at', 'updated_at', 'date', 'due_date', 'payment_date', 'start_date', 'end_date', 'member_since', 'date_completed']) && !empty($value) && $value !== '0000-00-00'): ?>
                                        <?= formatDate($value) ?>
                                    <?php elseif ($field === 'time' && !empty($value)): ?>
                                        <?= date('h:i A', strtotime($value)) ?>
                                    <?php elseif (in_array($field, ['description', 'remarks', 'message']) && !empty($value)): ?>
                                        <div style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($value)) ?></div>
                                    <?php elseif (is_numeric($value) && in_array($field, ['progress'])): ?>
                                        <?= (int)$value ?>%
                                    <?php elseif ($value === '0' || $value === 0 || $value === '' || $value === null): ?>
                                        -
                                    <?php else: ?>
                                        <?= htmlspecialchars($value) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
