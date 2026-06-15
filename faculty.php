<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Faculty Directory';
$extraCSS = ['faculty.css'];
$extraJS = ['faculty.js'];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS faculty (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        department VARCHAR(255),
        subjects VARCHAR(500),
        email VARCHAR(255),
        phone VARCHAR(50),
        photo_path VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {}

$stmt = $pdo->prepare("SELECT * FROM faculty ORDER BY name ASC");
$stmt->execute();
$faculty = $stmt->fetchAll();

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
  <div class="page-header">
    <div>
      <h2><i class="fas fa-chalkboard-teacher"></i> Faculty Directory</h2>
      <p>View and manage faculty members</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button class="btn btn-outline-primary btn-sm" id="addFacultyBtn"><i class="fas fa-plus"></i> Add Faculty</button>
    </div>
  </div>

  <?php if (empty($faculty)): ?>
    <div class="faculty-empty">
      <i class="fas fa-users"></i>
      <h5>No faculty members</h5>
      <p>Add your first faculty member to get started</p>
    </div>
  <?php else: ?>
    <div class="faculty-grid">
      <?php foreach ($faculty as $f): ?>
        <div class="faculty-card" data-id="<?= $f['id'] ?>">
          <div class="faculty-avatar">
            <i class="fas fa-user-tie"></i>
          </div>
          <h4 class="faculty-name"><?= htmlspecialchars($f['name']) ?></h4>
          <div class="faculty-dept"><?= htmlspecialchars($f['department'] ?? '') ?></div>
          <div class="faculty-subjects"><?= htmlspecialchars($f['subjects'] ?? '') ?></div>
          <div class="faculty-contact">
            <?php if (!empty($f['email'])): ?>
              <a href="mailto:<?= htmlspecialchars($f['email']) ?>"><i class="fas fa-envelope"></i> <?= htmlspecialchars($f['email']) ?></a>
            <?php endif; ?>
            <?php if (!empty($f['phone'])): ?>
              <a href="tel:<?= htmlspecialchars($f['phone']) ?>"><i class="fas fa-phone"></i> <?= htmlspecialchars($f['phone']) ?></a>
            <?php endif; ?>
          </div>
          <div class="faculty-card-actions">
            <button class="planner-action-btn action-edit" onclick="editFaculty(<?= $f['id'] ?>)" title="Edit"><i class="fas fa-pen"></i></button>
            <button class="planner-action-btn action-delete" onclick="confirmDeleteFaculty(<?= $f['id'] ?>)" title="Delete"><i class="fas fa-trash-alt"></i></button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Faculty Modal -->
<div class="modal fade" id="facultyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="facultyFormTitle">Add Faculty</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="faculty_handler.php" id="facultyForm">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add" id="faculty_action">
        <input type="hidden" name="id" id="faculty_id" value="">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" id="faculty_name" name="name" required placeholder="Dr. Rajesh Kumar">
            </div>
            <div class="col-12">
              <label class="form-label">Department</label>
              <input type="text" class="form-control" id="faculty_department" name="department" placeholder="Computer Science">
            </div>
            <div class="col-12">
              <label class="form-label">Subjects</label>
              <input type="text" class="form-control" id="faculty_subjects" name="subjects" placeholder="DBMS, AI/ML, Python">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="faculty_email" name="email" placeholder="faculty@sitm.ac.in">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" class="form-control" id="faculty_phone" name="phone" placeholder="+91-9876543210">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="faculty_submit_btn"><i class="fas fa-save"></i> Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
