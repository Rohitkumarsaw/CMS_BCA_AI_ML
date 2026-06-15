<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Placement Tracker';
$extraCSS = ['placement.css'];
$extraJS = ['placement.js'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS placement_tracker (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        company_name VARCHAR(255) NOT NULL,
        role VARCHAR(255),
        application_date DATE,
        status ENUM('applied','shortlisted','interviewed','selected','rejected') DEFAULT 'applied',
        round_details TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

$stmt = $pdo->prepare("SELECT * FROM placement_tracker WHERE user_id = ? ORDER BY application_date DESC, created_at DESC");
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

$statusLabels = [
    'applied' => 'Applied',
    'shortlisted' => 'Shortlisted',
    'interviewed' => 'Interviewed',
    'selected' => 'Selected',
    'rejected' => 'Rejected',
];
$statusColors = [
    'applied' => '#0090e7',
    'shortlisted' => '#ffab00',
    'interviewed' => '#7c3aed',
    'selected' => '#00d25b',
    'rejected' => '#fc424a',
];
$statusIcons = [
    'applied' => 'fas fa-paper-plane',
    'shortlisted' => 'fas fa-list-check',
    'interviewed' => 'fas fa-user-tie',
    'selected' => 'fas fa-check-circle',
    'rejected' => 'fas fa-times-circle',
];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-briefcase" style="color:#0090e7;"></i> Placement Tracker</h2>
      <p>Track your job applications, interview rounds & placement status</p>
    </div>
    <button class="btn btn-outline-primary btn-sm" id="addPlacementBtn"><i class="fas fa-plus"></i> Add Application</button>
  </div>

  <!-- Quick Stats -->
  <div class="row g-3 mb-4">
    <?php
    $stats = ['applied' => 0, 'shortlisted' => 0, 'interviewed' => 0, 'selected' => 0, 'rejected' => 0];
    foreach ($applications as $app) {
        if (isset($stats[$app['status']])) $stats[$app['status']]++;
    }
    ?>
    <div class="col">
      <div class="placement-stat" style="border-left-color:#0090e7;">
        <div style="color:#0090e7;font-size:1.2rem;font-weight:800;"><?= $stats['applied'] ?></div>
        <div style="font-size:0.65rem;color:#8f94a8;text-transform:uppercase;">Applied</div>
      </div>
    </div>
    <div class="col">
      <div class="placement-stat" style="border-left-color:#ffab00;">
        <div style="color:#ffab00;font-size:1.2rem;font-weight:800;"><?= $stats['shortlisted'] ?></div>
        <div style="font-size:0.65rem;color:#8f94a8;text-transform:uppercase;">Shortlisted</div>
      </div>
    </div>
    <div class="col">
      <div class="placement-stat" style="border-left-color:#7c3aed;">
        <div style="color:#7c3aed;font-size:1.2rem;font-weight:800;"><?= $stats['interviewed'] ?></div>
        <div style="font-size:0.65rem;color:#8f94a8;text-transform:uppercase;">Interviewed</div>
      </div>
    </div>
    <div class="col">
      <div class="placement-stat" style="border-left-color:#00d25b;">
        <div style="color:#00d25b;font-size:1.2rem;font-weight:800;"><?= $stats['selected'] ?></div>
        <div style="font-size:0.65rem;color:#8f94a8;text-transform:uppercase;">Selected</div>
      </div>
    </div>
    <div class="col">
      <div class="placement-stat" style="border-left-color:#fc424a;">
        <div style="color:#fc424a;font-size:1.2rem;font-weight:800;"><?= $stats['rejected'] ?></div>
        <div style="font-size:0.65rem;color:#8f94a8;text-transform:uppercase;">Rejected</div>
      </div>
    </div>
  </div>

  <!-- Status Filter -->
  <div class="placement-filters">
    <button class="placement-filter active" data-filter="all">All</button>
    <button class="placement-filter" data-filter="applied" style="--ft-color:#0090e7;"><i class="fas fa-paper-plane"></i> Applied</button>
    <button class="placement-filter" data-filter="shortlisted" style="--ft-color:#ffab00;"><i class="fas fa-list-check"></i> Shortlisted</button>
    <button class="placement-filter" data-filter="interviewed" style="--ft-color:#7c3aed;"><i class="fas fa-user-tie"></i> Interviewed</button>
    <button class="placement-filter" data-filter="selected" style="--ft-color:#00d25b;"><i class="fas fa-check-circle"></i> Selected</button>
    <button class="placement-filter" data-filter="rejected" style="--ft-color:#fc424a;"><i class="fas fa-times-circle"></i> Rejected</button>
  </div>

  <?php if (empty($applications)): ?>
    <div class="placement-empty">
      <i class="fas fa-briefcase"></i>
      <h5>No applications tracked</h5>
      <p>Add your first job application to start tracking</p>
    </div>
  <?php else: ?>
    <div class="placement-grid" id="placementGrid">
      <?php foreach ($applications as $app): ?>
        <div class="placement-card" data-id="<?= $app['id'] ?>" data-status="<?= $app['status'] ?>">
          <div class="placement-card-left" style="background:<?= $statusColors[$app['status']] ?>12;">
            <i class="<?= $statusIcons[$app['status']] ?>" style="color:<?= $statusColors[$app['status']] ?>;"></i>
          </div>
          <div class="placement-card-body">
            <div class="placement-card-top">
              <div>
                <h4 class="placement-company"><?= htmlspecialchars($app['company_name']) ?></h4>
                <?php if (!empty($app['role'])): ?>
                  <div class="placement-role"><?= htmlspecialchars($app['role']) ?></div>
                <?php endif; ?>
              </div>
              <div class="placement-card-actions">
                <button class="placement-action-btn" onclick="editPlacement(<?= $app['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                <button class="placement-action-btn action-delete" onclick="confirmDeletePlacement(<?= $app['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
              </div>
            </div>
            <span class="placement-status-badge" style="background:<?= $statusColors[$app['status']] ?>18;color:<?= $statusColors[$app['status']] ?>;">
              <i class="<?= $statusIcons[$app['status']] ?>"></i> <?= $statusLabels[$app['status']] ?>
            </span>
            <?php if (!empty($app['application_date'])): ?>
              <div class="placement-date"><i class="far fa-calendar-alt"></i> Applied: <?= date('d M Y', strtotime($app['application_date'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($app['round_details'])): ?>
              <div class="placement-rounds"><i class="fas fa-layer-group"></i> <?= nl2br(htmlspecialchars($app['round_details'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($app['notes'])): ?>
              <div class="placement-notes"><i class="fas fa-sticky-note"></i> <?= nl2br(htmlspecialchars($app['notes'])) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Placement Modal -->
<div class="modal fade" id="placementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="placementFormTitle">Add Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="placement_handler.php" id="placementForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add" id="placement_action">
        <input type="hidden" name="id" id="placement_id" value="">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label">Company Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="placement_company" name="company_name" required placeholder="e.g. Google">
            </div>
            <div class="col-md-5">
              <label class="form-label">Status</label>
              <select class="form-control" id="placement_status" name="status">
                <option value="applied">Applied</option>
                <option value="shortlisted">Shortlisted</option>
                <option value="interviewed">Interviewed</option>
                <option value="selected">Selected</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <input type="text" class="form-control" id="placement_role" name="role" placeholder="e.g. Software Engineer">
            </div>
            <div class="col-md-6">
              <label class="form-label">Application Date</label>
              <input type="date" class="form-control" id="placement_date" name="application_date">
            </div>
            <div class="col-12">
              <label class="form-label">Round Details</label>
              <textarea class="form-control" id="placement_rounds" name="round_details" rows="2" placeholder="e.g. Round 1: Coding test, Round 2: Tech interview..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea class="form-control" id="placement_notes" name="notes" rows="2" placeholder="Any additional notes..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="placement_submit_btn"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
