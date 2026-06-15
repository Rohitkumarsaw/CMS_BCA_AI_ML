<?php
$pageTitle = 'Certifications';
$extraCSS = ['style.css', 'certifications.css'];
$extraJS = ['main.js', 'certifications.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$where = "WHERE user_id = ?";
$params = [$userId];
$pagi = paginate($pdo, 'certifications', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM certifications $where ORDER BY date DESC" . $pagi['sql']);
$stmt->execute($params);
$certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$yearStmt = $pdo->prepare("SELECT YEAR(date) as year, COUNT(*) as count FROM certifications WHERE user_id = ? GROUP BY YEAR(date) ORDER BY year DESC");
$yearStmt->execute([$userId]);
$yearCounts = $yearStmt->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-certificate me-2 text-primary"></i>Certifications</h4>
                    <p class="text-muted mb-0">Track your professional certifications</p>
                </div>
                <a href="add_certification.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Certification
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= count($certifications) ?></h3>
                            <small class="text-muted">Total Certifications</small>
                        </div>
                    </div>
                </div>
                <?php foreach ($yearCounts as $year => $count): ?>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <h3 class="text-info mb-1"><?= $count ?></h3>
                                <small class="text-muted"><?= $year ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="section-search-container">
                <i class="fas fa-search section-search-icon"></i>
                <input type="text" class="custom-section-search" placeholder="Search this section..." data-target="#certGrid">
            </div>
            <div class="row" id="certGrid">
                <?php if (!empty($certifications)): ?>
                    <?php foreach ($certifications as $cert): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm certification-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-award me-1 text-warning"></i>
                                            <?= htmlspecialchars($cert['cert_name']) ?>
                                        </h5>
                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="fas fa-building me-1"></i><?= htmlspecialchars($cert['issuing_org']) ?>
                                    </p>

                                    <p class="mb-2">
                                        <i class="fas fa-calendar me-1 text-muted"></i>
                                        <small><?= formatDate($cert['date']) ?></small>
                                    </p>

                                    <?php if (!empty($cert['duration'])): ?>
                                        <p class="mb-2">
                                            <i class="fas fa-clock me-1 text-muted"></i>
                                            <small><?= htmlspecialchars($cert['duration']) ?></small>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?php if (!empty($cert['certificate_path'])): ?>
                                                <a href="<?= htmlspecialchars($cert['certificate_path']) ?>" class="btn btn-sm btn-outline-success" download>
                                                    <i class="fas fa-download me-1"></i>Certificate
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($cert['link'])): ?>
                                                <a href="<?= htmlspecialchars($cert['link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt me-1"></i>Link
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="edit_certification.php?id=<?= $cert['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=certification&id=<?= $cert['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=certification&id=<?= $cert['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this certification"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No certifications found</p>
                                <a href="add_certification.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Your First Certification
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?= paginationLinks($pagi['page'], $pagi['totalPages']) ?>
    </div>

<?php include 'includes/footer.php'; ?>
