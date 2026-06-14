<?php
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Manage Subjects';
$extraCSS = ['style.css'];
$extraJS = ['main.js', 'subjects.js'];

$userId = $_SESSION['user_id'];
$semester = $_GET['semester'] ?? $_SESSION['user_semester'] ?? 1;

require 'includes/header.php';
require 'includes/navbar.php';
require 'includes/sidebar.php';
?>

<div class="app-content">
    <div class="container-fluid">
        <?= getFlashMessage() ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book me-2"></i>Manage Subjects</h2>
            <div class="d-flex gap-2">
                <select class="form-select" style="width:auto" id="semesterSelect" onchange="changeSemester()">
                    <?php for($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                        Semester <?php echo $i; ?>
                    </option>
                    <?php endfor; ?>
                </select>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                    <i class="fas fa-plus me-1"></i>Add Subject
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Subject Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="subjectsTableBody">
                            <?php
                            $stmt = $pdo->prepare("SELECT id, subject_name FROM user_subjects WHERE user_id = ? AND semester = ? ORDER BY id");
                            $stmt->execute([$userId, $semester]);
                            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $count = 1;
                            foreach ($subjects as $sub):
                            ?>
                            <tr data-id="<?= $sub['id'] ?>">
                                <td><?= $count++ ?></td>
                                <td class="subject-name-cell"><?= htmlspecialchars($sub['subject_name']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-subject-btn" data-id="<?= $sub['id'] ?>" data-name="<?= htmlspecialchars($sub['subject_name']) ?>"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-subject-btn" data-id="<?= $sub['id'] ?>" data-name="<?= htmlspecialchars($sub['subject_name']) ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($subjects)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No subjects added yet</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addSubjectForm" method="POST" action="subjects_handler.php">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="semester" value="<?= $semester ?>">
                    <div class="mb-3">
                        <label class="form-label">Subject Name *</label>
                        <input type="text" class="form-control" name="subject_name" required placeholder="e.g., Data Structures">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="subjects_handler.php">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editSubjectId">
                    <div class="mb-3">
                        <label class="form-label">Subject Name *</label>
                        <input type="text" class="form-control" name="subject_name" id="editSubjectName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="subjects_handler.php">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Delete Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteSubjectId">
                    <p>Are you sure you want to delete <strong id="deleteSubjectName"></strong>?</p>
                    <p class="text-danger small">This will also delete all syllabus topics under this subject.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function changeSemester() {
    const semester = document.getElementById('semesterSelect').value;
    window.location.href = 'manage_subjects.php?semester=' + semester;
}
</script>

<?php require 'includes/footer.php'; ?>
