<?php
require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'College Circulars';
$extraCSS = ['style.css', 'circular.css'];
$extraJS = ['main.js', 'circular.js'];

$user_id = $_SESSION['user_id'];

$where = "WHERE user_id = ?";
$params = [$user_id];
$pagi = paginate($pdo, 'circulars', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM circulars $where ORDER BY date DESC" . $pagi['sql']);
$stmt->execute($params);
$circulars = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-scroll me-2 text-primary"></i>College Circulars</h4>
                    <p class="text-muted mb-0">View all college circulars and notifications</p>
                </div>
                <a href="add_circular.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Circular
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-table me-2 text-primary"></i>Circulars List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="section-search-container">
                        <i class="fas fa-search section-search-icon"></i>
                        <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#circularTable tbody">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="circularTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($circulars)): ?>
                                    <?php foreach ($circulars as $index => $circular): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($circular['title']) ?></strong></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 300px;" title="<?= htmlspecialchars($circular['message']) ?>">
                                                    <?= htmlspecialchars($circular['message']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1 text-muted"></i><?= formatDate($circular['date']) ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($circular['file_path'])): ?>
                                                    <a href="<?= htmlspecialchars($circular['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No file</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit_circular.php?id=<?= $circular['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="view.php?type=circular&id=<?= $circular['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                                <a href="delete.php?type=circular&id=<?= $circular['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this circular"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No circulars found
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
