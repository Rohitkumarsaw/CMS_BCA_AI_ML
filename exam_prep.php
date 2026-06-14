<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Exam Preparation';
$extraCSS = ['style.css', 'exam_prep.css'];
$extraJS = ['main.js', 'exam_prep.js'];

$user_id = $_SESSION['user_id'];
$user_semester = $_SESSION['user_semester'];

$stmt = $pdo->prepare("SELECT * FROM exam_prep WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$user_id]);
$examPreps = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjects = getSemesterSubjects($user_semester);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-brain me-2 text-primary"></i>Exam Preparation</h4>
                    <p class="text-muted mb-0">Track your exam preparation progress</p>
                </div>
                <a href="add_exam_prep.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Exam Prep
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Exam Preparation List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Exam Name</th>
                                    <th>Subject</th>
                                    <th>Topics to Cover</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($examPreps)): ?>
                                    <?php foreach ($examPreps as $index => $prep): ?>
                                        <?php
                                        $statusClass = 'secondary';
                                        if ($prep['status'] === 'Completed') $statusClass = 'success';
                                        elseif ($prep['status'] === 'In Progress') $statusClass = 'warning';
                                        elseif ($prep['status'] === 'Planned') $statusClass = 'primary';
                                        ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($prep['exam_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($prep['subject']) ?></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($prep['topics_to_cover']) ?>">
                                                    <?= htmlspecialchars($prep['topics_to_cover']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($prep['start_date']) ?>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($prep['end_date']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?php if ($prep['status'] === 'Completed'): ?>
                                                        <i class="fas fa-check-circle me-1"></i>
                                                    <?php elseif ($prep['status'] === 'In Progress'): ?>
                                                        <i class="fas fa-spinner me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-clock me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($prep['status']) ?>
                                                </span>
                                            </td>
                                            <td style="min-width: 150px;">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar <?= $prep['progress'] >= 100 ? 'bg-success' : ($prep['progress'] >= 50 ? 'bg-info' : 'bg-warning') ?>" 
                                                         role="progressbar" style="width: <?= $prep['progress'] ?>%"
                                                         aria-valuenow="<?= $prep['progress'] ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= $prep['progress'] ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="edit_exam_prep.php?id=<?= $prep['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=exam_prep&id=<?= $prep['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=exam_prep&id=<?= $prep['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this exam prep"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No exam preparation found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
