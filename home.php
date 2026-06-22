<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Fetch stats
$total_modules = 0;
$module_tables = ['attendance','homework','schedule','exams','grades','projects','notes','internship','skills','certifications','achievements','library','resources','meetings'];
foreach ($module_tables as $tbl) {
    try { $total_modules += (int)$pdo->query("SELECT COUNT(*) FROM $tbl WHERE user_id = $user_id")->fetchColumn(); } catch (Exception $e) {}
}

$activity_count = 0;
try { $activity_count = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE user_id = $user_id")->fetchColumn(); } catch (Exception $e) {}

$report_count = 0;
try { $report_count = (int)$pdo->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(logged_at) = CURDATE() AND user_id = $user_id")->fetchColumn(); } catch (Exception $e) {}

$recent_activities = [];
try {
    $stmt = $pdo->prepare("SELECT action_type, section_name, details, logged_at FROM activity_logs WHERE user_id = ? ORDER BY logged_at DESC LIMIT 6");
    $stmt->execute([$user_id]);
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

$pageTitle = 'Home';
$extraCSS = [];
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>

<style>
/* ===== HOME PAGE STYLES ===== */

/* Hero */
.hero-section {
    position: relative;
    overflow: hidden;
    padding: 40px 0 50px;
    margin-bottom: 24px;
}
.hero-bg-glow {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(102,126,234,0.12) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.hero-bg-glow2 {
    position: absolute;
    bottom: -60px;
    left: -60px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(118,75,162,0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(102,126,234,0.12);
    border: 1px solid rgba(102,126,234,0.25);
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    color: #667eea;
    margin-bottom: 16px;
}
.hero-badge i { font-size: 10px; }
.hero-title {
    font-size: 36px;
    font-weight: 800;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 10px;
}
.hero-title span {
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero-sub {
    font-size: 15px;
    color: #8a8aa0;
    max-width: 600px;
    margin-bottom: 24px;
    line-height: 1.6;
}
.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.btn-hero {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}
.btn-hero-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    box-shadow: 0 4px 20px rgba(102,126,234,0.3);
}
.btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 28px rgba(102,126,234,0.45);
    color: #fff;
}
.btn-hero-secondary {
    background: rgba(255,255,255,0.06);
    color: #e0e0e0;
    border: 1px solid rgba(255,255,255,0.1);
}
.btn-hero-secondary:hover {
    background: rgba(255,255,255,0.1);
    transform: translateY(-2px);
    color: #fff;
}

/* Section titles */
.section-heading {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-heading i {
    color: #667eea;
    font-size: 18px;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.stat-card-home {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(102,126,234,0.15);
    border-radius: 10px;
    padding: 18px 16px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: default;
}
.stat-card-home:hover {
    transform: translateY(-4px);
    border-color: rgba(102,126,234,0.35);
    box-shadow: 0 8px 30px rgba(102,126,234,0.1);
}
.stat-card-home .stat-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 18px;
}
.stat-card-home .stat-num {
    font-size: 26px;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 4px;
}
.stat-card-home .stat-label {
    font-size: 11px;
    color: #8a8aa0;
    font-weight: 500;
}

/* Features */
.features-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}
.feature-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(102,126,234,0.12);
    border-radius: 10px;
    padding: 18px 14px;
    text-align: center;
    transition: all 0.35s ease;
    cursor: default;
    position: relative;
    overflow: hidden;
}
.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2.5px;
    background: linear-gradient(90deg, transparent, var(--fc-color, #667eea), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.feature-card:hover::before { opacity: 1; }
.feature-card:hover {
    transform: translateY(-5px);
    border-color: rgba(102,126,234,0.3);
    box-shadow: 0 10px 35px rgba(102,126,234,0.08);
}
.feature-card .fc-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 20px;
    background: rgba(102,126,234,0.08);
    color: var(--fc-color, #667eea);
    transition: all 0.3s ease;
}
.feature-card:hover .fc-icon {
    background: rgba(102,126,234,0.15);
    transform: scale(1.08);
}
.feature-card .fc-title {
    font-size: 13px;
    font-weight: 600;
    color: #e0e0e0;
    margin-bottom: 4px;
}
.feature-card .fc-desc {
    font-size: 10.5px;
    color: #7a7a90;
    line-height: 1.5;
}

/* Welcome panel */
.welcome-panel {
    background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.04));
    border: 1px solid rgba(102,126,234,0.15);
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.welcome-panel-left { flex: 1; min-width: 200px; }
.welcome-panel-left h3 {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
}
.welcome-panel-left h3 i { color: #667eea; }
.welcome-panel-left p {
    color: #8a8aa0;
    font-size: 13px;
    margin: 0;
    line-height: 1.5;
}
.welcome-panel-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Activity timeline */
.activity-section { margin-bottom: 28px; }
.activity-list {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(102,126,234,0.12);
    border-radius: 10px;
    overflow: hidden;
}
.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid rgba(102,126,234,0.06);
    transition: background 0.2s ease;
}
.activity-item:last-child { border-bottom: none; }
.activity-item:hover { background: rgba(255,255,255,0.02); }
.activity-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-top: 6px;
    flex-shrink: 0;
}
.activity-content { flex: 1; }
.activity-content .ac-title {
    font-size: 12px;
    color: #e0e0e0;
    font-weight: 600;
    margin-bottom: 2px;
}
.activity-content .ac-title strong { color: #667eea; }
.activity-content .ac-meta {
    font-size: 10px;
    color: #6a6a80;
}
.activity-empty {
    text-align: center;
    padding: 30px;
    color: #6a6a80;
    font-size: 13px;
}

/* CTA section */
.cta-section {
    background: linear-gradient(135deg, rgba(102,126,234,0.08), rgba(118,75,162,0.05));
    border: 1px solid rgba(102,126,234,0.15);
    border-radius: 12px;
    padding: 30px 28px;
    text-align: center;
    margin-bottom: 20px;
}
.cta-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
}
.cta-section p {
    color: #8a8aa0;
    font-size: 13px;
    margin-bottom: 18px;
}
.cta-actions {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

/* Responsive */
@media (max-width: 991px) {
    .hero-title { font-size: 28px; }
    .stats-grid, .features-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 575px) {
    .hero-title { font-size: 24px; }
    .hero-section { padding: 20px 0 30px; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .features-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .stat-card-home { padding: 14px 10px; }
    .stat-card-home .stat-num { font-size: 22px; }
    .feature-card { padding: 14px 10px; }
    .welcome-panel { padding: 18px 16px; }
    .welcome-panel-left h3 { font-size: 17px; }
    .cta-section { padding: 22px 16px; }
    .hero-actions { flex-direction: column; }
    .hero-actions .btn-hero { width: 100%; justify-content: center; }
}
</style>

<div class="app-content">
    <div class="container-fluid">

        <!-- ===== HERO ===== -->
        <div class="hero-section">
            <div class="hero-bg-glow"></div>
            <div class="hero-bg-glow2"></div>
            <div class="hero-badge"><i class="fas fa-star"></i> Welcome to your CMS</div>
            <h1 class="hero-title">Hey, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!<br>Welcome to <span>CMS (BCA AI/ML)</span></h1>
            <p class="hero-sub">Your all-in-one academic management platform. Track attendance, manage homework, monitor progress, and stay on top of your coursework — all from one place.</p>
            <div class="hero-actions">
                <a href="dashboard.php" class="btn-hero btn-hero-primary"><i class="fas fa-tachometer-alt"></i> Open Dashboard</a>
                <a href="reports/reports.php" class="btn-hero btn-hero-secondary"><i class="fas fa-chart-bar"></i> View Reports</a>
                <a href="attendance.php" class="btn-hero btn-hero-secondary"><i class="fas fa-check-circle"></i> Attendance</a>
            </div>
        </div>

        <!-- ===== STATS ===== -->
        <div class="section-heading"><i class="fas fa-chart-pie"></i> Platform Overview</div>
        <div class="stats-grid">
            <div class="stat-card-home">
                <div class="stat-icon-wrap" style="background:rgba(102,126,234,0.1);color:#667eea;"><i class="fas fa-cubes"></i></div>
                <div class="stat-num"><?= $total_modules ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card-home">
                <div class="stat-icon-wrap" style="background:rgba(0,255,136,0.1);color:#00ff88;"><i class="fas fa-history"></i></div>
                <div class="stat-num"><?= $activity_count ?></div>
                <div class="stat-label">Activities Logged</div>
            </div>
            <div class="stat-card-home">
                <div class="stat-icon-wrap" style="background:rgba(255,107,107,0.1);color:#ff6b6b;"><i class="fas fa-file-pdf"></i></div>
                <div class="stat-num"><?= $report_count ?></div>
                <div class="stat-label">Today's Records</div>
            </div>
            <div class="stat-card-home">
                <div class="stat-icon-wrap" style="background:rgba(118,75,162,0.1);color:#764ba2;"><i class="fas fa-shield-halved"></i></div>
                <div class="stat-num">100%</div>
                <div class="stat-label">Secure Access</div>
            </div>
        </div>

        <!-- ===== FEATURES ===== -->
        <div class="section-heading"><i class="fas fa-th-large"></i> Academic Modules</div>
        <div class="features-grid">
            <div class="feature-card" style="--fc-color:#00d25b;"><div class="fc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-check-circle"></i></div><div class="fc-title">Attendance</div><div class="fc-desc">Track daily presence</div></div>
            <div class="feature-card" style="--fc-color:#00d25b;"><div class="fc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-book"></i></div><div class="fc-title">Homework</div><div class="fc-desc">Manage assignments</div></div>
            <div class="feature-card" style="--fc-color:#00f0ff;"><div class="fc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-calendar-alt"></i></div><div class="fc-title">Schedule</div><div class="fc-desc">Class timetable</div></div>
            <div class="feature-card" style="--fc-color:#00f0ff;"><div class="fc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-file-alt"></i></div><div class="fc-title">Exams</div><div class="fc-desc">Exam management</div></div>
            <div class="feature-card" style="--fc-color:#ffab00;"><div class="fc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-chart-line"></i></div><div class="fc-title">Grades</div><div class="fc-desc">Track performance</div></div>
            <div class="feature-card" style="--fc-color:#ffab00;"><div class="fc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-sticky-note"></i></div><div class="fc-title">Notes</div><div class="fc-desc">Study notes</div></div>
            <div class="feature-card" style="--fc-color:#ff007f;"><div class="fc-icon" style="background:rgba(255,0,127,0.1);color:#ff007f;"><i class="fas fa-project-diagram"></i></div><div class="fc-title">Projects</div><div class="fc-desc">Project showcase</div></div>
            <div class="feature-card" style="--fc-color:#8a2be2;"><div class="fc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-chart-bar"></i></div><div class="fc-title">Reports</div><div class="fc-desc">Analytics &amp; PDF</div></div>
        </div>

        <!-- ===== WELCOME PANEL ===== -->
        <div class="welcome-panel">
            <div class="welcome-panel-left">
                <h3><i class="fas fa-smile"></i> Welcome back, <?= htmlspecialchars($user_name) ?>!</h3>
                <p>You're logged in as <strong style="color:#667eea;"><?= htmlspecialchars(ucfirst($user_role)) ?></strong>. Manage your academic journey efficiently with real-time insights, automated tracking, and downloadable reports.</p>
            </div>
            <div class="welcome-panel-right">
                <a href="profile.php" class="btn-hero btn-hero-secondary" style="font-size:12px;padding:8px 16px;"><i class="fas fa-user"></i> Profile</a>
                <a href="history.php" class="btn-hero btn-hero-secondary" style="font-size:12px;padding:8px 16px;"><i class="fas fa-history"></i> History</a>
            </div>
        </div>

        <!-- ===== RECENT ACTIVITY ===== -->
        <div class="activity-section">
            <div class="section-heading"><i class="fas fa-history"></i> Recent Activity</div>
            <div class="activity-list">
                <?php if (count($recent_activities) > 0): ?>
                    <?php foreach ($recent_activities as $act):
                        $dotColor = '#667eea';
                        if (preg_match('/^(Added|Created|Submitted|Approved|Applied)/i', $act['action_type'])) $dotColor = '#00ff88';
                        elseif (preg_match('/^(Updated|Edited|Changed|Modified)/i', $act['action_type'])) $dotColor = '#ff6b6b';
                        elseif (preg_match('/^(Deleted|Removed|Cancelled|Rejected)/i', $act['action_type'])) $dotColor = '#ff4444';
                    ?>
                    <div class="activity-item">
                        <div class="activity-dot" style="background:<?= $dotColor ?>;"></div>
                        <div class="activity-content">
                            <div class="ac-title"><strong><?= htmlspecialchars($act['action_type']) ?></strong> in <?= htmlspecialchars(ucfirst($act['section_name'])) ?></div>
                            <div class="ac-meta"><?= date('d M Y, h:i A', strtotime($act['logged_at'])) ?><?= !empty($act['details']) ? ' &mdash; ' . htmlspecialchars($act['details']) : '' ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="activity-empty"><i class="fas fa-inbox me-2"></i>No recent activity found.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== CTA ===== -->
        <div class="cta-section">
            <h3><i class="fas fa-rocket"></i> Ready to explore?</h3>
            <p>Access any module from the sidebar or use the quick links below to jump right in.</p>
            <div class="cta-actions">
                <a href="schedule.php" class="btn-hero btn-hero-secondary"><i class="fas fa-calendar-alt"></i> View Schedule</a>
                <a href="exam.php" class="btn-hero btn-hero-secondary"><i class="fas fa-file-alt"></i> Upcoming Exams</a>
                <a href="homework.php" class="btn-hero btn-hero-secondary"><i class="fas fa-book"></i> Homework</a>
                <a href="grades.php" class="btn-hero btn-hero-secondary"><i class="fas fa-chart-line"></i> My Grades</a>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
