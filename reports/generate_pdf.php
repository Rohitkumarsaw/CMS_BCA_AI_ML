<?php
require_once '../config/config.php';
require_once '../config/db_connection.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) { header("Location: ../login.php"); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

$period = $_GET['period'] ?? 'daily';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$module = $_GET['module'] ?? '';

$where = [];
$params = [];

switch ($period) {
    case 'daily':
        $where[] = "DATE(logged_at) = CURDATE()";
        $periodLabel = 'Daily';
        break;
    case 'weekly':
        $where[] = "logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $periodLabel = 'Weekly';
        break;
    case 'monthly':
        $where[] = "logged_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        $periodLabel = 'Monthly';
        break;
    case 'custom':
        if (!empty($startDate)) $where[] = "logged_at >= ?"; $params[] = $startDate . ' 00:00:00';
        if (!empty($endDate)) $where[] = "logged_at <= ?"; $params[] = $endDate . ' 23:59:59';
        $periodLabel = $startDate && $endDate ? "$startDate to $endDate" : ($startDate ? "From $startDate" : "Until $endDate");
        break;
}

if (!empty($module)) { $where[] = "section_name = ?"; $params[] = $module; }

$whereClause = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM activity_logs $whereClause ORDER BY logged_at DESC");
$stmt->execute($params);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($activities);

// Action counts
$createCount = 0; $updateCount = 0; $deleteCount = 0;
foreach ($activities as $row) {
    $at = $row['action_type'];
    if (preg_match('/^(Added|Created|Submitted|Approved|Applied|Approve|Submit)/i', $at)) $createCount++;
    elseif (preg_match('/^(Updated|Edited|Changed|Modified|Update|Edit)/i', $at)) $updateCount++;
    elseif (preg_match('/^(Deleted|Removed|Cancelled|Rejected|Delete|Remove|Cancel|Reject)/i', $at)) $deleteCount++;
}

require_once '../libraries/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$dompdf = new Dompdf($options);

$dateFormatted = date('d M Y');
$generatedAt = date('d M Y h:i A');

// Build table rows
$rowsHtml = '';
if (count($activities) > 0) {
    $idx = 0;
    foreach ($activities as $row) {
        $idx++;
        $at = $row['action_type'];
        $actionClass = 'action-READ';
        if (preg_match('/^(Added|Created|Submitted|Approved|Applied|Approve|Submit)/i', $at)) $actionClass = 'action-CREATE';
        elseif (preg_match('/^(Updated|Edited|Changed|Modified|Update|Edit)/i', $at)) $actionClass = 'action-UPDATE';
        elseif (preg_match('/^(Deleted|Removed|Cancelled|Rejected|Delete|Remove|Cancel|Reject)/i', $at)) $actionClass = 'action-DELETE';

        $dt = date('d M Y, h:i A', strtotime($row['logged_at']));
        $userName = htmlspecialchars($row['user_name']);
        $section = htmlspecialchars(ucfirst($row['section_name']));
        $actionType = htmlspecialchars($at);
        $details = htmlspecialchars($row['details'] ?? '');

        $rowsHtml .= "<tr>
            <td style=\"text-align:center;font-weight:700;color:#8899d0;\">{$idx}</td>
            <td style=\"color:#c0c0d0;\">{$dt}</td>
            <td><strong style=\"color:#e0e0f0;\">{$userName}</strong></td>
            <td><span style=\"color:#667eea;font-weight:600;\">{$section}</span></td>
            <td class=\"{$actionClass}\" style=\"font-weight:700;\">{$actionType}</td>
            <td style=\"color:#b0b0c0;\">{$details}</td>
        </tr>";
    }
} else {
    $rowsHtml .= '<tr><td colspan="6" style="text-align:center;color:#a0a0b0;padding:30px;font-size:14px;"><strong>No activities found for this period.</strong></td></tr>';
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Activity Report - CMS BCA AI/ML</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
@page{margin:18mm 18mm 18mm 18mm;}

body{
    font-family:"DejaVu Sans",Tahoma,Geneva,Verdana,sans-serif;
    background:#0a0a0f;
    color:#e0e0e0;
    font-size:10.5px;
    line-height:1.6;
}

/* ====== HEADER ====== */
.report-header{
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    padding:30px 35px 24px;
    text-align:center;
    border-radius:12px;
    margin-bottom:24px;
    color:#ffffff;
    border:1px solid rgba(255,255,255,0.08);
    page-break-after:avoid;
}
.report-header h1{
    font-size:28px;
    font-weight:700;
    margin:0 0 8px;
    color:#ffffff;
    letter-spacing:0.5px;
}
.report-header .sub{
    font-size:12px;
    color:rgba(255,255,255,0.9);
    margin:0 0 4px;
}
.report-header .meta{
    margin-top:12px;
    padding-top:10px;
    border-top:1px solid rgba(255,255,255,0.12);
    font-size:9.5px;
    color:rgba(255,255,255,0.8);
}
.report-header .meta strong{color:#ffffff;}

/* ====== SUMMARY ====== */
.summary-section{
    margin-bottom:28px;
    page-break-after:always;
}
.summary-section .section-title{
    color:#667eea;
    font-size:16px;
    font-weight:700;
    margin:0 0 20px;
    padding-bottom:10px;
    border-bottom:1px solid rgba(102,126,234,0.15);
    letter-spacing:0.3px;
    text-transform:uppercase;
    text-align:center;
}
.summary-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:24px;
}
.summary-card{
    background:rgba(255,255,255,0.06);
    padding:12px 10px 10px;
    border-radius:8px;
    border:1.5px solid rgba(102,126,234,0.25);
    text-align:center;
}
.summary-card .label{
    color:#8a8aa0;
    font-size:8px;
    text-transform:uppercase;
    font-weight:700;
    letter-spacing:0.6px;
    display:block;
    margin-bottom:6px;
}
.summary-card .value{
    font-size:24px;
    font-weight:800;
    line-height:1;
    display:block;
}
.summary-card .value.total{color:#667eea;}
.summary-card .value.create{color:#00ff88;}
.summary-card .value.update{color:#ff6b6b;}
.summary-card .value.delete{color:#ff4444;}
.summary-card.create{border-color:rgba(0,255,136,0.3);background:rgba(0,255,136,0.05);}
.summary-card.update{border-color:rgba(255,107,107,0.3);background:rgba(255,107,107,0.05);}
.summary-card.delete{border-color:rgba(255,68,68,0.3);background:rgba(255,68,68,0.05);}

/* ====== ACTIVITY TABLE ====== */
.activities-section{
    margin-top:0;
}
.activities-section .section-title{
    color:#667eea;
    font-size:16px;
    font-weight:700;
    margin:0 0 14px;
    padding-bottom:10px;
    border-bottom:1px solid rgba(102,126,234,0.15);
    letter-spacing:0.3px;
    text-transform:uppercase;
    text-align:center;
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:9px;
    border:1px solid rgba(102,126,234,0.2);
}
thead th{
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#ffffff;
    padding:10px 10px;
    text-align:left;
    font-weight:700;
    font-size:8px;
    letter-spacing:0.5px;
    text-transform:uppercase;
}
tbody td{
    padding:8px 10px;
    border-bottom:1px solid rgba(102,126,234,0.08);
    color:#d0d0e0;
    vertical-align:middle;
    word-break:break-word;
}
tbody tr:nth-child(even) td{background:rgba(255,255,255,0.02);}
tbody tr{page-break-inside:avoid;}
tbody tr:last-child td{border-bottom:none;}
.action-CREATE{color:#00ff88 !important;font-weight:700 !important;}
.action-UPDATE{color:#ff6b6b !important;font-weight:700 !important;}
.action-DELETE{color:#ff4444 !important;font-weight:700 !important;}
.action-READ{color:#667eea !important;font-weight:700 !important;}

/* ====== FOOTER ====== */
.report-footer{
    text-align:center;
    margin-top:28px;
    padding:16px 20px 12px;
    border-top:1px solid rgba(102,126,234,0.15);
    font-size:8.5px;
    color:#6a6a80;
    line-height:1.8;
    page-break-inside:avoid;
}
.report-footer p{margin:2px 0;color:#6a6a80;}
.report-footer strong{color:#8899d0;}
</style>
</head>
<body>

<div class="report-header">
    <h1>Activity Report</h1>
    <p class="sub">SITM College &mdash; BCA (AI/ML)</p>
    <div class="meta">
        <strong>Period:</strong> {$periodLabel} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Date:</strong> {$dateFormatted} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Generated:</strong> {$generatedAt}
    </div>
</div>

<div class="summary-section">
    <div class="section-title">Summary Statistics</div>
    <div class="summary-grid">
        <div class="summary-card">
            <span class="label">Total Activities</span>
            <span class="value total">{$total}</span>
        </div>
        <div class="summary-card create">
            <span class="label">Created</span>
            <span class="value create">{$createCount}</span>
        </div>
        <div class="summary-card update">
            <span class="label">Updated</span>
            <span class="value update">{$updateCount}</span>
        </div>
        <div class="summary-card delete">
            <span class="label">Deleted</span>
            <span class="value delete">{$deleteCount}</span>
        </div>
    </div>
</div>

<div class="activities-section">
    <div class="section-title">Activity Details</div>
    <table>
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:17%;">Date &amp; Time</th>
                <th style="width:15%;">User</th>
                <th style="width:15%;">Module</th>
                <th style="width:10%;">Action</th>
                <th style="width:39%;">Description</th>
            </tr>
        </thead>
        <tbody>
            {$rowsHtml}
        </tbody>
    </table>
</div>

<div class="report-footer">
    <p><strong>Developer:</strong> Rohit Kumar Saw &nbsp;|&nbsp; BCA (AI/ML), SITM College</p>
    <p><strong>GitHub:</strong> github.com/Rohitkumarsaw/CMS_BCA_AI_ML</p>
</div>

</body>
</html>
HTML;

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'activity_report_' . $period . '_' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
