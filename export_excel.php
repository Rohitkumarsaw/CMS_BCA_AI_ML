<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'overall';

$filename = $type . '_report_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fputcsv($output, ['CMS (BCA AI/ML) - ' . ucfirst($type) . ' Report']);
fputcsv($output, ['Student: ' . $userName, 'Generated: ' . date('F d, Y h:i A')]);
fputcsv($output, []);

switch ($type) {
    case 'attendance':
        fputcsv($output, ['Date', 'Subject', 'Status']);
        $stmt = $pdo->prepare("SELECT date, subject, status FROM attendance WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            fputcsv($output, [formatDate($r['date']), $r['subject'], ucfirst($r['status'])]);
        }
        fputcsv($output, []);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count, SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_count, SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late_count FROM attendance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Total Days', $summary['total']]);
        fputcsv($output, ['Present', $summary['present_count']]);
        fputcsv($output, ['Absent', $summary['absent_count']]);
        fputcsv($output, ['Late', $summary['late_count']]);
        $pct = $summary['total'] > 0 ? round(($summary['present_count'] / $summary['total']) * 100, 1) : 0;
        fputcsv($output, ['Attendance %', $pct . '%']);
        break;

    case 'grades':
        fputcsv($output, ['Semester', 'Exam Name', 'Subject', 'Marks Obtained', 'Total Marks', 'Percentage', 'Date']);
        $stmt = $pdo->prepare("SELECT semester, exam_name, subject, marks_obtained, total_marks, date FROM grades WHERE user_id = ? ORDER BY semester, date DESC");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            $pct = $r['total_marks'] > 0 ? round(($r['marks_obtained'] / $r['total_marks']) * 100, 1) : 0;
            fputcsv($output, [$r['semester'], $r['exam_name'], $r['subject'], $r['marks_obtained'], $r['total_marks'], $pct . '%', formatDate($r['date'])]);
        }
        fputcsv($output, []);
        fputcsv($output, ['Semester Averages']);
        fputcsv($output, ['Semester', 'Average %', 'Exam Count']);
        $stmt = $pdo->prepare("SELECT semester, AVG(marks_obtained/total_marks*100) as avg_pct, COUNT(*) as count FROM grades WHERE user_id = ? GROUP BY semester ORDER BY semester");
        $stmt->execute([$userId]);
        $avgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($avgs as $g) {
            fputcsv($output, [$g['semester'], number_format($g['avg_pct'], 1) . '%', $g['count']]);
        }
        break;

    case 'homework':
        fputcsv($output, ['Title', 'Subject', 'Due Date', 'Status', 'Description']);
        $stmt = $pdo->prepare("SELECT title, subject, due_date, status, description FROM homework WHERE user_id = ? ORDER BY due_date DESC");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            fputcsv($output, [$r['title'], $r['subject'], formatDate($r['due_date']), $r['status'], $r['description']]);
        }
        fputcsv($output, []);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Submitted' THEN 1 ELSE 0 END) as submitted, SUM(CASE WHEN status!='Submitted' THEN 1 ELSE 0 END) as not_submitted FROM homework WHERE user_id = ?");
        $stmt->execute([$userId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Total', $summary['total']]);
        fputcsv($output, ['Submitted', $summary['submitted']]);
        fputcsv($output, ['Not Submitted', $summary['not_submitted']]);
        break;

    case 'syllabus':
        fputcsv($output, ['Semester', 'Subject', 'Topic', 'Status']);
        $stmt = $pdo->prepare("SELECT semester, subject, topic, status FROM syllabus WHERE user_id = ? ORDER BY semester, subject");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            fputcsv($output, [$r['semester'], $r['subject'], $r['topic'], $r['status']]);
        }
        break;

    case 'projects':
        fputcsv($output, ['Semester', 'Title', 'Category', 'Description', 'Start Date', 'End Date']);
        $stmt = $pdo->prepare("SELECT semester, title, category, description, start_date, end_date FROM projects WHERE user_id = ? ORDER BY semester, created_at DESC");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            fputcsv($output, [$r['semester'], $r['title'], $r['category'], $r['description'], formatDate($r['start_date']), !empty($r['end_date']) ? formatDate($r['end_date']) : 'Ongoing']);
        }
        break;

    case 'payments':
        fputcsv($output, ['Semester', 'Payment Type', 'Amount', 'Date', 'Method', 'Transaction ID', 'Status']);
        $stmt = $pdo->prepare("SELECT semester, payment_type, amount, payment_date, payment_method, transaction_id, status FROM payments WHERE user_id = ? ORDER BY semester, payment_date DESC");
        $stmt->execute([$userId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($records as $r) {
            fputcsv($output, [$r['semester'], $r['payment_type'], $r['amount'], formatDate($r['payment_date']), $r['payment_method'], $r['transaction_id'] ?? '', $r['status']]);
        }
        fputcsv($output, []);
        fputcsv($output, ['Semester Summary']);
        fputcsv($output, ['Semester', 'Paid', 'Unpaid', 'Total']);
        $stmt = $pdo->prepare("SELECT semester, SUM(CASE WHEN status='Paid' THEN amount ELSE 0 END) as paid, SUM(CASE WHEN status='Unpaid' THEN amount ELSE 0 END) as unpaid, SUM(amount) as total FROM payments WHERE user_id = ? GROUP BY semester ORDER BY semester");
        $stmt->execute([$userId]);
        $summaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($summaries as $s) {
            fputcsv($output, [$s['semester'], $s['paid'], $s['unpaid'], $s['total']]);
        }
        break;

    default:
        fputcsv($output, ['Category', 'Value']);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count FROM attendance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $att = $stmt->fetch(PDO::FETCH_ASSOC);
        fputcsv($output, ['Total Attendance Days', $att['total']]);
        $attPct = $att['total'] > 0 ? round(($att['present_count'] / $att['total']) * 100, 1) : 0;
        fputcsv($output, ['Attendance Percentage', $attPct . '%']);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM homework WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Homework', $stmt->fetchColumn()]);
        $stmt = $pdo->prepare("SELECT AVG(marks_obtained/total_marks*100) as avg_pct FROM grades WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Average Grade', number_format($stmt->fetchColumn() ?? 0, 1) . '%']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Projects', $stmt->fetchColumn()]);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Skills', $stmt->fetchColumn()]);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM certifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Certifications', $stmt->fetchColumn()]);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Internships', $stmt->fetchColumn()]);
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE user_id = ?");
        $stmt->execute([$userId]);
        fputcsv($output, ['Total Payment', 'Rs. ' . number_format($stmt->fetchColumn() ?? 0)]);
        break;
}

fclose($output);
exit;
