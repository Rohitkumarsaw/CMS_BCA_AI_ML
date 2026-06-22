<?php
require_once '../config/config.php';
require_once '../config/db_connection.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) { header("Location: ../login.php"); exit; }

header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$additionalEmails = trim($_POST['additional_emails'] ?? '');
$message = trim($_POST['message'] ?? '');
$period = $_POST['period'] ?? 'daily';
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

$where = []; $params = [];
switch ($period) {
    case 'daily': $where[] = "DATE(logged_at) = CURDATE()"; $periodLabel = 'Daily'; break;
    case 'weekly': $where[] = "logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; $periodLabel = 'Weekly'; break;
    case 'monthly': $where[] = "logged_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"; $periodLabel = 'Monthly'; break;
    case 'custom':
        if (!empty($startDate)) { $where[] = "logged_at >= ?"; $params[] = $startDate . ' 00:00:00'; }
        if (!empty($endDate)) { $where[] = "logged_at <= ?"; $params[] = $endDate . ' 23:59:59'; }
        $periodLabel = $startDate && $endDate ? "$startDate to $endDate" : 'Custom';
        break;
}
$whereClause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM activity_logs $whereClause ORDER BY logged_at DESC");
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsStmt = $pdo->prepare("SELECT action_type, COUNT(*) as cnt FROM activity_logs $whereClause GROUP BY action_type");
$statsStmt->execute($params);
$stats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$total = count($activities);
$created = 0; $updated = 0; $deleted = 0;
foreach ($activities as $row) {
    $at = $row['action_type'];
    if (preg_match('/^(Added|Created|Submitted|Approved|Applied|Approve|Submit)/i', $at)) $created++;
    elseif (preg_match('/^(Updated|Edited|Changed|Modified|Update|Edit)/i', $at)) $updated++;
    elseif (preg_match('/^(Deleted|Removed|Cancelled|Rejected|Delete|Remove|Cancel|Reject)/i', $at)) $deleted++;
}

// Generate PDF attachment
require_once '../libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

$pdfHtml = '<html><head><meta charset="UTF-8"><style>
    @page{margin:18mm 18mm 18mm 18mm;}
    body{font-family:"DejaVu Sans",Tahoma,sans-serif;background:#0a0a0f;color:#e0e0e0;font-size:10.5px;line-height:1.6;}
    .hdr{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:30px 35px 24px;text-align:center;border-radius:12px;margin-bottom:24px;color:#fff;border:1px solid rgba(255,255,255,0.08);}
    .hdr h1{font-size:28px;font-weight:700;margin:0 0 6px;color:#fff;letter-spacing:0.5px;}
    .hdr p{margin:2px 0;font-size:11px;color:rgba(255,255,255,0.9);}
    .pi{text-align:center;margin-bottom:22px;color:#6a6a80;font-size:9.5px;}
    .pi strong{color:#8899d0;}
    .sr{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;}
    .sc{background:rgba(255,255,255,0.06);padding:18px 14px 14px;border-radius:10px;border:1.5px solid rgba(102,126,234,0.25);text-align:center;}
    .sc .n{font-size:30px;font-weight:800;line-height:1;margin:0;}
    .sc .l{color:#8a8aa0;font-size:8.5px;text-transform:uppercase;font-weight:700;letter-spacing:0.6px;margin:8px 0 0;}
    .t{color:#667eea;}.c{color:#00ff88;}.u{color:#ff6b6b;}.d{color:#ff4444;}
    .sc.cr{border-color:rgba(0,255,136,0.3);background:rgba(0,255,136,0.05);}
    .sc.up{border-color:rgba(255,107,107,0.3);background:rgba(255,107,107,0.05);}
    .sc.dl{border-color:rgba(255,68,68,0.3);background:rgba(255,68,68,0.05);}
    table{width:100%;border-collapse:collapse;font-size:9px;border:1px solid rgba(102,126,234,0.2);}
    th{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:10px 10px;text-align:left;font-weight:700;font-size:8px;letter-spacing:0.5px;text-transform:uppercase;}
    td{padding:8px 10px;border-bottom:1px solid rgba(102,126,234,0.08);color:#d0d0e0;word-break:break-word;}
    tr:nth-child(even) td{background:rgba(255,255,255,0.02);}
    .ftr{text-align:center;margin-top:28px;padding:16px 20px 12px;border-top:1px solid rgba(102,126,234,0.15);font-size:8.5px;color:#6a6a80;line-height:1.8;}
    .ftr strong{color:#8899d0;}
</style></head><body>
<div class="hdr"><h1>Activity Report</h1><p>SITM College &mdash; BCA (AI/ML)</p></div>
<div class="pi"><strong>Period:</strong> ' . htmlspecialchars($periodLabel) . ' &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Generated:</strong> ' . date('d M Y h:i A') . '</div>
<div class="sr">
    <div class="sc"><p class="n t">' . $total . '</p><p class="l">Total Activities</p></div>
    <div class="sc cr"><p class="n c">' . $created . '</p><p class="l">Created</p></div>
    <div class="sc up"><p class="n u">' . $updated . '</p><p class="l">Updated</p></div>
    <div class="sc dl"><p class="n d">' . $deleted . '</p><p class="l">Deleted</p></div>
</div>
<table><thead><tr><th style="width:18%;">Date &amp; Time</th><th style="width:16%;">User</th><th style="width:16%;">Module</th><th style="width:11%;">Action</th><th style="width:39%;">Description</th></tr></thead><tbody>';
if (count($activities) > 0) {
    foreach ($activities as $row) {
        $at = $row['action_type'];
        $ac = 'color:#667eea;';
        if (preg_match('/^(Added|Created|Submitted|Approved|Applied|Approve|Submit)/i',$at)) $ac = 'color:#00ff88;font-weight:700;';
        elseif (preg_match('/^(Updated|Edited|Changed|Modified|Update|Edit)/i',$at)) $ac = 'color:#ff6b6b;font-weight:700;';
        elseif (preg_match('/^(Deleted|Removed|Cancelled|Rejected|Delete|Remove|Cancel|Reject)/i',$at)) $ac = 'color:#ff4444;font-weight:700;';
        $pdfHtml .= '<tr><td style="color:#c0c0d0;">' . date('d M Y, h:i A', strtotime($row['logged_at'])) . '</td>
            <td><strong style="color:#e0e0f0;">' . htmlspecialchars($row['user_name']) . '</strong></td>
            <td><span style="color:#667eea;font-weight:600;">' . htmlspecialchars(ucfirst($row['section_name'])) . '</span></td>
            <td style="' . $ac . '">' . htmlspecialchars($at) . '</td>
            <td style="color:#b0b0c0;">' . htmlspecialchars($row['details'] ?? '') . '</td></tr>';
    }
} else {
    $pdfHtml .= '<tr><td colspan="5" style="text-align:center;color:#6a6a80;padding:30px;"><strong>No activities found for this period.</strong></td></tr>';
}
$pdfHtml .= '</tbody></table>
<div class="ftr"><strong>Developer:</strong> Rohit Kumar Saw &nbsp;|&nbsp; BCA (AI/ML), SITM College</div>
</body></html>';

$dompdf->loadHtml($pdfHtml);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$pdfOutput = $dompdf->output();
$pdfFilename = 'activity_report_' . $period . '_' . date('Y-m-d') . '.pdf';

// Build email HTML
function buildEmailBody($stats, $total, $created, $updated, $deleted, $periodLabel, $message) {
    $msgHtml = !empty($message) ? '<div style="background:rgba(102,126,234,0.08);border-radius:10px;padding:16px 20px;margin-bottom:20px;border-left:3px solid #667eea"><p style="margin:0;color:#a0a0b0;font-size:13px"><strong style="color:#e0e0e0">Your Note:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p></div>' : '';
    return '<div style="background:#0a0a0f;padding:40px 20px;font-family:Segoe UI,Tahoma,sans-serif">
        <div style="max-width:560px;margin:auto;background:#1a1a2e;border-radius:14px;overflow:hidden;border:1px solid rgba(102,126,234,0.2)">
            <div style="background:linear-gradient(135deg,#0a0a1a,#1a0a2e);padding:30px;text-align:center;border-bottom:1px solid rgba(102,126,234,0.2)">
                <div style="width:50px;height:50px;border-radius:12px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:22px;color:#fff">&#128202;</div>
                <h1 style="margin:0;font-size:20px;color:#fff">Activity Report</h1>
                <p style="margin:4px 0 0;color:#a0a0b0;font-size:13px">CMS BCA AI/ML &bull; SITM College</p>
            </div>
            <div style="padding:25px 30px">
                <p style="color:#a0a0b0;font-size:14px;margin:0 0 5px">Hello Rohit,</p>
                <p style="color:#6c7293;font-size:13px;margin:0 0 20px">Your <strong style="color:#667eea">' . htmlspecialchars($periodLabel) . '</strong> activity report is ready.</p>
                <div style="background:rgba(102,126,234,0.06);border-radius:10px;padding:14px;margin-bottom:20px;text-align:center">
                    <span style="color:#667eea;font-size:12px">Period: ' . htmlspecialchars($periodLabel) . ' &nbsp;|&nbsp; Generated: ' . date('d M Y h:i A') . '</span>
                </div>
                ' . $msgHtml . '
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
                    <div style="background:#0a0a0f;border-radius:8px;padding:14px;text-align:center;border:1px solid rgba(102,126,234,0.15)">
                        <p style="font-size:22px;font-weight:bold;color:#667eea;margin:0">' . $total . '</p>
                        <p style="font-size:11px;color:#6c7293;margin:4px 0 0">Total Activities</p>
                    </div>
                    <div style="background:#0a0a0f;border-radius:8px;padding:14px;text-align:center;border:1px solid rgba(0,255,136,0.15)">
                        <p style="font-size:22px;font-weight:bold;color:#00ff88;margin:0">' . $created . '</p>
                        <p style="font-size:11px;color:#6c7293;margin:4px 0 0">Created</p>
                    </div>
                    <div style="background:#0a0a0f;border-radius:8px;padding:14px;text-align:center;border:1px solid rgba(255,107,107,0.15)">
                        <p style="font-size:22px;font-weight:bold;color:#ff6b6b;margin:0">' . $updated . '</p>
                        <p style="font-size:11px;color:#6c7293;margin:4px 0 0">Updated</p>
                    </div>
                    <div style="background:#0a0a0f;border-radius:8px;padding:14px;text-align:center;border:1px solid rgba(255,68,68,0.15)">
                        <p style="font-size:22px;font-weight:bold;color:#ff4444;margin:0">' . $deleted . '</p>
                        <p style="font-size:11px;color:#6c7293;margin:4px 0 0">Deleted</p>
                    </div>
                </div>
                <div style="background:rgba(102,126,234,0.06);border-radius:8px;padding:12px 16px;text-align:center;margin-bottom:16px">
                    <p style="margin:0;color:#6c7293;font-size:12px">&#128196; PDF report attached &bull; Open in your browser or save</p>
                </div>
                <div style="text-align:center">
                    <a href="http://localhost/bca-portal/reports/reports.php" style="display:inline-block;padding:11px 28px;background:linear-gradient(90deg,#667eea,#764ba2);color:#fff;text-decoration:none;border-radius:8px;font-size:13px;font-weight:600">Open Reports Dashboard</a>
                </div>
            </div>
            <div style="background:#0a0a0f;padding:16px 30px;text-align:center;border-top:1px solid rgba(102,126,234,0.1)">
                <p style="margin:0;color:#6c7293;font-size:11px">CMS BCA AI/ML &bull; SITM College &bull; ' . date('Y') . '</p>
                <p style="margin:2px 0 0;color:#4a4a5a;font-size:10px">Rohit Kumar Saw</p>
            </div>
        </div>
    </div>';
}

require_once '../config/mail.php';
require_once '../libraries/PHPMailer/src/Exception.php';
require_once '../libraries/PHPMailer/src/PHPMailer.php';
require_once '../libraries/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = MAIL_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = MAIL_USERNAME;
    $mail->Password = MAIL_PASSWORD;
    $mail->SMTPSecure = 'tls';
    $mail->Port = (int)MAIL_PORT;
    $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($email);

    if (!empty($additionalEmails)) {
        $emails = array_map('trim', explode(',', $additionalEmails));
        foreach ($emails as $e) {
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($e);
            }
        }
    }

    $mail->addStringAttachment($pdfOutput, $pdfFilename);
    $mail->isHTML(true);
    $mail->Subject = '📄 CMS Activity Report - ' . $periodLabel . ' (' . date('Y-m-d') . ')';
    $mail->Body = buildEmailBody($stats, $total, $created, $updated, $deleted, $periodLabel, $message);
    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Report sent successfully to ' . $email . (!empty($additionalEmails) ? ' (+ additional recipients)' : '') . '!']);
} catch (PHPMailerException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send email: ' . $mail->ErrorInfo]);
} catch (\Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
}
