<?php
$pageTitle = 'Holidays';
$extraCSS = ['style.css', 'holiday.css'];
$extraJS = ['main.js', 'holiday.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filter_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$filter_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if ($filter_year > 0) {
    $where .= " AND YEAR(date) = ?";
    $params[] = $filter_year;
}
if (!empty($filter_type)) {
    $where .= " AND type = ?";
    $params[] = $filter_type;
}

$pagi = paginate($pdo, 'holidays', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM holidays $where ORDER BY date ASC" . $pagi['sql']);
$stmt->execute($params);
$holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);

$monthCounts = [];
foreach ($holidays as $h) {
    $month = date('n', strtotime($h['date']));
    $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
}

$calendarYear = $filter_year > 0 ? $filter_year : (int)date('Y');
$calendarMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
if ($calendarMonth < 1) $calendarMonth = 1;
if ($calendarMonth > 12) $calendarMonth = 12;

$holidayDates = [];
foreach ($holidays as $h) {
    $d = date('Y-m-d', strtotime($h['date']));
    $holidayDates[$d] = $h;
}

$firstDay = mktime(0, 0, 0, $calendarMonth, 1, $calendarYear);
$daysInMonth = date('t', $firstDay);
$startDayOfWeek = date('w', $firstDay);

$typeColors = [
    'National' => 'danger',
    'College' => 'primary',
    'Private' => 'info'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-umbrella-beach me-2 text-primary"></i>Holidays</h4>
                    <p class="text-muted mb-0">Track your holidays and time off</p>
                </div>
                <a href="add_holiday.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Holiday
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Year</label>
                            <select name="year" class="form-select">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                    <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="National" <?= $filter_type === 'National' ? 'selected' : '' ?>>National</option>
                                <option value="College" <?= $filter_type === 'College' ? 'selected' : '' ?>>College</option>
                                <option value="Private" <?= $filter_type === 'Private' ? 'selected' : '' ?>>Private</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="holiday.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Calendar</h6>
                            <div class="d-flex gap-2 align-items-center">
                                <a href="?year=<?= $calendarYear ?>&month=<?= $calendarMonth - 1 ?>&type=<?= $filter_type ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                                <strong><?= date('F Y', mktime(0, 0, 0, $calendarMonth, 1, $calendarYear)) ?></strong>
                                <a href="?year=<?= $calendarYear ?>&month=<?= $calendarMonth + 1 ?>&type=<?= $filter_type ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered text-center mb-0">
                                <thead>
                                    <tr>
                                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $day = 1;
                                    $started = false;
                                    for ($row = 0; $row < 6; $row++): ?>
                                        <tr>
                                            <?php for ($col = 0; $col < 7; $col++): ?>
                                                <?php
                                                if (!$started && $col == $startDayOfWeek) $started = true;
                                                if ($started && $day <= $daysInMonth):
                                                    $dateStr = sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $day);
                                                    $isHoliday = isset($holidayDates[$dateStr]);
                                                    $isToday = ($dateStr === date('Y-m-d'));
                                                ?>
                                                    <td class="<?= $isHoliday ? 'bg-danger bg-opacity-10' : '' ?> <?= $isToday ? 'table-primary' : '' ?>">
                                                        <strong><?= $day ?></strong>
                                                        <?php if ($isHoliday): ?>
                                                            <br><small class="text-danger fw-bold">
                                                                <?= htmlspecialchars(substr($holidayDates[$dateStr]['holiday_name'], 0, 8)) ?>
                                                                <?= strlen($holidayDates[$dateStr]['holiday_name']) > 8 ? '...' : '' ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php else: ?>
                                                    <td class="text-muted"></td>
                                                <?php endif; ?>
                                                <?php if ($started && $day <= $daysInMonth) $day++; ?>
                                            <?php endfor; ?>
                                        </tr>
                                        <?php if ($day > $daysInMonth) break; ?>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Monthly Count</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            for ($m = 1; $m <= 12; $m++):
                                $count = $monthCounts[$m] ?? 0;
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small><?= $monthNames[$m] ?></small>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="width: 100px; height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?= min($count * 20, 100) ?>%"></div>
                                        </div>
                                        <small class="badge bg-primary"><?= $count ?></small>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Holiday List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Holiday Name</th>
                                    <th>Day</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($holidays)): ?>
                                    <?php foreach ($holidays as $index => $holiday): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($holiday['date']) ?></td>
                                            <td><strong><?= htmlspecialchars($holiday['holiday_name']) ?></strong></td>
                                            <td><?= date('l', strtotime($holiday['date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $typeColors[$holiday['type']] ?? 'secondary' ?>">
                                                    <?= htmlspecialchars($holiday['type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_holiday.php?id=<?= $holiday['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=holiday&id=<?= $holiday['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=holiday&id=<?= $holiday['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this holiday"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No holidays found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
