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
    'attendance'   => ['table' => 'attendance',   'redirect' => 'attendance.php'],
    'holiday'      => ['table' => 'holidays',      'redirect' => 'holiday.php'],
    'homework'     => ['table' => 'homework',      'redirect' => 'homework.php'],
    'payment'      => ['table' => 'payments',      'redirect' => 'payment.php'],
    'exam'         => ['table' => 'exams',         'redirect' => 'exam.php'],
    'schedule'     => ['table' => 'schedule',      'redirect' => 'schedule.php'],
    'grade'        => ['table' => 'grades',        'redirect' => 'grades.php'],
    'note'         => ['table' => 'notes',         'redirect' => 'notes.php'],
    'project'      => ['table' => 'projects',      'redirect' => 'projects.php'],
    'internship'   => ['table' => 'internships',   'redirect' => 'internship.php'],
    'resource'     => ['table' => 'resources',     'redirect' => 'resources.php'],
    'book'         => ['table' => 'books',         'redirect' => 'library.php'],
    'lab'          => ['table' => 'labs',          'redirect' => 'lab.php'],
    'assignment'   => ['table' => 'assignments',   'redirect' => 'assignment.php'],
    'presentation' => ['table' => 'presentations', 'redirect' => 'presentation.php'],
    'exam_prep'    => ['table' => 'exam_prep',     'redirect' => 'exam_prep.php'],
    'circular'     => ['table' => 'circulars',     'redirect' => 'circular.php'],
    'announcement' => ['table' => 'announcements', 'redirect' => 'announcement.php'],
    'event'        => ['table' => 'events',        'redirect' => 'event.php'],
    'syllabus'     => ['table' => 'syllabus',      'redirect' => 'syllabus.php'],
    'study_plan'   => ['table' => 'study_plans',   'redirect' => 'study_plan.php'],
    'skill'        => ['table' => 'skills',        'redirect' => 'skills.php'],
    'certification'=> ['table' => 'certifications','redirect' => 'certifications.php'],
    'job'          => ['table' => 'jobs',          'redirect' => 'jobs.php'],
    'group'        => ['table' => 'groups',        'redirect' => 'group.php'],
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
    setFlashMessage('danger', 'You do not have permission to delete this record.');
    redirect($redirect);
}

$fileColumns = ['file_path', 'report_path', 'receipt_path', 'certificate_path', 'image_path', 'photo_path'];
foreach ($fileColumns as $col) {
    if (!empty($record[$col]) && file_exists($record[$col])) {
        unlink($record[$col]);
    }
}

$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
$stmt->execute([$id]);

setFlashMessage('success', 'Record deleted successfully.');
redirect($redirect);
