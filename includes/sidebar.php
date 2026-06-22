<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteUrl = SITE_URL;
?>
<div class="app-sidebar" id="sidebar">
    <div class="app-sidebar-header">
        <h4><i class="fas fa-graduation-cap"></i> <?php echo SITE_NAME; ?></h4>
        <button class="close-sidebar" onclick="toggleSidebar()">&times;</button>
    </div>
    <div class="app-sidebar-nav">
        <a href="<?php echo $siteUrl; ?>/home.php" style="--nav-color:#667eea" class="<?php echo $currentPage == 'home' ? 'active' : ''; ?>"><i class="fas fa-house"></i><span>Home</span></a>
        <a href="<?php echo $siteUrl; ?>/sections.php" style="--nav-color:#667eea" class="<?php echo $currentPage == 'sections' ? 'active' : ''; ?>"><i class="fas fa-th-large"></i><span>Sections</span></a>
        <a href="<?php echo $siteUrl; ?>/dashboard.php" style="--nav-color:#0090e7" class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="<?php echo $siteUrl; ?>/attendance.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'attendance' ? 'active' : ''; ?>"><i class="fas fa-check-circle"></i><span>Attendance</span></a>
        <a href="<?php echo $siteUrl; ?>/homework.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'homework' ? 'active' : ''; ?>"><i class="fas fa-book"></i><span>Homework</span></a>
        <a href="<?php echo $siteUrl; ?>/schedule.php" style="--nav-color:#00f0ff" class="<?php echo $currentPage == 'schedule' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="<?php echo $siteUrl; ?>/exam.php" style="--nav-color:#00f0ff" class="<?php echo $currentPage == 'exam' ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i><span>Exams</span></a>
        <a href="<?php echo $siteUrl; ?>/syllabus.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'syllabus' ? 'active' : ''; ?>"><i class="fas fa-list-check"></i><span>Syllabus</span></a>
        <a href="<?php echo $siteUrl; ?>/manage_subjects.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'manage_subjects' || $currentPage == 'subjects_handler' ? 'active' : ''; ?>"><i class="fas fa-book"></i><span>Manage Subjects</span></a>
        <a href="<?php echo $siteUrl; ?>/notes.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'notes' ? 'active' : ''; ?>"><i class="fas fa-sticky-note"></i><span>Notes</span></a>
        <a href="<?php echo $siteUrl; ?>/grades.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'grades' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span>Grades</span></a>
        <a href="<?php echo $siteUrl; ?>/projects.php" style="--nav-color:#ff007f" class="<?php echo $currentPage == 'projects' ? 'active' : ''; ?>"><i class="fas fa-project-diagram"></i><span>Projects</span></a>
        <a href="<?php echo $siteUrl; ?>/internship.php" style="--nav-color:#ff007f" class="<?php echo $currentPage == 'internship' ? 'active' : ''; ?>"><i class="fas fa-building"></i><span>Internships</span></a>
        <a href="<?php echo $siteUrl; ?>/payment.php" style="--nav-color:#ff5e00" class="<?php echo $currentPage == 'payment' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i><span>Payments</span></a>
        <a href="<?php echo $siteUrl; ?>/holiday.php" style="--nav-color:#ff5e00" class="<?php echo $currentPage == 'holiday' ? 'active' : ''; ?>"><i class="fas fa-umbrella-beach"></i><span>Holidays</span></a>
        <a href="<?php echo $siteUrl; ?>/event.php" style="--nav-color:#fc424a" class="<?php echo $currentPage == 'event' ? 'active' : ''; ?>"><i class="fas fa-calendar-day"></i><span>Events</span></a>
        <a href="<?php echo $siteUrl; ?>/announcement.php" style="--nav-color:#fc424a" class="<?php echo $currentPage == 'announcement' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i><span>Announcements</span></a>
        <a href="<?php echo $siteUrl; ?>/study_plan.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'study_plan' ? 'active' : ''; ?>"><i class="fas fa-clock"></i><span>Study Plan</span></a>
        <a href="<?php echo $siteUrl; ?>/skills.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'skills' ? 'active' : ''; ?>"><i class="fas fa-cogs"></i><span>Skills</span></a>
        <a href="<?php echo $siteUrl; ?>/certifications.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'certifications' ? 'active' : ''; ?>"><i class="fas fa-certificate"></i><span>Certifications</span></a>
        <a href="<?php echo $siteUrl; ?>/achievements.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'achievements' ? 'active' : ''; ?>"><i class="fas fa-trophy"></i><span>Achievements</span></a>
        <a href="<?php echo $siteUrl; ?>/jobs.php" style="--nav-color:#ff007f" class="<?php echo $currentPage == 'jobs' ? 'active' : ''; ?>"><i class="fas fa-briefcase"></i><span>Jobs</span></a>
        <a href="<?php echo $siteUrl; ?>/placement.php" style="--nav-color:#0090e7" class="<?php echo $currentPage == 'placement' ? 'active' : ''; ?>"><i class="fas fa-briefcase"></i><span>Placement</span></a>
        <a href="<?php echo $siteUrl; ?>/library.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'library' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i><span>Library</span></a>
        <a href="<?php echo $siteUrl; ?>/resources.php" style="--nav-color:#0090e7" class="<?php echo $currentPage == 'resources' ? 'active' : ''; ?>"><i class="fas fa-link"></i><span>Resources</span></a>
        <a href="<?php echo $siteUrl; ?>/meetings.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'meetings' ? 'active' : ''; ?>"><i class="fas fa-video"></i><span>Meetings</span></a>
        <a href="<?php echo $siteUrl; ?>/routine.php" style="--nav-color:#00f0ff" class="<?php echo $currentPage == 'routine' ? 'active' : ''; ?>"><i class="fas fa-clock"></i><span>Routine</span></a>
        <a href="<?php echo $siteUrl; ?>/planner.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'planner' ? 'active' : ''; ?>"><i class="fas fa-clipboard-list"></i><span>Planner</span></a>
        <div class="sidebar-divider"></div>
        <a href="<?php echo $siteUrl; ?>/roadmap.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'roadmap' ? 'active' : ''; ?>"><i class="fas fa-road"></i><span>Roadmap</span></a>
        <a href="<?php echo $siteUrl; ?>/history.php" style="--nav-color:#0090e7" class="<?php echo $currentPage == 'history' ? 'active' : ''; ?>"><i class="fas fa-history"></i><span>History</span></a>
        <a href="<?php echo $siteUrl; ?>/reports/reports.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'reports' ? 'active' : ''; ?>"><i class="fas fa-chart-bar"></i><span>Reports</span></a>
        <a href="<?php echo $siteUrl; ?>/circular.php" style="--nav-color:#fc424a" class="<?php echo $currentPage == 'circular' ? 'active' : ''; ?>"><i class="fas fa-scroll"></i><span>Circulars</span></a>
        <a href="<?php echo $siteUrl; ?>/lab.php" style="--nav-color:#ff5e00" class="<?php echo $currentPage == 'lab' ? 'active' : ''; ?>"><i class="fas fa-flask"></i><span>Labs</span></a>
        <a href="<?php echo $siteUrl; ?>/presentation.php" style="--nav-color:#ff007f" class="<?php echo $currentPage == 'presentation' ? 'active' : ''; ?>"><i class="fas fa-desktop"></i><span>Presentations</span></a>
        <a href="<?php echo $siteUrl; ?>/assignment.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'assignment' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="<?php echo $siteUrl; ?>/about_settings.php" style="--nav-color:#a3a6b7" class="<?php echo $currentPage == 'about_settings' ? 'active' : ''; ?>"><i class="fas fa-info-circle"></i><span>About Settings</span></a>
        <a href="<?php echo $siteUrl; ?>/backup.php" style="--nav-color:#ff5e00" class="<?php echo $currentPage == 'backup' ? 'active' : ''; ?>"><i class="fas fa-database"></i><span>Backup</span></a>
        <a href="<?php echo $siteUrl; ?>/exam_prep.php" style="--nav-color:#00f0ff" class="<?php echo $currentPage == 'exam_prep' ? 'active' : ''; ?>"><i class="fas fa-brain"></i><span>Exam Prep</span></a>
        <div class="sidebar-divider"></div>
        <a href="<?php echo $siteUrl; ?>/group.php" style="--nav-color:#0090e7" class="<?php echo $currentPage == 'group' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span>Groups</span></a>
        <a href="<?php echo $siteUrl; ?>/leave.php" style="--nav-color:#ffab00" class="<?php echo $currentPage == 'leave' ? 'active' : ''; ?>"><i class="fas fa-file-signature"></i><span>Leave</span></a>
        <a href="<?php echo $siteUrl; ?>/faculty.php" style="--nav-color:#00d25b" class="<?php echo $currentPage == 'faculty' ? 'active' : ''; ?>"><i class="fas fa-chalkboard-teacher"></i><span>Faculty</span></a>
        <a href="<?php echo $siteUrl; ?>/profile.php" style="--nav-color:#8a2be2" class="<?php echo $currentPage == 'profile' ? 'active' : ''; ?>"><i class="fas fa-user-circle"></i><span>Profile</span></a>
        <a href="<?php echo $siteUrl; ?>/smtp_settings.php" style="--nav-color:#ff6b6b" class="<?php echo $currentPage == 'smtp_settings' ? 'active' : ''; ?>"><i class="fas fa-cogs"></i><span>SMTP Settings</span></a>
        <a href="<?php echo $siteUrl; ?>/logout.php" style="--nav-color:#fc424a" class="text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
