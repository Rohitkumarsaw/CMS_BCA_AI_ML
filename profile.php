<?php
$pageTitle = 'Profile';
$extraCSS = ['style.css', 'profile.css'];
$extraJS = ['main.js', 'profile.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

$profileStmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
$profileStmt->execute([$userId]);
$profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

$attendanceStmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
    FROM attendance WHERE user_id = ?");
$attendanceStmt->execute([$userId]);
$attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);
$attendancePct = ($attendance['total'] > 0) ? round(($attendance['present'] / $attendance['total']) * 100, 1) : 0;

$gradesStmt = $pdo->prepare("SELECT AVG(marks_obtained / total_marks * 100) as avg_grade FROM grades WHERE user_id = ?");
$gradesStmt->execute([$userId]);
$gradesAvg = $gradesStmt->fetch(PDO::FETCH_ASSOC);
$avgGrade = round($gradesAvg['avg_grade'] ?? 0, 1);

$projectsStmt = $pdo->prepare("SELECT COUNT(*) as total FROM projects WHERE user_id = ?");
$projectsStmt->execute([$userId]);
$totalProjects = $projectsStmt->fetch()['total'] ?? 0;

$internshipsStmt = $pdo->prepare("SELECT COUNT(*) as total FROM internships WHERE user_id = ?");
$internshipsStmt->execute([$userId]);
$totalInternships = $internshipsStmt->fetch()['total'] ?? 0;

$displayName = $profile['name'] ?? $user['name'] ?? 'User';
$displayCollege = $profile['college'] ?? $user['college'] ?? '';
$displaySemester = $profile['semester'] ?? $user['semester'] ?? 1;
$displayRollNo = $profile['roll_no'] ?? $user['roll_no'] ?? '';
$displayEmail = $profile['email'] ?? $user['email'] ?? '';
$displayPhone = $profile['phone'] ?? $user['phone'] ?? '';
$displayAddress = $profile['address'] ?? $user['address'] ?? '';
$displayPhoto = $profile['photo_path'] ?? $user['photo_path'] ?? '';

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-user-circle me-2 text-primary"></i>Profile</h4>
                    <p class="text-muted mb-0">View your profile information</p>
                </div>
                <a href="edit_profile.php" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>Edit Profile
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-lg-4 mb-4">
                    <div class="card profile-card h-100">
                        <div class="card-body text-center">
                            <?php if (!empty($displayPhoto)): ?>
                                <img src="<?= htmlspecialchars($displayPhoto) ?>" alt="Profile Photo" class="profile-photo mb-3">
                            <?php else: ?>
                                <div class="profile-photo-placeholder mb-3 mx-auto">
                                    <i class="fas fa-user fa-4x"></i>
                                </div>
                            <?php endif; ?>
                            <h4 class="mb-1"><?= htmlspecialchars($displayName) ?></h4>
                            <p class="text-muted mb-1"><?= htmlspecialchars($displayCollege) ?></p>
                            <span class="badge bg-primary">Semester <?= $displaySemester ?></span>
                            <hr>
                            <div class="text-start">
                                <?php if (!empty($displayRollNo)): ?>
                                    <p class="mb-2"><i class="fas fa-id-card me-2 text-primary"></i><strong>Roll No:</strong> <?= htmlspecialchars($displayRollNo) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($displayEmail)): ?>
                                    <p class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i><strong>Email:</strong> <?= htmlspecialchars($displayEmail) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($displayPhone)): ?>
                                    <p class="mb-2"><i class="fas fa-phone me-2 text-primary"></i><strong>Phone:</strong> <?= htmlspecialchars($displayPhone) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($displayAddress)): ?>
                                    <p class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i><strong>Address:</strong> <?= htmlspecialchars($displayAddress) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent text-center">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>Member since <?= formatDate($user['created_at']) ?>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-primary text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                                    <h3 class="mb-0"><?= $attendancePct ?>%</h3>
                                    <small>Attendance</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-success text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                                    <h3 class="mb-0"><?= $avgGrade ?>%</h3>
                                    <small>Avg Grade</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-info text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-project-diagram fa-2x mb-2"></i>
                                    <h3 class="mb-0"><?= $totalProjects ?></h3>
                                    <small>Projects</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card stat-card bg-warning text-white h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-building fa-2x mb-2"></i>
                                    <h3 class="mb-0"><?= $totalInternships ?></h3>
                                    <small>Internships</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Academic Overview</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Attendance</span>
                                            <span class="fw-bold"><?= $attendancePct ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-primary" style="width: <?= $attendancePct ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Average Grade</span>
                                            <span class="fw-bold"><?= $avgGrade ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar bg-success" style="width: <?= $avgGrade ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Quick Links</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <a href="attendance.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-check-circle me-1"></i>Attendance
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="grades.php" class="btn btn-outline-success w-100">
                                        <i class="fas fa-chart-line me-1"></i>Grades
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="projects.php" class="btn btn-outline-info w-100">
                                        <i class="fas fa-project-diagram me-1"></i>Projects
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="internship.php" class="btn btn-outline-warning w-100">
                                        <i class="fas fa-building me-1"></i>Internships
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="certifications.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-certificate me-1"></i>Certifications
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="skills.php" class="btn btn-outline-dark w-100">
                                        <i class="fas fa-cogs me-1"></i>Skills
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
