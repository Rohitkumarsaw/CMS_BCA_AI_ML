<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Daily Routine';
$extraCSS = ['routine.css'];
$extraJS = ['routine.js'];

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS routine_tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        task_name VARCHAR(255) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        category ENUM('study','break','exercise','other') DEFAULT 'study',
        status ENUM('pending','completed') DEFAULT 'pending',
        task_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

// Fetch today's tasks ordered by start time
$stmt = $pdo->prepare("SELECT * FROM routine_tasks WHERE user_id = ? AND task_date = ? ORDER BY start_time ASC");
$stmt->execute([$userId, $today]);
$tasks = $stmt->fetchAll();

$totalTasks = count($tasks);
$completedTasks = 0;
$totalMinutes = 0;
$completedMinutes = 0;
foreach ($tasks as $t) {
    $start = strtotime($t['start_time']);
    $end = strtotime($t['end_time']);
    $mins = max(0, ($end - $start) / 60);
    $totalMinutes += $mins;
    if ($t['status'] === 'completed') {
        $completedTasks++;
        $completedMinutes += $mins;
    }
}
$progressPct = $totalMinutes > 0 ? round(($completedMinutes / $totalMinutes) * 100) : 0;
$scheduledHours = $totalMinutes > 0 ? round($totalMinutes / 60, 1) : 0;

function formatTime($t) {
    return date('h:i A', strtotime($t));
}

function categoryBadge($cat) {
    $map = [
        'study' => 'routine-badge-study',
        'coding' => 'routine-badge-coding',
        'fitness' => 'routine-badge-fitness',
        'break' => 'routine-badge-break',
    ];
    return $map[$cat] ?? 'routine-badge-study';
}

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-clock"></i> Daily Routine</h2>
      <p>Manage your personal timetable and track daily progress</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('routineForm').scrollIntoView({behavior:'smooth'})">
        <i class="fas fa-plus"></i> Add Task
      </button>
    </div>
  </div>

  <div class="routine-wrapper">

    <!-- LEFT COLUMN: TIMELINE -->
    <div class="routine-left">
      <div class="routine-card">
        <div class="routine-card-header">
          <h3><i class="fas fa-list"></i> Today's Schedule</h3>
          <span style="font-size:0.75rem;color:#8f94a8;"><?php echo date('l, d M Y'); ?></span>
        </div>
        <div class="section-search-container">
            <i class="fas fa-search section-search-icon"></i>
            <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#routineTimeline">
        </div>
        <div class="routine-timeline" id="routineTimeline">
          <?php if (empty($tasks)): ?>
            <div class="routine-empty">
              <i class="fas fa-calendar-plus"></i>
              <h5>No tasks scheduled</h5>
              <p>Add your first routine task to get started</p>
            </div>
          <?php else: ?>
            <?php foreach ($tasks as $task):
              $rowClass = $task['status'] === 'completed' ? 'task-completed' : 'task-pending';
              $iconClass = $task['status'] === 'completed' ? 'fa-undo' : 'fa-check';
              $toggleTitle = $task['status'] === 'completed' ? 'Mark pending' : 'Mark complete';
            ?>
              <div class="routine-slot <?= $rowClass ?>" data-id="<?= $task['id'] ?>">
                <div class="routine-slot-time"><?= formatTime($task['start_time']) ?></div>
                <div class="routine-slot-connector"></div>
                <div class="routine-task <?= $rowClass ?>">
                  <div class="routine-task-info">
                    <div class="routine-task-title"><?= htmlspecialchars($task['task_name']) ?></div>
                    <div class="routine-task-meta">
                      <span class="routine-task-time"><i class="far fa-clock"></i> <?= formatTime($task['start_time']) ?> — <?= formatTime($task['end_time']) ?></span>
                      <span class="routine-badge <?= categoryBadge($task['category']) ?>"><?= ucfirst($task['category']) ?></span>
                      <?php if ($task['status'] === 'completed'): ?>
                        <span class="routine-badge task-done-badge">Done</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="routine-task-actions">
                    <button class="routine-action-btn action-toggle" onclick="toggleTask(<?= $task['id'] ?>)" title="<?= $toggleTitle ?>">
                      <i class="fas <?= $iconClass ?>"></i>
                    </button>
                    <button class="routine-action-btn action-edit" onclick="editTask(<?= $task['id'] ?>)" title="Edit">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="routine-action-btn action-delete" onclick="confirmDelete(<?= $task['id'] ?>)" title="Delete">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN: FORM + PROGRESS -->
    <div class="routine-right">
      <!-- Add Task Form -->
      <div class="routine-card" id="routineForm">
        <div class="routine-card-header">
          <h3><i class="fas fa-plus-circle"></i> Add Task</h3>
        </div>
        <form class="routine-form" method="POST" action="routine_handler.php" id="addTaskForm">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="add">
          <div class="form-group">
            <label for="taskName">Task Name</label>
            <input type="text" id="taskName" name="task_name" placeholder="e.g. Web Dev Practice" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="startTime">Start Time</label>
              <input type="time" id="startTime" name="start_time" required>
            </div>
            <div class="form-group">
              <label for="endTime">End Time</label>
              <input type="time" id="endTime" name="end_time" required>
            </div>
          </div>
          <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category">
              <option value="study">Study / BCA</option>
              <option value="coding">Coding / Web Dev</option>
              <option value="fitness">Fitness / Routine</option>
              <option value="break">Break / Rest</option>
            </select>
          </div>
          <button type="submit" class="routine-btn">
            <i class="fas fa-save"></i> Save Task
          </button>
        </form>
      </div>

      <!-- Progress Widget -->
      <div class="routine-card">
        <div class="routine-card-header">
          <h3><i class="fas fa-chart-simple"></i> Today's Progress</h3>
        </div>
        <div class="routine-progress-stats">
          <div class="routine-stat-box">
            <div class="stat-value"><?= $scheduledHours ?>h</div>
            <div class="stat-label">Scheduled</div>
          </div>
          <div class="routine-stat-box">
            <div class="stat-value"><?= $progressPct ?>%</div>
            <div class="stat-label">Complete</div>
          </div>
        </div>
        <div class="routine-progress-bar-wrap">
          <div class="routine-progress-bar-fill" style="width:<?= $progressPct ?>%;"></div>
        </div>
        <div class="routine-progress-label"><?= $completedTasks ?> of <?= $totalTasks ?> tasks completed</div>
      </div>

      <!-- Edit Task Modal -->
      <div class="routine-card" id="editFormCard" style="display:none;">
        <div class="routine-card-header">
          <h3><i class="fas fa-pen"></i> Edit Task</h3>
          <button type="button" class="routine-action-btn" onclick="cancelEdit()" title="Cancel"><i class="fas fa-times"></i></button>
        </div>
        <form class="routine-form" method="POST" action="routine_handler.php" id="editTaskForm">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="editId" value="">
          <div class="form-group">
            <label for="editTaskName">Task Name</label>
            <input type="text" id="editTaskName" name="task_name" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="editStartTime">Start Time</label>
              <input type="time" id="editStartTime" name="start_time" required>
            </div>
            <div class="form-group">
              <label for="editEndTime">End Time</label>
              <input type="time" id="editEndTime" name="end_time" required>
            </div>
          </div>
          <div class="form-group">
            <label for="editCategory">Category</label>
            <select id="editCategory" name="category">
              <option value="study">Study / BCA</option>
              <option value="coding">Coding / Web Dev</option>
              <option value="fitness">Fitness / Routine</option>
              <option value="break">Break / Rest</option>
            </select>
          </div>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="routine-btn" style="flex:1;"><i class="fas fa-save"></i> Update</button>
            <button type="button" class="routine-btn" style="flex:0;background:#3d4352;" onclick="cancelEdit()">Cancel</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<!-- Hidden delete form -->
<form id="deleteForm" method="POST" action="routine_handler.php" style="display:none;">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="id" id="deleteId" value="">
</form>

<?php require 'includes/footer.php'; ?>
