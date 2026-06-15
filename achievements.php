<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Achievements Wall';
$extraCSS = ['achievements.css'];
$extraJS = ['achievements.js'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        category ENUM('award','hackathon','extracurricular','other') DEFAULT 'other',
        description TEXT,
        issuer VARCHAR(255),
        date_achieved DATE,
        link VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

// Fetch achievements
$stmt = $pdo->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY date_achieved DESC, created_at DESC");
$stmt->execute([$userId]);
$achievements = $stmt->fetchAll();

$categoryLabels = [
    'award' => 'Award',
    'hackathon' => 'Hackathon',
    'extracurricular' => 'Extracurricular',
    'other' => 'Other',
];
$categoryIcons = [
    'award' => 'fas fa-trophy',
    'hackathon' => 'fas fa-laptop-code',
    'extracurricular' => 'fas fa-running',
    'other' => 'fas fa-star',
];
$categoryColors = [
    'award' => '#ffab00',
    'hackathon' => '#7c3aed',
    'extracurricular' => '#00d25b',
    'other' => '#0090e7',
];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-trophy" style="color:#ffab00;"></i> Achievements Wall</h2>
      <p>Showcase your awards, hackathon wins & extracurricular achievements</p>
    </div>
    <button class="btn btn-primary btn-sm" id="addAchievementBtn"><i class="fas fa-plus"></i> Add Achievement</button>
  </div>

  <!-- Category Filter -->
  <div class="achievement-filters">
    <button class="achievement-filter active" data-filter="all">All</button>
    <button class="achievement-filter" data-filter="award"><i class="fas fa-trophy"></i> Awards</button>
    <button class="achievement-filter" data-filter="hackathon"><i class="fas fa-laptop-code"></i> Hackathons</button>
    <button class="achievement-filter" data-filter="extracurricular"><i class="fas fa-running"></i> Extracurricular</button>
    <button class="achievement-filter" data-filter="other"><i class="fas fa-star"></i> Other</button>
  </div>

  <?php if (empty($achievements)): ?>
    <div class="achievement-empty">
      <i class="fas fa-trophy"></i>
      <h5>No achievements yet</h5>
      <p>Add your first achievement to showcase your accomplishments</p>
    </div>
  <?php else: ?>
            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#achievementGrid">
            </div>
    <div class="achievement-grid" id="achievementGrid">
      <?php foreach ($achievements as $a): ?>
        <div class="achievement-card" data-id="<?= $a['id'] ?>" data-category="<?= $a['category'] ?>">
          <div class="achievement-card-top" style="border-left-color:<?= $categoryColors[$a['category']] ?>;">
            <div class="achievement-icon" style="color:<?= $categoryColors[$a['category']] ?>;background:<?= $categoryColors[$a['category']] ?>12;">
              <i class="<?= $categoryIcons[$a['category']] ?>"></i>
            </div>
            <div class="achievement-card-actions">
              <button class="achievement-action-btn" onclick="editAchievement(<?= $a['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
              <button class="achievement-action-btn action-delete" onclick="confirmDeleteAchievement(<?= $a['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
            </div>
          </div>
          <h4 class="achievement-title"><?= htmlspecialchars($a['title']) ?></h4>
          <span class="achievement-category" style="background:<?= $categoryColors[$a['category']] ?>18;color:<?= $categoryColors[$a['category']] ?>;">
            <i class="<?= $categoryIcons[$a['category']] ?>"></i> <?= $categoryLabels[$a['category']] ?>
          </span>
          <?php if (!empty($a['issuer'])): ?>
            <div class="achievement-issuer"><i class="fas fa-building"></i> <?= htmlspecialchars($a['issuer']) ?></div>
          <?php endif; ?>
          <?php if (!empty($a['date_achieved'])): ?>
            <div class="achievement-date"><i class="far fa-calendar-alt"></i> <?= date('M Y', strtotime($a['date_achieved'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($a['description'])): ?>
            <div class="achievement-desc"><?= nl2br(htmlspecialchars($a['description'])) ?></div>
          <?php endif; ?>
          <?php if (!empty($a['link'])): ?>
            <a href="<?= htmlspecialchars($a['link']) ?>" target="_blank" class="achievement-link"><i class="fas fa-external-link-alt"></i> View Details</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Achievement Modal -->
<div class="modal fade" id="achievementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="achievementFormTitle">Add Achievement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="achievements_handler.php" id="achievementForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add" id="achievement_action">
        <input type="hidden" name="id" id="achievement_id" value="">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="achievement_title" name="title" required placeholder="e.g. 1st Place Hackathon">
            </div>
            <div class="col-12">
              <label class="form-label">Category</label>
              <select class="form-control" id="achievement_category" name="category">
                <option value="award">Award</option>
                <option value="hackathon">Hackathon</option>
                <option value="extracurricular">Extracurricular</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Issuer / Organization</label>
              <input type="text" class="form-control" id="achievement_issuer" name="issuer" placeholder="e.g. SITM College">
            </div>
            <div class="col-md-6">
              <label class="form-label">Date Achieved</label>
              <input type="date" class="form-control" id="achievement_date" name="date_achieved">
            </div>
            <div class="col-12">
              <label class="form-label">Description</label>
              <textarea class="form-control" id="achievement_desc" name="description" rows="3" placeholder="Describe your achievement..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Link (optional)</label>
              <input type="url" class="form-control" id="achievement_link" name="link" placeholder="https://...">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="achievement_submit_btn"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
