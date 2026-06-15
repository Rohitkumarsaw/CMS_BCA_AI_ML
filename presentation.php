<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Presentations';
$extraCSS = ['style.css', 'presentation.css'];
$extraJS = ['main.js', 'presentation.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];

$filter_subject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "WHERE user_id = ?";
$params = [$user_id];

if (!empty($filter_subject)) {
    $where .= " AND subject = ?";
    $params[] = $filter_subject;
}
if (!empty($filter_status)) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}

$pagi = paginate($pdo, 'presentations', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM presentations $where ORDER BY date DESC" . $pagi['sql']);
$stmt->execute($params);
$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($user_semester);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-desktop me-2 text-primary"></i>Presentations</h4>
                    <p class="text-muted mb-0">Manage your presentations and slides</p>
                </div>
                <a href="add_presentation.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Presentation
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter Presentations</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <select name="subject" class="form-select">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject ?>" <?= $filter_subject === $subject ? 'selected' : '' ?>><?= $subject ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="Planned" <?= $filter_status === 'Planned' ? 'selected' : '' ?>>Planned</option>
                                <option value="Done" <?= $filter_status === 'Done' ? 'selected' : '' ?>>Done</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                            <a href="presentation.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Presentations List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="section-search-container">
                        <i class="fas fa-search section-search-icon"></i>
                        <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#presentationTable tbody">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="presentationTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($presentations)): ?>
                                    <?php foreach ($presentations as $index => $presentation): ?>
                                        <?php
                                        $statusClass = 'secondary';
                                        if ($presentation['status'] === 'Done') $statusClass = 'success';
                                        elseif ($presentation['status'] === 'Planned') $statusClass = 'primary';
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($presentation['title']) ?></strong></td>
                                            <td><?= htmlspecialchars($presentation['subject']) ?></td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($presentation['date']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?php if ($presentation['status'] === 'Done'): ?>
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($presentation['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($presentation['file_path'])): ?>
                                                    <a href="<?= htmlspecialchars($presentation['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_presentation.php?id=<?= $presentation['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=presentation&id=<?= $presentation['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=presentation&id=<?= $presentation['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this presentation"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No presentations found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
