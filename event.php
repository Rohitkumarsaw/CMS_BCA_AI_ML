<?php
$pageTitle = 'Calendar';
$extraCSS = ['event.css'];
$extraJS = ['event_calendar.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$ym = sprintf('%04d-%02d', $year, $month);

$stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date ASC, time ASC");
$stmt->execute([$userId, $ym]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <?= getFlashMessage() ?>
  <div class="ec-container">

    <!-- LEFT COLUMN -->
    <div class="ec-left">

      <!-- Live Clock -->
      <div class="ec-card ec-clock-card">
        <div class="ec-clock-time" id="ecClockTime">00:00:00</div>
        <div class="ec-clock-date" id="ecClockDate">Loading...</div>
      </div>

      <!-- Calendar Card -->
      <div class="ec-card">
        <div class="ec-cal-header">
          <button class="ec-nav-btn" onclick="location.href='event.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>'"><i class="fas fa-chevron-left"></i></button>
          <div class="ec-cal-title">
            <span class="ec-cal-month"><?php echo date('F', mktime(0, 0, 0, $month, 1, $year)); ?></span>
            <span class="ec-cal-year"><?php echo $year; ?></span>
          </div>
          <button class="ec-nav-btn" onclick="location.href='event.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>'"><i class="fas fa-chevron-right"></i></button>
        </div>

        <div class="ec-weekdays">
          <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>

        <div class="ec-grid" id="ecGrid">
          <?php
          $firstDay = mktime(0, 0, 0, $month, 1, $year);
          $startDow = (int)date('w', $firstDay);
          $daysInMonth = (int)date('t', $firstDay);
          $today = (int)date('j');
          $currYM = date('Y-m');

          $eventMap = [];
          foreach ($events as $e) {
            $d = (int)date('j', strtotime($e['date']));
            if (!isset($eventMap[$d])) $eventMap[$d] = [];
            $eventMap[$d][] = $e;
          }

          for ($i = 0; $i < $startDow; $i++) {
            echo '<div class="ec-cell ec-other"></div>';
          }

          for ($d = 1; $d <= $daysInMonth; $d++) {
            $classes = 'ec-cell';
            $isToday = ($d == $today && $ym == $currYM);
            if ($isToday) $classes .= ' ec-today';
            if (isset($eventMap[$d])) $classes .= ' ec-has-event';

            echo '<div class="' . $classes . '" data-day="' . $d . '" onclick="openScheduler(' . $d . ', ' . $month . ', ' . $year . ')">';
            echo '<span class="ec-day-num">' . $d . '</span>';

            if (isset($eventMap[$d])) {
              echo '<div class="ec-dots">';
              foreach ($eventMap[$d] as $ev) {
                $typeClass = strtolower(str_replace(' ', '-', $ev['type']));
                echo '<span class="ec-dot ec-dot-' . htmlspecialchars($typeClass) . '" title="' . htmlspecialchars($ev['event_name']) . '"></span>';
              }
              echo '</div>';
            }

            echo '</div>';
          }
          ?>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="ec-right">

      <!-- Add Event Form -->
      <div class="ec-card" id="ecFormCard">
        <div class="ec-card-header">
          <h3><i class="fas fa-plus-circle"></i> <span id="ecFormTitle">Add Event</span></h3>
        </div>
        <form class="ec-form" method="POST" action="event_handler.php" id="ecForm">
          <?= csrfField() ?>
          <input type="hidden" name="action" id="ecFormAction" value="add">
          <input type="hidden" name="event_id" id="ecInputEventId" value="">
          <input type="hidden" name="month" value="<?php echo $month; ?>">
          <input type="hidden" name="year" value="<?php echo $year; ?>">

          <div class="ec-field">
            <label>Event Title</label>
            <input type="text" name="title" id="ecInputTitle" placeholder="e.g. Mid-Term Exam" required>
          </div>

          <div class="ec-field-row">
            <div class="ec-field">
              <label>Date</label>
              <input type="date" name="date" id="ecInputDate" required>
            </div>
            <div class="ec-field">
              <label>Time</label>
              <input type="time" name="time" id="ecInputTime">
            </div>
          </div>

          <div class="ec-field">
            <label>Category</label>
            <select name="type" id="ecInputType">
              <option value="Exam">Exam</option>
              <option value="Holiday">Holiday</option>
              <option value="Personal Task">Personal Task</option>
              <option value="Sports">Sports</option>
              <option value="Technical">Technical</option>
              <option value="Cultural">Cultural</option>
              <option value="Workshop">Workshop</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="ec-form-buttons" style="display:flex;gap:8px;">
            <button type="submit" class="ec-btn ec-btn-primary flex-fill" id="ecFormSubmit"><i class="fas fa-calendar-plus"></i> <span id="ecSubmitText">Save Event</span></button>
            <button type="button" class="ec-btn ec-btn-secondary" id="ecCancelEdit" style="display:none;background:#3d4352;color:#fff;">Cancel</button>
          </div>
        </form>
      </div>

      <!-- Upcoming Events -->
      <div class="ec-card">
        <div class="ec-card-header">
          <h3><i class="fas fa-list"></i> Upcoming Events</h3>
        </div>
            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#ecEventList">
            </div>
        <div class="ec-event-list" id="ecEventList">
          <?php if (empty($events)): ?>
            <div class="ec-empty">
              <i class="fas fa-calendar-day"></i>
              <p>No events this month</p>
            </div>
          <?php else: ?>
            <?php foreach ($events as $e):
              $ts = strtotime($e['date']);
              $dayNum = date('j', $ts);
              $dayName = date('D', $ts);
            ?>
              <div class="ec-event-item">
                <div class="ec-event-date">
                  <span class="ec-event-daynum"><?php echo $dayNum; ?></span>
                  <span class="ec-event-dayname"><?php echo $dayName; ?></span>
                </div>
                <div class="ec-event-info">
                  <div class="ec-event-title"><?php echo htmlspecialchars($e['event_name']); ?></div>
                  <div class="ec-event-meta">
                    <?php if (!empty($e['time'])): ?>
                      <span><i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($e['time'])); ?></span>
                    <?php endif; ?>
                    <span class="ec-event-badge ec-badge-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $e['type']))); ?>">
                      <?php echo ucfirst(htmlspecialchars($e['type'])); ?>
                    </span>
                  </div>
                </div>
                <div class="ec-event-actions">
                  <button type="button" class="ec-event-btn text-info ec-edit-event" data-id="<?php echo $e['id']; ?>" title="Edit"><i class="fas fa-pen"></i></button>
                  <button type="button" class="ec-event-btn text-danger ec-delete-event" data-id="<?php echo $e['id']; ?>" data-title="<?php echo htmlspecialchars($e['event_name']); ?>" title="Delete"><i class="fas fa-trash"></i></button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require 'includes/footer.php'; ?>
