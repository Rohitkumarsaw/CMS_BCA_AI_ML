<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Meeting & Class Links';
$extraCSS = ['meetings.css'];
$extraJS = ['meetings.js'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS meeting_links (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        platform ENUM('zoom','google_meet','microsoft_teams','other') DEFAULT 'zoom',
        url VARCHAR(500) NOT NULL,
        description TEXT,
        meeting_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

$stmt = $pdo->prepare("SELECT * FROM meeting_links WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$meetings = $stmt->fetchAll();

$platformLabels = [
    'zoom' => 'Zoom',
    'google_meet' => 'Google Meet',
    'microsoft_teams' => 'MS Teams',
    'other' => 'Other',
];
$platformIcons = [
    'zoom' => 'fas fa-video',
    'google_meet' => 'fab fa-google',
    'microsoft_teams' => 'fab fa-microsoft',
    'other' => 'fas fa-link',
];
$platformColors = [
    'zoom' => '#2d8cff',
    'google_meet' => '#34a853',
    'microsoft_teams' => '#7b83eb',
    'other' => '#a3a6b7',
];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-video" style="color:#2d8cff;"></i> Meeting & Class Links</h2>
      <p>Store all your Zoom, Google Meet & Teams links in one place</p>
    </div>
    <button class="btn btn-outline-primary btn-sm" id="addMeetingBtn"><i class="fas fa-plus"></i> Add Link</button>
  </div>

  <div class="meeting-stats">
    <div class="meeting-stat"><i class="fas fa-video" style="color:#2d8cff;"></i> <span id="zoomCount">0</span> Zoom</div>
    <div class="meeting-stat"><i class="fab fa-google" style="color:#34a853;"></i> <span id="meetCount">0</span> Meet</div>
    <div class="meeting-stat"><i class="fab fa-microsoft" style="color:#7b83eb;"></i> <span id="teamsCount">0</span> Teams</div>
    <div class="meeting-stat"><i class="fas fa-link" style="color:#a3a6b7;"></i> <span id="otherCount">0</span> Other</div>
  </div>

  <?php if (empty($meetings)): ?>
    <div class="meeting-empty">
      <i class="fas fa-video"></i>
      <h5>No meeting links</h5>
      <p>Add your first class or meeting link</p>
    </div>
  <?php else: ?>
    <div class="meeting-grid" id="meetingGrid">
      <?php foreach ($meetings as $m): ?>
        <div class="meeting-card" data-id="<?= $m['id'] ?>" data-platform="<?= $m['platform'] ?>">
          <div class="meeting-card-left" style="background:<?= $platformColors[$m['platform']] ?>18;color:<?= $platformColors[$m['platform']] ?>;">
            <i class="<?= $platformIcons[$m['platform']] ?>"></i>
          </div>
          <div class="meeting-card-body">
            <div class="meeting-card-top">
              <h4 class="meeting-title"><?= htmlspecialchars($m['title']) ?></h4>
              <div class="meeting-card-actions">
                <button class="meeting-action-btn" onclick="editMeeting(<?= $m['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                <button class="meeting-action-btn action-delete" onclick="confirmDeleteMeeting(<?= $m['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
              </div>
            </div>
            <span class="meeting-platform" style="background:<?= $platformColors[$m['platform']] ?>18;color:<?= $platformColors[$m['platform']] ?>;">
              <i class="<?= $platformIcons[$m['platform']] ?>"></i> <?= $platformLabels[$m['platform']] ?>
            </span>
            <a href="<?= htmlspecialchars($m['url']) ?>" target="_blank" class="meeting-url"><i class="fas fa-external-link-alt"></i> Open Link</a>
            <?php if (!empty($m['description'])): ?>
              <div class="meeting-desc"><?= nl2br(htmlspecialchars($m['description'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($m['meeting_date'])): ?>
              <div class="meeting-date"><i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($m['meeting_date'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Meeting Modal -->
<div class="modal fade" id="meetingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="meetingFormTitle">Add Meeting Link</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="meetings_handler.php" id="meetingForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add" id="meeting_action">
        <input type="hidden" name="id" id="meeting_id" value="">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="meeting_title" name="title" required placeholder="e.g. AI/ML Class">
            </div>
            <div class="col-md-6">
              <label class="form-label">Platform</label>
              <select class="form-control" id="meeting_platform" name="platform">
                <option value="zoom">Zoom</option>
                <option value="google_meet">Google Meet</option>
                <option value="microsoft_teams">Microsoft Teams</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Date (optional)</label>
              <input type="date" class="form-control" id="meeting_date" name="meeting_date">
            </div>
            <div class="col-12">
              <label class="form-label">Link / URL <span class="text-danger">*</span></label>
              <input type="url" class="form-control" id="meeting_url" name="url" required placeholder="https://zoom.us/j/... or https://meet.google.com/...">
            </div>
            <div class="col-12">
              <label class="form-label">Description (optional)</label>
              <textarea class="form-control" id="meeting_desc" name="description" rows="2" placeholder="Add notes about this meeting..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="meeting_submit_btn"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
