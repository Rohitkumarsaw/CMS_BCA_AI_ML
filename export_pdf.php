<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

require_once __DIR__ . '/libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : 'overall';

$title = ucfirst($type) . ' Report';
$data = [];

switch ($type) {
    case 'attendance':
        $stmt = $pdo->prepare("SELECT date, subject, status FROM attendance WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count, SUM(CASE WHEN status='Absent' THEN 1 ELSE 0 END) as absent_count, SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END) as late_count FROM attendance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);
        break;
    case 'grades':
        $stmt = $pdo->prepare("SELECT semester, exam_name, subject, marks_obtained, total_marks, date FROM grades WHERE user_id = ? ORDER BY semester, date DESC");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT semester, AVG(marks_obtained/total_marks*100) as avg_pct, COUNT(*) as count FROM grades WHERE user_id = ? GROUP BY semester ORDER BY semester");
        $stmt->execute([$userId]);
        $data['semester_avg'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'homework':
        $stmt = $pdo->prepare("SELECT title, subject, due_date, status, description FROM homework WHERE user_id = ? ORDER BY due_date DESC");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Submitted' THEN 1 ELSE 0 END) as submitted, SUM(CASE WHEN status!='Submitted' THEN 1 ELSE 0 END) as not_submitted FROM homework WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['summary'] = $stmt->fetch(PDO::FETCH_ASSOC);
        break;
    case 'syllabus':
        $stmt = $pdo->prepare("SELECT semester, subject, topic, status FROM syllabus WHERE user_id = ? ORDER BY semester, subject");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'projects':
        $stmt = $pdo->prepare("SELECT semester, title, category, description, start_date, end_date FROM projects WHERE user_id = ? ORDER BY semester, created_at DESC");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    case 'payments':
        $stmt = $pdo->prepare("SELECT semester, payment_type, amount, payment_date, payment_method, transaction_id, status FROM payments WHERE user_id = ? ORDER BY semester, payment_date DESC");
        $stmt->execute([$userId]);
        $data['records'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT semester, SUM(CASE WHEN status='Paid' THEN amount ELSE 0 END) as paid, SUM(CASE WHEN status='Unpaid' THEN amount ELSE 0 END) as unpaid, SUM(amount) as total FROM payments WHERE user_id = ? GROUP BY semester ORDER BY semester");
        $stmt->execute([$userId]);
        $data['semester_summary'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;
    default:
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present_count FROM attendance WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['attendance'] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='Submitted' THEN 1 ELSE 0 END) as submitted FROM homework WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['homework'] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT AVG(marks_obtained/total_marks*100) as avg_pct, COUNT(*) as count FROM grades WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['grades'] = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['projects'] = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM certifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['certifications'] = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['skills'] = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['total_payment'] = $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM internships WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data['internships'] = $stmt->fetchColumn();
        break;
}

ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:DejaVu Sans,sans-serif;padding:20px;color:#333;line-height:1.5;font-size:10pt}
.header{text-align:center;margin-bottom:20px;border-bottom:2px solid #2563eb;padding-bottom:12px}
.header h1{color:#2563eb;font-size:16pt;margin-bottom:3px}
.header p{color:#666;font-size:7.5pt;margin:1px 0}
.section{margin-bottom:15px}
.section h2{color:#1e293b;font-size:11pt;margin-bottom:6px;border-left:3px solid #2563eb;padding-left:8px}
table{width:100%;border-collapse:collapse;margin:6px 0;font-size:8pt}
th{background:#2563eb;color:#fff;padding:5px 7px;text-align:left;font-weight:600}
td{border:1px solid #ddd;padding:4px 7px}
tr:nth-child(even){background:#f8fafc}
.summary-grid{display:table;width:100%;margin:10px 0;border-collapse:separate;border-spacing:6px}
.summary-row{display:table-row}
.summary-card{display:table-cell;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:8px;text-align:center;width:12.5%}
.summary-card h3{font-size:14pt;color:#2563eb;margin:0 0 2px}
.summary-card p{font-size:6.5pt;color:#666;margin:0}
.badge{display:inline-block;padding:1px 5px;border-radius:2px;font-size:6.5pt;font-weight:600}
.badge-success{background:#d1e7dd;color:#0f5132}
.badge-danger{background:#f8d7da;color:#842029}
.badge-warning{background:#fff3cd;color:#664d03}
.footer{text-align:center;margin-top:15px;padding-top:8px;border-top:1px solid #e2e8f0;font-size:7pt;color:#999}
</style>
</head>
<body>
<div class="header">
  <h1><?php echo $title; ?></h1>
  <p><?php echo SITE_NAME; ?> | Student: <?php echo htmlspecialchars($userName); ?> | Generated: <?php echo date('F d, Y h:i A'); ?></p>
</div>

<?php if ($type === 'attendance' && isset($data['summary'])): ?>
    <div class="summary-grid">
        <div class="summary-row">
        <div class="summary-card"><h3><?php echo $data['summary']['total'] ?? 0; ?></h3><p>Total Days</p></div>
        <div class="summary-card"><h3><?php echo $data['summary']['present_count'] ?? 0; ?></h3><p>Present</p></div>
        <div class="summary-card"><h3><?php echo $data['summary']['absent_count'] ?? 0; ?></h3><p>Absent</p></div>
        <div class="summary-card"><h3><?php echo $data['summary']['late_count'] ?? 0; ?></h3><p>Late</p></div>
        </div>
    </div>
    <?php if (!empty($data['records'])): ?>
    <div class="section">
        <h2>Attendance Records</h2>
        <table>
            <thead><tr><th>Date</th><th>Subject</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <tr>
                    <td><?php echo formatDate($r['date']); ?></td>
                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                    <td><span class="badge badge-<?php echo strtolower($r['status'])==='present'?'success':(strtolower($r['status'])==='absent'?'danger':'warning'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($type === 'grades'): ?>
    <?php if (!empty($data['semester_avg'])): ?>
    <div class="section">
        <h2>Semester Averages</h2>
        <table>
            <thead><tr><th>Semester</th><th>Average %</th><th>Exams</th></tr></thead>
            <tbody>
            <?php foreach ($data['semester_avg'] as $g): ?>
                <tr>
                    <td>Semester <?php echo $g['semester']; ?></td>
                    <td><?php echo number_format($g['avg_pct'], 1); ?>%</td>
                    <td><?php echo $g['count']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <?php if (!empty($data['records'])): ?>
    <div class="section">
        <h2>Grade Records</h2>
        <table>
            <thead><tr><th>Semester</th><th>Exam</th><th>Marks</th><th>Total</th><th>Percentage</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <?php $pct = $r['total_marks'] > 0 ? round(($r['marks_obtained']/$r['total_marks'])*100, 1) : 0; ?>
                <tr>
                    <td><?php echo $r['semester']; ?></td>
                    <td><?php echo htmlspecialchars($r['exam_name']); ?></td>
                    <td><?php echo $r['marks_obtained']; ?></td>
                    <td><?php echo $r['total_marks']; ?></td>
                    <td><?php echo $pct; ?>%</td>
                    <td><?php echo formatDate($r['date']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($type === 'homework'): ?>
    <?php if (isset($data['summary'])): ?>
    <div class="summary-grid">
        <div class="summary-row">
        <div class="summary-card"><h3><?php echo $data['summary']['total'] ?? 0; ?></h3><p>Total</p></div>
        <div class="summary-card"><h3><?php echo $data['summary']['submitted'] ?? 0; ?></h3><p>Submitted</p></div>
        <div class="summary-card"><h3><?php echo $data['summary']['not_submitted'] ?? 0; ?></h3><p>Not Submitted</p></div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($data['records'])): ?>
    <div class="section">
        <h2>Homework Records</h2>
        <table>
            <thead><tr><th>Title</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                    <td><?php echo formatDate($r['due_date']); ?></td>
                    <td><span class="badge badge-<?php echo $r['status']==='Submitted'?'success':'warning'; ?>"><?php echo $r['status']; ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($type === 'syllabus' && !empty($data['records'])): ?>
    <div class="section">
        <h2>Syllabus Progress</h2>
        <table>
            <thead><tr><th>Semester</th><th>Subject</th><th>Topic</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <tr>
                    <td><?php echo $r['semester']; ?></td>
                    <td><?php echo htmlspecialchars($r['subject']); ?></td>
                    <td><?php echo htmlspecialchars($r['topic']); ?></td>
                    <td><span class="badge badge-<?php echo $r['status']==='Completed'?'success':($r['status']==='In Progress'?'warning':'danger'); ?>"><?php echo $r['status']; ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($type === 'projects' && !empty($data['records'])): ?>
    <div class="section">
        <h2>Projects</h2>
        <table>
            <thead><tr><th>Semester</th><th>Title</th><th>Category</th><th>Start</th><th>End</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <tr>
                    <td><?php echo $r['semester']; ?></td>
                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                    <td><?php echo htmlspecialchars($r['category']); ?></td>
                    <td><?php echo formatDate($r['start_date']); ?></td>
                    <td><?php echo !empty($r['end_date']) ? formatDate($r['end_date']) : 'Ongoing'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($type === 'payments'): ?>
    <?php if (!empty($data['semester_summary'])): ?>
    <div class="section">
        <h2>Payment Summary</h2>
        <table>
            <thead><tr><th>Semester</th><th>Paid</th><th>Unpaid</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($data['semester_summary'] as $ps): ?>
                <tr>
                    <td>Semester <?php echo $ps['semester']; ?></td>
                    <td>&#8377;<?php echo number_format($ps['paid']); ?></td>
                    <td>&#8377;<?php echo number_format($ps['unpaid']); ?></td>
                    <td>&#8377;<?php echo number_format($ps['total']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <?php if (!empty($data['records'])): ?>
    <div class="section">
        <h2>Payment Records</h2>
        <table>
            <thead><tr><th>Sem</th><th>Type</th><th>Amount</th><th>Date</th><th>Method</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($data['records'] as $r): ?>
                <tr>
                    <td><?php echo $r['semester']; ?></td>
                    <td><?php echo htmlspecialchars($r['payment_type']); ?></td>
                    <td>&#8377;<?php echo number_format($r['amount']); ?></td>
                    <td><?php echo formatDate($r['payment_date']); ?></td>
                    <td><?php echo htmlspecialchars($r['payment_method']); ?></td>
                    <td><span class="badge badge-<?php echo $r['status']==='Paid'?'success':($r['status']==='Partial'?'warning':'danger'); ?>"><?php echo $r['status']; ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($type === 'overall'): ?>
    <div class="summary-grid">
        <div class="summary-row">
        <div class="summary-card"><h3><?php echo $data['attendance']['total'] ?? 0; ?></h3><p>Attendance Days</p></div>
        <div class="summary-card"><h3><?php echo $data['homework']['total'] ?? 0; ?></h3><p>Homework</p></div>
        <div class="summary-card"><h3><?php echo number_format($data['grades']['avg_pct'] ?? 0, 1); ?>%</h3><p>Avg Grade</p></div>
        <div class="summary-card"><h3><?php echo $data['projects'] ?? 0; ?></h3><p>Projects</p></div>
        </div>
        <div class="summary-row">
        <div class="summary-card"><h3><?php echo $data['skills'] ?? 0; ?></h3><p>Skills</p></div>
        <div class="summary-card"><h3><?php echo $data['certifications'] ?? 0; ?></h3><p>Certifications</p></div>
        <div class="summary-card"><h3><?php echo $data['internships'] ?? 0; ?></h3><p>Internships</p></div>
        <div class="summary-card"><h3>&#8377;<?php echo number_format($data['total_payment'] ?? 0); ?></h3><p>Total Payment</p></div>
        </div>
    </div>
<?php endif; ?>

<div class="footer">
    <p><?php echo SITE_NAME; ?> &copy; <?php echo date('Y'); ?> | This is a computer-generated report.</p>
</div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isFontSubsettingEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream(ucfirst($type) . '_Report_' . date('Ymd') . '.pdf', ['Attachment' => true]);
