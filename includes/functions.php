<?php
/**
 * CMS (BCA AI/ML) - Helper Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('Asia/Kolkata');

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

function logActivity($pdo, $userId, $userName, $actionType, $sectionName, $referenceId = null, $details = null) {
    if (!$pdo || !$userId) return;
    try {
        $pdo->prepare("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            user_name VARCHAR(100) NOT NULL,
            action_type VARCHAR(255) NOT NULL,
            section_name VARCHAR(100) NOT NULL,
            reference_id INT DEFAULT NULL,
            details TEXT,
            logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4")->execute();
    } catch (PDOException $e) {}
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, user_name, action_type, section_name, reference_id, details) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $userName, $actionType, $sectionName, $referenceId, $details]);
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

function sendMail($subject, $body) {
    require_once __DIR__ . '/../libraries/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../libraries/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../libraries/PHPMailer/src/SMTP.php';

    $host = 'smtp.gmail.com';
    $port = 587;
    $username = '';
    $password = '';
    $from = '';
    $fromName = 'CMS BCA AI/ML';
    $to = '';
    $encryption = 'tls';

    try {
        global $pdo;
        if (isset($pdo) && $pdo) {
            $stmt = $pdo->prepare("SELECT mail_config FROM system_settings LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row && $row['mail_config']) {
                $cfg = json_decode($row['mail_config'], true);
                if ($cfg) {
                    $host = $cfg['mail_host'] ?? $host;
                    $port = $cfg['mail_port'] ?? $port;
                    $username = $cfg['mail_username'] ?? $username;
                    $password = $cfg['mail_password'] ?? $password;
                    $from = $cfg['mail_from'] ?? $from;
                    $fromName = $cfg['mail_from_name'] ?? $fromName;
                    $to = $cfg['mail_to'] ?? $to;
                    $encryption = $cfg['mail_encryption'] ?? $encryption;
                }
            }
        }
    } catch (PDOException $e) {}

    if (empty($username) || empty($password) || empty($to)) {
        require_once __DIR__ . '/../config/mail.php';
        $username = MAIL_USERNAME;
        $password = MAIL_PASSWORD;
        $from = MAIL_FROM;
        $to = MAIL_TO;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $encryption;
        $mail->Port = (int)$port;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("Mail error: " . $e->getMessage());
        return false;
    } catch (\Exception $e) {
        error_log("Mail error: " . $e->getMessage());
        return false;
    }
}

function notifyEmail($section, $action, $details = null) {
    $userName = $_SESSION['user_name'] ?? 'System';
    $time = date('d M Y h:i A');
    $subject = "CMS Update - {$section} - " . ucfirst($action);

    $actionColor = match(strtolower($action)) {
        'added', 'created', 'submitted' => '#00d25b',
        'updated', 'changed', 'status updated' => '#0090e7',
        'deleted', 'removed', 'cancelled' => '#fc424a',
        'approved' => '#00d25b',
        'rejected' => '#fc424a',
        'exported', 'imported' => '#8a2be2',
        default => '#00d2ff'
    };
    $actionIcon = match(strtolower($action)) {
        'added', 'created', 'submitted' => '&#10003;',
        'updated', 'changed', 'status updated' => '&#9881;',
        'deleted', 'removed', 'cancelled' => '&#10007;',
        'approved' => '&#10003;',
        'rejected' => '&#10007;',
        default => '&#9654;'
    };

    $body = "<div style='font-family:Inter,Segoe UI,sans-serif;max-width:580px;margin:0 auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 20px 60px rgba(0,0,0,0.12)'>";

    $body .= "<div style='background:linear-gradient(135deg,#0a0a1a 0%,#1a0a2e 50%,#0a1a2e 100%);padding:35px 35px 25px;text-align:center'>";
    $body .= "<div style='width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#00d2ff,#8a2be2);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:26px;color:#fff'>&#127891;</div>";
    $body .= "<h1 style='margin:0;font-size:22px;font-weight:800;letter-spacing:-0.5px;background:linear-gradient(90deg,#00d2ff,#0090e7,#8a2be2);-webkit-background-clip:text;-webkit-text-fill-color:transparent'>CMS BCA AI/ML</h1>";
    $body .= "<p style='margin:5px 0 0;color:#6c7293;font-size:13px;letter-spacing:0.5px'>Course Management System &bull; SITM College</p>";
    $body .= "</div>";

    $body .= "<div style='padding:30px 35px'>";

    $body .= "<p style='color:#4a4a6a;font-size:14px;margin:0 0 5px'>Hello Rohit,</p>";
    $body .= "<p style='color:#6b7280;font-size:14px;margin:0 0 20px'>A new activity has been recorded in the system.</p>";

    $body .= "<div style='display:flex;align-items:center;gap:12px;margin-bottom:20px'>";
    $body .= "<div style='display:inline-flex;align-items:center;justify-content:center;min-width:50px;height:32px;padding:0 16px;border-radius:20px;background:{$actionColor}15;color:{$actionColor};font-size:13px;font-weight:700;letter-spacing:0.3px;text-transform:uppercase'>{$actionIcon} " . ucfirst($action) . "</div>";
    $body .= "<span style='color:#9ca3af;font-size:13px'>{$time}</span>";
    $body .= "</div>";

    $body .= "<table style='width:100%;border-collapse:collapse;background:#f9fafb;border-radius:12px;overflow:hidden;margin-bottom:20px'>";
    $body .= "<tr><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;width:120px;color:#6b7280;font-size:13px'>Section</td><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;color:#1a1a2e;font-size:14px;font-weight:600'>" . htmlspecialchars($section) . "</td></tr>";
    $body .= "<tr><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;width:120px;color:#6b7280;font-size:13px'>Action</td><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;color:#1a1a2e;font-size:14px'>" . ucfirst($action) . "</td></tr>";
    $body .= "<tr><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;width:120px;color:#6b7280;font-size:13px'>Performed By</td><td style='padding:14px 18px;border-bottom:1px solid #f0f0f0;color:#1a1a2e;font-size:14px'>" . htmlspecialchars($userName) . "</td></tr>";
    $body .= "<tr><td style='padding:14px 18px;width:120px;color:#6b7280;font-size:13px'>Date &amp; Time</td><td style='padding:14px 18px;color:#1a1a2e;font-size:14px'>{$time}</td></tr>";
    if ($details) {
        $body .= "<tr><td style='padding:14px 18px;border-top:1px solid #f0f0f0;width:120px;color:#6b7280;font-size:13px'>Details</td><td style='padding:14px 18px;border-top:1px solid #f0f0f0;color:#1a1a2e;font-size:14px'>" . htmlspecialchars($details) . "</td></tr>";
    }
    $body .= "</table>";

    $body .= "<div style='background:linear-gradient(135deg,rgba(0,210,255,0.04),rgba(138,43,226,0.04));border-radius:10px;padding:16px 20px;text-align:center;margin-bottom:20px'>";
    $body .= "<p style='margin:0;color:#6b7280;font-size:13px'>You are receiving this email because you are registered as the administrator of <strong style='color:#1a1a2e'>CMS BCA AI/ML</strong>.</p>";
    $body .= "</div>";

    $body .= "<div style='text-align:center'>";
    $body .= "<a href='http://localhost/bca-portal' style='display:inline-block;padding:12px 32px;background:linear-gradient(90deg,#00d2ff,#0090e7,#8a2be2);color:#ffffff;text-decoration:none;border-radius:10px;font-size:14px;font-weight:600'>Open Dashboard</a>";
    $body .= "</div>";

    $body .= "</div>";

    $body .= "<div style='background:#f9fafb;padding:18px 35px;text-align:center;border-top:1px solid #f0f0f0'>";
    $body .= "<p style='margin:0;color:#9ca3af;font-size:12px'>This is an automated notification from CMS BCA AI/ML &bull; SITM College</p>";
    $body .= "<p style='margin:3px 0 0;color:#d1d5db;font-size:11px'>&copy; " . date('Y') . " Rohit Kumar Saw &bull; BCA (AI/ML)</p>";
    $body .= "</div>";

    $body .= "</div>";

    sendMail($subject, $body);
}
