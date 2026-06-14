<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="app-sidebar" id="sidebar">
    <div class="app-sidebar-header">
        <h4><i class="fas fa-graduation-cap"></i> <?php echo SITE_NAME; ?></h4>
        <button class="close-sidebar" onclick="toggleSidebar()">&times;</button>
    </div>
    <div class="app-sidebar-nav">
        <a href="dashboard.php" class="<?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="attendance.php" class="<?php echo $currentPage == 'attendance' ? 'active' : ''; ?>"><i class="fas fa-check-circle"></i><span>Attendance</span></a>
        <a href="homework.php" class="<?php echo $currentPage == 'homework' ? 'active' : ''; ?>"><i class="fas fa-book"></i><span>Homework</span></a>
        <a href="schedule.php" class="<?php echo $currentPage == 'schedule' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i><span>Schedule</span></a>
        <a href="exam.php" class="<?php echo $currentPage == 'exam' ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i><span>Exams</span></a>
        <a href="syllabus.php" class="<?php echo $currentPage == 'syllabus' ? 'active' : ''; ?>"><i class="fas fa-list-check"></i><span>Syllabus</span></a>
        <a href="manage_subjects.php" class="<?php echo $currentPage == 'manage_subjects' || $currentPage == 'subjects_handler' ? 'active' : ''; ?>"><i class="fas fa-book"></i><span>Manage Subjects</span></a>
        <a href="notes.php" class="<?php echo $currentPage == 'notes' ? 'active' : ''; ?>"><i class="fas fa-sticky-note"></i><span>Notes</span></a>
        <a href="grades.php" class="<?php echo $currentPage == 'grades' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span>Grades</span></a>
        <a href="projects.php" class="<?php echo $currentPage == 'projects' ? 'active' : ''; ?>"><i class="fas fa-project-diagram"></i><span>Projects</span></a>
        <a href="internship.php" class="<?php echo $currentPage == 'internship' ? 'active' : ''; ?>"><i class="fas fa-building"></i><span>Internships</span></a>
        <a href="payment.php" class="<?php echo $currentPage == 'payment' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i><span>Payments</span></a>
        <a href="holiday.php" class="<?php echo $currentPage == 'holiday' ? 'active' : ''; ?>"><i class="fas fa-umbrella-beach"></i><span>Holidays</span></a>
        <a href="event.php" class="<?php echo $currentPage == 'event' ? 'active' : ''; ?>"><i class="fas fa-calendar-day"></i><span>Events</span></a>
        <a href="announcement.php" class="<?php echo $currentPage == 'announcement' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i><span>Announcements</span></a>
        <a href="study_plan.php" class="<?php echo $currentPage == 'study_plan' ? 'active' : ''; ?>"><i class="fas fa-clock"></i><span>Study Plan</span></a>
        <a href="skills.php" class="<?php echo $currentPage == 'skills' ? 'active' : ''; ?>"><i class="fas fa-cogs"></i><span>Skills</span></a>
        <a href="certifications.php" class="<?php echo $currentPage == 'certifications' ? 'active' : ''; ?>"><i class="fas fa-certificate"></i><span>Certifications</span></a>
        <a href="jobs.php" class="<?php echo $currentPage == 'jobs' ? 'active' : ''; ?>"><i class="fas fa-briefcase"></i><span>Jobs</span></a>
        <a href="library.php" class="<?php echo $currentPage == 'library' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i><span>Library</span></a>
        <a href="resources.php" class="<?php echo $currentPage == 'resources' ? 'active' : ''; ?>"><i class="fas fa-link"></i><span>Resources</span></a>
        <a href="routine.php" class="<?php echo $currentPage == 'routine' ? 'active' : ''; ?>"><i class="fas fa-clock"></i><span>Routine</span></a>
        <div class="sidebar-divider"></div>
        <a href="circular.php" class="<?php echo $currentPage == 'circular' ? 'active' : ''; ?>"><i class="fas fa-scroll"></i><span>Circulars</span></a>
        <a href="lab.php" class="<?php echo $currentPage == 'lab' ? 'active' : ''; ?>"><i class="fas fa-flask"></i><span>Labs</span></a>
        <a href="presentation.php" class="<?php echo $currentPage == 'presentation' ? 'active' : ''; ?>"><i class="fas fa-desktop"></i><span>Presentations</span></a>
        <a href="assignment.php" class="<?php echo $currentPage == 'assignment' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i><span>Assignments</span></a>
        <a href="backup.php" class="<?php echo $currentPage == 'backup' ? 'active' : ''; ?>"><i class="fas fa-database"></i><span>Backup</span></a>
        <a href="exam_prep.php" class="<?php echo $currentPage == 'exam_prep' ? 'active' : ''; ?>"><i class="fas fa-brain"></i><span>Exam Prep</span></a>
        <div class="sidebar-divider"></div>
        <a href="group.php" class="<?php echo $currentPage == 'group' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span>Groups</span></a>
        <a href="profile.php" class="<?php echo $currentPage == 'profile' ? 'active' : ''; ?>"><i class="fas fa-user-circle"></i><span>Profile</span></a>
        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
