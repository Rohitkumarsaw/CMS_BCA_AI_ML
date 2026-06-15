<?php
$pageTitle = 'Notes & Study Materials';
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];
$userSemester = $_SESSION['user_semester'];

$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 0;
$subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where = "WHERE n.user_id = :user_id";
$params = [':user_id' => $userId];

if ($semester > 0) {
    $where .= " AND n.semester = :semester";
    $params[':semester'] = $semester;
}
if (!empty($subject)) {
    $where .= " AND n.subject = :subject";
    $params[':subject'] = $subject;
}
if (!empty($type)) {
    $where .= " AND n.type = :type";
    $params[':type'] = $type;
}
if (!empty($search)) {
    $where .= " AND (n.title LIKE :search OR n.tags LIKE :search)";
    $params[':search'] = "%$search%";
}

$pagi = paginate($pdo, 'notes n', $where, $params, 20);
$query = "SELECT n.*
          FROM notes n
          $where
          ORDER BY n.created_at DESC" . $pagi['sql'];
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notes = $stmt->fetchAll();

$countQuery = "SELECT n.subject, COUNT(*) as count 
               FROM notes n 
               WHERE n.user_id = :user_id 
               GROUP BY n.subject";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute([':user_id' => $userId]);
$subjectCounts = $countStmt->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
include 'includes/navbar.php';
include 'includes/sidebar.php';
?>
<div class="app-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-book me-2"></i>Notes & Study Materials</h2>
            <a href="add_note.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add Note
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Notes</h5>
                        <h2><?php echo count($notes); ?></h2>
                    </div>
                </div>
            </div>
            <?php foreach ($subjectCounts as $subjectId => $count): ?>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $subjectId ?: 'Unassigned'; ?></h5>
                            <h2><?php echo $count; ?> notes</h2>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <select name="semester" class="form-select">
                            <option value="0">All Semesters</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="subject" class="form-select">
                            <option value="">All Subjects</option>
                            <?php
                            $subjects = getSemesterSubjects($userSemester);
                            foreach ($subjects as $subj): ?>
                                <option value="<?php echo $subj; ?>" <?php echo $subject === $subj ? 'selected' : ''; ?>>
                                    <?php echo $subj; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="PDF" <?php echo $type == 'PDF' ? 'selected' : ''; ?>>PDF</option>
                            <option value="Image" <?php echo $type == 'Image' ? 'selected' : ''; ?>>Image</option>
                            <option value="Text" <?php echo $type == 'Text' ? 'selected' : ''; ?>>Text</option>
                            <option value="Video" <?php echo $type == 'Video' ? 'selected' : ''; ?>>Video</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search notes..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-search-container">
                    <i class="fas fa-search section-search-icon"></i>
                    <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#notesTable tbody">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="notesTable">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Semester</th>
                                <th>Tags</th>
                                <th>Favorite</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No notes found</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($note['title']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo formatDate($note['created_at']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($note['subject'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $typeIcons = [
                                                'PDF' => ['icon' => 'fas fa-file-pdf', 'color' => 'text-danger'],
                                                'Image' => ['icon' => 'fas fa-file-image', 'color' => 'text-primary'],
                                                'Text' => ['icon' => 'fas fa-file-alt', 'color' => 'text-success'],
                                                'Video' => ['icon' => 'fas fa-file-video', 'color' => 'text-warning']
                                            ];
                                            $typeInfo = $typeIcons[$note['type']] ?? ['icon' => 'fas fa-file', 'color' => 'text-secondary'];
                                            ?>
                                            <span class="<?php echo $typeInfo['color']; ?>">
                                                <i class="<?php echo $typeInfo['icon']; ?> me-1"></i>
                                                <?php echo $note['type']; ?>
                                            </span>
                                        </td>
                                        <td>Sem <?php echo $note['semester']; ?></td>
                                        <td>
                                            <?php 
                                            $tags = explode(',', $note['tags'] ?? '');
                                            foreach ($tags as $tag): ?>
                                                <span class="badge bg-light text-dark"><?php echo trim($tag); ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm favorite-btn <?php echo $note['is_favorite'] ? 'text-warning' : 'text-muted'; ?>" 
                                                    data-id="<?php echo $note['id']; ?>"
                                                    onclick="toggleFavorite(<?php echo $note['id']; ?>)">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <?php if (!empty($note['file_path'])): ?>
                                                <a href="<?php echo $note['file_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo $note['file_path']; ?>" class="btn btn-sm btn-outline-success" download>
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=note&id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=note&id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this note"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<script>
function toggleFavorite(noteId) {
    fetch('api/toggle_favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + noteId + '&type=note'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
</div>

<?php include 'includes/footer.php'; ?>
