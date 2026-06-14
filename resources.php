<?php
$pageTitle = 'Resources';
$extraCSS = ['style.css', 'resources.css'];
$extraJS = ['main.js', 'resources.js'];

require_once 'config/config.php';
require_once 'config/db_connection.php';
require_once 'includes/functions.php';
requireLogin();

$userId = $_SESSION['user_id'];

$filterSubject = isset($_GET['subject']) ? sanitize($_GET['subject']) : '';
$filterType = isset($_GET['type']) ? sanitize($_GET['type']) : '';

$where = "WHERE user_id = ?";
$params = [$userId];

if (!empty($filterSubject)) {
    $where .= " AND subject = ?";
    $params[] = $filterSubject;
}
if (!empty($filterType)) {
    $where .= " AND type = ?";
    $params[] = $filterType;
}

$pagi = paginate($pdo, 'resources', $where, $params, 20);
$stmt = $pdo->prepare("SELECT * FROM resources $where ORDER BY created_at DESC" . $pagi['sql']);
$stmt->execute($params);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subjectStmt = $pdo->prepare("SELECT subject, COUNT(*) as count FROM resources WHERE user_id = ? GROUP BY subject ORDER BY subject");
$subjectStmt->execute([$userId]);
$subjectCounts = $subjectStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$subjects = array_keys($subjectCounts);

$typeIcons = [
    'Video' => 'fas fa-video',
    'Article' => 'fas fa-file-alt',
    'Course' => 'fas fa-graduation-cap',
    'Book' => 'fas fa-book',
    'Other' => 'fas fa-link'
];

$typeColors = [
    'Video' => 'danger',
    'Article' => 'info',
    'Course' => 'primary',
    'Book' => 'success',
    'Other' => 'secondary'
];

include 'includes/header.php';
include 'includes/navbar.php';
?>

<?php include 'includes/sidebar.php'; ?>

    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1"><i class="fas fa-link me-2 text-primary"></i>Resources</h4>
                    <p class="text-muted mb-0">Your learning resources collection</p>
                </div>
                <a href="add_resource.php" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Resource
                </a>
            </div>

            <?= getFlashMessage() ?>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-primary mb-1"><?= count($resources) ?></h3>
                            <small class="text-muted">Total Resources</small>
                        </div>
                    </div>
                </div>
                <?php foreach ($typeIcons as $type => $icon): ?>
                    <?php
                    $typeCount = 0;
                    foreach ($resources as $r) {
                        if ($r['type'] === $type) $typeCount++;
                    }
                    ?>
                <?php endforeach; ?>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-danger mb-1"><?= count(array_filter($resources, fn($r) => $r['type'] === 'Video')) ?></h3>
                            <small class="text-muted">Videos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-info mb-1"><?= count(array_filter($resources, fn($r) => $r['type'] === 'Article')) ?></h3>
                            <small class="text-muted">Articles</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <h3 class="text-success mb-1"><?= count(array_filter($resources, fn($r) => $r['type'] === 'Course')) ?></h3>
                            <small class="text-muted">Courses</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-filter me-2 text-primary"></i>Filter</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Subject</label>
                                    <select name="subject" class="form-select">
                                        <option value="">All Subjects</option>
                                        <?php foreach ($subjects as $subj): ?>
                                            <option value="<?= htmlspecialchars($subj) ?>" <?= $filterSubject === $subj ? 'selected' : '' ?>><?= htmlspecialchars($subj) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <select name="type" class="form-select">
                                        <option value="">All Types</option>
                                        <?php foreach (array_keys($typeIcons) as $type): ?>
                                            <option value="<?= $type ?>" <?= $filterType === $type ? 'selected' : '' ?>><?= $type ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                                    <a href="resources.php" class="btn btn-outline-secondary"><i class="fas fa-redo"></i></a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent">
                            <h6 class="card-title mb-0"><i class="fas fa-book me-2 text-primary"></i>By Subject</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($subjectCounts as $subj => $count): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted"><?= htmlspecialchars($subj) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= $count ?></span>
                                </div>
                            <?php endforeach; ?>
                            <?php if (empty($subjectCounts)): ?>
                                <p class="text-muted text-center">No subjects yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php if (!empty($resources)): ?>
                    <?php foreach ($resources as $resource): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm resource-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <i class="<?= $typeIcons[$resource['type']] ?? 'fas fa-link' ?> me-1 text-<?= $typeColors[$resource['type']] ?? 'secondary' ?>"></i>
                                            <?= htmlspecialchars($resource['name']) ?>
                                        </h5>
                                        <span class="badge bg-<?= $typeColors[$resource['type']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($resource['type']) ?>
                                        </span>
                                    </div>

                                    <p class="text-muted mb-2">
                                        <i class="fas fa-book-open me-1"></i><?= htmlspecialchars($resource['subject']) ?>
                                    </p>

                                    <?php if (!empty($resource['tags'])): ?>
                                        <div class="mb-2">
                                            <?php
                                            $tags = array_slice(explode(',', $resource['tags']), 0, 3);
                                            foreach ($tags as $tag): ?>
                                                <span class="badge bg-light text-dark"><?= trim(htmlspecialchars($tag)) ?></span>
                                            <?php endforeach;
                                            $totalTags = count(explode(',', $resource['tags']));
                                            if ($totalTags > 3): ?>
                                                <span class="badge bg-light text-dark">+<?= $totalTags - 3 ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <?php if (!empty($resource['link'])): ?>
                                                <a href="<?= htmlspecialchars($resource['link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-external-link-alt me-1"></i>Open Resource
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="edit_resource.php?id=<?= $resource['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                            <a href="view.php?type=resource&id=<?= $resource['id'] ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                            <a href="delete.php?type=resource&id=<?= $resource['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" data-confirm="this resource"><i class="fas fa-trash"></i></a>
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
                                <i class="fas fa-link fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No resources found</p>
                                <a href="add_resource.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Add Your First Resource
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
