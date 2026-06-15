<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Class Schedule';
$extraCSS = ['style.css', 'schedule.css'];
$extraJS = ['main.js', 'schedule.js'];

$selectedSemester = $_GET['semester'] ?? $_SESSION['user_semester'] ?? 1;
$selectedDay = $_GET['day'] ?? date('l');

$daysOfWeek = getDaysOfWeek();

$where = "WHERE semester = ?";
$params = [$selectedSemester];
$pagi = paginate($pdo, 'schedule', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM schedule $where ORDER BY FIELD(day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'), start_time" . $pagi['sql']);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

$todayName = date('l');
$todaySchedule = array_filter($schedules, function($s) use ($todayName) {
    return $s['day'] === $todayName;
});

$currentTime = date('H:i:s');

function isCurrentClass($startTime, $endTime, $currentTime) {
    return $currentTime >= $startTime && $currentTime <= $endTime;
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-alt me-2"></i>Class Schedule</h2>
            <a href="add_schedule.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Schedule
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-calendar-day me-2"></i>Today's Schedule</h5>
                        <h3><?php echo count($todaySchedule); ?></h3>
                        <p>Classes Today</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-clock me-2"></i>Current Class</h5>
                        <h3><?php 
                        $currentClass = null;
                        foreach($todaySchedule as $s) {
                            if(isCurrentClass($s['start_time'], $s['end_time'], $currentTime)) {
                                $currentClass = $s;
                                break;
                            }
                        }
                        echo $currentClass ? $currentClass['subject'] : 'None';
                        ?></h3>
                        <p><?php echo $currentClass ? $currentClass['start_time'].' - '.$currentClass['end_time'] : ''; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-book me-2"></i>Subjects</h5>
                        <h3><?php echo count(array_unique(array_column($schedules, 'subject'))); ?></h3>
                        <p>Total Subjects</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-chalkboard me-2"></i>Rooms</h5>
                        <h3><?php echo count(array_unique(array_column($schedules, 'room_no'))); ?></h3>
                        <p>Classrooms</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if(!empty($todaySchedule)): ?>
        <div class="card mb-4 border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-sun me-2"></i>Today's Quick View - <?php echo $todayName; ?></h5>
            </div>
            <div class="card-body">
                <div class="section-search-container">
                    <i class="fas fa-search section-search-icon"></i>
                    <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#scheduleDailyTable tbody">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="scheduleDailyTable">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Time</th>
                                <th>Teacher</th>
                                <th>Room</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($todaySchedule as $schedule): ?>
                            <tr class="<?php echo isCurrentClass($schedule['start_time'], $schedule['end_time'], $currentTime) ? 'table-success current-class' : ''; ?>">
                                <td><strong><?php echo htmlspecialchars($schedule['subject']); ?></strong></td>
                                <td><?php echo date('h:i A', strtotime($schedule['start_time'])).' - '.date('h:i A', strtotime($schedule['end_time'])); ?></td>
                                <td><?php echo htmlspecialchars($schedule['teacher_name']); ?></td>
                                <td><?php echo htmlspecialchars($schedule['room_no']); ?></td>
                                <td><span class="badge bg-<?php echo $schedule['type'] == 'Lab' ? 'warning' : ($schedule['type'] == 'Tutorial' ? 'info' : 'primary'); ?>"><?php echo $schedule['type']; ?></span></td>
                                <td>
                                    <?php
                                    if(isCurrentClass($schedule['start_time'], $schedule['end_time'], $currentTime)):
                                    ?>
                                        <span class="badge bg-success pulse">Ongoing</span>
                                    <?php elseif($schedule['start_time'] > $currentTime): ?>
                                        <span class="badge bg-secondary">Upcoming</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Select Semester</label>
                <select class="form-select" id="semesterFilter" onchange="filterSchedule()">
                    <?php for($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $selectedSemester == $i ? 'selected' : ''; ?>>Semester <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="dayTabs" role="tablist">
                    <?php foreach($daysOfWeek as $index => $day): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?php echo $selectedDay == $day ? 'active' : ''; ?>" 
                                id="<?php echo strtolower($day); ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#<?php echo strtolower($day); ?>" 
                                type="button" 
                                role="tab"
                                onclick="selectDay('<?php echo $day; ?>')">
                            <?php echo substr($day, 0, 3); ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="dayTabContent">
                    <?php foreach($daysOfWeek as $day): ?>
                    <div class="tab-pane fade <?php echo $selectedDay == $day ? 'show active' : ''; ?>" 
                         id="<?php echo strtolower($day); ?>" role="tabpanel">
                        <?php
                        $daySchedule = array_filter($schedules, function($s) use ($day) {
                            return $s['day'] === $day;
                        });
                        ?>
                        <?php if(empty($daySchedule)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No classes scheduled for <?php echo $day; ?></p>
                            </div>
                        <?php else: ?>
                        <div class="section-search-container">
                            <i class="fas fa-search section-search-icon"></i>
                            <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#scheduleWeeklyTable tbody">
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="scheduleWeeklyTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Teacher</th>
                                        <th>Room</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    foreach($daySchedule as $schedule): 
                                        $isCurrent = $day === $todayName && isCurrentClass($schedule['start_time'], $schedule['end_time'], $currentTime);
                                    ?>
                                    <tr class="<?php echo $isCurrent ? 'table-warning current-class-row' : ''; ?>">
                                        <td><?php echo $counter++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($schedule['subject']); ?></strong></td>
                                        <td><?php echo date('h:i A', strtotime($schedule['start_time'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($schedule['end_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($schedule['teacher_name']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($schedule['room_no']); ?></span></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $schedule['type'] == 'Lecture' ? 'primary' : 
                                                     ($schedule['type'] == 'Lab' ? 'success' : 
                                                     ($schedule['type'] == 'Tutorial' ? 'info' : 'warning')); 
                                            ?>"><?php echo $schedule['type']; ?></span>
                                        </td>
                                        <td>
                                            <a href="edit_schedule.php?id=<?= $schedule['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=schedule&id=<?= $schedule['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=schedule&id=<?= $schedule['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this schedule"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>
</div>

<script>
function filterSchedule() {
    const semester = document.getElementById('semesterFilter').value;
    window.location.href = 'schedule.php?semester=' + semester + '&day=<?php echo $selectedDay; ?>';
}

function selectDay(day) {
    const semester = document.getElementById('semesterFilter').value;
    window.history.pushState({}, '', 'schedule.php?semester=' + semester + '&day=' + day);
}
</script>

<?php require 'includes/footer.php'; ?>
