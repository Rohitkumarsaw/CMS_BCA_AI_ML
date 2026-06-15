<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Leave Applications';
$extraCSS = ['leave.css'];
$extraJS = ['leave.js'];

$userId = $_SESSION['user_id'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS leave_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        subject VARCHAR(255) NOT NULL,
        reason TEXT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        admin_remark TEXT,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

// Fetch my leaves
$stmt = $pdo->prepare("SELECT * FROM leave_applications WHERE user_id = ? ORDER BY applied_at DESC");
$stmt->execute([$userId]);
$leaves = $stmt->fetchAll();

$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
foreach ($leaves as $l) {
    if ($l['status'] === 'pending') $pendingCount++;
    elseif ($l['status'] === 'approved') $approvedCount++;
    elseif ($l['status'] === 'rejected') $rejectedCount++;
}

$statusColors = [
    'pending' => 'leave-status-pending',
    'approved' => 'leave-status-approved',
    'rejected' => 'leave-status-rejected',
];

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-file-signature"></i> Leave Applications</h2>
      <p>Apply for leave and track your application status</p>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-4">
      <div class="leave-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#fbbf24;"><?= $pendingCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Pending</div>
      </div>
    </div>
    <div class="col-md-4 col-4">
      <div class="leave-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#00d25b;"><?= $approvedCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Approved</div>
      </div>
    </div>
    <div class="col-md-4 col-4">
      <div class="leave-card text-center" style="padding:14px;">
        <div style="font-size:1.3rem;font-weight:800;color:#fc424a;"><?= $rejectedCount ?></div>
        <div style="font-size:0.7rem;color:#8f94a8;text-transform:uppercase;letter-spacing:0.5px;">Rejected</div>
      </div>
    </div>
  </div>

  <div class="leave-wrapper">

    <!-- LEFT: Apply Form -->
    <div class="leave-card">
      <div class="leave-card-header">
        <h3><i class="fas fa-pen"></i> Apply for Leave</h3>
      </div>
      <form class="leave-form" method="POST" action="leave_handler.php" id="leaveForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="apply">
        <div class="form-group">
          <label for="leaveSubject">Subject</label>
          <input type="text" id="leaveSubject" name="subject" placeholder="e.g. Medical Leave" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="leaveStart">Start Date</label>
            <input type="date" id="leaveStart" name="start_date" required>
          </div>
          <div class="form-group">
            <label for="leaveEnd">End Date</label>
            <input type="date" id="leaveEnd" name="end_date" required>
          </div>
        </div>
        <div class="form-group">
          <label for="leaveReason">Reason</label>
          <textarea id="leaveReason" name="reason" rows="4" placeholder="Describe the reason for your leave..." required></textarea>
        </div>
        <button type="submit" class="leave-btn"><i class="fas fa-paper-plane"></i> Submit Application</button>
      </form>
    </div>

    <!-- RIGHT: My Leaves -->
    <div class="leave-card">
      <div class="leave-card-header">
        <h3><i class="fas fa-list"></i> My Applications</h3>
        <span style="font-size:0.72rem;color:#8f94a8;"><?= count($leaves) ?> total</span>
      </div>
            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#leaveList">
            </div>
      <div class="leave-list" id="leaveList">
        <?php if (empty($leaves)): ?>
          <div class="leave-empty">
            <i class="fas fa-inbox"></i>
            <h5>No leave applications</h5>
            <p>Apply for leave using the form</p>
          </div>
        <?php else: ?>
          <?php foreach ($leaves as $leave): ?>
            <div class="leave-item <?= $leave['status'] ?>" data-id="<?= $leave['id'] ?>" data-subject="<?= htmlspecialchars($leave['subject']) ?>" data-start="<?= $leave['start_date'] ?>" data-end="<?= $leave['end_date'] ?>" data-reason="<?= htmlspecialchars($leave['reason']) ?>">
              <div class="leave-item-top">
                <h5 class="leave-item-subject"><?= htmlspecialchars($leave['subject']) ?></h5>
                <span class="leave-status-badge <?= $statusColors[$leave['status']] ?>"><?= ucfirst($leave['status']) ?></span>
              </div>
              <div class="leave-item-dates">
                <i class="far fa-calendar-alt"></i> <?= date('d M Y', strtotime($leave['start_date'])) ?> — <?= date('d M Y', strtotime($leave['end_date'])) ?>
              </div>
              <div class="leave-item-reason"><?= nl2br(htmlspecialchars($leave['reason'])) ?></div>
              <div class="leave-item-footer">
                <div class="leave-item-remark">
                  <?php if (!empty($leave['admin_remark'])): ?>
                    <i class="fas fa-comment"></i> <?= htmlspecialchars($leave['admin_remark']) ?>
                  <?php else: ?>
                    <span style="color:#3d4352;">No remarks</span>
                  <?php endif; ?>
                </div>
                <?php if ($leave['status'] === 'pending'): ?>
                  <div class="leave-item-actions">
                    <button class="planner-action-btn action-edit" onclick="editLeave(<?= $leave['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
                    <button class="planner-action-btn action-approve" onclick="confirmApproveLeave(<?= $leave['id'] ?>)" title="Approve"><i class="fas fa-check"></i></button>
                    <button class="planner-action-btn action-reject" onclick="confirmRejectLeave(<?= $leave['id'] ?>)" title="Reject"><i class="fas fa-times"></i></button>
                    <button class="planner-action-btn action-delete" onclick="confirmCancelLeave(<?= $leave['id'] ?>)" title="Cancel"><i class="fas fa-ban"></i></button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Leave Edit Modal -->
<div class="modal fade" id="leaveEditModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Leave Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="leave_handler.php" id="leaveEditForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_leave_id" value="">
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" class="form-control" id="edit_leave_subject" name="subject" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Start Date</label>
              <input type="date" class="form-control" id="edit_leave_start" name="start_date" required>
            </div>
            <div class="form-group">
              <label class="form-label">End Date</label>
              <input type="date" class="form-control" id="edit_leave_end" name="end_date" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Reason</label>
            <textarea class="form-control" id="edit_leave_reason" name="reason" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
