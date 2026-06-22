<?php
$pageTitle = 'Activity Reports';
$extraCSS = ['reports.css'];
$extraJS = ['reports.js'];
require_once '../config/config.php';
require_once '../config/db_connection.php';
require_once '../includes/functions.php';
if (!isLoggedIn()) { header("Location: ../login.php"); exit; }

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
if (!$user || $user['role'] !== 'admin') {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Access denied. Admin only.'];
    header("Location: ../dashboard.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1"><i class="fas fa-chart-bar me-2" style="color:#0090e7"></i>Activity Reports</h4>
                <p class="text-muted mb-0">View and export detailed activity logs across all modules</p>
            </div>
        </div>

        <div id="reportStatus"></div>

        <div class="report-card">
            <div class="report-card-body">
                <h5><i class="fas fa-filter me-2" style="color:#00d2ff"></i>Time Period</h5>
                <div class="report-tabs" id="periodTabs">
                    <button class="report-tab active" data-period="daily">Daily</button>
                    <button class="report-tab" data-period="weekly">Weekly</button>
                    <button class="report-tab" data-period="monthly">Monthly</button>
                    <button class="report-tab" data-period="custom">Custom</button>
                </div>
                <div class="report-date-range" id="dateRange" style="display:none">
                    <div class="row g-2">
                        <div class="col-12 col-sm-5">
                            <label class="report-label">Start Date</label>
                            <input type="date" class="report-input" id="startDate">
                        </div>
                        <div class="col-12 col-sm-5">
                            <label class="report-label">End Date</label>
                            <input type="date" class="report-input" id="endDate">
                        </div>
                        <div class="col-12 col-sm-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="applyCustomBtn" style="height:44px">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="report-filters mt-3">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <label class="report-label">Module</label>
                            <select class="report-input" id="moduleFilter">
                                <option value="">All Modules</option>
                                <option value="attendance">Attendance</option>
                                <option value="homework">Homework</option>
                                <option value="schedule">Schedule</option>
                                <option value="exam">Exams</option>
                                <option value="syllabus">Syllabus</option>
                                <option value="notes">Notes</option>
                                <option value="grades">Grades</option>
                                <option value="projects">Projects</option>
                                <option value="internship">Internships</option>
                                <option value="payment">Payments</option>
                                <option value="event">Events</option>
                                <option value="announcement">Announcements</option>
                                <option value="study_plan">Study Plan</option>
                                <option value="skills">Skills</option>
                                <option value="certifications">Certifications</option>
                                <option value="jobs">Jobs</option>
                                <option value="library">Library</option>
                                <option value="resources">Resources</option>
                                <option value="meetings">Meetings</option>
                                <option value="routine">Routine</option>
                                <option value="planner">Planner</option>
                                <option value="circular">Circulars</option>
                                <option value="lab">Labs</option>
                                <option value="presentation">Presentations</option>
                                <option value="assignment">Assignments</option>
                                <option value="group">Groups</option>
                                <option value="leave">Leave</option>
                                <option value="faculty">Faculty</option>
                                <option value="placement">Placement</option>
                                <option value="backup">Backup</option>
                                <option value="profile">Profile</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="generateBtn" style="height:44px">
                                <i class="fas fa-magic me-1"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="reportResults" style="display:none">
            <div class="report-stats" id="statsContainer"></div>

            <div class="report-card mt-3">
                <div class="report-card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h5 class="mb-0"><i class="fas fa-list-alt me-2" style="color:#00d2ff"></i>Activity Details</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-primary btn-sm" id="downloadPdfBtn"><i class="fas fa-file-pdf me-1"></i>Download PDF</button>
                            <button class="btn btn-success btn-sm" id="exportCsvBtn"><i class="fas fa-file-csv me-1"></i>Export CSV</button>
                            <button class="btn btn-info btn-sm" id="emailReportBtn"><i class="fas fa-envelope me-1"></i>Email Report</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>User</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="activityTableBody">
                                <tr><td colspan="5" class="text-center text-muted py-4">No activities found</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="text-muted small" id="showingInfo">Showing 0 entries</span>
                        <nav><ul class="pagination pagination-sm mb-0" id="pagination"></ul></nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var reportData = null;
</script>
<?php require_once '../includes/footer.php'; ?>
