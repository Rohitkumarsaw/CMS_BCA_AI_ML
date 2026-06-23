<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Sections';
include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>

<style>
/* Sections Hub */
.sections-hero {
    text-align: center;
    padding: 30px 0 10px;
    margin-bottom: 20px;
}
.sections-hero h1 {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 6px;
}
.sections-hero h1 i { color: #667eea; }
.sections-hero p {
    color: #8a8aa0;
    font-size: 14px;
    max-width: 500px;
    margin: 0 auto;
}

.sections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
    padding-bottom: 30px;
}

.section-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 14px 16px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(102,126,234,0.12);
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.section-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, transparent, var(--sc-color, #667eea), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.section-card:hover::before { opacity: 1; }
.section-card:hover {
    transform: translateY(-4px);
    border-color: rgba(102,126,234,0.3);
    box-shadow: 0 8px 30px rgba(102,126,234,0.08);
    background: rgba(255,255,255,0.05);
}
.section-card .sc-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    background: rgba(102,126,234,0.08);
    color: var(--sc-color, #667eea);
    transition: all 0.3s ease;
}
.section-card:hover .sc-icon {
    background: rgba(102,126,234,0.14);
    transform: scale(1.06);
}
.section-card .sc-name {
    font-size: 13px;
    font-weight: 600;
    color: #e0e0e0;
    text-align: center;
    line-height: 1.3;
}
.section-card .sc-count {
    font-size: 10px;
    color: #6a6a80;
}

/* Search bar */
.sections-search {
    position: relative;
    max-width: 400px;
    margin: 0 auto 22px;
}
.sections-search input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border-radius: 10px;
    border: 1px solid rgba(102,126,234,0.15);
    background: rgba(255,255,255,0.04);
    color: #e0e0e0;
    font-size: 13px;
    outline: none;
    transition: border-color 0.3s ease;
}
.sections-search input:focus {
    border-color: rgba(102,126,234,0.4);
    background: rgba(255,255,255,0.06);
}
.sections-search input::placeholder { color: #6a6a80; }
.sections-search i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #6a6a80;
    font-size: 14px;
}

@media (max-width: 767px) {
    .sections-grid { grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .sections-hero h1 { font-size: 22px; }
    .section-card { padding: 14px 10px; }
    .section-card .sc-icon { width: 40px; height: 40px; font-size: 16px; }
    .section-card .sc-name { font-size: 11px; }
}
@media (max-width: 480px) {
    .sections-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="app-content">
    <div class="container-fluid">

        <div class="sections-hero">
            <h1><i class="fas fa-th-large"></i> All Sections
                <span class="sidebar-toggle-wrap">
                    <span class="toggle-label">Sidebar</span>
                    <label class="switch-toggle">
                        <input type="checkbox" id="sidebarToggle">
                        <span class="slider"></span>
                    </label>
                </span>
            </h1>
            <p>Browse all academic modules and tools at a glance</p>
        </div>

        <div class="sections-search">
            <i class="fas fa-search"></i>
            <input type="text" id="sectionSearch" placeholder="Search sections..." oninput="filterSections(this.value)">
        </div>

        <div class="sections-grid" id="sectionsGrid">

            <a href="dashboard.php" class="section-card" style="--sc-color:#0090e7;">
                <div class="sc-icon" style="background:rgba(0,144,231,0.1);color:#0090e7;"><i class="fas fa-tachometer-alt"></i></div>
                <span class="sc-name">Dashboard</span>
                <span class="sc-count">Overview &amp; Stats</span>
            </a>

            <a href="attendance.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-check-circle"></i></div>
                <span class="sc-name">Attendance</span>
                <span class="sc-count">Track presence</span>
            </a>

            <a href="homework.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-book"></i></div>
                <span class="sc-name">Homework</span>
                <span class="sc-count">Assignments</span>
            </a>

            <a href="schedule.php" class="section-card" style="--sc-color:#00f0ff;">
                <div class="sc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-calendar-alt"></i></div>
                <span class="sc-name">Schedule</span>
                <span class="sc-count">Class timetable</span>
            </a>

            <a href="exam.php" class="section-card" style="--sc-color:#00f0ff;">
                <div class="sc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-file-alt"></i></div>
                <span class="sc-name">Exams</span>
                <span class="sc-count">Exam management</span>
            </a>

            <a href="syllabus.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-list-check"></i></div>
                <span class="sc-name">Syllabus</span>
                <span class="sc-count">Course syllabus</span>
            </a>

            <a href="manage_subjects.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-book-open"></i></div>
                <span class="sc-name">Manage Subjects</span>
                <span class="sc-count">Subject management</span>
            </a>

            <a href="notes.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-sticky-note"></i></div>
                <span class="sc-name">Notes</span>
                <span class="sc-count">Study notes</span>
            </a>

            <a href="grades.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-chart-line"></i></div>
                <span class="sc-name">Grades</span>
                <span class="sc-count">Academic performance</span>
            </a>

            <a href="projects.php" class="section-card" style="--sc-color:#ff007f;">
                <div class="sc-icon" style="background:rgba(255,0,127,0.1);color:#ff007f;"><i class="fas fa-project-diagram"></i></div>
                <span class="sc-name">Projects</span>
                <span class="sc-count">Project showcase</span>
            </a>

            <a href="internship.php" class="section-card" style="--sc-color:#ff007f;">
                <div class="sc-icon" style="background:rgba(255,0,127,0.1);color:#ff007f;"><i class="fas fa-building"></i></div>
                <span class="sc-name">Internships</span>
                <span class="sc-count">Internship records</span>
            </a>

            <a href="payment.php" class="section-card" style="--sc-color:#ff5e00;">
                <div class="sc-icon" style="background:rgba(255,94,0,0.1);color:#ff5e00;"><i class="fas fa-credit-card"></i></div>
                <span class="sc-name">Payments</span>
                <span class="sc-count">Fee management</span>
            </a>

            <a href="holiday.php" class="section-card" style="--sc-color:#ff5e00;">
                <div class="sc-icon" style="background:rgba(255,94,0,0.1);color:#ff5e00;"><i class="fas fa-umbrella-beach"></i></div>
                <span class="sc-name">Holidays</span>
                <span class="sc-count">Holiday calendar</span>
            </a>

            <a href="event.php" class="section-card" style="--sc-color:#fc424a;">
                <div class="sc-icon" style="background:rgba(252,66,74,0.1);color:#fc424a;"><i class="fas fa-calendar-day"></i></div>
                <span class="sc-name">Events</span>
                <span class="sc-count">College events</span>
            </a>

            <a href="announcement.php" class="section-card" style="--sc-color:#fc424a;">
                <div class="sc-icon" style="background:rgba(252,66,74,0.1);color:#fc424a;"><i class="fas fa-bullhorn"></i></div>
                <span class="sc-name">Announcements</span>
                <span class="sc-count">Important notices</span>
            </a>

            <a href="study_plan.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-clock"></i></div>
                <span class="sc-name">Study Plan</span>
                <span class="sc-count">Study schedule</span>
            </a>

            <a href="skills.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-cogs"></i></div>
                <span class="sc-name">Skills</span>
                <span class="sc-count">Skill tracking</span>
            </a>

            <a href="certifications.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-certificate"></i></div>
                <span class="sc-name">Certifications</span>
                <span class="sc-count">Certificates</span>
            </a>

            <a href="achievements.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-trophy"></i></div>
                <span class="sc-name">Achievements</span>
                <span class="sc-count">Awards &amp; honors</span>
            </a>

            <a href="jobs.php" class="section-card" style="--sc-color:#ff007f;">
                <div class="sc-icon" style="background:rgba(255,0,127,0.1);color:#ff007f;"><i class="fas fa-briefcase"></i></div>
                <span class="sc-name">Jobs</span>
                <span class="sc-count">Job listings</span>
            </a>

            <a href="placement.php" class="section-card" style="--sc-color:#0090e7;">
                <div class="sc-icon" style="background:rgba(0,144,231,0.1);color:#0090e7;"><i class="fas fa-briefcase"></i></div>
                <span class="sc-name">Placement</span>
                <span class="sc-count">Placement records</span>
            </a>

            <a href="library.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-book-open"></i></div>
                <span class="sc-name">Library</span>
                <span class="sc-count">Library resources</span>
            </a>

            <a href="resources.php" class="section-card" style="--sc-color:#0090e7;">
                <div class="sc-icon" style="background:rgba(0,144,231,0.1);color:#0090e7;"><i class="fas fa-link"></i></div>
                <span class="sc-name">Resources</span>
                <span class="sc-count">Learning resources</span>
            </a>

            <a href="meetings.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-video"></i></div>
                <span class="sc-name">Meetings</span>
                <span class="sc-count">Meeting links</span>
            </a>

            <a href="routine.php" class="section-card" style="--sc-color:#00f0ff;">
                <div class="sc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-clock"></i></div>
                <span class="sc-name">Routine</span>
                <span class="sc-count">Daily routine</span>
            </a>

            <a href="planner.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-clipboard-list"></i></div>
                <span class="sc-name">Planner</span>
                <span class="sc-count">Task planner</span>
            </a>

            <a href="roadmap.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-road"></i></div>
                <span class="sc-name">Roadmap</span>
                <span class="sc-count">Career roadmap</span>
            </a>

            <a href="history.php" class="section-card" style="--sc-color:#0090e7;">
                <div class="sc-icon" style="background:rgba(0,144,231,0.1);color:#0090e7;"><i class="fas fa-history"></i></div>
                <span class="sc-name">History</span>
                <span class="sc-count">Activity log</span>
            </a>

            <a href="reports/reports.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-chart-bar"></i></div>
                <span class="sc-name">Reports</span>
                <span class="sc-count">Analytics &amp; PDF</span>
            </a>

            <a href="circular.php" class="section-card" style="--sc-color:#fc424a;">
                <div class="sc-icon" style="background:rgba(252,66,74,0.1);color:#fc424a;"><i class="fas fa-scroll"></i></div>
                <span class="sc-name">Circulars</span>
                <span class="sc-count">Official circulars</span>
            </a>

            <a href="lab.php" class="section-card" style="--sc-color:#ff5e00;">
                <div class="sc-icon" style="background:rgba(255,94,0,0.1);color:#ff5e00;"><i class="fas fa-flask"></i></div>
                <span class="sc-name">Labs</span>
                <span class="sc-count">Lab work</span>
            </a>

            <a href="presentation.php" class="section-card" style="--sc-color:#ff007f;">
                <div class="sc-icon" style="background:rgba(255,0,127,0.1);color:#ff007f;"><i class="fas fa-desktop"></i></div>
                <span class="sc-name">Presentations</span>
                <span class="sc-count">Presentation slides</span>
            </a>

            <a href="assignment.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-tasks"></i></div>
                <span class="sc-name">Assignments</span>
                <span class="sc-count">Assignment tracking</span>
            </a>

            <a href="exam_prep.php" class="section-card" style="--sc-color:#00f0ff;">
                <div class="sc-icon" style="background:rgba(0,240,255,0.1);color:#00f0ff;"><i class="fas fa-brain"></i></div>
                <span class="sc-name">Exam Prep</span>
                <span class="sc-count">Exam preparation</span>
            </a>

            <a href="group.php" class="section-card" style="--sc-color:#0090e7;">
                <div class="sc-icon" style="background:rgba(0,144,231,0.1);color:#0090e7;"><i class="fas fa-users"></i></div>
                <span class="sc-name">Groups</span>
                <span class="sc-count">Student groups</span>
            </a>

            <a href="leave.php" class="section-card" style="--sc-color:#ffab00;">
                <div class="sc-icon" style="background:rgba(255,171,0,0.1);color:#ffab00;"><i class="fas fa-file-signature"></i></div>
                <span class="sc-name">Leave</span>
                <span class="sc-count">Leave applications</span>
            </a>

            <a href="faculty.php" class="section-card" style="--sc-color:#00d25b;">
                <div class="sc-icon" style="background:rgba(0,210,91,0.1);color:#00d25b;"><i class="fas fa-chalkboard-teacher"></i></div>
                <span class="sc-name">Faculty</span>
                <span class="sc-count">Faculty management</span>
            </a>

            <a href="about_settings.php" class="section-card" style="--sc-color:#a3a6b7;">
                <div class="sc-icon" style="background:rgba(163,166,183,0.1);color:#a3a6b7;"><i class="fas fa-info-circle"></i></div>
                <span class="sc-name">About Settings</span>
                <span class="sc-count">Site information</span>
            </a>

            <a href="backup.php" class="section-card" style="--sc-color:#ff5e00;">
                <div class="sc-icon" style="background:rgba(255,94,0,0.1);color:#ff5e00;"><i class="fas fa-database"></i></div>
                <span class="sc-name">Backup</span>
                <span class="sc-count">Data backup</span>
            </a>

            <a href="profile.php" class="section-card" style="--sc-color:#8a2be2;">
                <div class="sc-icon" style="background:rgba(138,43,226,0.1);color:#8a2be2;"><i class="fas fa-user-circle"></i></div>
                <span class="sc-name">Profile</span>
                <span class="sc-count">User profile</span>
            </a>

            <a href="ga_setup.php" class="section-card" style="--sc-color:#00d2ff;">
                <div class="sc-icon" style="background:rgba(0,210,255,0.1);color:#00d2ff;"><i class="fas fa-shield-halved"></i></div>
                <span class="sc-name">2FA (Google Auth)</span>
                <span class="sc-count">Two-factor authentication</span>
            </a>

            <a href="smtp_settings.php" class="section-card" style="--sc-color:#ff6b6b;">
                <div class="sc-icon" style="background:rgba(255,107,107,0.1);color:#ff6b6b;"><i class="fas fa-cogs"></i></div>
                <span class="sc-name">SMTP Settings</span>
                <span class="sc-count">Mail configuration</span>
            </a>

            <a href="home.php" class="section-card" style="--sc-color:#667eea;">
                <div class="sc-icon" style="background:rgba(102,126,234,0.1);color:#667eea;"><i class="fas fa-house"></i></div>
                <span class="sc-name">Home</span>
                <span class="sc-count">Welcome page</span>
            </a>

        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
(function() {
    var toggle = document.getElementById('sidebarToggle');
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        toggle.checked = false;
    } else {
        toggle.checked = true;
    }
    toggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.remove('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                document.body.classList.add('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        });
})();

function filterSections(val) {
    var q = val.toLowerCase().trim();
    var cards = document.querySelectorAll('.section-card');
    var visible = 0;
    for (var i = 0; i < cards.length; i++) {
        var txt = cards[i].querySelector('.sc-name').textContent.toLowerCase();
        if (txt.indexOf(q) !== -1) {
            cards[i].style.display = '';
            visible++;
        } else {
            cards[i].style.display = 'none';
        }
    }
    var empty = document.getElementById('searchEmpty');
    if (visible === 0) {
        if (!empty) {
            empty = document.createElement('div');
            empty.id = 'searchEmpty';
            empty.style.cssText = 'grid-column:1/-1;text-align:center;padding:40px;color:#6a6a80;font-size:14px;';
            empty.innerHTML = '<i class="fas fa-search me-2"></i>No sections match "' + val + '"';
            document.getElementById('sectionsGrid').appendChild(empty);
        }
    } else if (empty) {
        empty.remove();
    }
}
</script>
