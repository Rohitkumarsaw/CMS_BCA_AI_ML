<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Daily Routine';
$extraCSS = ['routine.css'];
$extraJS = ['routine.js'];

$userId = $_SESSION['user_id'];

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
      <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('routineForm').scrollIntoView({behavior:'smooth'})">
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
        <div class="routine-timeline">
          <!-- PHP will loop here -->
          <div class="routine-empty">
            <i class="fas fa-calendar-plus"></i>
            <h5>No tasks scheduled</h5>
            <p>Add your first routine task to get started</p>
          </div>
          <!--
          <div class="routine-slot active">
            <div class="routine-slot-time">09:00 AM</div>
            <div class="routine-slot-connector"></div>
            <div class="routine-task">
              <div class="routine-task-info">
                <div class="routine-task-title">Web Dev Project</div>
                <div class="routine-task-meta">
                  <span class="routine-task-time"><i class="far fa-clock"></i> 09:00 - 11:00</span>
                  <span class="routine-badge routine-badge-coding">Coding</span>
                </div>
              </div>
              <div class="routine-task-actions">
                <button class="routine-task-btn edit" onclick="editTask(1)" title="Edit"><i class="fas fa-pen"></i></button>
                <button class="routine-task-btn delete" onclick="deleteTask(1)" title="Delete"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>
          -->
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
        <form class="routine-form" method="POST" action="routine_handler.php">
          <?= csrfField() ?>
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
            <div class="stat-value">0h</div>
            <div class="stat-label">Scheduled</div>
          </div>
          <div class="routine-stat-box">
            <div class="stat-value">0%</div>
            <div class="stat-label">Complete</div>
          </div>
        </div>
        <div class="routine-progress-bar-wrap">
          <div class="routine-progress-bar-fill" style="width:0%;"></div>
        </div>
        <div class="routine-progress-label">0 of 0 tasks completed</div>
      </div>
    </div>

  </div>
</div>

<?php require 'includes/footer.php'; ?>
