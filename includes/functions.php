<?php
/**
 * CMS (BCA AI/ML) - Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        $safeMsg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $iconMap = ['success'=>'success','error'=>'error','danger'=>'error','warning'=>'warning','info'=>'info'];
        $titleMap = ['success'=>'Success','error'=>'Error','danger'=>'Error','warning'=>'Warning','info'=>'Info'];
        $icon = $iconMap[$safeType] ?? 'info';
        $title = $titleMap[$safeType] ?? 'Info';
        $id = 'sa-' . uniqid();
        return <<<HTML
<div id="$id" style="display:none" data-icon="$icon" data-title="$title" data-msg="$safeMsg"></div>
<script>
(function(){var e=document.getElementById('$id');if(e){Swal.fire({icon:e.dataset.icon,title:e.dataset.title,text:e.dataset.msg,confirmButtonText:'OK',buttonsStyling:false,customClass:{confirmButton:'swal2-confirm'}});}})();
</script>
HTML;
    }
    return '';
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function uploadFile($file, $folder, $allowedTypes = []) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowedTypes) && !in_array($ext, $allowedTypes)) {
        return false;
    }

    $uploadDir = UPLOAD_PATH . $folder;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $destination = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/' . $folder . '/' . $filename;
    }

    return false;
}

function calculateGrade($marks, $total) {
    $percentage = ($total > 0) ? ($marks / $total) * 100 : 0;
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 40) return 'D';
    return 'F';
}

function getSemesterSubjects($semester) {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? 0;
    if (!$userId || !$pdo) return [];

    $stmt = $pdo->prepare("SELECT subject_name FROM user_subjects WHERE user_id = ? AND semester = ? ORDER BY id");
    $stmt->execute([$userId, $semester]);
    $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($subjects)) {
        $defaults = [
            1 => ['Programming C', 'Programming Python', 'DBMS', 'Computer Organization', 'Discrete Mathematics', 'English'],
            2 => ['Data Structures', 'Operating System', 'Web Development', 'AI Basics', 'Java Programming', 'Mathematics for CS'],
            3 => ['OOP Java', 'Advanced Data Structures', 'Algorithms', 'Computer Networks', 'UNIX/Linux', 'Elective 1'],
            4 => ['Machine Learning Basics', 'Deep Learning Intro', 'NLP Basics', 'Big Data Analytics', 'Cloud Computing', 'Elective 2'],
            5 => ['Advanced ML', 'Deep Learning Advanced', 'Computer Vision', 'Reinforcement Learning', 'Data Engineering', 'Elective 3'],
            6 => ['AI Ethics', 'AI for Robotics', 'NLP Advanced', 'Big Data Tools', 'Cloud AI', 'Internship 1'],
            7 => ['Final Project Part 1', 'Advanced AI', 'Data Science', 'Industry Project', 'Internship 2'],
            8 => ['Final Project Part 2', 'AI Deployment', 'MLOps', 'Career Prep', 'Portfolio Building'],
        ];
        $defaultSubs = $defaults[$semester] ?? [];
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_subjects (user_id, semester, subject_name) VALUES (?, ?, ?)");
        foreach ($defaultSubs as $sub) {
            $stmt->execute([$userId, $semester, $sub]);
        }
        return $defaultSubs;
    }

    return $subjects;
}

function getDaysOfWeek() {
    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
}

function getTimeSlots() {
    return ['09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '14:00-15:00', '15:00-16:00', '16:00-17:00'];
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCSRFToken($token)) {
            setFlashMessage('error', 'Security token expired. Please try again.');
            redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard.php');
        }
    }
}

function paginate($pdo, $table, $where, $params, $limit = 20, $pageKey = 'page') {
    $page = max(1, (int)($_GET[$pageKey] ?? 1));
    $countSql = "SELECT COUNT(*) FROM $table $where";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    $totalPages = max(1, ceil($total / $limit));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $limit;
    return [
        'page' => $page,
        'limit' => $limit,
        'offset' => $offset,
        'total' => $total,
        'totalPages' => $totalPages,
        'sql' => " LIMIT $limit OFFSET $offset"
    ];
}

function paginationLinks($currentPage, $totalPages, $pageKey = 'page') {
    if ($totalPages <= 1) return '';

    $queryParams = $_GET;
    $html = '<nav><ul class="pagination pagination-sm justify-content-center mt-3 mb-0">';

    unset($queryParams[$pageKey]);
    $baseQuery = http_build_query($queryParams);
    $baseUrl = basename($_SERVER['PHP_SELF']) . ($baseQuery ? '?' . $baseQuery . '&' : '?');

    $prevPage = $currentPage - 1;
    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . $pageKey . '=' . $prevPage . '">&laquo;</a></li>';

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $pageKey . '=1">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $html .= '<li class="page-item ' . ($i == $currentPage ? 'active' : '') . '">';
        $html .= '<a class="page-link" href="' . $baseUrl . $pageKey . '=' . $i . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $pageKey . '=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    $nextPage = $currentPage + 1;
    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . $pageKey . '=' . $nextPage . '">&raquo;</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

function syncNotifications($pdo, $userId) {
    if (!$pdo || !$userId) return;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() > 0) return;

    $hwStmt = $pdo->prepare("SELECT id, title, subject, due_date FROM homework WHERE user_id = ? AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status != 'Submitted'");
    $hwStmt->execute([$userId]);
    foreach ($hwStmt->fetchAll() as $row) {
        $ins = $pdo->prepare("INSERT IGNORE INTO notifications (user_id, type, title, message, reference_id) VALUES (?, 'homework', ?, ?, ?)");
        $ins->execute([$userId, 'Homework Due: ' . $row['title'], $row['subject'] . ' — Due ' . date('M d', strtotime($row['due_date'])), $row['id']]);
    }

    $exStmt = $pdo->prepare("SELECT id, exam_name, subject, date FROM exams WHERE user_id = ? AND date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $exStmt->execute([$userId]);
    foreach ($exStmt->fetchAll() as $row) {
        $ins = $pdo->prepare("INSERT IGNORE INTO notifications (user_id, type, title, message, reference_id) VALUES (?, 'exam', ?, ?, ?)");
        $ins->execute([$userId, 'Exam: ' . $row['exam_name'], $row['subject'] . ' on ' . date('M d', strtotime($row['date'])), $row['id']]);
    }

    $payStmt = $pdo->prepare("SELECT id, payment_type, amount FROM payments WHERE user_id = ? AND status = 'Unpaid'");
    $payStmt->execute([$userId]);
    foreach ($payStmt->fetchAll() as $row) {
        $ins = $pdo->prepare("INSERT IGNORE INTO notifications (user_id, type, title, message, reference_id) VALUES (?, 'payment', ?, ?, ?)");
        $ins->execute([$userId, 'Payment Due', $row['payment_type'] . ' — ₹' . number_format($row['amount'], 2), $row['id']]);
    }
}

function getNotifications($pdo, $userId) {
    syncNotifications($pdo, $userId);
    $stmt = $pdo->prepare("SELECT id, type, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getUnreadNotificationCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
